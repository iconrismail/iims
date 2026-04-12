<?php
namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Services\LeaveIntelligenceService;
use App\Services\LeaveBalanceService;
use App\Services\HolidayService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = LeaveRequest::with('employee.user', 'leaveType', 'approvedBy')->latest();

        if ($user->isAdmin()) {
            // sees all
        } elseif ($user->isManager()) {
            $deptId = $user->employee?->department_id;
            if (!$deptId) {
                return view('leaves.index', ['leaves' => collect(), 'leaveTypes' => collect(), 'employees' => collect(), 'statusFilter' => null]);
            }
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        } else {
            $employee = $user->employee;
            if (!$employee) {
                return view('leaves.index', ['leaves' => collect(), 'leaveTypes' => collect(), 'employees' => collect(), 'statusFilter' => null]);
            }
            $query->where('employee_id', $employee->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('employee_id') && $user->isAdminOrManager()) {
            $query->where('employee_id', $request->employee_id);
        }

        $leaves = $query->paginate(15);
        $leaveTypes = LeaveType::all();
        $employees = $user->isAdminOrManager()
            ? Employee::with('user')->where('status', 'active')
                ->when($user->isManager(), fn($q) => $q->where('department_id', $user->employee?->department_id))
                ->get()
            : collect();

        return view('leaves.index', compact('leaves', 'leaveTypes', 'employees'));
    }

    public function create(Request $request, LeaveBalanceService $leaveBalance): View
    {
        $leaveTypes = LeaveType::all();
        $balances   = collect();
        $employee   = $request->user()->employee;
        if ($employee) {
            $balances = $leaveBalance->forEmployee($employee, now()->year);
        }
        return view('leaves.create', compact('leaveTypes', 'balances'));
    }

    public function store(Request $request, LeaveBalanceService $leaveBalance, HolidayService $holidayService): RedirectResponse
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $employee = $request->user()->employee;
        if (!$employee) return back()->with('error', 'No employee profile found.');

        $start     = Carbon::parse($request->start_date);
        $end       = Carbon::parse($request->end_date);
        $totalDays = max($holidayService->countWorkingDays($start, $end), 1);

        // Check leave balance for paid leave types
        if (!$leaveBalance->hasBalance($employee, (int) $request->leave_type_id, $totalDays, $start->year)) {
            $balance = $leaveBalance->getOrCreate($employee->id, (int) $request->leave_type_id, $start->year);
            return back()->withInput()->with('error',
                "Insufficient leave balance. You have {$balance->remaining()} day(s) remaining but requested {$totalDays} day(s)."
            );
        }

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // Notify all admins AND managers of the same department
        try {
            $leave->load('employee.user', 'leaveType');
            $notifyUsers = \App\Models\User::where('role', 'admin')
                ->orWhere(fn($q) => $q->where('role', 'manager')
                    ->whereHas('employee', fn($eq) => $eq->where('department_id', $employee->department_id)))
                ->get();
            foreach ($notifyUsers as $u) {
                $u->notify(new \App\Notifications\LeaveRequestSubmittedNotification($leave));
            }
        } catch (\Exception $e) {}

        return redirect()->route('leaves.index')->with('success', 'Leave request submitted successfully.');
    }

    public function show(Request $request, LeaveRequest $leave, LeaveIntelligenceService $leaveIntel): View
    {
        $user = $request->user();

        // Employees may only view their own leave requests
        if (!$user->isAdminOrManager()) {
            abort_if(!$user->employee || $leave->employee_id !== $user->employee->id, 403);
        }

        // Managers may only view leaves from their own department
        if ($user->isManager()) {
            abort_if($leave->employee->department_id !== $user->employee?->department_id, 403);
        }

        $leave->load('employee.user', 'employee.department', 'leaveType', 'approvedBy');

        // Compute staffing warnings only for pending leaves viewed by admin/manager
        $leaveWarnings = ($user->isAdminOrManager() && $leave->status === 'pending')
            ? $leaveIntel->analyzeApproval($leave)
            : [];

        return view('leaves.show', compact('leave', 'leaveWarnings'));
    }

    public function approve(Request $request, LeaveRequest $leave, LeaveBalanceService $leaveBalance): RedirectResponse
    {
        // Manager can only approve leaves from their own department
        if ($request->user()->isManager()) {
            $deptId = $request->user()->employee?->department_id;
            abort_if($leave->employee->department_id !== $deptId, 403);
        }

        abort_if($leave->status === 'approved', 422, 'Already approved.');

        $leave->update(['status' => 'approved', 'approved_by' => $request->user()->id]);
        $leaveBalance->deduct($leave);

        try {
            $fresh = $leave->fresh()->load('employee.user', 'leaveType');
            \Illuminate\Support\Facades\Mail::to($fresh->employee->user->email)->send(new \App\Mail\LeaveStatusUpdated($fresh));
            $fresh->employee->user->notify(new \App\Notifications\LeaveStatusNotification($fresh));
        } catch (\Exception $e) {}
        return back()->with('success', 'Leave request approved.');
    }

    public function reject(Request $request, LeaveRequest $leave, LeaveBalanceService $leaveBalance): RedirectResponse
    {
        // Manager can only reject leaves from their own department
        if ($request->user()->isManager()) {
            $deptId = $request->user()->employee?->department_id;
            abort_if($leave->employee->department_id !== $deptId, 403);
        }

        $request->validate(['admin_note' => 'nullable|string|max:500']);

        // If revoking a previously-approved leave, refund the balance
        if ($leave->status === 'approved') {
            $leaveBalance->refund($leave);
        }

        $leave->update(['status' => 'rejected', 'admin_note' => $request->admin_note, 'approved_by' => $request->user()->id]);
        try {
            $fresh = $leave->fresh()->load('employee.user', 'leaveType');
            \Illuminate\Support\Facades\Mail::to($fresh->employee->user->email)->send(new \App\Mail\LeaveStatusUpdated($fresh));
            $fresh->employee->user->notify(new \App\Notifications\LeaveStatusNotification($fresh));
        } catch (\Exception $e) {}
        return back()->with('success', 'Leave request rejected.');
    }
}
