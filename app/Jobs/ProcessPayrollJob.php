<?php

namespace App\Jobs;

use App\Services\PayrollService;
use App\Models\Payroll;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;

    public function __construct(
        public readonly int $month,
        public readonly int $year
    ) {}

    public function handle(PayrollService $service): void
    {
        Log::info("Processing payroll for {$this->month}/{$this->year}");
        $service->processPayroll($this->month, $this->year);
        Log::info("Payroll processed for {$this->month}/{$this->year}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Payroll job failed for {$this->month}/{$this->year}: " . $exception->getMessage());
    }
}
