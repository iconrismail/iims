<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\ContractExpiryNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckContractExpiry extends Command
{
    protected $signature   = 'contracts:check-expiry';
    protected $description = 'Notify admins and employees when a contract or probation expires in 30, 7, or 1 day(s).';

    /** Days before expiry to send notifications */
    private const ALERT_DAYS = [30, 7, 1];

    public function handle(): int
    {
        $today  = Carbon::today();
        $admins = User::where('role', 'admin')->get();
        $sent   = 0;

        foreach (self::ALERT_DAYS as $days) {
            $target = $today->copy()->addDays($days)->toDateString();

            // ── Fixed-term / probation contract end ──────────────────────
            $contractExpiring = Employee::with('user')
                ->whereIn('contract_type', ['fixed-term', 'probation'])
                ->whereDate('contract_end_date', $target)
                ->where('status', 'active')
                ->get();

            foreach ($contractExpiring as $employee) {
                $this->notifyAll($admins, $employee, 'contract', $days);
                $sent++;
            }

            // ── Probation end date (separate field, any contract type) ───
            $probationExpiring = Employee::with('user')
                ->whereDate('probation_end_date', $target)
                ->where('status', 'active')
                ->get();

            foreach ($probationExpiring as $employee) {
                $this->notifyAll($admins, $employee, 'probation', $days);
                $sent++;
            }
        }

        $this->info("Contract expiry check done. {$sent} notification batch(es) sent.");
        return self::SUCCESS;
    }

    private function notifyAll(\Illuminate\Support\Collection $admins, Employee $employee, string $type, int $daysLeft): void
    {
        $notification = new ContractExpiryNotification($employee, $type, $daysLeft);

        // Notify all admins
        foreach ($admins as $admin) {
            try {
                $admin->notify($notification);
            } catch (\Exception $e) {}
        }

        // Notify the employee's manager (via department)
        if ($employee->department_id) {
            $manager = User::where('role', 'manager')
                ->whereHas('employee', fn ($q) => $q->where('department_id', $employee->department_id))
                ->first();
            if ($manager) {
                try {
                    $manager->notify($notification);
                } catch (\Exception $e) {}
            }
        }

        // Notify the employee themselves
        if ($employee->user) {
            try {
                $employee->user->notify($notification);
            } catch (\Exception $e) {}
        }
    }
}
