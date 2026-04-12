<?php

namespace App\Services;

use App\Models\PublicHoliday;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HolidayService
{
    /**
     * Get all public holiday dates for a given year as a set of 'Y-m-d' strings.
     * Recurring holidays are resolved to the requested year.
     * Cached for 1 hour since holidays rarely change.
     */
    public function getHolidayDates(int $year): array
    {
        return Cache::remember("public_holidays_{$year}", 3600, function () use ($year) {
            return PublicHoliday::all()
                ->map(fn($h) => $h->resolveForYear($year)?->toDateString())
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        });
    }

    /**
     * Count working days between $start and $end inclusive,
     * excluding weekends and public holidays.
     */
    public function countWorkingDays(Carbon $start, Carbon $end): int
    {
        $holidays = $this->getHolidayDates($start->year);

        // If date range spans two years, also fetch holidays for the end year
        if ($end->year !== $start->year) {
            $holidays = array_unique(array_merge(
                $holidays,
                $this->getHolidayDates($end->year)
            ));
        }

        $count   = 0;
        $current = $start->copy()->startOfDay();

        while ($current->lte($end)) {
            if ($current->isWeekday() && !in_array($current->toDateString(), $holidays)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    /**
     * Check whether a specific date is a public holiday.
     */
    public function isHoliday(Carbon $date): bool
    {
        $holidays = $this->getHolidayDates($date->year);
        return in_array($date->toDateString(), $holidays);
    }

    /**
     * Get upcoming holidays in the next $days calendar days.
     */
    public function upcoming(int $days = 30): Collection
    {
        $today = Carbon::today();
        $until = $today->copy()->addDays($days);
        $year  = $today->year;

        $dates = $this->getHolidayDates($year);

        // Also include next year's if the window crosses Dec 31
        if ($until->year !== $year) {
            $dates = array_unique(array_merge($dates, $this->getHolidayDates($until->year)));
        }

        return PublicHoliday::all()
            ->flatMap(function ($h) use ($today, $until) {
                $resolved = $h->resolveForYear($today->year);
                $results  = [];
                if ($resolved && $resolved->between($today, $until)) {
                    $results[] = ['name' => $h->name, 'date' => $resolved->toDateString(), 'is_recurring' => $h->is_recurring];
                }
                // Check next year too if window crosses year boundary
                $resolvedNext = $h->resolveForYear($today->year + 1);
                if ($resolvedNext && $resolvedNext->between($today, $until)) {
                    $results[] = ['name' => $h->name, 'date' => $resolvedNext->toDateString(), 'is_recurring' => $h->is_recurring];
                }
                return $results;
            })
            ->sortBy('date')
            ->values();
    }
}
