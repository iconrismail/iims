<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAnomaly extends Model
{
    protected $fillable = [
        'payroll_id',
        'payslip_id',
        'employee_id',
        'type',
        'severity',
        'description',
        'current_value',
        'expected_value',
        'is_resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'current_value'  => 'decimal:2',
            'expected_value' => 'decimal:2',
            'is_resolved'    => 'boolean',
            'resolved_at'    => 'datetime',
        ];
    }

    public function payroll(): BelongsTo  { return $this->belongsTo(Payroll::class); }
    public function payslip(): BelongsTo  { return $this->belongsTo(Payslip::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function resolver(): BelongsTo { return $this->belongsTo(User::class, 'resolved_by'); }

    public function severityColor(): string
    {
        return match($this->severity) {
            'high'   => '#ef4444',
            'medium' => '#f59e0b',
            default  => '#3b82f6',
        };
    }

    public function typeLabel(): string
    {
        return match($this->type) {
            'salary_drop'      => 'Salary Drop',
            'overtime_spike'   => 'Overtime Spike',
            'deduction_spike'  => 'Deduction Spike',
            'attendance_drop'  => 'Attendance Drop',
            'bonus_large'      => 'Large Bonus',
            default            => ucwords(str_replace('_', ' ', $this->type)),
        };
    }
}
