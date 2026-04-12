<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class PerformanceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'review_period',
        'period_year',
        'scores',
        'overall_score',
        'comments',
        'status',
        'salary_increment_pct',
        'acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'scores' => 'array',
            'overall_score' => 'decimal:2',
            'salary_increment_pct' => 'decimal:2',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function calculateOverallScore(): float
    {
        $scores = $this->scores ?? [];
        if (empty($scores)) {
            return 0;
        }

        $categories = KpiCategory::where('is_active', true)->get()->keyBy('id');
        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($scores as $categoryId => $score) {
            if (isset($categories[$categoryId])) {
                $weight = (float) $categories[$categoryId]->weight;
                $weightedSum += $score * $weight;
                $totalWeight += $weight;
            }
        }

        if ($totalWeight === 0) {
            return 0;
        }

        return round($weightedSum / $totalWeight, 2);
    }

    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForPeriod(Builder $query, string $period, int $year): Builder
    {
        return $query->where('review_period', $period)->where('period_year', $year);
    }
}
