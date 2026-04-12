<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payslip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PayrollForecastService
{
    /**
     * Projects next month's total payroll cost using last 3 months of actual data.
     * Cached for 1 hour.
     *
     * Returns:
     *   total      float  – projected net payroll (NLE)
     *   base       float  – sum of active employees' (basic_salary + allowances - deductions)
     *   overtime   float  – 3-month average overtime component
     *   bonus      float  – 3-month average bonus component
     *   employees  int    – active headcount
     *   confidence string – 'high' | 'medium' | 'low'
     *   history    array  – last 3 months totals for sparkline display
     */
    public function forecast(): array
    {
        return Cache::remember('payroll_forecast', 3600, function () {
            $now = Carbon::now();

            // Collect last 3 months of payroll actuals
            $history        = [];
            $totalOvertime  = 0.0;
            $totalBonus     = 0.0;
            $monthsWithData = 0;

            for ($i = 1; $i <= 3; $i++) {
                $date = $now->copy()->subMonths($i);

                $row = Payslip::whereHas('payroll', fn($q) =>
                    $q->where('month', $date->month)->where('year', $date->year)
                )->selectRaw('
                    SUM(net_salary)   AS net_total,
                    SUM(overtime_pay) AS ot_total,
                    SUM(bonus)        AS bonus_total
                ')->first();

                $net   = (float) ($row->net_total   ?? 0);
                $ot    = (float) ($row->ot_total    ?? 0);
                $bonus = (float) ($row->bonus_total ?? 0);

                $history[] = [
                    'label' => $date->format('M Y'),
                    'total' => $net,
                ];

                if ($net > 0) {
                    $totalOvertime += $ot;
                    $totalBonus    += $bonus;
                    $monthsWithData++;
                }
            }

            $history = array_reverse($history);

            // Base: sum of active employees' contracted net (before OT/bonus)
            $base = (float) Employee::where('status', 'active')
                ->selectRaw('SUM(basic_salary + allowances - deductions) AS net_base')
                ->value('net_base');

            $divisor     = max(1, $monthsWithData);
            $avgOvertime = $totalOvertime / $divisor;
            $avgBonus    = $totalBonus    / $divisor;
            $total       = max(0, $base + $avgOvertime + $avgBonus);

            $confidence = match(true) {
                $monthsWithData >= 3 => 'high',
                $monthsWithData >= 1 => 'medium',
                default              => 'low',
            };

            return [
                'total'      => round($total, 2),
                'base'       => round($base, 2),
                'overtime'   => round($avgOvertime, 2),
                'bonus'      => round($avgBonus, 2),
                'employees'  => Employee::where('status', 'active')->count(),
                'confidence' => $confidence,
                'history'    => $history,
            ];
        });
    }
}
