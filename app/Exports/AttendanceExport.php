<?php
namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private int $month, private int $year) {}

    public function collection()
    {
        return Attendance::with('employee.user')->whereMonth('date',$this->month)->whereYear('date',$this->year)->orderBy('date')->get();
    }

    public function headings(): array
    {
        return ['Employee ID','Name','Date','Status','Remarks'];
    }

    public function map($a): array
    {
        return [
            $a->employee->employee_id,
            $a->employee->user->name,
            $a->date->format('Y-m-d'),
            ucfirst($a->status),
            $a->remarks ?? '',
        ];
    }
}
