<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\LeaveRequest;
use App\Models\OvertimeRecord;
use App\Models\EmployeeDocument;
use App\Models\Bonus;
use App\Models\PerformanceReview;
use App\Models\EmployeeLeaveBalance;
use App\Services\AttendanceInsightService;
use App\Services\PayrollForecastService;
use App\Services\LeaveBalanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function __construct(
        private AttendanceInsightService $attendanceInsight,
        private PayrollForecastService   $payrollForecast,
        private LeaveBalanceService      $leaveBalance
    ) {}
    public function index(Request $request): View
    {
        $user = $request->user();
        $now = Carbon::now();

        if ($user->isAdmin()) {
            return $this->adminDashboard($now);
        }

        if ($user->isManager()) {
            return $this->managerDashboard($user, $now);
        }

        if ($user->isHR()) {
            return $this->hrDashboard($now);
        }

        return $this->employeeDashboard($user, $now);
    }

    private function adminDashboard(Carbon $now): View
    {
        $totalEmployees = Employee::where('status', 'active')->count();
        $totalDepartments = Department::count();

        // Attendance summary for today
        $todayPresent = Attendance::whereDate('date', $now->toDateString())
            ->where('status', 'present')->count();
        $todayAbsent = Attendance::whereDate('date', $now->toDateString())
            ->where('status', 'absent')->count();

        // Current month payroll status
        $currentPayroll = Payroll::where('month', $now->month)
            ->where('year', $now->year)
            ->first();

        // Recent employees
        $recentEmployees = Employee::with('department', 'user')
            ->latest()
            ->take(5)
            ->get();

        // Payroll chart data built below (after pending approvals)

        // Department headcount
        $deptData = Department::withCount(['employees' => fn($q) => $q->where('status','active')])->get();

        // Pending approvals
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
        $pendingOvertimes = OvertimeRecord::where('status', 'pending')->count();

        // On leave today (approved leaves covering today's date)
        $onLeaveToday = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', $now->toDateString())
            ->whereDate('end_date', '>=', $now->toDateString())
            ->count();

        // Total net payroll this month
        $totalPayrollThisMonth = Payslip::whereHas('payroll', fn($q) =>
            $q->where('month', $now->month)->where('year', $now->year)
        )->sum('net_salary');

        // Pending leave requests — last 5, for dashboard mini-table
        $pendingLeaveRequests = LeaveRequest::with(['employee.user', 'leaveType'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // Last 12 months payroll totals (JS slices to 3/6/12)
        $payrollChartData = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $total = Payslip::whereHas('payroll', fn($q) => $q->where('month',$date->month)->where('year',$date->year))->sum('net_salary');
            $payrollChartData->push(['label' => $date->format('M Y'), 'value' => (float)$total]);
        }

        // Last 12 months attendance
        $attendanceChartData = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $present = Attendance::whereMonth('date',$date->month)->whereYear('date',$date->year)->where('status','present')->count();
            $absent = Attendance::whereMonth('date',$date->month)->whereYear('date',$date->year)->where('status','absent')->count();
            $attendanceChartData->push(['label'=>$date->format('M Y'),'present'=>$present,'absent'=>$absent]);
        }

        // Upcoming events (next 7 days): birthdays, anniversaries, expiring docs
        $upcomingEvents = $this->getUpcomingEvents($now);

        // Recent activity feed (last 5 audit entries)
        $recentActivity = Activity::with('causer')->latest()->take(5)->get();

        $attendanceInsights = $this->attendanceInsight->analyze();
        $payrollForecast    = $this->payrollForecast->forecast();

        // Contracts / probation expiring within 30 days
        $expiringContracts = Employee::with('user', 'department')
            ->where('status', 'active')
            ->where(function ($q) use ($now) {
                $in30 = $now->copy()->addDays(30)->toDateString();
                $today = $now->toDateString();
                $q->where(function ($q2) use ($today, $in30) {
                    $q2->whereIn('contract_type', ['fixed-term', 'probation'])
                       ->whereNotNull('contract_end_date')
                       ->whereDate('contract_end_date', '>=', $today)
                       ->whereDate('contract_end_date', '<=', $in30);
                })->orWhere(function ($q2) use ($today, $in30) {
                    $q2->whereNotNull('probation_end_date')
                       ->whereDate('probation_end_date', '>=', $today)
                       ->whereDate('probation_end_date', '<=', $in30);
                });
            })
            ->get();

        return view('dashboard.admin', compact(
            'totalEmployees',
            'totalDepartments',
            'todayPresent',
            'todayAbsent',
            'pendingLeaves',
            'pendingOvertimes',
            'onLeaveToday',
            'totalPayrollThisMonth',
            'pendingLeaveRequests',
            'currentPayroll',
            'recentEmployees',
            'payrollChartData',
            'deptData',
            'attendanceChartData',
            'upcomingEvents',
            'recentActivity',
            'attendanceInsights',
            'expiringContracts',
            'payrollForecast'
        ));
    }

    private function getUpcomingEvents(Carbon $now): \Illuminate\Support\Collection
    {
        $today = $now->copy()->startOfDay();
        $in7   = $today->copy()->addDays(7);

        $activeEmployees = Employee::with('user')->where('status', 'active')->get();

        $birthdays = $activeEmployees->filter(function ($emp) use ($today, $in7) {
            if (!$emp->date_of_birth) return false;
            $bd = $emp->date_of_birth->copy()->year($today->year);
            if ($bd->lt($today)) $bd->addYear();
            return $bd->between($today, $in7);
        })->map(function ($emp) use ($today) {
            $bd = $emp->date_of_birth->copy()->year($today->year);
            if ($bd->lt($today)) $bd->addYear();
            return ['name' => $emp->user->name, 'date' => $bd, 'type' => 'birthday', 'detail' => ''];
        });

        $anniversaries = $activeEmployees->filter(function ($emp) use ($today, $in7) {
            if (!$emp->hire_date) return false;
            $ann = $emp->hire_date->copy()->year($today->year);
            if ($ann->lt($today)) $ann->addYear();
            return $ann->between($today, $in7);
        })->map(function ($emp) use ($today) {
            $ann = $emp->hire_date->copy()->year($today->year);
            if ($ann->lt($today)) $ann->addYear();
            $years = $emp->hire_date->diffInYears($ann);
            return ['name' => $emp->user->name, 'date' => $ann, 'type' => 'anniversary', 'detail' => $years . ' yr' . ($years != 1 ? 's' : '')];
        });

        $expiringDocs = EmployeeDocument::with('employee.user')
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '>=', $today->toDateString())
            ->whereDate('expires_at', '<=', $in7->toDateString())
            ->get()
            ->map(fn ($doc) => [
                'name'   => $doc->employee->user->name ?? '—',
                'date'   => $doc->expires_at,
                'type'   => 'document',
                'detail' => $doc->title,
            ]);

        return collect()
            ->merge($birthdays)
            ->merge($anniversaries)
            ->merge($expiringDocs)
            ->sortBy('date')
            ->values();
    }

    private function hrDashboard(Carbon $now): View
    {
        // ── Headcount ────────────────────────────────────────
        $totalEmployees  = Employee::where('status', 'active')->count();
        $newHiresMonth   = Employee::whereMonth('hire_date', $now->month)
                                   ->whereYear('hire_date', $now->year)->count();
        $inactiveCount   = Employee::where('status', 'inactive')->count();

        // ── Attendance today ──────────────────────────────────
        $todayPresent = Attendance::whereDate('date', $now->toDateString())
                                  ->where('status', 'present')->count();
        $todayAbsent  = Attendance::whereDate('date', $now->toDateString())
                                  ->where('status', 'absent')->count();

        // ── On leave today ────────────────────────────────────
        $onLeaveToday = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', $now->toDateString())
            ->whereDate('end_date', '>=', $now->toDateString())
            ->count();

        // ── Pending approvals ─────────────────────────────────
        $pendingLeaves         = LeaveRequest::where('status', 'pending')->count();
        $pendingProfileUpdates = \App\Models\ProfileUpdateRequest::where('status', 'pending')->count();

        // ── Pending leave requests (table) ────────────────────
        $pendingLeaveRequests = LeaveRequest::with(['employee.user', 'leaveType'])
            ->where('status', 'pending')
            ->latest()
            ->take(8)
            ->get();

        // ── Department headcount ──────────────────────────────
        $deptData = Department::withCount(['employees' => fn($q) => $q->where('status', 'active')])->get();

        // ── Contracts / probation expiring within 30 days ─────
        $expiringContracts = Employee::with('user', 'department')
            ->where('status', 'active')
            ->where(function ($q) use ($now) {
                $in30  = $now->copy()->addDays(30)->toDateString();
                $today = $now->toDateString();
                $q->where(function ($q2) use ($today, $in30) {
                    $q2->whereIn('contract_type', ['fixed-term', 'probation'])
                       ->whereNotNull('contract_end_date')
                       ->whereDate('contract_end_date', '>=', $today)
                       ->whereDate('contract_end_date', '<=', $in30);
                })->orWhere(function ($q2) use ($today, $in30) {
                    $q2->whereNotNull('probation_end_date')
                       ->whereDate('probation_end_date', '>=', $today)
                       ->whereDate('probation_end_date', '<=', $in30);
                });
            })
            ->get();

        // ── Recent hires ──────────────────────────────────────
        $recentHires = Employee::with('user', 'department')
            ->latest('hire_date')
            ->take(5)
            ->get();

        // ── Upcoming events (next 7 days) ─────────────────────
        $upcomingEvents = $this->getUpcomingEvents($now);

        // ── Leave requests this month by type ─────────────────
        $monthLeaveByType = LeaveRequest::with('leaveType')
            ->whereMonth('start_date', $now->month)
            ->whereYear('start_date', $now->year)
            ->where('status', 'approved')
            ->get()
            ->groupBy(fn($l) => $l->leaveType?->name ?? 'Other')
            ->map(fn($g) => $g->count())
            ->sortDesc();

        // ── ADD-ON 1: Attendance Rate Trend (6 months) ────────
        $attendanceRateTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date    = $now->copy()->subMonths($i);
            $present = Attendance::whereMonth('date', $date->month)->whereYear('date', $date->year)->where('status', 'present')->count();
            $absent  = Attendance::whereMonth('date', $date->month)->whereYear('date', $date->year)->where('status', 'absent')->count();
            $total   = $present + $absent;
            $attendanceRateTrend->push([
                'label' => $date->format('M Y'),
                'rate'  => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            ]);
        }

        // ── ADD-ON 2: Gender & Contract Type Breakdown ────────
        $genderData = Employee::where('status', 'active')
            ->get()
            ->groupBy(fn($e) => $e->gender ?? 'Not Set')
            ->map(fn($g) => $g->count());

        $contractTypeData = Employee::where('status', 'active')
            ->get()
            ->groupBy(fn($e) => $e->contract_type ?? 'Not Set')
            ->map(fn($g) => $g->count());

        // ── ADD-ON 3: Org-wide Attendance Snapshot (today) ────
        $notRecordedToday = max(0, $totalEmployees - $todayPresent - $todayAbsent - $onLeaveToday);

        // ── ADD-ON 4: Leave Balance Warnings (≥80% used) ─────
        $leaveWarnings = EmployeeLeaveBalance::with(['employee.user', 'leaveType'])
            ->where('year', $now->year)
            ->whereRaw('entitled_days + carried_forward > 0')
            ->whereRaw('used_days >= (entitled_days + carried_forward) * 0.8')
            ->orderByRaw('used_days / (entitled_days + carried_forward) DESC')
            ->take(10)
            ->get();

        // ── ADD-ON 5: Pending Overtime Requests ───────────────
        $pendingOvertimeRequests = OvertimeRecord::with('employee.user')
            ->where('status', 'pending')
            ->latest()
            ->take(8)
            ->get();

        // ── ADD-ON 6: Headcount Growth Trend (6 months) ───────
        $headcountTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i)->endOfMonth();
            $count = Employee::whereDate('hire_date', '<=', $date->toDateString())->count();
            $headcountTrend->push(['label' => $date->format('M Y'), 'count' => $count]);
        }

        // ── ADD-ON 7: Performance Review Status Summary ───────
        // Overall counts by status for current year
        $reviewStatusCounts = PerformanceReview::where('period_year', $now->year)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Per-department breakdown — 3 queries, no N+1
        $empsByDept = Employee::where('status', 'active')
            ->select('id', 'department_id')
            ->get()
            ->groupBy('department_id');

        $reviewedEmpStatus = PerformanceReview::where('period_year', $now->year)
            ->select('id', 'employee_id', 'status')
            ->get()
            ->groupBy('employee_id')
            ->map(fn($r) => $r->sortByDesc('id')->first()->status);

        $deptReviewSummary = Department::withCount([
                'employees as active_count' => fn($q) => $q->where('status', 'active'),
            ])
            ->get()
            ->filter(fn($d) => $d->active_count > 0)
            ->map(function ($dept) use ($empsByDept, $reviewedEmpStatus) {
                $empIds = $empsByDept->get($dept->id, collect())->pluck('id');
                $submitted   = $empIds->filter(fn($id) => in_array($reviewedEmpStatus->get($id), ['submitted', 'acknowledged']))->count();
                $draft       = $empIds->filter(fn($id) => $reviewedEmpStatus->get($id) === 'draft')->count();
                $noReview    = $empIds->filter(fn($id) => !$reviewedEmpStatus->has($id))->count();
                return [
                    'name'      => $dept->name,
                    'total'     => $empIds->count(),
                    'submitted' => $submitted,
                    'draft'     => $draft,
                    'no_review' => $noReview,
                ];
            })
            ->sortByDesc('no_review')
            ->values();

        // ── ADD-ON 8: Employees With No Attendance This Month ──
        $attendedThisMonth = Attendance::whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->distinct()
            ->pluck('employee_id');

        $noAttendanceThisMonth = Employee::with('user', 'department')
            ->where('status', 'active')
            ->whereNotIn('id', $attendedThisMonth)
            ->orderBy('hire_date')
            ->get();

        return view('dashboard.hr', compact(
            'totalEmployees', 'newHiresMonth', 'inactiveCount',
            'todayPresent', 'todayAbsent', 'onLeaveToday',
            'pendingLeaves', 'pendingProfileUpdates',
            'pendingLeaveRequests', 'deptData',
            'expiringContracts', 'recentHires',
            'upcomingEvents', 'monthLeaveByType', 'now',
            'attendanceRateTrend', 'genderData', 'contractTypeData',
            'notRecordedToday', 'leaveWarnings',
            'pendingOvertimeRequests', 'headcountTrend',
            'reviewStatusCounts', 'deptReviewSummary',
            'noAttendanceThisMonth'
        ));
    }

    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        $now = Carbon::now();

        $totalEmployees       = Employee::where('status', 'active')->count();
        $totalDepartments     = Department::count();
        $todayPresent         = Attendance::whereDate('date', $now->toDateString())->where('status', 'present')->count();
        $todayAbsent          = Attendance::whereDate('date', $now->toDateString())->where('status', 'absent')->count();
        $onLeaveToday         = LeaveRequest::where('status', 'approved')->whereDate('start_date', '<=', $now->toDateString())->whereDate('end_date', '>=', $now->toDateString())->count();
        $pendingLeaves        = LeaveRequest::where('status', 'pending')->count();
        $pendingOvertimes     = OvertimeRecord::where('status', 'pending')->count();
        $totalPayrollThisMonth = Payslip::whereHas('payroll', fn($q) => $q->where('month', $now->month)->where('year', $now->year))->sum('net_salary');
        $currentPayroll       = Payroll::where('month', $now->month)->where('year', $now->year)->first();
        $deptData             = Department::withCount(['employees' => fn($q) => $q->where('status', 'active')])->get();
        $pendingLeaveRequests = LeaveRequest::with(['employee.user', 'leaveType'])->where('status', 'pending')->latest()->take(5)->get();
        $upcomingEvents       = $this->getUpcomingEvents($now);
        $recentActivity       = Activity::with('causer')->latest()->take(5)->get();

        $pdf = Pdf::loadView('dashboard.pdf', compact(
            'now', 'totalEmployees', 'totalDepartments', 'todayPresent', 'todayAbsent',
            'onLeaveToday', 'pendingLeaves', 'pendingOvertimes', 'totalPayrollThisMonth',
            'currentPayroll', 'deptData', 'pendingLeaveRequests', 'upcomingEvents', 'recentActivity'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('dashboard-report-' . $now->format('Y-m-d') . '.pdf');
    }

    private function managerDashboard($user, Carbon $now): View
    {
        $employee = $user->employee;

        if (!$employee) {
            return view('dashboard.manager', [
                'employee' => null, 'department' => null,
                'teamSize' => 0, 'teamPresent' => 0, 'teamAbsent' => 0,
                'pendingLeaves' => 0, 'pendingOvertimes' => 0,
                'pendingLeaveRequests' => collect(), 'pendingOvertimeRequests' => collect(),
                'teamMembers' => collect(), 'teamAttendanceTrend' => collect(),
                'monthPresent' => 0, 'monthAbsent' => 0, 'now' => $now,
                'deptPayrollCost' => 0,
                'teamPerformance' => null,
                'onLeaveToday' => collect(),
                'todayRoster' => collect(),
                'monthLeaves' => collect(),
            ]);
        }

        $deptId = $employee->department_id;
        $department = $employee->department;

        // Team stats
        $teamSize = Employee::where('department_id', $deptId)->where('status', 'active')->count();

        $teamPresent = Attendance::whereDate('date', $now->toDateString())
            ->where('status', 'present')
            ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
            ->count();

        $teamAbsent = Attendance::whereDate('date', $now->toDateString())
            ->where('status', 'absent')
            ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
            ->count();

        // Pending approvals scoped to dept
        $pendingLeaves = LeaveRequest::where('status', 'pending')
            ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
            ->count();

        $pendingOvertimes = OvertimeRecord::where('status', 'pending')
            ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
            ->count();

        $pendingLeaveRequests = LeaveRequest::with(['employee.user', 'leaveType'])
            ->where('status', 'pending')
            ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
            ->latest()->take(5)->get();

        $pendingOvertimeRequests = OvertimeRecord::with('employee.user')
            ->where('status', 'pending')
            ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
            ->latest()->take(5)->get();

        // Team members
        $teamMembers = Employee::with('user')
            ->where('department_id', $deptId)
            ->where('status', 'active')
            ->get();

        // Team 6-month attendance trend
        $teamAttendanceTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $p = Attendance::whereHas('employee', fn($q) => $q->where('department_id', $deptId))
                ->whereMonth('date', $date->month)->whereYear('date', $date->year)
                ->where('status', 'present')->count();
            $a = Attendance::whereHas('employee', fn($q) => $q->where('department_id', $deptId))
                ->whereMonth('date', $date->month)->whereYear('date', $date->year)
                ->where('status', 'absent')->count();
            $teamAttendanceTrend->push(['label' => $date->format('M'), 'present' => $p, 'absent' => $a]);
        }

        // Manager's own this-month attendance
        $monthPresent = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $now->month)->whereYear('date', $now->year)
            ->where('status', 'present')->count();

        $monthAbsent = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $now->month)->whereYear('date', $now->year)
            ->where('status', 'absent')->count();

        // Department total payroll cost (most recent payroll period)
        $deptEmployeeIds = $teamMembers->pluck('id');
        $deptPayrollCost = Payslip::whereIn('employee_id', $deptEmployeeIds)
            ->whereHas('payroll', fn($q) => $q->where('month', $now->month)->where('year', $now->year))
            ->sum('net_salary');
        // Fall back to previous month if current month not yet processed
        if ($deptPayrollCost == 0) {
            $prev = $now->copy()->subMonth();
            $deptPayrollCost = Payslip::whereIn('employee_id', $deptEmployeeIds)
                ->whereHas('payroll', fn($q) => $q->where('month', $prev->month)->where('year', $prev->year))
                ->sum('net_salary');
        }

        // Team performance summary (average overall_score for this year)
        $teamPerformance = PerformanceReview::whereIn('employee_id', $deptEmployeeIds)
            ->where('period_year', $now->year)
            ->whereNotNull('overall_score')
            ->selectRaw('AVG(overall_score) as avg_score, COUNT(*) as review_count')
            ->first();

        // Who's on approved leave today
        $onLeaveToday = LeaveRequest::where('status', 'approved')
            ->whereIn('employee_id', $deptEmployeeIds)
            ->whereDate('start_date', '<=', $now->toDateString())
            ->whereDate('end_date', '>=', $now->toDateString())
            ->with('employee.user')
            ->get();

        // Today's roster — per-member status for the dept overview
        $todayAttendanceKeyed = Attendance::whereDate('date', $now->toDateString())
            ->whereIn('employee_id', $deptEmployeeIds)
            ->get()
            ->keyBy('employee_id');
        $onLeaveTodayIds = $onLeaveToday->pluck('employee_id')->toArray();
        $todayRoster = $teamMembers->map(function ($member) use ($todayAttendanceKeyed, $onLeaveTodayIds) {
            if (in_array($member->id, $onLeaveTodayIds)) {
                $statusKey = 'on-leave';
            } elseif ($todayAttendanceKeyed->has($member->id)) {
                $statusKey = $todayAttendanceKeyed[$member->id]->status; // 'present' or 'absent'
            } else {
                $statusKey = 'not-recorded';
            }
            return ['member' => $member, 'status' => $statusKey];
        });

        // Month leave calendar — approved leaves overlapping this calendar month
        $monthStart  = $now->copy()->startOfMonth();
        $monthEnd    = $now->copy()->endOfMonth();
        $monthLeaves = LeaveRequest::where('status', 'approved')
            ->whereIn('employee_id', $deptEmployeeIds)
            ->where('start_date', '<=', $monthEnd->toDateString())
            ->where('end_date', '>=', $monthStart->toDateString())
            ->with('employee.user')
            ->get();

        return view('dashboard.manager', compact(
            'employee', 'department', 'teamSize', 'teamPresent', 'teamAbsent',
            'pendingLeaves', 'pendingOvertimes', 'pendingLeaveRequests',
            'pendingOvertimeRequests', 'teamMembers', 'teamAttendanceTrend',
            'monthPresent', 'monthAbsent', 'now',
            'deptPayrollCost', 'teamPerformance', 'onLeaveToday',
            'todayRoster', 'monthLeaves'
        ));
    }

    private function employeeDashboard($user, Carbon $now): View
    {
        $employee = $user->employee;

        if (!$employee) {
            return view('dashboard.employee', [
                'employee'         => null,
                'attendanceSummary'=> [],
                'recentPayslips'   => collect(),
                'currentPayroll'   => null,
                'recentLeaves'     => collect(),
                'leaveSummary'     => [],
                'latestReview'     => null,
                'attendanceTrend'  => collect(),
                'salaryTrend'      => collect(),
                'expiringDocs'     => collect(),
                'ytdSalary'        => 0,
                'bonusTotalThisYear'=> 0,
                'bonusCount'       => 0,
            ]);
        }

        // ── This month's attendance ────────────────────────────
        $monthPresent = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $now->month)->whereYear('date', $now->year)
            ->where('status', 'present')->count();

        $monthAbsent = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $now->month)->whereYear('date', $now->year)
            ->where('status', 'absent')->count();

        $attendanceSummary = ['present' => $monthPresent, 'absent' => $monthAbsent];

        // ── Recent payslips ────────────────────────────────────
        $recentPayslips = Payslip::where('employee_id', $employee->id)
            ->with('payroll')->latest()->take(3)->get();

        // ── Current month payroll ──────────────────────────────
        $currentPayroll = Payroll::where('month', $now->month)->where('year', $now->year)->first();

        // ── Leave summary ──────────────────────────────────────
        $leavesTakenDays = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')->whereYear('start_date', $now->year)->sum('total_days');

        $leavesPending = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'pending')->count();

        $leaveSummary = [
            'taken_days'  => (int) $leavesTakenDays,
            'pending'     => $leavesPending,
        ];

        $recentLeaves = LeaveRequest::with('leaveType')
            ->where('employee_id', $employee->id)
            ->latest()->take(4)->get();

        // ── Latest submitted/acknowledged performance review ───
        $latestReview = PerformanceReview::where('employee_id', $employee->id)
            ->whereIn('status', ['submitted', 'acknowledged'])
            ->latest()->first();

        // ── 6-month attendance trend (this employee) ──────────
        $attendanceTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $p = Attendance::where('employee_id', $employee->id)
                ->whereMonth('date', $date->month)->whereYear('date', $date->year)
                ->where('status', 'present')->count();
            $a = Attendance::where('employee_id', $employee->id)
                ->whereMonth('date', $date->month)->whereYear('date', $date->year)
                ->where('status', 'absent')->count();
            $attendanceTrend->push(['label' => $date->format('M'), 'present' => $p, 'absent' => $a]);
        }

        // ── 6-month salary trend ───────────────────────────────
        $salaryTrend = Payslip::where('employee_id', $employee->id)
            ->with('payroll')->latest()->take(6)->get()
            ->reverse()->values()
            ->map(fn ($s) => ['label' => $s->payroll->periodLabel(), 'net' => (float) $s->net_salary]);

        // ── Documents expiring in next 30 days ─────────────────
        $expiringDocs = EmployeeDocument::where('employee_id', $employee->id)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '>=', $now->toDateString())
            ->whereDate('expires_at', '<=', $now->copy()->addDays(30)->toDateString())
            ->orderBy('expires_at')->get();

        // ── YTD net salary ─────────────────────────────────────
        $ytdSalary = Payslip::where('employee_id', $employee->id)
            ->whereHas('payroll', fn ($q) => $q->where('year', $now->year))
            ->sum('net_salary');

        // ── Approved bonuses this year ─────────────────────────
        $bonuses = Bonus::where('employee_id', $employee->id)
            ->where('status', 'approved')->where('year', $now->year)->get();
        $bonusTotalThisYear = (float) $bonuses->sum('amount');
        $bonusCount         = $bonuses->count();

        // ── Leave balances for current year ────────────────────
        $leaveBalances = $this->leaveBalance->forEmployee($employee, $now->year);

        return view('dashboard.employee', compact(
            'employee',
            'attendanceSummary',
            'recentPayslips',
            'currentPayroll',
            'leaveSummary',
            'recentLeaves',
            'latestReview',
            'attendanceTrend',
            'salaryTrend',
            'expiringDocs',
            'ytdSalary',
            'bonusTotalThisYear',
            'bonusCount',
            'leaveBalances'
        ));
    }
}
