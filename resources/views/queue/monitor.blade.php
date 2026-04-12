@extends('layouts.app')
@section('title', 'Queue Monitor')
@section('page-title', 'Queue Monitor')

@section('content')
    {{-- Stats --}}
    <div class="stats-grid" style="margin-bottom:1.5rem">
        <div class="stat-card">
            <div class="stat-icon blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $stats['pending'] }}</h3>
                <div class="stat-label">Pending Jobs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $stats['processing'] }}</h3>
                <div class="stat-label">Processing</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $stats['failed'] }}</h3>
                <div class="stat-label">Failed Jobs</div>
            </div>
        </div>
    </div>

    {{-- Queue Worker Command --}}
    <div class="card mb-3" style="background:rgba(108,99,255,0.08);border:1px solid rgba(108,99,255,0.3)">
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6c63ff" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            <div>
                <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.25rem">Run this command in your terminal to process queued jobs:</div>
                <code style="background:rgba(0,0,0,0.3);padding:0.4rem 0.8rem;border-radius:4px;font-size:0.9rem;color:#6c63ff">
                    php artisan queue:work --tries=3 --timeout=300
                </code>
            </div>
        </div>
    </div>

    {{-- Pending Jobs --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Pending Jobs ({{ $stats['pending'] }})</h3>
        </div>
        @if($pending->isEmpty())
            <div class="empty-state"><p>No pending jobs.</p></div>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Job</th><th>Queue</th><th>Attempts</th><th>Available At</th><th>Created</th></tr>
                    </thead>
                    <tbody>
                        @foreach($pending as $job)
                            <tr>
                                <td class="font-bold">{{ $job->job_name }}</td>
                                <td><span class="badge badge-info">{{ $job->queue }}</span></td>
                                <td>{{ $job->attempts }}</td>
                                <td class="text-secondary">{{ date('M d, H:i', $job->available_at) }}</td>
                                <td class="text-secondary">{{ date('M d, H:i', $job->created_at) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Failed Jobs --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Failed Jobs ({{ $stats['failed'] }})</h3>
            @if($stats['failed'] > 0)
                <form action="{{ route('queue.clearFailed') }}" method="POST" onsubmit="return confirm('Clear all failed jobs?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger">Clear All</button>
                </form>
            @endif
        </div>
        @if($failed->isEmpty())
            <div class="empty-state"><p>No failed jobs.</p></div>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Job</th><th>Queue</th><th>Failed At</th><th>Error</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach($failed as $job)
                            <tr>
                                <td class="font-bold">{{ $job->job_name }}</td>
                                <td><span class="badge badge-warning">{{ $job->queue }}</span></td>
                                <td class="text-secondary">{{ $job->failed_at }}</td>
                                <td class="text-danger" style="font-size:0.78rem;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $job->exception }}">
                                    {{ $job->exception }}
                                </td>
                                <td>
                                    <form action="{{ route('queue.retryFailed') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="uuid" value="{{ $job->uuid }}">
                                        <button type="submit" class="btn btn-sm btn-secondary">Retry</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
