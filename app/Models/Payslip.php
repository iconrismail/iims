<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Payslip extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'basic_salary',
        'allowances',
        'deductions',
        'overtime_pay',
        'bonus',
        'net_salary',
        'days_worked',
        'days_absent',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'allowances' => 'decimal:2',
            'deductions' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'bonus' => 'decimal:2',
            'net_salary' => 'decimal:2',
        ];
    }

    // --- Relationships ---

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function anomalies(): HasMany
    {
        return $this->hasMany(PayrollAnomaly::class);
    }
}
