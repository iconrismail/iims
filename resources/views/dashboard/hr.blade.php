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

        {{-- ADD-ON 1: Attendance Rate Trend --}}
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">
                <h3 class="card-title">Attendance Rate Trend</h3>
                <span style="font-size:.8rem;color:var(--text-secondary);">Last 6 months (present / recorded)</span>
            </div>
            <canvas id="attendanceRateChart" height="80"></canvas>
        </div>

        {{-- ADD-ON 2: Gender & Contract Type Breakdown --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Gender Breakdown</h3>
                <span style="font-size:.8rem;color:var(--text-secondary);">Active employees</span>
            </div>
            <div style="max-width:260px;margin:0 auto;">
                <canvas id="genderChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Contract Type Breakdown</h3>
                <span style="font-size:.8rem;color:var(--text-secondary);">Active employees</span>
            </div>
            <div style="max-width:260px;margin:0 auto;">
                <canvas id="contractTypeChart"></canvas>
            </div>
        </div>

        {{-- ADD-ON 3: Org-wide Attendance Snapshot Today --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attendance Snapshot</h3>
                <span style="font-size:.8rem;color:var(--text-secondary);">Today · {{ $now->format('d M Y') }}</span>
            </div>
            @php
                $snapTotal = $todayPresent + $todayAbsent + $onLeaveToday + $notRecordedToday;
                $snapTotal = $snapTotal ?: 1;
                $pctPresent  = round(($todayPresent  / $snapTotal) * 100);
                $pctAbsent   = round(($todayAbsent   / $snapTotal) * 100);
                $pctLeave    = round(($onLeaveToday  / $snapTotal) * 100);
                $pctNone     = max(0, 100 - $pctPresent - $pctAbsent - $pctLeave);
            @endphp
            <div style="margin-top:1rem;">
                {{-- Stacked bar --}}
                <div style="display:flex;height:20px;border-radius:10px;overflow:hidden;margin-bottom:1rem;">
                    @if($pctPresent > 0)
                        <div style="width:{{ $pctPresent }}%;background:#00e676;" title="Present: {{ $todayPresent }}"></div>
                    @endif
                    @if($pctAbsent > 0)
                        <div style="width:{{ $pctAbsent }}%;background:#ff5252;" title="Absent: {{ $todayAbsent }}"></div>
                    @endif
                    @if($pctLeave > 0)
                        <div style="width:{{ $pctLeave }}%;background:#ffab00;" title="On Leave: {{ $onLeaveToday }}"></div>
                    @endif
                    @if($pctNone > 0)
                        <div style="width:{{ $pctNone }}%;background:var(--bg-tertiary);" title="Not Recorded: {{ $notRecordedToday }}"></div>
                    @endif
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
                    <div style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;">
                        <span style="width:10px;height:10px;background:#00e676;border-radius:50%;flex-shrink:0;"></span>
                        Present <strong style="margin-left:auto;">{{ $todayPresent }}</strong>
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;">
                        <span style="width:10px;height:10px;background:#ff5252;border-radius:50%;flex-shrink:0;"></span>
                        Absent <strong style="margin-left:auto;">{{ $todayAbsent }}</strong>
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;">
                        <span style="width:10px;height:10px;background:#ffab00;border-radius:50%;flex-shrink:0;"></span>
                        On Leave <strong style="margin-left:auto;">{{ $onLeaveToday }}</strong>
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;">
                        <span style="width:10px;height:10px;background:var(--bg-tertiary);border:1px solid var(--border);border-radius:50%;flex-shrink:0;"></span>
                        Not Recorded <strong style="margin-left:auto;">{{ $notRecordedToday }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- ADD-ON 6: Headcount Growth Trend --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Headcount Growth</h3>
                <span style="font-size:.8rem;color:var(--text-secondary);">Last 6 months</span>
            </div>
            <canvas id="headcountChart" height="120"></canvas>
        </div>

        {{-- ADD-ON 4: Leave Balance Warnings --}}
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">
                <h3 class="card-title">Leave Balance Warnings</h3>
                <span style="font-size:.8rem;color:#ef4444;">Employees at ≥80% leave usage ({{ $now->year }})</span>
            </div>
            @if($leaveWarnings->isEmpty())
                <div class="empty-state" style="padding:1.5rem 0;">
                    <p style="color:var(--text-secondary);">No employees near their leave limit.</p>
                </div>
            @else
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Entitled</th>
                                <th>Used</th>
                                <th>Remaining</th>
                                <th>Usage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveWarnings as $bal)
                                @php
                                    $total   = $bal->entitled_days + $bal->carried_forward;
                                    $pct     = $total > 0 ? round(($bal->used_days / $total) * 100) : 0;
                                    $barColor = $pct >= 100 ? '#ef4444' : ($pct >= 90 ? '#f97316' : '#f59e0b');
                                @endphp
                                <tr>
                                    <td class="font-bold">{{ $bal->employee?->user?->name ?? '—' }}</td>
                                    <td>{{ $bal->leaveType?->name ?? '—' }}</td>
                                    <td>{{ $total }}d</td>
                                    <td>{{ $bal->used_days }}d</td>
                                    <td>{{ $bal->remaining() }}d</td>
                                    <td style="min-width:120px;">
                                        <div style="display:flex;align-items:center;gap:.5rem;">
                                            <div style="flex:1;background:var(--bg-tertiary);border-radius:99px;height:6px;">
                                                <div style="background:{{ $barColor }};border-radius:99px;height:6px;width:{{ min($pct,100) }}%;"></div>
                                            </div>
                                            <span style="font-size:.75rem;font-weight:600;color:{{ $barColor }};width:36px;text-align:right;">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ADD-ON 5: Pending Overtime Requests --}}
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">
                <h3 class="card-title">Pending Overtime Requests</h3>
                <a href="{{ route('overtime.index') }}" class="btn btn-secondary btn-sm">View All</a>
            </div>
            @if($pendingOvertimeRequests->isEmpty())
                <div class="empty-state" style="padding:1.5rem 0;">
                    <p style="color:var(--text-secondary);">No pending overtime requests.</p>
                </div>
            @else
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Hours</th>
                                <th>Reason</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingOvertimeRequests as $ot)
                                <tr>
                                    <td class="font-bold">{{ $ot->employee?->user?->name ?? '—' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($ot->date)->format('d M Y') }}</td>
                                    <td>{{ $ot->hours }}h</td>
                                    <td style="max-width:200px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">{{ $ot->reason ?? '—' }}</td>
                                    <td class="text-secondary">{{ $ot->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <form action="{{ route('overtime.approve', $ot) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form action="{{ route('overtime.reject', $ot) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-secondary">Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
@endsection

@push('scripts')
<script>
(function () {
    function animateCounter(el) {
        const raw = el.textContent.replace(/,/g, '');
        const target = parseInt(raw, 10);
        if (isNaN(target) || target === 0) return;
        el.textContent = '0';
        const duration = 900;
        const startTime = performance.now();
        function step(now) {
            const progress = Math.min((now - startTime) / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(ease * target).toLocaleString();
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target.toLocaleString();
        }
        requestAnimationFrame(step);
    }
    document.querySelectorAll('.stat-info h3').forEach(animateCounter);
})();

const chartScales = {
    x: { ticks: { color: '#aaa' }, grid: { color: 'rgba(255,255,255,0.05)' } },
    y: { ticks: { color: '#aaa' }, grid: { color: 'rgba(255,255,255,0.05)' } }
};

// ADD-ON 1: Attendance Rate Trend
const attendanceRateData = @json($attendanceRateTrend);
new Chart(document.getElementById('attendanceRateChart'), {
    type: 'line',
    data: {
        labels: attendanceRateData.map(d => d.label),
        datasets: [{
            label: 'Attendance Rate (%)',
            data: attendanceRateData.map(d => d.rate),
            borderColor: '#6c63ff',
            backgroundColor: 'rgba(108,99,255,0.12)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: '#6c63ff',
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#aaa' } } },
        scales: {
            x: chartScales.x,
            y: { ...chartScales.y, min: 0, max: 100, ticks: { color: '#aaa', callback: v => v + '%' } }
        }
    }
});

// ADD-ON 2a: Gender Donut
const genderRaw = @json($genderData);
new Chart(document.getElementById('genderChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(genderRaw),
        datasets: [{
            data: Object.values(genderRaw),
            backgroundColor: ['#6c63ff', '#ff6b6b', '#00e676', '#ffab00'],
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#aaa', font: { size: 11 } } } }
    }
});

// ADD-ON 2b: Contract Type Donut
const contractRaw = @json($contractTypeData);
new Chart(document.getElementById('contractTypeChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(contractRaw),
        datasets: [{
            data: Object.values(contractRaw),
            backgroundColor: ['#29b6f6', '#ffab00', '#ef5350', '#00e676', '#ab47bc'],
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#aaa', font: { size: 11 } } } }
    }
});

// ADD-ON 6: Headcount Growth Bar
const headcountData = @json($headcountTrend);
new Chart(document.getElementById('headcountChart'), {
    type: 'bar',
    data: {
        labels: headcountData.map(d => d.label),
        datasets: [{
            label: 'Total Headcount',
            data: headcountData.map(d => d.count),
            backgroundColor: 'rgba(108,99,255,0.7)',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#aaa' } } },
        scales: { ...chartScales, y: { ...chartScales.y, beginAtZero: true } }
    }
});
</script>
@endpush
