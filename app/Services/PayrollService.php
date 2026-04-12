<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\Attendance;
use App\Models\OvertimeRecord;
use App\Models\Bonus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollService
{
    public function __construct(
        private PayrollAnomalyService $anomalyService
    ) {}
    /**
     * Calculate net salary for an employee for a given month/year.
     * Takes attendance into account (pro-rata based on working days).
     */
    public function calculateSalary(Employee $employee, int $month, int $year): array
    {
        $totalDaysInMonth = Carbon::create($year, $month)->daysInMonth;

        // Count working days (weekdays) in the month
        $workingDays = $this->getWorkingDaysInMonth($month, $year);

        // Count days present
        $daysPresent = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'present')
            ->count();

        $daysAbsent = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'absent')
            ->count();

        // If no attendance records exist, assume full month worked
        if ($daysPresent === 0 && $daysAbsent === 0) {
            $daysPresent = $workingDays;
            $daysAbsent = 0;
        }

        $basicSalary = (float) $employee->basic_salary;
        $allowances = (float) $employee->allowances;
        $deductions = (float) $employee->deductions;

        // Pro-rate salary based on attendance
        $attendanceRatio = $workingDays > 0 ? $daysPresent / $workingDays : 1;
        $proratedBasic = round($basicSalary * $attendanceRatio, 2);
        $proratedAllowances = round($allowances * $attendanceRatio, 2);

        $grossForTax = $proratedBasic + $proratedAllowances;
        $taxAmount = \App\Models\TaxBracket::calculateTax($grossForTax);

        $activeRules = \App\Models\DeductionRule::where('is_active', true)->get();
        $ruleDeductions = 0;
        foreach ($activeRules as $rule) {
            if ($rule->type === 'percentage') {
                $ruleDeductions += round($grossForTax * ((float)$rule->value / 100), 2);
            } else {
                $ruleDeductions += (float)$rule->value;
            }
        }

        $totalDeductions = $deductions + $taxAmount + $ruleDeductions;

        // Sum approved overtime pay for the employee for this month/year
        $overtimePay = (float) OvertimeRecord::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'approved')
            ->sum('amount');

        // Sum approved bonuses for the employee for this month/year
        $bonusAmount = (float) Bonus::where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', 'approved')
            ->sum('amount');

        $netSalary = round($proratedBasic + $proratedAllowances - $totalDeductions + $overtimePay + $bonusAmount, 2);
        $netSalary = max($netSalary, 0);

        return [
            'basic_salary' => $proratedBasic,
            'allowances' => $proratedAllowances,
            'deductions' => $totalDeductions,
            'overtime_pay' => round($overtimePay, 2),
            'bonus' => round($bonusAmount, 2),
            'net_salary' => $netSalary,
            'days_worked' => $daysPresent,
            'days_absent' => $daysAbsent,
        ];
    }

    /**
     * Process payroll for all active employees for a given month/year.
     * Creates a Payroll record and generates Payslips for each employee.
     */
    public function processPayroll(int $month, int $year): Payroll
    {
        return DB::transaction(function () use ($month, $year) {
            // Create or retrieve the payroll record
            $payroll = Payroll::firstOrCreate(
                ['month' => $month, 'year' => $year],
                ['status' => 'draft']
            );

            // Delete old payslips if re-processing
            $payroll->payslips()->delete();

            // Get all active employees
            $employees = Employee::where('status', 'active')->get();

            foreach ($employees as $employee) {
                $salaryData = $this->calculateSalary($employee, $month, $year);

                $payslip = Payslip::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $salaryData['basic_salary'],
                    'allowances' => $salaryData['allowances'],
                    'deductions' => $salaryData['deductions'],
                    'overtime_pay' => $salaryData['overtime_pay'],
                    'bonus' => $salaryData['bonus'],
                    'net_salary' => $salaryData['net_salary'],
                    'days_worked' => $salaryData['days_worked'],
                    'days_absent' => $salaryData['days_absent'],
                ]);

                try {
                    $this->anomalyService->detect($payslip);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Anomaly detection failed for payslip ' . $payslip->id . ': ' . $e->getMessage());
                }

                try {
                    \Illuminate\Support\Facades\Mail::to($employee->user->email)->send(new \App\Mail\PayslipReady($payslip->load('payroll', 'employee.user')));
                    $employee->user->notify(new \App\Notifications\PayslipReadyNotification($payslip));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Payslip notification failed: ' . $e->getMessage());
                }
            }

            $payroll->update(['status' => 'processed']);

            return $payroll->load('payslips.employee');
        });
    }

    /**
     * Count weekdays (Mon-Fri) in a given month.
     */
    private function getWorkingDaysInMonth(int $month, int $year): int
    {
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();
        $workingDays = 0;

        while ($start->lte($end)) {
            if ($start->isWeekday()) {
                $workingDays++;
            }
            $start->addDay();
        }

        return $workingDays;
    }
}
