<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class LeaveIntelligenceService
{
    /**
     * Analyse a pending leave request for staffing/overlap risks before approval.
     * Returns an array of warning strings (empty = safe to approve).
     */
    public function analyzeApproval(LeaveRequest $leave): array
    {
        $employee = $leave->employee;
        if (!$employee || !$employee->department_id) {
            return [];
        }

        $deptId   = $employee->department_id;
        $deptSize = Employee::where('department_id', $deptId)
            ->where('status', 'active')
            ->count();

        if ($deptSize === 0) {
            return [];
        }

        $warnings = [];

        // ── 1. Approved overlapping leaves in the same department ────────────
        $overlapping = LeaveRequest::where('status', 'approved')
            ->where('employee_id', '!=', $employee->id)
            ->whereHas('employee', fn($q) => $q->where('department_id', $deptId)->where('status', 'active'))
            ->where('start_date', '<=', $leave->end_date)
            ->where('end_date',   '>=', $leave->start_date)
            ->with('employee.user')
            ->get();

        if ($overlapping->isNotEmpty()) {
            $names = $overlapping->map(fn($l) => $l->employee?->user?->name ?? 'Unknown')->implode(', ');
            $warnings[] = "Already on approved leave during this period: {$names}.";
        }

        // ── 2. Staffing threshold: if approving would leave dept ≥40% empty ──
        $awayCount = $overlapping->count() + 1; // this leave + already approved
        $awayPct   = $awayCount / $deptSize;

        if ($awayPct >= 0.50) {
            $deptName = $employee->department?->name ?? 'this department';
            $warnings[] = "Warning: approving will have " . round($awayPct * 100) . "% of {$deptName} on leave simultaneously ({$awayCount} of {$deptSize} staff).";
        } elseif ($awayPct >= 0.40) {
            $deptName = $employee->department?->name ?? 'this department';
            $warnings[] = round($awayPct * 100) . "% of {$deptName} will be on leave during this period ({$awayCount} of {$deptSize} staff).";
        }

        // ── 3. Other PENDING overlapping requests in the same department ──────
        $pendingOverlap = LeaveRequest::where('status', 'pending')
            ->where('id', '!=', $leave->id)
            ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
            ->where('start_date', '<=', $leave->end_date)
            ->where('end_date',   '>=', $leave->start_date)
            ->count();

        if ($pendingOverlap > 0) {
            $warnings[] = "{$pendingOverlap} other pending leave request(s) overlap this period in the same department.";
        }

        return $warnings;
    }

    /**
     * Returns the top 3 months historically with the most leave requests.
     * Used for dashboard "upcoming surge" prediction.
     */
    public function peakLeaveMonths(): array
    {
        return LeaveRequest::where('status', '!=', 'rejected')
            ->selectRaw('MONTH(start_date) AS month_num, COUNT(*) AS total')
            ->groupBy('month_num')
            ->orderByDesc('total')
            ->limit(3)
            ->get()
            ->map(fn($r) => [
                'month' => Carbon::createFromDate(null, $r->month_num, 1)->format('F'),
                'total' => $r->total,
            ])
            ->toArray();
    }
}
