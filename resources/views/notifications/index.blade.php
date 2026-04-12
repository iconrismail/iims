@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Notifications</h3>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-secondary">Mark All Read</button>
                </form>
            @endif
        </div>

        @forelse($notifications as $notif)
            @php $data = $notif->data; @endphp
            <div style="display:flex;align-items:flex-start;gap:1rem;padding:1rem 1.25rem;border-bottom:1px solid var(--border-color);{{ $notif->read_at ? '' : 'background:rgba(108,99,255,0.06)' }}">
                <div style="width:36px;height:36px;border-radius:50%;background:{{ $data['icon'] === 'success' ? 'rgba(0,230,118,0.15)' : ($data['icon'] === 'danger' ? 'rgba(255,82,82,0.15)' : 'rgba(108,99,255,0.15)') }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    @if($data['icon'] === 'payslip')
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c63ff" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                    @elseif($data['icon'] === 'leave')
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c63ff" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
                    @elseif($data['icon'] === 'success')
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00e676" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    @else
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff5252" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    @endif
                </div>
                <div style="flex:1">
                    <div style="font-weight:{{ $notif->read_at ? '400' : '600' }};color:var(--text-primary)">{{ $data['title'] }}</div>
                    <div style="font-size:0.85rem;color:var(--text-secondary);margin-top:0.2rem">{{ $data['message'] }}</div>
                    <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.3rem">{{ $notif->created_at->diffForHumans() }}</div>
                </div>
                <div style="display:flex;gap:0.5rem;align-items:center">
                    @if(!$notif->read_at)
                        <span style="width:8px;height:8px;background:#6c63ff;border-radius:50%;flex-shrink:0"></span>
                    @endif
                    @if(isset($data['url']))
                        <a href="{{ route('notifications.read', $notif->id) }}?redirect={{ urlencode($data['url']) }}"
                           class="btn btn-sm btn-secondary">View</a>
                    @else
                        <form action="{{ route('notifications.read', $notif->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-secondary">Dismiss</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state"><p>No notifications yet.</p></div>
        @endforelse

        @if($notifications->hasPages())
            <div class="pagination-wrapper">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
