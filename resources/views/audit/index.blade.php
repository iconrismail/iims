@extends('layouts.app')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log')

@section('content')

    {{-- Stats Row --}}
    <div class="stats-grid" style="margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-icon blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ number_format($totalLogs) }}</h3>
                <div class="stat-label">Total Log Entries</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $todayLogs }}</h3>
                <div class="stat-label">Events Today</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $eventCounts->get('created', 0) }}</h3>
                <div class="stat-label">Records Created</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $eventCounts->get('updated', 0) }}</h3>
                <div class="stat-label">Records Updated</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $eventCounts->get('deleted', 0) }}</h3>
                <div class="stat-label">Records Deleted</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Audit Log</h3>
        </div>

        {{-- Filters --}}
        <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
            <select name="causer_id" class="form-control" style="width:auto;">
                <option value="">All Users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('causer_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
            <select name="event" class="form-control" style="width:auto;">
                <option value="">All Events</option>
                <option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Created</option>
                <option value="updated" {{ request('event') === 'updated' ? 'selected' : '' }}>Updated</option>
                <option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Deleted</option>
            </select>
            <input type="date" name="date" class="form-control" value="{{ request('date') }}" style="width:auto;">
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="{{ route('audit.index') }}" class="btn btn-secondary">Reset</a>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Event</th>
                        <th>Subject</th>
                        <th>Changes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td class="text-secondary" style="white-space:nowrap;">{{ $activity->created_at->format('d M Y, H:i') }}</td>
                            <td>{{ $activity->causer?->name ?? '<em class="text-muted">System</em>' }}</td>
                            <td>
                                @if($activity->event === 'created')
                                    <span class="badge badge-success">Created</span>
                                @elseif($activity->event === 'updated')
                                    <span class="badge badge-info">Updated</span>
                                @elseif($activity->event === 'deleted')
                                    <span class="badge badge-danger">Deleted</span>
                                @else
                                    <span class="badge">{{ ucfirst($activity->event ?? 'log') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="font-bold" style="font-size:0.8rem;">{{ class_basename($activity->subject_type ?? '') }}</div>
                                @if($activity->subject_id)
                                    <div class="text-muted" style="font-size:0.75rem;">ID: {{ $activity->subject_id }}</div>
                                @endif
                                @if($activity->description)
                                    <div class="text-secondary" style="font-size:0.75rem;">{{ $activity->description }}</div>
                                @endif
                            </td>
                            <td style="max-width:300px;">
                                @php $props = $activity->properties->toArray(); @endphp
                                @if(!empty($props['attributes']) && !empty($props['old']))
                                    <div style="font-size:0.75rem;">
                                        @foreach($props['attributes'] as $key => $newVal)
                                            @if(isset($props['old'][$key]) && $props['old'][$key] != $newVal)
                                                <div><span class="text-muted">{{ $key }}:</span>
                                                    <span class="text-danger">{{ is_array($props['old'][$key]) ? json_encode($props['old'][$key]) : $props['old'][$key] }}</span>
                                                    → <span class="text-success">{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @elseif(!empty($props['attributes']))
                                    <div style="font-size:0.75rem;color:var(--text-muted);">
                                        {{ count($props['attributes']) }} field(s) set
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted" style="padding:2rem">No activity records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
            <div class="pagination-wrapper">{{ $activities->links() }}</div>
        @endif
    </div>
@endsection
