<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\PerformanceReview;
use Carbon\Carbon;

class ChurnRiskService
{
    /**
     * Compute a 0–100 churn risk score for the given employee.
     * Returns an array with 'score', 'level' (low/medium/high), and 'factors'.
     */
    public function score(Employee $employee): array
    {
        $factors = [];
        $total   = 0;

        $total += $this->attendanceDeclineScore($employee, $factors);
        $total += $this->performanceTrendScore($employee, $factors);
        $total += $this->salaryStagnationScore($employee, $factors);
        $total += $this->leaveFrequencyScore($employee, $factors);
        $total += $this->tenureScore($employee, $factors);

        $score = min(100, $total);

        return [
            'score'   => $score,
            'level'   => $this->level($score),
            'factors' => $factors,
        ];
    }

    // ── Factors ──────────────────────────────────────────────────────────────

    /**
     * Attendance decline — up to 30 pts.
     * Compares average days present in the last 3 months vs the 3 months before that.
     */
    private function attendanceDeclineScore(Employee $employee, array &$factors): int
    {
        $now    = Carbon::now();
        $recent = $this->avgAttendance($employee, $now->copy()->subMonths(3), $now);
        $prior  = $this->avgAttendance($employee, $now->copy()->subMonths(6), $now->copy()->subMonths(3));

        if ($prior <= 0) {
            return 0;
        }

        $decline = ($prior - $recent) / $prior;

        if ($decline >= 0.30) {
            $factors[] = ['label' => 'Attendance drop (≥30%)', 'pts' => 30];
            return 30;
        }
        if ($decline >= 0.15) {
            $factors[] = ['label' => 'Attendance declining (15–29%)', 'pts' => 15];
            return 15;
        }

        return 0;
    }

    /**
     * Performance trend — up to 25 pts.
     * Flags if the most recent review score is lower than the previous one.
     */
    private function performanceTrendScore(Employee $employee, array &$factors): int
    {
        $reviews = PerformanceReview::where('employee_id', $employee->id)
            ->whereNotNull('overall_score')
            ->orderByDesc('period_year')
            ->orderByDesc('review_period')
            ->limit(2)
            ->get();

        if ($reviews->count() < 2) {
            return 0;
        }

        $latest = (float) $reviews->first()->overall_score;
        $prev   = (float) $reviews->last()->overall_score;

        if ($prev <= 0) {
            return 0;
        }

        $drop = ($prev - $latest) / $prev;

        if ($drop >= 0.20) {
            $factors[] = ['label' => 'Performance score dropped ≥20%', 'pts' => 25];
            return 25;
        }
        if ($drop >= 0.10) {
            $factors[] = ['label' => 'Performance score declining (10–19%)', 'pts' => 12];
            return 12;
        }

        return 0;
    }

    /**
     * Salary stagnation despite high performance — up to 20 pts.
     * High performer (overall_score ≥ 80) with no salary increment in last 2 reviews.
     */
    private function salaryStagnationScore(Employee $employee, array &$factors): int
    {
        $reviews = PerformanceReview::where('employee_id', $employee->id)
            ->whereNotNull('overall_score')
            ->orderByDesc('period_year')
            ->orderByDesc('review_period')
            ->limit(2)
            ->get();

        if ($reviews->isEmpty()) {
            return 0;
        }

        $latestScore = (float) $reviews->first()->overall_score;
        $noIncrement = $reviews->every(fn($r) => (float)($r->salary_increment_pct ?? 0) === 0.0);

        if ($latestScore >= 80 && $noIncrement) {
            $factors[] = ['label' => 'High performer with no salary increment', 'pts' => 20];
            return 20;
        }

        return 0;
    }

    /**
     * Leave frequency increase — up to 15 pts.
     * Compares leave requests in the last 6 months vs the 6 months before that.
     */
    private function leaveFrequencyScore(Employee $employee, array &$factors): int
    {
        $now    = Carbon::now();
        $recent = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', '!=', 'rejected')
            ->where('start_date', '>=', $now->copy()->subMonths(6))
            ->count();

        $prior = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', '!=', 'rejected')
            ->whereBetween('start_date', [$now->copy()->subMonths(12), $now->copy()->subMonths(6)])
            ->count();

        if ($prior <= 0 || $recent <= $prior) {
            return 0;
        }

        $increase = ($recent - $prior) / $prior;

        if ($increase >= 0.50) {
            $factors[] = ['label' => 'Leave requests up ≥50% vs prior period', 'pts' => 15];
            return 15;
        }
        if ($increase >= 0.25) {
            $factors[] = ['label' => 'Leave requests up 25–49% vs prior period', 'pts' => 7];
            return 7;
        }

        return 0;
    }

    /**
     * Tenure signals — up to 10 pts.
     * Short tenure (< 12 months) alongside any other risk factor amplifies risk.
     */
    private function tenureScore(Employee $employee, array &$factors): int
    {
        if (!$employee->hire_date) {
            return 0;
        }

        $months = (int) $employee->hire_date->diffInMonths(Carbon::now());

        if ($months < 6) {
            $factors[] = ['label' => 'Very short tenure (< 6 months)', 'pts' => 10];
            return 10;
        }
        if ($months < 12) {
            $factors[] = ['label' => 'Short tenure (6–11 months)', 'pts' => 5];
            return 5;
        }

        return 0;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function avgAttendance(Employee $employee, Carbon $from, Carbon $to): float
    {
        $count = Attendance::where('employee_id', $employee->id)
            ->where('status', 'present')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->count();

        $months = max(1, $from->diffInMonths($to));
        return $count / $months;
    }

    private function level(int $score): string
    {
        if ($score >= 60) return 'high';
        if ($score >= 30) return 'medium';
        return 'low';
    }
}
