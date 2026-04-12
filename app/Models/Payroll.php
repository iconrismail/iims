<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Payroll extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    protected $fillable = [
        'month',
        'year',
        'status',
        'notes',
    ];

    // --- Relationships ---

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    // --- Helpers ---

    public function periodLabel(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 1)) . ' ' . $this->year;
    }
}
