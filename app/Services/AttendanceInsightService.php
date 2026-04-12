<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PerformanceReview;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AttendanceInsightService
{
    /**
     * Returns up to 10 actionable attendance insights for the admin dashboard.
     * Results are cached for 30 minutes.
     * Each insight: ['type' => string, 'severity' => 'warning'|'info', 'message' => string]
     */
    public function analyze(): array
    {
        return Cache::remember('attendance_insights', 1800, function () {
            $insights = array_merge(
                $this->chronicMondayFridayAbsences(),
                $this->postReviewAttendanceDrop(),
                $this->departmentMonthOverMonthDecline(),
                $this->adjacentHighAbsenceDays()
            );

            return array_slice($insights, 0, 10);
        });
    }

    // ── Pattern 1: Mon/Fri chronic absence ───────────────────────────────────
    // Uses a single aggregate query; DAYOFWEEK in MySQL: 2=Mon, 6=Fri, 3-5=Tue-Thu
    private function chronicMondayFridayAbsences(): array
    {
        $from = Carbon::now()->subMonths(3)->toDateString();

        $stats = Attendance::where('date', '>=', $from)
            ->where('status', 'absent')
            ->whereHas('employee', fn($q) => $q->where('status', 'active'))
            ->selectRaw('employee_id,
                SUM(DAYOFWEEK(date) IN (2,6)) AS mon_fri,
                SUM(DAYOFWEEK(date) IN (3,4,5)) AS midweek')
            ->groupBy('employee_id')
            ->havingRaw('SUM(DAYOFWEEK(date) IN (2,6)) >= 3')
            ->get();

        if ($stats->isEmpty()) {
            return [];
        }

        $employees = Employee::with('user')
            ->whereIn('id', $stats->pluck('employee_id'))
            ->get()->keyBy('id');

        $insights = [];
        foreach ($stats as $stat) {
            $monFri  = (int) $stat->mon_fri;
            $midweek = (int) $stat->midweek;

            // ~52 Mon/Fri days and ~78 Tue-Thu days over 3 months (weekdays only)
            $monFriRate  = $monFri / 52;
            $midweekRate = $midweek > 0 ? ($midweek / 78) : 0;

            if ($monFriRate >= 0.10 && ($midweekRate === 0 || $monFriRate >= $midweekRate * 2.0)) {
                $name = $employees[$stat->employee_id]?->user?->name ?? 'Unknown';
                $insights[] = [
                    'type'     => 'mon_fri_pattern',
                    'severity' => 'warning',
                    'message'  => "{$name} has a Mon/Fri absence pattern — {$monFri} Mon/Fri vs {$midweek} mid-week absences in 3 months",
                ];
            }
        }

        return $insights;
    }

    // ── Pattern 2: Attendance drop after a performance review ─────────────────
    private function postReviewAttendanceDrop(): array
    {
        $recentReviews = PerformanceReview::with('employee.user')
            ->whereNotNull('overall_score')
            ->where('created_at', '>=', Carbon::now()->subMonths(3))
            ->latest()
            ->limit(30)
            ->get();

        $insights = [];
        foreach ($recentReviews as $review) {
            $emp = $review->employee;
            if (!$emp) {
                continue;
            }

            $reviewDate = $review->created_at->toDateString();
            $before30   = $review->created_at->copy()->subDays(30)->toDateString();
            $after30    = $review->created_at->copy()->addDays(30)->toDateString();

            $before = Attendance::where('employee_id', $emp->id)
                ->whereBetween('date', [$before30, $reviewDate])
                ->where('status', 'present')
                ->count();

            $after = Attendance::where('employee_id', $emp->id)
                ->whereBetween('date', [$reviewDate, $after30])
                ->where('status', 'present')
                ->count();

            if ($before < 5) {
                continue;
            }

            $drop = ($before - $after) / $before;
            if ($drop >= 0.30) {
                $name = $emp->user?->name ?? 'Unknown';
                $insights[] = [
                    'type'     => 'post_review_drop',
                    'severity' => 'warning',
                    'message'  => "{$name}'s attendance dropped " . round($drop * 100) . "% in the 30 days after their {$review->review_period} {$review->period_year} review",
                ];
            }
        }

        return $insights;
    }

    // ── Pattern 3: Department attendance declining month-over-month ───────────
    private function departmentMonthOverMonthDecline(): array
    {
        $now      = Carbon::now();
        $prevDate = $now->copy()->subMonth();

        // Two aggregate queries joined with employees
        $aggregate = function (int $month, int $year) {
            return DB::table('attendances')
                ->join('employees', 'attendances.employee_id', '=', 'employees.id')
                ->where('employees.status', 'active')
                ->whereMonth('attendances.date', $month)
                ->whereYear('attendances.date', $year)
                ->selectRaw('employees.department_id,
                    SUM(attendances.status = "present") AS present,
                    SUM(attendances.status = "absent")  AS absent')
                ->groupBy('employees.department_id')
                ->get()->keyBy('department_id');
        };

        $thisMonth = $aggregate($now->month, $now->year);
        $lastMonth = $aggregate($prevDate->month, $prevDate->year);

        if ($thisMonth->isEmpty() || $lastMonth->isEmpty()) {
            return [];
        }

        $departments = Department::whereIn('id', $thisMonth->keys()->merge($lastMonth->keys())->unique())
            ->pluck('name', 'id');

        $insights = [];
        foreach ($thisMonth as $deptId => $curr) {
            $prev = $lastMonth[$deptId] ?? null;
            if (!$prev) {
                continue;
            }

            $currTotal = ($curr->present + $curr->absent);
            $prevTotal = ($prev->present + $prev->absent);

            if ($currTotal < 5 || $prevTotal < 5) {
                continue;
            }

            $currRate = $curr->present / $currTotal;
            $prevRate = $prev->present / $prevTotal;

            if ($prevRate > 0 && ($prevRate - $currRate) >= 0.10) {
                $deptName = $departments[$deptId] ?? "Dept #{$deptId}";
                $insights[] = [
                    'type'     => 'dept_decline',
                    'severity' => 'info',
                    'message'  => "{$deptName}: attendance rate down " . round(($prevRate - $currRate) * 100) . "% month-over-month (" . round($currRate * 100) . "% this month vs " . round($prevRate * 100) . "% last month)",
                ];
            }
        }

        return $insights;
    }

    // ── Pattern 4: Absences adjacent to company-wide high-absence days ────────
    private function adjacentHighAbsenceDays(): array
    {
        $totalActive = Employee::where('status', 'active')->count();
        if ($totalActive < 3) {
            return [];
        }

        $from = Carbon::now()->subMonths(3)->toDateString();

        // Find days where ≥35% of all active staff were absent (likely informal off-days)
        $threshold = max(2, (int) ceil($totalActive * 0.35));

        $highAbsenceDays = DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->where('employees.status', 'active')
            ->where('attendances.date', '>=', $from)
            ->where('attendances.status', 'absent')
            ->selectRaw('attendances.date, COUNT(DISTINCT attendances.employee_id) AS cnt')
            ->groupBy('attendances.date')
            ->having('cnt', '>=', $threshold)
            ->pluck('date')
            ->toArray();

        if (empty($highAbsenceDays)) {
            return [];
        }

        // Collect adjacent weekday dates
        $adjacentDates = [];
        foreach ($highAbsenceDays as $day) {
            $d = Carbon::parse($day);
            $before = $d->copy()->subDay();
            $after  = $d->copy()->addDay();
            if ($before->isWeekday()) {
                $adjacentDates[] = $before->toDateString();
            }
            if ($after->isWeekday()) {
                $adjacentDates[] = $after->toDateString();
            }
        }

        $adjacentDates = array_values(array_unique(array_diff($adjacentDates, $highAbsenceDays)));
        if (empty($adjacentDates)) {
            return [];
        }

        // Employees absent on those adjacent days
        $absentNames = DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->where('employees.status', 'active')
            ->where('attendances.status', 'absent')
            ->whereIn('attendances.date', $adjacentDates)
            ->distinct()
            ->pluck('users.name')
            ->toArray();

        if (empty($absentNames)) {
            return [];
        }

        $count    = count($absentNames);
        $nameStr  = implode(', ', array_slice($absentNames, 0, 3));
        if ($count > 3) {
            $nameStr .= ' and ' . ($count - 3) . ' more';
        }

        return [[
            'type'     => 'adjacent_holiday',
            'severity' => 'info',
            'message'  => "{$count} employee(s) were absent immediately before/after company-wide high-absence days: {$nameStr}",
        ]];
    }
}
