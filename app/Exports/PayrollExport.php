<?php
namespace App\Exports;

use App\Models\Payslip;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PayrollExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private int $payrollId) {}

    public function collection()
    {
        return Payslip::with('employee.user','employee.department')->where('payroll_id', $this->payrollId)->get();
    }

    public function headings(): array
    {
        return ['Employee ID','Name','Department','Position','Basic Salary','Allowances','Deductions','Net Salary','Days Worked','Days Absent'];
    }

    public function map($payslip): array
    {
        return [
            $payslip->employee->employee_id,
            $payslip->employee->user->name,
            $payslip->employee->department->name,
            $payslip->employee->position,
            $payslip->basic_salary,
            $payslip->allowances,
            $payslip->deductions,
            $payslip->net_salary,
            $payslip->days_worked,
            $payslip->days_absent,
        ];
    }
}
