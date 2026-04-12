<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveBalance extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'entitled_days',
        'used_days',
        'carried_forward',
    ];

    protected function casts(): array
    {
        return [
            'year'           => 'integer',
            'entitled_days'  => 'integer',
            'used_days'      => 'integer',
            'carried_forward'=> 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Days remaining (entitled + carried_forward - used).
     * Never goes below zero.
     */
    public function remaining(): int
    {
        return max(0, $this->entitled_days + $this->carried_forward - $this->used_days);
    }
}
