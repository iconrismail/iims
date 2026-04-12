<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LeaveBalanceService
{
    /**
     * Get (or lazily create) a balance record.
     */
    public function getOrCreate(int $employeeId, int $leaveTypeId, int $year): EmployeeLeaveBalance
    {
        return EmployeeLeaveBalance::firstOrCreate(
            ['employee_id' => $employeeId, 'leave_type_id' => $leaveTypeId, 'year' => $year],
            ['entitled_days' => LeaveType::find($leaveTypeId)?->days_per_year ?? 0, 'used_days' => 0, 'carried_forward' => 0]
        );
    }

    /**
     * Return all balances for an employee in a given year,
     * creating any missing records so every leave type is represented.
     * Keyed by leave_type_id for easy lookup in views.
     */
    public function forEmployee(Employee $employee, int $year): Collection
    {
        $types = LeaveType::all();
        foreach ($types as $type) {
            $this->getOrCreate($employee->id, $type->id, $year);
        }

        return EmployeeLeaveBalance::where('employee_id', $employee->id)
            ->where('year', $year)
            ->with('leaveType')
            ->get()
            ->keyBy('leave_type_id');
    }

    /**
     * Check if employee has enough balance for the requested days.
     * Unpaid leave types always pass (no cap).
     */
    public function hasBalance(Employee $employee, int $leaveTypeId, int $days, int $year): bool
    {
        $type = LeaveType::find($leaveTypeId);
        if (!$type || !$type->is_paid) {
            return true; // unpaid leave: no entitlement cap
        }
        $balance = $this->getOrCreate($employee->id, $leaveTypeId, $year);
        return $balance->remaining() >= $days;
    }

    /**
     * Deduct days from balance when a leave request is approved.
     */
    public function deduct(LeaveRequest $leave): void
    {
        $type = LeaveType::find($leave->leave_type_id);
        if (!$type || !$type->is_paid) {
            return; // unpaid leave: nothing to deduct
        }
        $year    = Carbon::parse($leave->start_date)->year;
        $balance = $this->getOrCreate($leave->employee_id, $leave->leave_type_id, $year);
        $balance->increment('used_days', $leave->total_days);
    }

    /**
     * Refund days when an already-approved leave is revoked / cancelled.
     */
    public function refund(LeaveRequest $leave): void
    {
        $type = LeaveType::find($leave->leave_type_id);
        if (!$type || !$type->is_paid) {
            return;
        }
        $year    = Carbon::parse($leave->start_date)->year;
        $balance = $this->getOrCreate($leave->employee_id, $leave->leave_type_id, $year);
        // Never go below zero
        $reduce = min($leave->total_days, $balance->used_days);
        $balance->decrement('used_days', $reduce);
    }

    /**
     * Seed balances for a newly hired employee for the current year.
     */
    public function initForEmployee(Employee $employee, int $year): void
    {
        foreach (LeaveType::all() as $type) {
            $this->getOrCreate($employee->id, $type->id, $year);
        }
    }

    /**
     * Year-end carry-forward: copy remaining days from $fromYear into $toYear.
     * Carry-forward is capped at the leave type's annual entitlement.
     */
    public function carryForward(int $fromYear): int
    {
        $toYear   = $fromYear + 1;
        $balances = EmployeeLeaveBalance::with('leaveType')
            ->where('year', $fromYear)
            ->get();

        $processed = 0;
        foreach ($balances as $balance) {
            $cap   = $balance->leaveType?->days_per_year ?? 0;
            $carry = min($balance->remaining(), $cap);
            if ($carry <= 0) {
                continue;
            }

            $next = $this->getOrCreate($balance->employee_id, $balance->leave_type_id, $toYear);
            // Add carry-forward (may already have some from a partial run)
            $next->carried_forward = min($cap, $next->carried_forward + $carry);
            $next->save();
            $processed++;
        }

        return $processed;
    }
}
