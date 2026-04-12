<?php

namespace App\Console\Commands;

use App\Services\LeaveBalanceService;
use Illuminate\Console\Command;

class LeaveCarryForward extends Command
{
    protected $signature   = 'leave:carry-forward {year? : The year to carry forward FROM (defaults to previous year)}';
    protected $description = 'Carry unused leave balances forward into the next year.';

    public function handle(LeaveBalanceService $leaveBalance): int
    {
        $fromYear = (int) ($this->argument('year') ?? (now()->year - 1));
        $toYear   = $fromYear + 1;

        $this->info("Carrying forward unused leave from {$fromYear} → {$toYear}...");

        $processed = $leaveBalance->carryForward($fromYear);

        $this->info("Done. {$processed} balance record(s) updated with carry-forward.");

        return self::SUCCESS;
    }
}
