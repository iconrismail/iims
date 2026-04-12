<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'title',
        'type',
        'file_path',
        'original_name',
        'file_size',
        'mime_type',
        'expires_at',
        'uploaded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'file_size' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return $this->expires_at
            && $this->expires_at->isFuture()
            && $this->expires_at->diffInDays(now()) <= 30;
    }

    public function formattedSize(): string
    {
        $size = $this->file_size;
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1048576) {
            return round($size / 1024, 1) . ' KB';
        } else {
            return round($size / 1048576, 1) . ' MB';
        }
    }
}
