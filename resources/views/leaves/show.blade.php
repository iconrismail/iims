@extends('layouts.app')

@section('title', 'Leave Request Details')
@section('page-title', 'Leave Request Details')

@section('content')
    <div class="card" style="max-width:600px;">
        <div class="card-header">
            <h3 class="card-title">Leave Request</h3>
            @if($leave->status === 'approved')
                <span class="badge badge-success">Approved</span>
            @elseif($leave->status === 'rejected')
                <span class="badge badge-danger">Rejected</span>
            @else
                <span class="badge badge-warning">Pending</span>
            @endif
        </div>

        <div style="background:var(--bg-secondary);border-radius:var(--radius);padding:1rem 1.25rem;margin-bottom:1.5rem;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div>
                    <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;">Employee</div>
                    <div class="font-bold">{{ $leave->employee->user->name }}</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;">Leave Type</div>
                    <div class="font-bold">{{ $leave->leaveType->name }}</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;">Start Date</div>
                    <div>{{ $leave->start_date->format('d M Y') }}</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;">End Date</div>
                    <div>{{ $leave->end_date->format('d M Y') }}</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;">Total Days</div>
                    <div class="font-bold">{{ $leave->total_days }} working day(s)</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;">Submitted</div>
                    <div>{{ $leave->created_at->format('d M Y, H:i') }}</div>
                </div>
            </div>
            @if($leave->reason)
                <div style="margin-top:0.75rem;">
                    <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;">Reason</div>
                    <div>{{ $leave->reason }}</div>
                </div>
            @endif
        </div>

        @if($leave->approvedBy)
            <div style="margin-bottom:1rem;">
                <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;">Reviewed by</div>
                <div>{{ $leave->approvedBy->name }}</div>
            </div>
        @endif

        @if($leave->admin_note)
            <div style="background:rgba(255,82,82,0.08);border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1rem;">
                <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;">Admin Note</div>
                <div>{{ $leave->admin_note }}</div>
            </div>
        @endif

        @if(!empty($leaveWarnings))
            <div style="background:#ef444412;border:1px solid #ef444440;border-radius:var(--radius);padding:0.85rem 1rem;margin-bottom:1rem;">
                <div style="font-size:0.72rem;text-transform:uppercase;color:#ef4444;font-weight:700;letter-spacing:0.05em;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.4rem;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Staffing Warnings
                </div>
                <ul style="margin:0;padding-left:1.1rem;display:flex;flex-direction:column;gap:0.35rem;">
                    @foreach($leaveWarnings as $w)
                        <li style="font-size:0.82rem;color:#fca5a5;line-height:1.45;">{{ $w }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(auth()->user()->isAdminOrManager() && $leave->status === 'pending')
            <div style="display:flex;gap:0.75rem;margin-bottom:1rem;flex-wrap:wrap;">
                <form method="POST" action="{{ route('leaves.approve', $leave) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Approve this leave request?')">Approve</button>
                </form>

                <form method="POST" action="{{ route('leaves.reject', $leave) }}" id="rejectForm">
                    @csrf
                    <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                        <input type="text" name="admin_note" class="form-control" placeholder="Rejection note (optional)" style="width:250px;">
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Reject this leave request?')">Reject</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="mt-3">
            <a href="{{ route('leaves.index') }}" class="btn btn-secondary">Back to Leaves</a>
        </div>
    </div>
@endsection
