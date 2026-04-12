@extends('layouts.app')
@section('title', 'Profile Update Requests')
@section('page-title', 'Profile Update Requests')

@section('breadcrumbs')
    <span>Profile Update Requests</span>
@endsection

@section('content')

{{-- Status Filter --}}
<div class="card" style="margin-bottom:1.25rem">
    <form method="GET" style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap">
        <label style="font-size:0.82rem;font-weight:600;color:var(--text-secondary)">Filter:</label>
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $val => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
               class="btn btn-sm {{ $status === $val ? 'btn-primary' : 'btn-secondary' }}"
               style="font-size:0.8rem">
                {{ $label }}
                @if($val === 'pending' && $pendingCount > 0)
                    <span class="nav-badge" style="position:static;margin-left:4px;top:auto;right:auto">{{ $pendingCount }}</span>
                @endif
            </a>
        @endforeach
    </form>
</div>

@if($requests->isEmpty())
    <div class="empty-state">
        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="8" y="8" width="48" height="48" rx="4" stroke="currentColor" stroke-width="2"/>
            <path d="M22 32h20M22 24h20M22 40h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <p>No {{ $status !== 'all' ? $status : '' }} requests found.</p>
    </div>
@else
<div style="display:flex;flex-direction:column;gap:1rem">
    @foreach($requests as $req)
    @php
        $emp = $req->employee;
        $isPending = $req->status === 'pending';
    @endphp
    <div class="card" style="border-left:3px solid {{ $req->status === 'approved' ? '#22c55e' : ($req->status === 'rejected' ? '#ef4444' : '#f59e0b') }}">
        <div class="card-header" style="align-items:flex-start">
            <div>
                <div style="font-weight:700;font-size:0.95rem;color:var(--text-primary)">{{ $emp->user->name ?? 'Unknown' }}</div>
                <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px">
                    {{ $emp->employee_id }} &nbsp;·&nbsp; {{ $emp->department->name ?? 'N/A' }} &nbsp;·&nbsp; {{ $emp->position }}
                </div>
                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px">
                    Submitted {{ $req->created_at->diffForHumans() }} &nbsp;·&nbsp; {{ $req->created_at->format('d M Y H:i') }}
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:0.4rem">
                @if($req->status === 'pending')
                    <span class="badge badge-warning">Pending</span>
                @elseif($req->status === 'approved')
                    <span class="badge badge-success">Approved</span>
                @else
                    <span class="badge badge-danger">Rejected</span>
                @endif
                @if($req->reviewed_at)
                    <div style="font-size:0.72rem;color:var(--text-muted)">
                        Reviewed by {{ $req->reviewer->name ?? '—' }} on {{ $req->reviewed_at->format('d M Y') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Requested changes --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:0.5rem;margin:0.5rem 0 1rem">
            @foreach($req->requested_fields as $field => $newValue)
            @php $currentValue = $emp->$field ?? null; @endphp
            <div style="padding:0.6rem 0.75rem;background:var(--input-bg);border-radius:6px;border:1px solid var(--border-color)">
                <div style="font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:4px">
                    {{ \App\Models\ProfileUpdateRequest::FIELD_LABELS[$field] ?? $field }}
                </div>
                <div style="font-size:0.75rem;color:var(--text-muted);text-decoration:line-through;margin-bottom:2px">{{ $currentValue ?: '—' }}</div>
                <div style="font-size:0.85rem;font-weight:600;color:var(--text-primary)">{{ $newValue }}</div>
            </div>
            @endforeach
        </div>

        @if($req->admin_note)
            <div style="font-size:0.8rem;color:var(--text-muted);background:var(--input-bg);border-radius:6px;padding:0.5rem 0.75rem;margin-bottom:1rem">
                <strong>Note:</strong> {{ $req->admin_note }}
            </div>
        @endif

        @if($isPending)
        <div style="display:flex;gap:0.75rem;align-items:flex-start;flex-wrap:wrap">
            {{-- Approve --}}
            <form method="POST" action="{{ route('profile-updates.approve', $req) }}"
                  onsubmit="return confirm('Approve and apply these changes to {{ addslashes($emp->user->name ?? '') }}\'s profile?')">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">Approve &amp; Apply</button>
            </form>

            {{-- Reject --}}
            <form method="POST" action="{{ route('profile-updates.reject', $req) }}"
                  id="reject-form-{{ $req->id }}"
                  onsubmit="return confirm('Reject this profile update request?')">
                @csrf
                <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap">
                    <input type="text" name="admin_note" class="form-control" style="width:260px;height:36px;font-size:0.82rem"
                           placeholder="Rejection reason (optional)" maxlength="500">
                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                </div>
            </form>
        </div>
        @endif
    </div>
    @endforeach
</div>

<div style="margin-top:1.25rem">
    {{ $requests->withQueryString()->links() }}
</div>
@endif

@endsection
