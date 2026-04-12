<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\OvertimeRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = OvertimeRecord::with(['employee.user', 'approver'])->orderBy('date', 'desc');

        // Managers see only their department's overtime
        if ($user->isManager()) {
            $deptId = $user->employee?->department_id;
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $overtimes = $query->paginate(20)->withQueryString();
        $employees = Employee::with('user')->where('status', 'active')
            ->when($user->isManager(), fn($q) => $q->where('department_id', $user->employee?->department_id))
            ->get();

        return view('overtime.index', compact('overtimes', 'employees'));
    }

    public function create()
    {
        $employees = Employee::with('user')->where('status', 'active')->get();
        return view('overtime.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.5|max:24',
            'rate_multiplier' => 'required|numeric|min:1.0|max:3.0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $date = Carbon::parse($validated['date']);
        $workingDaysInMonth = $this->getWorkingDaysInMonth($date->month, $date->year);

        // amount = (basic_salary / working_days / 8) * hours * rate_multiplier
        $hourlyRate = $workingDaysInMonth > 0
            ? (float) $employee->basic_salary / $workingDaysInMonth / 8
            : 0;
        $amount = round($hourlyRate * $validated['hours'] * $validated['rate_multiplier'], 2);

        OvertimeRecord::create([
            'employee_id' => $validated['employee_id'],
            'date' => $validated['date'],
            'hours' => $validated['hours'],
            'rate_multiplier' => $validated['rate_multiplier'],
            'amount' => $amount,
            'status' => 'pending',
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('overtime.index')
            ->with('success', 'Overtime record created successfully.');
    }

    public function approve(Request $request, OvertimeRecord $overtime)
    {
        if ($request->user()->isManager()) {
            $deptId = $request->user()->employee?->department_id;
            abort_if($overtime->employee->department_id !== $deptId, 403);
        }

        $overtime->update(['status' => 'approved', 'approved_by' => auth()->id()]);
        return back()->with('success', 'Overtime approved.');
    }

    public function reject(Request $request, OvertimeRecord $overtime)
    {
        if ($request->user()->isManager()) {
            $deptId = $request->user()->employee?->department_id;
            abort_if($overtime->employee->department_id !== $deptId, 403);
        }

        $overtime->update(['status' => 'rejected', 'approved_by' => auth()->id()]);
        return back()->with('success', 'Overtime rejected.');
    }

    public function destroy(OvertimeRecord $overtime)
    {
        $overtime->delete();
        return redirect()->route('overtime.index')->with('success', 'Overtime record deleted.');
    }

    private function getWorkingDaysInMonth(int $month, int $year): int
    {
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();
        $workingDays = 0;
        while ($start->lte($end)) {
            if ($start->isWeekday()) {
                $workingDays++;
            }
            $start->addDay();
        }
        return $workingDays;
    }
}
