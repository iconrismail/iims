<?php
namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        abort_if(!$request->user()->isAdminOrHR(), 403);

        // ── Stats ─────────────────────────────────────────────
        $totalLogs    = Activity::count();
        $todayLogs    = Activity::whereDate('created_at', today())->count();
        $eventCounts  = Activity::selectRaw('event, COUNT(*) as count')
            ->groupBy('event')
            ->pluck('count', 'event');

        // ── Filtered query ────────────────────────────────────
        $query = Activity::with('causer')->latest();
        if ($request->filled('causer_id')) $query->where('causer_id', $request->causer_id);
        if ($request->filled('date'))      $query->whereDate('created_at', $request->date);
        if ($request->filled('event'))     $query->where('event', $request->event);

        $activities = $query->paginate(25);
        $users      = \App\Models\User::orderBy('name')->get();

        return view('audit.index', compact(
            'activities', 'users',
            'totalLogs', 'todayLogs', 'eventCounts'
        ));
    }
}
