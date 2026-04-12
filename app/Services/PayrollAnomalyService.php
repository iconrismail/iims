<?php

namespace App\Services;

use App\Models\Payslip;
use App\Models\PayrollAnomaly;

class PayrollAnomalyService
{
    // Minimum historical payslips needed before flagging
    private const MIN_HISTORY = 2;

    /**
     * Analyse a freshly created payslip against the employee's history.
     * Persists any anomalies found and returns them as a collection.
     */
    public function detect(Payslip $payslip): \Illuminate\Support\Collection
    {
        // Fetch the last 3 payslips BEFORE this one
        $history = Payslip::where('employee_id', $payslip->employee_id)
            ->where('id', '<', $payslip->id)
            ->latest('id')
            ->limit(3)
            ->get();

        if ($history->count() < self::MIN_HISTORY) {
            return collect(); // not enough data to compare
        }

        $anomalies = collect();

        $anomalies = $anomalies->merge($this->checkSalaryDrop($payslip, $history));
        $anomalies = $anomalies->merge($this->checkOvertimeSpike($payslip, $history));
        $anomalies = $anomalies->merge($this->checkDeductionSpike($payslip, $history));
        $anomalies = $anomalies->merge($this->checkAttendanceDrop($payslip, $history));
        $anomalies = $anomalies->merge($this->checkLargeBonus($payslip, $history));

        return $anomalies;
    }

    // ── Checks ──────────────────────────────────────────────────────────────

    private function checkSalaryDrop(Payslip $payslip, $history): array
    {
        $avgNet = $history->avg('net_salary');
        if ($avgNet <= 0) return [];

        $drop = ($avgNet - $payslip->net_salary) / $avgNet;

        if ($drop >= 0.25) {
            return [$this->store($payslip, 'salary_drop',
                $drop >= 0.40 ? 'high' : 'medium',
                "Net salary dropped " . round($drop * 100) . "% below 3-month average",
                $payslip->net_salary,
                round($avgNet, 2)
            )];
        }

        return [];
    }

    private function checkOvertimeSpike(Payslip $payslip, $history): array
    {
        $avgOt = $history->avg('overtime_pay');
        if ($avgOt < 1) return []; // no overtime history — skip

        $ratio = $payslip->overtime_pay / $avgOt;

        if ($ratio >= 3) {
            return [$this->store($payslip, 'overtime_spike',
                $ratio >= 5 ? 'high' : 'medium',
                "Overtime pay is " . round($ratio, 1) . "× the 3-month average",
                $payslip->overtime_pay,
                round($avgOt, 2)
            )];
        }

        return [];
    }

    private function checkDeductionSpike(Payslip $payslip, $history): array
    {
        $avgDed = $history->avg('deductions');
        if ($avgDed <= 0) return [];

        $ratio = $payslip->deductions / $avgDed;

        if ($ratio >= 1.5) {
            return [$this->store($payslip, 'deduction_spike',
                $ratio >= 2 ? 'high' : 'medium',
                "Deductions are " . round($ratio, 1) . "× the 3-month average",
                $payslip->deductions,
                round($avgDed, 2)
            )];
        }

        return [];
    }

    private function checkAttendanceDrop(Payslip $payslip, $history): array
    {
        $avgDays = $history->avg('days_worked');
        if ($avgDays <= 0) return [];

        $ratio = $payslip->days_worked / $avgDays;

        if ($ratio <= 0.5) {
            return [$this->store($payslip, 'attendance_drop',
                $ratio <= 0.25 ? 'high' : 'medium',
                "Days worked (" . $payslip->days_worked . ") is " . round((1 - $ratio) * 100) . "% below average",
                $payslip->days_worked,
                round($avgDays, 1)
            )];
        }

        return [];
    }

    private function checkLargeBonus(Payslip $payslip, $history): array
    {
        if ($payslip->bonus <= 0) return [];

        // Flag if bonus > 50% of basic salary
        if ($payslip->basic_salary > 0 && $payslip->bonus > $payslip->basic_salary * 0.5) {
            return [$this->store($payslip, 'bonus_large',
                'low',
                "Bonus (NLE " . number_format($payslip->bonus, 2) . ") exceeds 50% of basic salary — verify approval",
                $payslip->bonus,
                round($payslip->basic_salary * 0.5, 2)
            )];
        }

        return [];
    }

    // ── Persistence ─────────────────────────────────────────────────────────

    private function store(
        Payslip $payslip,
        string  $type,
        string  $severity,
        string  $description,
        float   $current,
        float   $expected
    ): PayrollAnomaly {
        return PayrollAnomaly::create([
            'payroll_id'     => $payslip->payroll_id,
            'payslip_id'     => $payslip->id,
            'employee_id'    => $payslip->employee_id,
            'type'           => $type,
            'severity'       => $severity,
            'description'    => $description,
            'current_value'  => $current,
            'expected_value' => $expected,
        ]);
    }
}
