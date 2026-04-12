<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PublicHoliday extends Model
{
    protected $fillable = ['date', 'name', 'is_recurring'];

    protected function casts(): array
    {
        return [
            'date'         => 'date',
            'is_recurring' => 'boolean',
        ];
    }

    /**
     * Resolve the actual date of this holiday in the given year.
     * Returns null if the holiday doesn't apply to that year.
     */
    public function resolveForYear(int $year): ?Carbon
    {
        if ($this->is_recurring) {
            return $this->date->copy()->year($year);
        }
        if ($this->date->year === $year) {
            return $this->date->copy();
        }
        return null;
    }
}
