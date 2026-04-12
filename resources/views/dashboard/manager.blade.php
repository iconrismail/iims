@extends('layouts.app')

@section('title', 'Manager Dashboard')
@section('page-title', 'Manager Dashboard')

@section('content')
@php
    $user = auth()->user();
    $hour = $now->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

{{-- Greeting Banner --}}
<div class="card" style="background:linear-gradient(135deg,var(--accent) 0%,#0099bb 100%);color:#fff;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div>
            <div style="font-size:1.35rem;font-weight:700;">{{ $greeting }}, {{ $user->name }}</div>
            <div style="opacity:.85;font-size:.9rem;margin-top:.25rem;">
                Department Manager &nbsp;·&nbsp;
                {{ $department?->name ?? 'No Department' }}
                &nbsp;·&nbsp;{{ $now->format('l, d M Y') }}
            </div>
        </div>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
            @if($pendingLeaves > 0)
                <a href="{{ route('leaves.index', ['status'=>'pending']) }}" class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.35);">
                    {{ $pendingLeaves }} Leave{{ $pendingLeaves != 1 ? 's' : '' }} Pending
                </a>
            @endif
            @if($pendingOvertimes > 0)
                <a href="{{ route('overtime.index', ['status'=>'pending']) }}" class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.35);">
                    {{ $pendingOvertimes }} OT Pending
                </a>
            @endif
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        <div style="font-size:2rem;font-weight:700;color:var(--accent);">{{ $teamSize }}</div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">Team Members</div>
    </div>
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        <div style="font-size:2rem;font-weight:700;color:#28a745;">{{ $teamPresent }}</div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">Present Today</div>
    </div>
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        <div style="font-size:2rem;font-weight:700;color:#dc3545;">{{ $teamAbsent }}</div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">Absent Today</div>
    </div>
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        <div style="font-size:2rem;font-weight:700;color:#f0ad4e;">{{ $pendingLeaves }}</div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">Pending Leaves</div>
    </div>
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        <div style="font-size:2rem;font-weight:700;color:#9b59b6;">{{ $pendingOvertimes }}</div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">Pending Overtime</div>
    </div>
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        @php
            $total = $monthPresent + $monthAbsent;
            $rate = $total > 0 ? round(($monthPresent / $total) * 100) : 0;
            $rateColor = $rate >= 90 ? '#28a745' : ($rate >= 75 ? '#f0ad4e' : '#dc3545');
        @endphp
        <div style="font-size:2rem;font-weight:700;color:{{ $rateColor }};">{{ $rate }}%</div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">My Attendance</div>
    </div>
    {{-- Dept Payroll Cost --}}
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        <div style="font-size:1.35rem;font-weight:700;color:var(--accent);">NLE {{ number_format($deptPayrollCost, 0) }}</div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">Dept Payroll Cost</div>
    </div>
    {{-- Team Avg Performance --}}
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        @php
            $avgScore   = round((float)($teamPerformance?->avg_score ?? 0), 1);
            $reviewCount = (int)($teamPerformance?->review_count ?? 0);
            $scoreColor = $avgScore >= 7.5 ? '#22c55e' : ($avgScore >= 5 ? '#f59e0b' : '#ef4444');
        @endphp
        <div style="font-size:2rem;font-weight:700;color:{{ $avgScore > 0 ? $scoreColor : 'var(--text-muted)' }};">
            {{ $avgScore > 0 ? $avgScore : '—' }}
        </div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">
            Team Avg Score
            @if($reviewCount > 0)
                <span style="display:block;font-size:.7rem;">({{ $reviewCount }} reviews)</span>
            @endif
        </div>
    </div>
</div>

{{-- On Leave Today --}}
@if($onLeaveToday->isNotEmpty())
<div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.35);border-radius:8px;padding:.65rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" style="flex-shrink:0"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M9 14l2 2 4-4"/></svg>
    <span style="font-size:.82rem;color:#f59e0b;font-weight:600;">On approved leave today:</span>
    @foreach($onLeaveToday as $lr)
        <span style="font-size:.8rem;background:rgba(245,158,11,.15);border-radius:99px;padding:.15rem .6rem;color:var(--text-secondary);">{{ $lr->employee->user->name }}</span>
    @endforeach
</div>
@endif

{{-- Pending Leave Requests + Pending Overtime Requests --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">

    {{-- Pending Leave Requests --}}
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 class="card-title">Pending Leave Requests</h3>
            <a href="{{ route('leaves.index', ['status'=>'pending']) }}" style="font-size:.8rem;color:var(--accent);">View all</a>
        </div>
        @if($pendingLeaveRequests->isEmpty())
            <div class="empty-state"><p>No pending leave requests.</p></div>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingLeaveRequests as $leave)
                        <tr>
                            <td style="font-weight:600;font-size:.85rem;">{{ $leave->employee->user->name }}</td>
                            <td style="font-size:.8rem;">{{ $leave->leaveType->name }}</td>
                            <td style="font-size:.75rem;color:var(--text-muted);">
                                {{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M') }}
                            </td>
                            <td style="font-size:.85rem;text-align:center;">{{ $leave->total_days }}</td>
                            <td>
                                <div style="display:flex;gap:.4rem;">
                                    <form method="POST" action="{{ route('leaves.approve', $leave) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm" style="background:#28a74520;color:#28a745;border:none;padding:.2rem .6rem;font-size:.75rem;cursor:pointer;">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('leaves.reject', $leave) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm" style="background:#dc354520;color:#dc3545;border:none;padding:.2rem .6rem;font-size:.75rem;cursor:pointer;">Reject</button>
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

    {{-- Pending Overtime Requests --}}
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 class="card-title">Pending Overtime</h3>
            <a href="{{ route('overtime.index', ['status'=>'pending']) }}" style="font-size:.8rem;color:var(--accent);">View all</a>
        </div>
        @if($pendingOvertimeRequests->isEmpty())
            <div class="empty-state"><p>No pending overtime requests.</p></div>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Hours</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingOvertimeRequests as $ot)
                        <tr>
                            <td style="font-weight:600;font-size:.85rem;">{{ $ot->employee->user->name }}</td>
                            <td style="font-size:.8rem;">{{ \Carbon\Carbon::parse($ot->date)->format('d M Y') }}</td>
                            <td style="font-size:.85rem;text-align:center;">{{ $ot->hours }}h</td>
                            <td style="font-size:.85rem;">NLE {{ number_format($ot->amount, 2) }}</td>
                            <td>
                                <div style="display:flex;gap:.4rem;">
                                    <form method="POST" action="{{ route('overtime.approve', $ot) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm" style="background:#28a74520;color:#28a745;border:none;padding:.2rem .6rem;font-size:.75rem;cursor:pointer;">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('overtime.reject', $ot) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm" style="background:#dc354520;color:#dc3545;border:none;padding:.2rem .6rem;font-size:.75rem;cursor:pointer;">Reject</button>
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

{{-- Team Attendance Trend + Today's Roster --}}
<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">

    {{-- Team Attendance Trend --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Team Attendance Trend (6 Months)</h3>
        </div>
        <canvas id="teamAttendanceChart" height="110"></canvas>
    </div>

    {{-- Today's Roster --}}
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 class="card-title">Today's Roster</h3>
            <span class="badge badge-info">{{ $teamSize }} active</span>
        </div>
        @if($todayRoster->isEmpty())
            <div class="empty-state"><p>No team members found.</p></div>
        @else
            <div style="display:flex;flex-direction:column;gap:0;max-height:280px;overflow-y:auto;">
                @foreach($todayRoster as $row)
                @php
                    $s = $row['status'];
                    $statusLabel = match($s) {
                        'present'      => 'Present',
                        'absent'       => 'Absent',
                        'on-leave'     => 'On Leave',
                        default        => 'Not Recorded',
                    };
                    $statusColor = match($s) {
                        'present'      => '#22c55e',
                        'absent'       => '#ef4444',
                        'on-leave'     => '#f59e0b',
                        default        => 'var(--text-muted)',
                    };
                    $statusBg = match($s) {
                        'present'      => 'rgba(34,197,94,.12)',
                        'absent'       => 'rgba(239,68,68,.12)',
                        'on-leave'     => 'rgba(245,158,11,.15)',
                        default        => 'rgba(100,116,139,.1)',
                    };
                @endphp
                <div style="display:flex;align-items:center;gap:.75rem;padding:.45rem .25rem;border-bottom:1px solid var(--border-color);">
                    <div style="width:32px;height:32px;border-radius:50%;background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0;">
                        {{ strtoupper(substr($row['member']->user->name, 0, 1)) }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;font-size:.83rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">{{ $row['member']->user->name }}</div>
                        <div style="font-size:.72rem;color:var(--text-muted);">{{ $row['member']->position }}</div>
                    </div>
                    <span style="font-size:.68rem;font-weight:600;padding:.15rem .5rem;border-radius:99px;background:{{ $statusBg }};color:{{ $statusColor }};white-space:nowrap;flex-shrink:0;">
                        {{ $statusLabel }}
                    </span>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Department Leave Calendar --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
        <h3 class="card-title">Department Leave Calendar — {{ $now->format('F Y') }}</h3>
        <span style="font-size:.78rem;color:var(--text-muted);">Approved leaves only</span>
    </div>
    @php
        $calStart    = $now->copy()->startOfMonth();
        $calEnd      = $now->copy()->endOfMonth();
        $daysInMonth = $calEnd->day;
        // Monday-based offset: Mon=0 … Sun=6
        $startOffset = ($calStart->dayOfWeek === 0 ? 6 : $calStart->dayOfWeek - 1);

        // Build day-number => [name, ...] map
        $leavesByDay = [];
        foreach ($monthLeaves as $lr) {
            $s = $lr->start_date->lt($calStart) ? $calStart->copy() : $lr->start_date->copy();
            $e = $lr->end_date->gt($calEnd)     ? $calEnd->copy()   : $lr->end_date->copy();
            for ($d = $s->copy(); $d->lte($e); $d->addDay()) {
                $leavesByDay[$d->day][] = $lr->employee->user->name ?? '?';
            }
        }
    @endphp

    {{-- Day-of-week headers --}}
    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;margin-bottom:3px;">
        @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dh)
            <div style="text-align:center;font-size:.68rem;font-weight:700;color:var(--text-muted);padding:.3rem 0;letter-spacing:.03em;">{{ $dh }}</div>
        @endforeach
    </div>

    {{-- Calendar grid --}}
    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;">
        {{-- Leading empty cells --}}
        @for($i = 0; $i < $startOffset; $i++)
            <div></div>
        @endfor

        {{-- Day cells --}}
        @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $isToday  = ($day === $now->day);
                $names    = $leavesByDay[$day] ?? [];
                $hasLeave = count($names) > 0;
                $cellBg   = $isToday ? 'rgba(14,165,233,.12)' : ($hasLeave ? 'rgba(245,158,11,.07)' : 'transparent');
                $cellBd   = $isToday ? 'var(--accent)' : 'var(--border-color)';
                $dayColor = $isToday ? 'var(--accent)' : 'var(--text-secondary)';
            @endphp
            <div style="min-height:54px;border-radius:5px;padding:.3rem .3rem .2rem;background:{{ $cellBg }};border:1px solid {{ $cellBd }};">
                <div style="font-size:.72rem;font-weight:{{ $isToday ? '700' : '500' }};color:{{ $dayColor }};margin-bottom:.18rem;line-height:1;">{{ $day }}</div>
                @foreach($names as $name)
                    <div style="font-size:.6rem;background:rgba(245,158,11,.22);color:#92400e;border-radius:3px;padding:.08rem .22rem;margin-bottom:.1rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;line-height:1.3;" title="{{ $name }}">
                        {{ \Illuminate\Support\Str::limit($name, 9, '…') }}
                    </div>
                @endforeach
            </div>
        @endfor
    </div>

    @if($monthLeaves->isEmpty())
        <p style="font-size:.82rem;color:var(--text-muted);text-align:center;padding:.75rem 0;">No approved leaves this month.</p>
    @endif
</div>

{{-- My Own Stats (quick row) --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">My Quick Actions</h3>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.75rem;">
        <a href="{{ route('leaves.create') }}" class="card" style="text-align:center;padding:1rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:.5rem;border:1px solid var(--border-color);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--accent)" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M9 14l2 2 4-4"/></svg>
            <span style="font-size:.8rem;font-weight:600;">Apply Leave</span>
        </a>
        <a href="{{ route('payslips.index') }}" class="card" style="text-align:center;padding:1rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:.5rem;border:1px solid var(--border-color);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--accent)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span style="font-size:.8rem;font-weight:600;">My Payslips</span>
        </a>
        <a href="{{ route('attendance.index') }}" class="card" style="text-align:center;padding:1rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:.5rem;border:1px solid var(--border-color);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--accent)" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span style="font-size:.8rem;font-weight:600;">Attendance</span>
        </a>
        <a href="{{ route('leaves.index') }}" class="card" style="text-align:center;padding:1rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:.5rem;border:1px solid var(--border-color);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--accent)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <span style="font-size:.8rem;font-weight:600;">Team Leaves</span>
        </a>
        <a href="{{ route('overtime.index') }}" class="card" style="text-align:center;padding:1rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:.5rem;border:1px solid var(--border-color);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--accent)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span style="font-size:.8rem;font-weight:600;">Team Overtime</span>
        </a>
        <a href="{{ route('performance.index') }}" class="card" style="text-align:center;padding:1rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:.5rem;border:1px solid var(--border-color);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--accent)" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            <span style="font-size:.8rem;font-weight:600;">Reviews</span>
        </a>
        <a href="{{ route('performance.create') }}" class="card" style="text-align:center;padding:1rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:.5rem;border:1px solid var(--border-color);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--accent)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            <span style="font-size:.8rem;font-weight:600;">New Review</span>
        </a>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const trend = @json($teamAttendanceTrend);
    const ctx = document.getElementById('teamAttendanceChart')?.getContext('2d');
    if (!ctx || !trend.length) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: trend.map(d => d.label),
            datasets: [
                {
                    label: 'Present',
                    data: trend.map(d => d.present),
                    backgroundColor: 'rgba(40,167,69,.7)',
                    borderRadius: 4,
                },
                {
                    label: 'Absent',
                    data: trend.map(d => d.absent),
                    backgroundColor: 'rgba(220,53,69,.5)',
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
})();
</script>
@endpush

@endsection
