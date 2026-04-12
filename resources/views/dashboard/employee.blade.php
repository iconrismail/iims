@extends('layouts.app')

@section('title', 'My Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumbs')
    <span>Dashboard</span>
@endsection

@section('content')
@if(!$employee)
    <div class="alert alert-info">
        Your employee profile has not been set up yet. Please contact an administrator.
    </div>
@else
@php
    $hour     = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $present  = $attendanceSummary['present'] ?? 0;
    $absent   = $attendanceSummary['absent']  ?? 0;
    $total    = $present + $absent;
    $rate     = $total > 0 ? round($present / $total * 100) : 100;
    $rateColor= $rate >= 90 ? '#00e676' : ($rate >= 75 ? '#ffab00' : '#ff5252');
    $tenure   = $employee->hire_date->diff(now());
    $tenureStr= ($tenure->y > 0 ? $tenure->y . ' yr' . ($tenure->y > 1 ? 's' : '') . ' ' : '')
              . ($tenure->m > 0 ? $tenure->m . ' mo' : ($tenure->y === 0 ? '< 1 mo' : ''));
@endphp

    {{-- ── Document Expiry Alert ──────────────────────────────── --}}
    @if($expiringDocs->count())
        <div style="background:rgba(255,171,0,0.12);border:1px solid #ffab00;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:0.75rem">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffab00" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div style="font-size:0.85rem">
                <strong style="color:#ffab00">Document expiry alert:</strong>
                @foreach($expiringDocs as $doc)
                    <span style="margin-left:0.5rem">{{ $doc->title }} expires <strong>{{ $doc->expires_at->format('d M Y') }}</strong>{{ !$loop->last ? ' ·' : '' }}</span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Greeting Banner ─────────────────────────────────────── --}}
    <div style="background:linear-gradient(135deg,var(--card-bg) 0%,rgba(108,99,255,0.08) 100%);border:1px solid var(--border-color);border-radius:12px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem">
        <div>
            <div style="font-size:1.1rem;font-weight:700;color:var(--text-primary)">{{ $greeting }}, {{ auth()->user()->name }} 👋</div>
            <div style="font-size:0.82rem;color:var(--text-muted);margin-top:3px">
                {{ $employee->position }} &nbsp;·&nbsp; {{ $employee->department->name ?? 'N/A' }} &nbsp;·&nbsp; {{ now()->format('l, d F Y') }}
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:0.5rem">
            <span class="badge {{ $employee->status === 'active' ? 'badge-success' : 'badge-danger' }}" style="font-size:0.78rem;padding:4px 12px">
                {{ ucfirst($employee->status) }}
            </span>
            <span style="font-size:0.78rem;color:var(--text-muted);background:var(--input-bg);padding:4px 10px;border-radius:20px;border:1px solid var(--border-color)">
                {{ $employee->employee_id }}
            </span>
        </div>
    </div>

    {{-- ── Stat Cards ───────────────────────────────────────────── --}}
    <div class="stats-grid">

        {{-- Attendance Rate --}}
        <div class="stat-card">
            <div style="position:relative;width:48px;height:48px;flex-shrink:0">
                <svg viewBox="0 0 36 36" style="width:48px;height:48px;transform:rotate(-90deg)">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="3"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="{{ $rateColor }}" stroke-width="3"
                        stroke-dasharray="{{ round($rate * 100 / 100) }} 100"
                        stroke-linecap="round"/>
                </svg>
                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:0.6rem;font-weight:700;color:{{ $rateColor }}">{{ $rate }}%</div>
            </div>
            <div class="stat-info">
                <h3 style="color:{{ $rateColor }}">{{ $rate }}%</h3>
                <div class="stat-label">Attendance Rate</div>
            </div>
        </div>

        {{-- Present This Month --}}
        <div class="stat-card">
            <div class="stat-icon green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $present }}</h3>
                <div class="stat-label">Present This Month</div>
            </div>
        </div>

        {{-- Absent This Month --}}
        <div class="stat-card">
            <div class="stat-icon red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $absent }}</h3>
                <div class="stat-label">Absent This Month</div>
            </div>
        </div>

        {{-- Net Salary --}}
        <div class="stat-card">
            <div class="stat-icon orange">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ number_format($employee->netSalary(), 0) }}</h3>
                <div class="stat-label">Net Salary (NLE)</div>
            </div>
        </div>

        {{-- YTD Salary --}}
        <div class="stat-card">
            <div class="stat-icon emerald">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ number_format($ytdSalary, 0) }}</h3>
                <div class="stat-label">YTD Earnings (NLE)</div>
            </div>
        </div>

        {{-- Leave Taken --}}
        <div class="stat-card">
            <div class="stat-icon teal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M9 14l2 2 4-4"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $leaveSummary['taken_days'] }}</h3>
                <div class="stat-label">Leave Days Taken ({{ now()->year }})</div>
            </div>
            @if($leaveSummary['pending'] > 0)
                <span class="stat-action-badge">{{ $leaveSummary['pending'] }} pending</span>
            @endif
        </div>

        {{-- Tenure --}}
        <div class="stat-card">
            <div class="stat-icon purple">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $tenureStr ?: '< 1 mo' }}</h3>
                <div class="stat-label">Years of Service</div>
            </div>
        </div>

        {{-- Bonuses --}}
        <div class="stat-card">
            <div class="stat-icon yellow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $bonusCount > 0 ? 'NLE ' . number_format($bonusTotalThisYear, 0) : '—' }}</h3>
                <div class="stat-label">Bonuses This Year</div>
            </div>
        </div>
    </div>

    {{-- ── Leave Balance Summary ───────────────────────────────── --}}
    @if($leaveBalances->isNotEmpty())
    <div class="card" style="margin-top:1.5rem">
        <div class="card-header">
            <h3 class="card-title">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M9 14l2 2 4-4"/></svg>
                Leave Balances ({{ now()->year }})
            </h3>
            <a href="{{ route('leaves.create') }}" class="btn btn-primary btn-sm">Apply Leave</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:0.75rem;">
            @foreach($leaveBalances as $bal)
            @php
                $total = $bal->entitled_days + $bal->carried_forward;
                $pct   = $total > 0 ? round($bal->remaining() / $total * 100) : 0;
                $barColor = $pct > 50 ? '#22c55e' : ($pct > 20 ? '#f59e0b' : '#ef4444');
            @endphp
            <div style="padding:0.85rem;background:var(--input-bg);border-radius:8px;border:1px solid var(--border-color);">
                <div style="font-size:0.78rem;font-weight:600;color:var(--text-secondary);margin-bottom:0.5rem;">
                    {{ $bal->leaveType->name }}
                    @if(!$bal->leaveType->is_paid)
                        <span style="font-size:0.68rem;color:var(--text-muted);font-weight:400;"> (Unpaid)</span>
                    @endif
                </div>
                <div style="display:flex;align-items:baseline;gap:0.3rem;margin-bottom:0.4rem;">
                    <span style="font-size:1.4rem;font-weight:800;color:{{ $barColor }};">{{ $bal->remaining() }}</span>
                    <span style="font-size:0.75rem;color:var(--text-muted);">/ {{ $total }} days left</span>
                </div>
                <div style="height:5px;background:var(--border-color);border-radius:3px;overflow:hidden;">
                    <div style="height:100%;width:{{ $pct }}%;background:{{ $barColor }};border-radius:3px;transition:width .4s;"></div>
                </div>
                <div style="font-size:0.68rem;color:var(--text-muted);margin-top:0.35rem;">
                    {{ $bal->used_days }} used
                    @if($bal->carried_forward > 0)
                        · {{ $bal->carried_forward }} carried fwd
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Row 1: Salary Breakdown + Quick Actions ──────────────── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-top:1.5rem">

        {{-- Salary Breakdown --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Salary Breakdown</h3>
                @if($currentPayroll)
                    <span style="font-size:0.75rem;color:var(--text-muted)">{{ $currentPayroll->periodLabel() }}</span>
                @endif
            </div>
            @php
                $gross    = (float)$employee->basic_salary + (float)$employee->allowances;
                $ded      = (float)$employee->deductions;
                $net      = $employee->netSalary();
                $barTotal = $gross > 0 ? $gross : 1;
            @endphp
            <div style="display:flex;flex-direction:column;gap:0.85rem">
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:0.82rem;margin-bottom:4px">
                        <span style="color:var(--text-secondary)">Basic Salary</span>
                        <span style="font-weight:600">NLE {{ number_format($employee->basic_salary, 2) }}</span>
                    </div>
                    <div style="height:6px;background:var(--input-bg);border-radius:3px;overflow:hidden">
                        <div style="height:100%;width:{{ round($employee->basic_salary / $barTotal * 100) }}%;background:#6c63ff;border-radius:3px"></div>
                    </div>
                </div>
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:0.82rem;margin-bottom:4px">
                        <span style="color:var(--text-secondary)">Allowances</span>
                        <span style="font-weight:600;color:#00e676">+ NLE {{ number_format($employee->allowances, 2) }}</span>
                    </div>
                    <div style="height:6px;background:var(--input-bg);border-radius:3px;overflow:hidden">
                        <div style="height:100%;width:{{ round($employee->allowances / $barTotal * 100) }}%;background:#00e676;border-radius:3px"></div>
                    </div>
                </div>
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:0.82rem;margin-bottom:4px">
                        <span style="color:var(--text-secondary)">Deductions</span>
                        <span style="font-weight:600;color:#ff5252">− NLE {{ number_format($ded, 2) }}</span>
                    </div>
                    <div style="height:6px;background:var(--input-bg);border-radius:3px;overflow:hidden">
                        <div style="height:100%;width:{{ round($ded / $barTotal * 100) }}%;background:#ff5252;border-radius:3px"></div>
                    </div>
                </div>
                <div style="border-top:1px solid var(--border-color);padding-top:0.75rem;display:flex;justify-content:space-between;align-items:center">
                    <span style="font-size:0.82rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.4px">Net Salary</span>
                    <span style="font-size:1.3rem;font-weight:700;color:var(--text-primary)">NLE {{ number_format($net, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem">
                <a href="{{ route('leaves.create') }}" class="btn btn-secondary" style="display:flex;align-items:center;gap:0.5rem;justify-content:center;font-size:0.83rem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M9 14l2 2 4-4"/></svg>
                    Apply for Leave
                </a>
                <a href="{{ route('payslips.index') }}" class="btn btn-secondary" style="display:flex;align-items:center;gap:0.5rem;justify-content:center;font-size:0.83rem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    My Payslips
                </a>
                <a href="{{ route('attendance.index') }}" class="btn btn-secondary" style="display:flex;align-items:center;gap:0.5rem;justify-content:center;font-size:0.83rem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    My Attendance
                </a>
                <a href="{{ route('performance.index') }}" class="btn btn-secondary" style="display:flex;align-items:center;gap:0.5rem;justify-content:center;font-size:0.83rem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg>
                    My Performance
                </a>
                <a href="{{ route('leaves.index') }}" class="btn btn-secondary" style="display:flex;align-items:center;gap:0.5rem;justify-content:center;font-size:0.83rem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    My Leave History
                </a>
                <a href="{{ route('notifications.index') }}" class="btn btn-secondary" style="display:flex;align-items:center;gap:0.5rem;justify-content:center;font-size:0.83rem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    Notifications
                </a>
                <a href="{{ route('profile-updates.create') }}" class="btn btn-secondary" style="display:flex;align-items:center;gap:0.5rem;justify-content:center;font-size:0.83rem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M16 3.13a4 4 0 0 1 0 7.75" style="display:none"/></svg>
                    Update Profile
                </a>
            </div>
        </div>
    </div>

    {{-- ── Row 2: Leave Requests + Latest Performance Review ─────── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-top:1.5rem">

        {{-- My Recent Leave Requests --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">My Leave Requests</h3>
                <a href="{{ route('leaves.create') }}" class="btn btn-sm btn-primary">+ Apply</a>
            </div>
            @forelse($recentLeaves as $leave)
                <div style="display:flex;align-items:center;gap:0.75rem;padding:0.6rem 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border-color)' : '' }}">
                    <div style="flex:1;min-width:0">
                        <div style="font-size:0.83rem;font-weight:600;color:var(--text-primary)">{{ $leave->leaveType->name ?? 'Leave' }}</div>
                        <div style="font-size:0.75rem;color:var(--text-muted)">
                            {{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M Y') }}
                            &nbsp;·&nbsp; {{ $leave->total_days }} day{{ $leave->total_days != 1 ? 's' : '' }}
                        </div>
                    </div>
                    @if($leave->status === 'approved')
                        <span class="badge badge-success">Approved</span>
                    @elseif($leave->status === 'pending')
                        <span class="badge badge-warning">Pending</span>
                    @else
                        <span class="badge badge-danger">Rejected</span>
                    @endif
                </div>
            @empty
                <div class="empty-state" style="padding:1.5rem 0">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="8" y="12" width="48" height="40" rx="4" stroke="currentColor" stroke-width="2"/>
                        <line x1="8" y1="24" x2="56" y2="24" stroke="currentColor" stroke-width="2"/>
                        <path d="M22 38l7 7 13-13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p>No leave requests yet.</p>
                    <a href="{{ route('leaves.create') }}" class="btn btn-primary btn-sm mt-2">Apply Now</a>
                </div>
            @endforelse
        </div>

        {{-- Latest Performance Review --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Latest Performance Review</h3>
                <a href="{{ route('performance.index') }}" class="btn btn-sm btn-secondary">View All</a>
            </div>
            @if($latestReview)
                @php
                    $score     = (float) $latestReview->overall_score;
                    $scoreColor= $score >= 80 ? '#00e676' : ($score >= 60 ? '#ffab00' : '#ff5252');
                    $scoreLabel= $score >= 80 ? 'Excellent' : ($score >= 60 ? 'Good' : 'Needs Improvement');
                    $circumf   = 2 * M_PI * 30;
                    $dash      = round($score / 100 * $circumf, 2);
                @endphp
                <div style="display:flex;align-items:center;gap:1.5rem;padding:0.5rem 0">
                    {{-- Score ring --}}
                    <div style="position:relative;flex-shrink:0">
                        <svg width="80" height="80" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="30" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="6"/>
                            <circle cx="40" cy="40" r="30" fill="none" stroke="{{ $scoreColor }}" stroke-width="6"
                                stroke-dasharray="{{ $dash }} {{ $circumf }}"
                                stroke-linecap="round" transform="rotate(-90 40 40)"/>
                        </svg>
                        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;line-height:1.1">
                            <span style="font-size:1.1rem;font-weight:700;color:{{ $scoreColor }}">{{ number_format($score, 0) }}</span>
                            <span style="font-size:0.6rem;color:var(--text-muted)">/ 100</span>
                        </div>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:1rem;font-weight:700;color:{{ $scoreColor }}">{{ $scoreLabel }}</div>
                        <div style="font-size:0.8rem;color:var(--text-secondary);margin-top:3px">
                            {{ ucfirst($latestReview->review_period) }} {{ $latestReview->period_year }}
                        </div>
                        <div style="margin-top:6px">
                            @if($latestReview->status === 'acknowledged')
                                <span class="badge badge-success">Acknowledged</span>
                            @else
                                <span class="badge badge-info">Submitted</span>
                            @endif
                        </div>
                        @if($latestReview->salary_increment_pct > 0)
                            <div style="font-size:0.78rem;color:#00e676;margin-top:6px">
                                ↑ {{ number_format($latestReview->salary_increment_pct, 1) }}% salary increment
                            </div>
                        @endif
                        @if($latestReview->comments)
                            <div style="font-size:0.75rem;color:var(--text-muted);margin-top:6px;font-style:italic;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">
                                "{{ $latestReview->comments }}"
                            </div>
                        @endif
                    </div>
                </div>
                <div style="margin-top:0.75rem">
                    <a href="{{ route('performance.show', $latestReview) }}" class="btn btn-sm btn-secondary" style="width:100%;justify-content:center">
                        View Full Review
                    </a>
                </div>
            @else
                <div class="empty-state" style="padding:1.5rem 0">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <polyline points="8 48 24 32 36 44 56 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p>No performance review submitted yet.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Row 3: Attendance Trend + Salary Trend ───────────────── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-top:1.5rem">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attendance Trend</h3>
                <span style="font-size:0.75rem;color:var(--text-muted)">Last 6 months</span>
            </div>
            <canvas id="empAttendanceChart" height="130"></canvas>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Salary Trend</h3>
                <span style="font-size:0.75rem;color:var(--text-muted)">Last 6 payslips</span>
            </div>
            @if($salaryTrend->count())
                <canvas id="empSalaryChart" height="130"></canvas>
            @else
                <div class="empty-state" style="padding:1.5rem 0">
                    <p>No payslip data yet.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Row 4: Profile Summary + Recent Payslips ─────────────── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-top:1.5rem">

        {{-- Profile Summary --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">My Profile</h3>
            </div>
            <div>
                @php $rows = [
                    ['Name',       auth()->user()->name],
                    ['Email',      auth()->user()->email],
                    ['Department', $employee->department->name ?? 'N/A'],
                    ['Position',   $employee->position],
                    ['Hire Date',  $employee->hire_date->format('d M Y')],
                    ['Phone',      $employee->phone ?? '—'],
                ]; @endphp
                @foreach($rows as [$label, $value])
                    <div class="payslip-row">
                        <span class="label">{{ $label }}</span>
                        <span class="value">{{ $value }}</span>
                    </div>
                @endforeach
                <div class="payslip-row">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="badge {{ $employee->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                            {{ ucfirst($employee->status) }}
                        </span>
                    </span>
                </div>
                <div class="payslip-row" style="border-bottom:none">
                    <span class="label">Service</span>
                    <span class="value" style="color:var(--accent)">{{ $tenureStr ?: '< 1 month' }}</span>
                </div>
            </div>
        </div>

        {{-- Recent Payslips --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Payslips</h3>
                <a href="{{ route('payslips.index') }}" class="btn btn-sm btn-secondary">View All</a>
            </div>
            @if($recentPayslips->count())
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Net (NLE)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPayslips as $slip)
                                <tr>
                                    <td>{{ $slip->payroll->periodLabel() }}</td>
                                    <td class="text-accent font-bold">{{ number_format($slip->net_salary, 2) }}</td>
                                    <td>
                                        <div style="display:flex;gap:0.4rem">
                                            <a href="{{ route('payslips.show', $slip) }}" class="btn btn-sm btn-secondary">View</a>
                                            <a href="{{ route('payslips.download', $slip) }}" class="btn btn-sm btn-secondary" title="Download PDF">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <p>No payslips available yet.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Unread Notifications Feed ────────────────────────────── --}}
    @php
        $allUnread  = auth()->user()->unreadNotifications()->latest()->take(5)->get();
        $dashNotifs = $allUnread->take(3);
    @endphp
    <div class="card" style="margin-top:1.5rem">
        <div class="card-header">
            <h3 class="card-title" style="display:flex;align-items:center;gap:0.5rem">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Notifications
                @if($allUnread->count())
                    <span style="background:#ff5252;color:#fff;font-size:0.68rem;font-weight:700;padding:1px 7px;border-radius:20px;line-height:1.6">
                        {{ $allUnread->count() }}
                    </span>
                @endif
            </h3>
            <div style="display:flex;align-items:center;gap:0.75rem">
                @if($allUnread->count())
                    <form action="{{ route('notifications.readAll') }}" method="POST" style="margin:0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-secondary">Mark all read</button>
                    </form>
                @endif
                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-secondary">View All</a>
            </div>
        </div>

        @if($dashNotifs->isEmpty())
            <div style="display:flex;align-items:center;gap:0.75rem;padding:1.25rem 0;color:var(--text-muted);font-size:0.85rem">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                All caught up — no unread notifications.
            </div>
        @else
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem;padding:0.25rem 0">
                @foreach($dashNotifs as $notif)
                    @php
                        $nUrl   = $notif->data['url']   ?? route('notifications.index');
                        $nIcon  = $notif->data['icon']  ?? 'info';
                        $nTitle = $notif->data['title'] ?? 'Notification';
                        $nMsg   = \Illuminate\Support\Str::limit($notif->data['message'] ?? '', 80);
                        $dotColor = match($nIcon) {
                            'success' => '#00e676',
                            'danger'  => '#ff5252',
                            'warning' => '#ffab00',
                            default   => '#6c63ff',
                        };
                        $bgColor = match($nIcon) {
                            'success' => 'rgba(0,230,118,0.08)',
                            'danger'  => 'rgba(255,82,82,0.08)',
                            'warning' => 'rgba(255,171,0,0.08)',
                            default   => 'rgba(108,99,255,0.08)',
                        };
                    @endphp
                    <a href="{{ route('notifications.read', $notif->id) }}?redirect={{ urlencode($nUrl) }}"
                       style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.85rem 1rem;background:{{ $bgColor }};border:1px solid {{ $dotColor }}33;border-radius:8px;text-decoration:none;transition:opacity 0.15s"
                       onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                        <span style="width:32px;height:32px;border-radius:50%;background:{{ $dotColor }}22;border:1px solid {{ $dotColor }}55;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px">
                            @if($nIcon === 'success')
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="{{ $dotColor }}" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            @elseif($nIcon === 'danger')
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="{{ $dotColor }}" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            @elseif($nIcon === 'warning')
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="{{ $dotColor }}" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            @else
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="{{ $dotColor }}" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            @endif
                        </span>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:0.83rem;font-weight:600;color:var(--text-primary);margin-bottom:2px">{{ $nTitle }}</div>
                            <div style="font-size:0.77rem;color:var(--text-secondary);line-height:1.4">{{ $nMsg }}</div>
                            <div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px">{{ $notif->created_at->diffForHumans() }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

@endif
@endsection

@push('scripts')
@if($employee)
<script>
const empAttendanceData = @json($attendanceTrend);
const empSalaryData     = @json($salaryTrend);

const chartScales = {
    x: { ticks: { color: '#aaa', font: { size: 11 } }, grid: { color: 'rgba(255,255,255,0.05)' } },
    y: { ticks: { color: '#aaa', font: { size: 11 } }, grid: { color: 'rgba(255,255,255,0.05)' } }
};

// Attendance Trend
if (document.getElementById('empAttendanceChart')) {
    new Chart(document.getElementById('empAttendanceChart'), {
        type: 'bar',
        data: {
            labels: empAttendanceData.map(d => d.label),
            datasets: [
                { label: 'Present', data: empAttendanceData.map(d => d.present), backgroundColor: 'rgba(0,230,118,0.7)', borderRadius: 4 },
                { label: 'Absent',  data: empAttendanceData.map(d => d.absent),  backgroundColor: 'rgba(255,82,82,0.7)',  borderRadius: 4 }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#aaa', font: { size: 11 } } } },
            scales: chartScales
        }
    });
}

// Salary Trend
if (empSalaryData.length && document.getElementById('empSalaryChart')) {
    new Chart(document.getElementById('empSalaryChart'), {
        type: 'line',
        data: {
            labels: empSalaryData.map(d => d.label),
            datasets: [{
                label: 'Net Salary (NLE)',
                data: empSalaryData.map(d => d.net),
                borderColor: '#6c63ff',
                backgroundColor: 'rgba(108,99,255,0.12)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#6c63ff',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#aaa', font: { size: 11 } } } },
            scales: chartScales
        }
    });
}

// Counter animation
(function () {
    function animateCounter(el) {
        const raw = el.textContent.replace(/[^0-9.]/g, '');
        const target = parseFloat(raw);
        if (isNaN(target) || target === 0) return;
        const isInt = Number.isInteger(target);
        el.dataset.original = el.textContent;
        const duration = 900;
        const startTime = performance.now();
        function step(now) {
            const progress = Math.min((now - startTime) / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 3);
            const val = ease * target;
            el.textContent = isInt ? Math.round(val).toLocaleString() : val.toLocaleString(undefined, {minimumFractionDigits:0, maximumFractionDigits:0});
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = el.dataset.original;
        }
        requestAnimationFrame(step);
    }
    document.querySelectorAll('.stat-info h3').forEach(animateCounter);
})();
</script>
@endif
@endpush
