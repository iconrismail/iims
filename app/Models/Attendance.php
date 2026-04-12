<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'time_in',
        'time_out',
        'shift',
        'late_minutes',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date'         => 'date',
            'late_minutes' => 'integer',
        ];
    }

    // Shift start times (24h HH:MM)
    public const SHIFT_STARTS = [
        'morning'   => '08:00',
        'afternoon' => '14:00',
        'night'     => '22:00',
    ];

    /**
     * Auto-compute late_minutes from shift start and time_in.
     */
    public static function computeLateMinutes(?string $shift, ?string $timeIn): int
    {
        if (!$shift || !$timeIn || !isset(self::SHIFT_STARTS[$shift])) {
            return 0;
        }
        [$sh, $sm] = explode(':', self::SHIFT_STARTS[$shift]);
        [$th, $tm] = explode(':', substr($timeIn, 0, 5));
        $shiftStart = (int)$sh * 60 + (int)$sm;
        $actualIn   = (int)$th * 60 + (int)$tm;
        return max(0, $actualIn - $shiftStart);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
