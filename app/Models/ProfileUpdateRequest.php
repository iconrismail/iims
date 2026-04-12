<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileUpdateRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'requested_fields',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_fields' => 'array',
            'reviewed_at'      => 'datetime',
        ];
    }

    // Human-readable labels for the fields employees can update
    public const FIELD_LABELS = [
        'phone'             => 'Phone Number',
        'address'           => 'Address',
        'emergency_contact' => 'Emergency Contact',
        'bank_name'         => 'Bank Name',
        'bank_account'      => 'Bank Account Number',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
