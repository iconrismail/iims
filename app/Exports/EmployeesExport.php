<?php
namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Employee::with('user','department')->where('status','active')->get();
    }

    public function headings(): array
    {
        return ['Employee ID','Name','Email','Department','Position','Phone','Hire Date','Basic Salary','Status'];
    }

    public function map($e): array
    {
        return [
            $e->employee_id,
            $e->user->name,
            $e->user->email,
            $e->department->name,
            $e->position,
            $e->phone,
            $e->hire_date->format('Y-m-d'),
            $e->basic_salary,
            $e->status,
        ];
    }
}
