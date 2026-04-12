<?php
namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Exports\PayrollExport;
use App\Exports\AttendanceExport;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    public function payrollSummary(Request $request): View
    {
        $year = $request->get('year', now()->year);
        $payrolls = Payroll::with('payslips')
            ->where('year', $year)
            ->orderBy('month')
            ->get()
            ->map(function($p) {
                return [
                    'label' => $p->periodLabel(),
                    'month' => $p->month,
                    'status' => $p->status,
                    'total_net' => $p->payslips->sum('net_salary'),
                    'total_gross' => $p->payslips->sum(fn($s) => $s->basic_salary + $s->allowances),
                    'employee_count' => $p->payslips->count(),
                    'payroll' => $p,
                ];
            });
        $years = Payroll::selectRaw('DISTINCT year')->orderBy('year','desc')->pluck('year');
        return view('reports.payroll', compact('payrolls','year','years'));
    }

    public function attendanceSummary(Request $request): View
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $employees = Employee::with(['user','attendances' => function($q) use ($month,$year) {
            $q->whereMonth('date',$month)->whereYear('date',$year);
        }])->where('status','active')->get()->map(function($e) {
            $present = $e->attendances->where('status','present')->count();
            $absent = $e->attendances->where('status','absent')->count();
            return ['employee' => $e, 'present' => $present, 'absent' => $absent, 'total' => $present+$absent];
        });
        return view('reports.attendance', compact('employees','month','year'));
    }

    public function exportPayroll(Request $request)
    {
        abort_if(!$request->user()->isAdmin(), 403);

        $payroll = Payroll::where('month',$request->get('month',now()->month))->where('year',$request->get('year',now()->year))->first();
        if (!$payroll) return back()->with('error','No payroll found for that period.');
        return Excel::download(new PayrollExport($payroll->id), "payroll-{$payroll->periodLabel()}.xlsx");
    }

    public function exportAttendance(Request $request)
    {
        abort_if(!$request->user()->isAdmin(), 403);

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        return Excel::download(new AttendanceExport($month,$year), "attendance-{$year}-{$month}.xlsx");
    }

    public function exportEmployees(Request $request)
    {
        abort_if(!$request->user()->isAdmin(), 403);

        return Excel::download(new EmployeesExport(), 'employees.xlsx');
    }

    public function analytics(Request $request): View
    {
        $year = (int) $request->get('year', now()->year);

        // ── Department payroll cost breakdown (paid payslips for the selected year) ──
        $deptCosts = Department::with(['employees' => function ($q) use ($year) {
            $q->with(['payslips' => function ($q2) use ($year) {
                // payslips has no 'year' column — year lives on the payrolls table
                $q2->whereHas('payroll', fn($q3) => $q3->where('year', $year));
            }]);
        }])->get()->map(function ($dept) {
            $total = $dept->employees->flatMap->payslips->sum('net_salary');
            return ['name' => $dept->name, 'total' => (float) $total, 'headcount' => $dept->employees->count()];
        })->filter(fn($d) => $d['total'] > 0)->sortByDesc('total')->values();

        // ── Headcount per department ──
        $headcount = Department::withCount(['employees' => fn($q) => $q->where('status', 'active')])->get()
            ->map(fn($d) => ['name' => $d->name, 'count' => $d->employees_count])
            ->filter(fn($d) => $d['count'] > 0)->sortByDesc('count')->values();

        // ── Leave utilisation by type this year ──
        $leaveUtil = LeaveType::withCount(['leaveRequests' => function ($q) use ($year) {
            $q->whereYear('start_date', $year)->where('status', 'approved');
        }])->withSum(['leaveRequests' => function ($q) use ($year) {
            $q->whereYear('start_date', $year)->where('status', 'approved');
        }], 'total_days')->get()
            ->map(fn($lt) => [
                'name'       => $lt->name,
                'requests'   => $lt->leave_requests_count,
                'total_days' => (int) ($lt->leave_requests_sum_total_days ?? 0),
            ])->filter(fn($l) => $l['requests'] > 0)->sortByDesc('total_days')->values();

        // ── Monthly payroll cost trend this year ──
        $monthlyPayroll = Payroll::with('payslips')
            ->where('year', $year)->orderBy('month')->get()
            ->map(fn($p) => [
                'label'     => Carbon::create($year, $p->month)->format('M'),
                'month'     => $p->month,
                'net_total' => (float) $p->payslips->sum('net_salary'),
                'count'     => $p->payslips->count(),
            ]);

        // ── YTD payroll summary ──
        $ytdGross = Payslip::whereHas('payroll', fn($q) => $q->where('year', $year))
            ->selectRaw('SUM(basic_salary + allowances) as gross, SUM(net_salary) as net, SUM(deductions) as deductions')
            ->first();

        // ── Turnover: employees whose status became inactive in this year ──
        // Using hire_date as proxy — employees hired in year vs current active headcount
        $newHires    = Employee::whereYear('hire_date', $year)->count();
        $activeCount = Employee::where('status', 'active')->count();
        $totalCount  = Employee::count();
        $inactiveCount = $totalCount - $activeCount;

        $availableYears = Payroll::selectRaw('DISTINCT year')->orderBy('year', 'desc')->pluck('year');
        if ($availableYears->isEmpty()) {
            $availableYears = collect([$year]);
        }

        return view('reports.analytics', compact(
            'year', 'availableYears',
            'deptCosts', 'headcount',
            'leaveUtil', 'monthlyPayroll',
            'ytdGross', 'newHires', 'activeCount', 'inactiveCount'
        ));
    }
}
