<?php

namespace App\Http\Controllers;

use App\Models\PublicHoliday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PublicHolidayController extends Controller
{
    public function index(): View
    {
        $holidays = PublicHoliday::orderBy('date')->get();
        return view('holidays.index', compact('holidays'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'date'         => 'required|date',
            'name'         => 'required|string|max:120',
            'is_recurring' => 'boolean',
        ]);

        PublicHoliday::create([
            'date'         => $request->date,
            'name'         => $request->name,
            'is_recurring' => $request->boolean('is_recurring'),
        ]);

        // Bust cached holiday lists
        $this->bustCache();

        return back()->with('success', "Holiday \"{$request->name}\" added.");
    }

    public function destroy(PublicHoliday $holiday): RedirectResponse
    {
        $name = $holiday->name;
        $holiday->delete();
        $this->bustCache();
        return back()->with('success', "Holiday \"{$name}\" removed.");
    }

    private function bustCache(): void
    {
        // Bust the current and next 2 years of cached holiday lists
        $year = now()->year;
        foreach ([$year - 1, $year, $year + 1, $year + 2] as $y) {
            Cache::forget("public_holidays_{$y}");
        }
    }
}
