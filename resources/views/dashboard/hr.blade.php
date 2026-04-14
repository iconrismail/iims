@extends('layouts.app')

@section('title', 'HR Dashboard')
@section('page-title', 'HR Dashboard')

@section('breadcrumbs')
    <span>Dashboard</span>
@endsection

@section('content')

    {{-- ── Stat Cards ───────────────────────────────────────────── --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $totalEmployees }}</h3>
                <div class="stat-label">Active Employees</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $newHiresMonth }}</h3>
                <div class="stat-label">New Hires This Month</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $todayPresent }}</h3>
                <div class="stat-label">Present Today</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon amber">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M9 14l2 2 4-4"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $onLeaveToday }}</h3>
                <div class="stat-label">On Leave Today</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $pendingLeaves }}</h3>
                <div class="stat-label">Pending Leave Requests</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $pendingProfileUpdates }}</h3>
                <div class="stat-label">Profile Update Requests</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $expiringContracts->count() }}</h3>
                <div class="stat-label">Expiring Contracts / Probation</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon grey">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $inactiveCount }}</h3>
                <div class="stat-label">Inactive Employees</div>
            </div>
        </div>
    </div>

    {{-- ── Main Grid ────────────────────────────────────────────── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-top:1.5rem;">

        {{-- Pending Leave Requests --}}
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">
                <h3 class="card-title">Pending Leave Requests</h3>
                <a href="{{ route('leaves.index') }}?status=pending" class="btn btn-secondary btn-sm">View All</a>
            </div>
            @if($pendingLeaveRequests->isEmpty())
                <div class="empty-state" style="padding:2rem 0;">
                    <div style="font-size:2rem;opacity:.3;margin-bottom:.5rem;">✓</div>
                    <p style="color:var(--text-secondary);">No pending leave requests.</p>
                </div>
            @else
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Days</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingLeaveRequests as $leave)
                                <tr>
                                    <td class="font-bold">{{ $leave->employee?->user?->name ?? '—' }}</td>
                                    <td>{{ $leave->leaveType?->name ?? '—' }}</td>
                                    <td>{{ $leave->start_date->format('d M Y') }}</td>
                                    <td>{{ $leave->end_date->format('d M Y') }}</td>
                                    <td>{{ $leave->total_days }}</td>
                                    <td class="text-secondary">{{ $leave->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <form action="{{ route('leaves.approve', $leave) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form action="{{ route('leaves.reject', $leave) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-secondary">Reject</button>
                                            </form>
                                            <a href="{{ route('leaves.show', $leave) }}" class="btn btn-sm btn-secondary">View</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Department Headcount --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Department Headcount</h3>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm">All Employees</a>
            </div>
            @if($deptData->isEmpty())
                <p style="color:var(--text-secondary);padding:1rem 0;">No departments found.</p>
            @else
                <div style="display:flex;flex-direction:column;gap:.75rem;margin-top:.5rem;">
                    @php $maxCount = $deptData->max('employees_count') ?: 1; @endphp
                    @foreach($deptData->sortByDesc('employees_count') as $dept)
                        <div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:.25rem;">
                                <span style="font-size:.85rem;font-weight:600;">{{ $dept->name }}</span>
                                <span style="font-size:.85rem;color:var(--text-secondary);">{{ $dept->employees_count }} employees</span>
                            </div>
                            <div style="background:var(--bg-tertiary);border-radius:99px;height:6px;">
                                <div style="background:var(--accent);border-radius:99px;height:6px;width:{{ ($dept->employees_count / $maxCount) * 100 }}%;transition:width .3s;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Upcoming Events --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Upcoming Events <span style="font-size:.75rem;font-weight:400;color:var(--text-secondary);">Next 7 days</span></h3>
            </div>
            @if($upcomingEvents->isEmpty())
                <div class="empty-state" style="padding:1.5rem 0;">
                    <p style="color:var(--text-secondary);">No upcoming events.</p>
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:.6rem;margin-top:.25rem;">
                    @foreach($upcomingEvents as $event)
                        <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem .75rem;background:var(--bg-tertiary);border-radius:8px;">
                            @if($event['type'] === 'birthday')
                                <span style="font-size:1.2rem;">🎂</span>
                            @elseif($event['type'] === 'anniversary')
                                <span style="font-size:1.2rem;">🎉</span>
                            @else
                                <span style="font-size:1.2rem;">📄</span>
                            @endif
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:600;font-size:.85rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">{{ $event['name'] }}</div>
                                <div style="font-size:.75rem;color:var(--text-secondary);">
                                    {{ $event['date']->format('d M') }}
                                    @if($event['detail']) · {{ $event['detail'] }} @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Expiring Contracts & Probation --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Expiring Contracts / Probation <span style="font-size:.75rem;font-weight:400;color:var(--accent-red,#ef4444);">Next 30 days</span></h3>
            </div>
            @if($expiringContracts->isEmpty())
                <div class="empty-state" style="padding:1.5rem 0;">
                    <p style="color:var(--text-secondary);">No contracts or probation periods expiring soon.</p>
                </div>
            @else
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>Employee</th><th>Department</th><th>Type</th><th>Expiry</th></tr>
                        </thead>
                        <tbody>
                            @foreach($expiringContracts as $emp)
                                @php
                                    $expiry = $emp->contract_end_date ?? $emp->probation_end_date;
                                    $type   = $emp->probation_end_date && (!$emp->contract_end_date || $emp->probation_end_date->lt($emp->contract_end_date))
                                              ? 'Probation' : ucfirst($emp->contract_type ?? 'Contract');
                                    $daysLeft = now()->diffInDays($expiry, false);
                                @endphp
                                <tr>
                                    <td class="font-bold">{{ $emp->user?->name ?? '—' }}</td>
                                    <td>{{ $emp->department?->name ?? '—' }}</td>
                                    <td><span class="badge badge-warning">{{ $type }}</span></td>
                                    <td>
                                        <span style="color:{{ $daysLeft <= 7 ? '#ef4444' : '#f59e0b' }};font-weight:600;">
                                            {{ $expiry?->format('d M Y') ?? '—' }}
                                            @if($daysLeft >= 0)
                                                <span style="font-size:.75rem;font-weight:400;">({{ $daysLeft }}d)</span>
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Recent Hires --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Hires</h3>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm">All Employees</a>
            </div>
            @if($recentHires->isEmpty())
                <div class="empty-state" style="padding:1.5rem 0;">
                    <p style="color:var(--text-secondary);">No employees yet.</p>
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:.5rem;margin-top:.25rem;">
                    @foreach($recentHires as $emp)
                        <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem .75rem;background:var(--bg-tertiary);border-radius:8px;">
                            <div style="width:34px;height:34px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;color:#000;flex-shrink:0;">
                                {{ strtoupper(substr($emp->user?->name ?? '?', 0, 1)) }}
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:600;font-size:.85rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">{{ $emp->user?->name ?? '—' }}</div>
                                <div style="font-size:.75rem;color:var(--text-secondary);">{{ $emp->position }} · {{ $emp->department?->name ?? '—' }}</div>
                            </div>
                            <div style="font-size:.75rem;color:var(--text-secondary);flex-shrink:0;">{{ $emp->hire_date?->format('d M Y') ?? '—' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Leave Approvals This Month by Type --}}
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">
                <h3 class="card-title">Approved Leave This Month by Type</h3>
                <span style="font-size:.8rem;color:var(--text-secondary);">{{ $now->format('F Y') }}</span>
            </div>
            @if($monthLeaveByType->isEmpty())
                <div class="empty-state" style="padding:1.5rem 0;">
                    <p style="color:var(--text-secondary);">No approved leave requests this month.</p>
                </div>
            @else
                @php $maxLeave = $monthLeaveByType->max() ?: 1; @endphp
                <div style="display:flex;flex-direction:column;gap:.75rem;margin-top:.5rem;">
                    @foreach($monthLeaveByType as $type => $count)
                        <div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:.25rem;">
                                <span style="font-size:.85rem;font-weight:600;">{{ $type }}</span>
                                <span style="font-size:.85rem;color:var(--text-secondary);">{{ $count }} request{{ $count != 1 ? 's' : '' }}</span>
                            </div>
                            <div style="background:var(--bg-tertiary);border-radius:99px;height:6px;">
                                <div style="background:var(--accent);border-radius:99px;height:6px;width:{{ ($count / $maxLeave) * 100 }}%;transition:width .3s;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
@endsection
