<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\OvertimeRecord;
use App\Models\Bonus;
use App\Models\EmployeeDocument;
use App\Models\PerformanceReview;
use App\Models\ProfileUpdateRequest;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Employee extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    protected $fillable = [
        'user_id',
        'department_id',
        'employee_id',
        'position',
        'phone',
        'address',
        'emergency_contact',
        'bank_name',
        'bank_account',
        'date_of_birth',
        'hire_date',
        'basic_salary',
        'allowances',
        'deductions',
        'status',
        'contract_type',
        'contract_end_date',
        'probation_end_date',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth'     => 'date',
            'hire_date'         => 'date',
            'contract_end_date' => 'date',
            'probation_end_date'=> 'date',
            'basic_salary'      => 'decimal:2',
            'allowances'        => 'decimal:2',
            'deductions'        => 'decimal:2',
        ];
    }

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function overtimeRecords(): HasMany
    {
        return $this->hasMany(OvertimeRecord::class);
    }

    public function bonuses(): HasMany
    {
        return $this->hasMany(Bonus::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function profileUpdateRequests(): HasMany
    {
        return $this->hasMany(ProfileUpdateRequest::class);
    }

    // --- Helpers ---

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isContractExpiringSoon(int $days = 30): bool
    {
        return $this->contract_end_date !== null
            && in_array($this->contract_type, ['fixed-term', 'probation'])
            && $this->contract_end_date->between(now(), now()->addDays($days));
    }

    public function isProbationExpiringSoon(int $days = 30): bool
    {
        return $this->probation_end_date !== null
            && $this->probation_end_date->between(now(), now()->addDays($days));
    }

    public function contractDaysRemaining(): ?int
    {
        if (!$this->contract_end_date) return null;
        $diff = (int) now()->startOfDay()->diffInDays($this->contract_end_date, false);
        return $diff >= 0 ? $diff : null;
    }

    public function probationDaysRemaining(): ?int
    {
        if (!$this->probation_end_date) return null;
        $diff = (int) now()->startOfDay()->diffInDays($this->probation_end_date, false);
        return $diff >= 0 ? $diff : null;
    }

    public function grossSalary(): float
    {
        return (float) $this->basic_salary + (float) $this->allowances;
    }

    public function netSalary(): float
    {
        return $this->grossSalary() - (float) $this->deductions;
    }
}
