@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumbs')
    <span>Dashboard</span>
@endsection

@section('content')
    {{-- Header row with PDF export --}}
    <div style="display:flex;justify-content:flex-end;margin-bottom:1rem;">
        <a href="{{ route('dashboard.exportPdf') }}" class="btn btn-secondary btn-sm" style="display:inline-flex;align-items:center;gap:0.4rem;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Report
        </a>
    </div>

    {{-- Stat Cards --}}
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
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $totalDepartments }}</h3>
                <div class="stat-label">Departments</div>
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
            <div class="stat-icon red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $todayAbsent }}</h3>
                <div class="stat-label">Absent Today</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon teal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01M12 14h.01M8 18h.01"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $onLeaveToday }}</h3>
                <div class="stat-label">On Leave Today</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon emerald">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ number_format($totalPayrollThisMonth) }}</h3>
                <div class="stat-label">Payroll This Month (NLE)</div>
            </div>
            @if($totalPayrollThisMonth > 0)
                <a href="{{ route('payroll.index') }}" class="stat-action-badge stat-action-badge-green">View</a>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-icon yellow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M9 14l2 2 4-4"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $pendingLeaves }}</h3>
                <div class="stat-label">Pending Leaves</div>
            </div>
            @if($pendingLeaves > 0)
                <a href="{{ route('leaves.index') }}" class="stat-action-badge">Review</a>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $pendingOvertimes }}</h3>
                <div class="stat-label">Pending Overtime</div>
            </div>
            @if($pendingOvertimes > 0)
                <a href="{{ route('overtime.index') }}" class="stat-action-badge">Review</a>
            @endif
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        {{-- Payroll Status --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Current Month Payroll</h3>
                <a href="{{ route('payroll.index') }}" class="btn btn-sm btn-secondary">View All</a>
            </div>

            @if($currentPayroll)
                <div style="text-align: center; padding: 1rem 0;">
                    <div style="font-size: 1.6rem; font-weight: 700; color: var(--text-primary)">
                        {{ $currentPayroll->periodLabel() }}
                    </div>
                    <div class="mt-1">
                        @if($currentPayroll->status === 'paid')
                            <span class="badge badge-success">Paid</span>
                        @elseif($currentPayroll->status === 'processed')
                            <span class="badge badge-info">Processed</span>
                        @else
                            <span class="badge badge-warning">Draft</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="8" y="12" width="48" height="40" rx="4" stroke="currentColor" stroke-width="2"/>
                        <line x1="8" y1="24" x2="56" y2="24" stroke="currentColor" stroke-width="2"/>
                        <line x1="20" y1="8" x2="20" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="44" y1="8" x2="44" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M32 36v-4M29 34h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <p>No payroll processed for this month yet.</p>
                    <a href="{{ route('payroll.create') }}" class="btn btn-primary btn-sm mt-2">Process Payroll</a>
                </div>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <a href="{{ route('employees.create') }}" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add New Employee
                </a>
                <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
                    Record Attendance
                </a>
                <a href="{{ route('payroll.create') }}" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Process Payroll
                </a>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;margin-top:1.5rem">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Payroll Trends</h3>
                <div style="display:flex;align-items:center;gap:0.5rem">
                    <select class="chart-range-select" id="payrollRange" onchange="applyPayrollRange(this.value)">
                        <option value="3">Last 3 months</option>
                        <option value="6" selected>Last 6 months</option>
                        <option value="12">Last 12 months</option>
                    </select>
                    <button class="chart-export-btn" onclick="exportChart('payrollChart','payroll-trend')" title="Export as PNG">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </button>
                </div>
            </div>
            <canvas id="payrollChart" height="120"></canvas>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Headcount by Dept</h3>
                <button class="chart-export-btn" onclick="exportChart('deptChart','dept-headcount')" title="Export as PNG">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </button>
            </div>
            <canvas id="deptChart" height="120"></canvas>
        </div>
    </div>
    <div style="margin-top:1.5rem">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attendance Trends</h3>
                <div style="display:flex;align-items:center;gap:0.5rem">
                    <select class="chart-range-select" id="attendanceRange" onchange="applyAttendanceRange(this.value)">
                        <option value="3">Last 3 months</option>
                        <option value="6" selected>Last 6 months</option>
                        <option value="12">Last 12 months</option>
                    </select>
                    <button class="chart-export-btn" onclick="exportChart('attendanceChart','attendance-trend')" title="Export as PNG">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </button>
                </div>
            </div>
            <canvas id="attendanceChart" height="80"></canvas>
        </div>
    </div>

    {{-- AI Row: Payroll Forecast + Attendance Insights --}}
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:1.5rem;margin-top:1.5rem">

        {{-- Payroll Forecast Widget --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    Payroll Forecast
                </h3>
                <span style="font-size:0.72rem;padding:0.15rem 0.5rem;border-radius:99px;background:{{ $payrollForecast['confidence']==='high'?'#22c55e22':($payrollForecast['confidence']==='medium'?'#f59e0b22':'#6b728022') }};color:{{ $payrollForecast['confidence']==='high'?'#22c55e':($payrollForecast['confidence']==='medium'?'#f59e0b':'#9ca3af') }}">
                    {{ ucfirst($payrollForecast['confidence']) }} confidence
                </span>
            </div>
            <div style="text-align:center;padding:1rem 0 0.5rem;">
                <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;">Projected Next Month</div>
                <div style="font-size:1.9rem;font-weight:800;color:var(--accent);margin:0.25rem 0;">
                    NLE {{ number_format($payrollForecast['total'], 0) }}
                </div>
                <div style="font-size:0.75rem;color:var(--text-muted);">{{ $payrollForecast['employees'] }} active employees</div>
            </div>
            <div style="border-top:1px solid var(--border-color);padding-top:0.75rem;margin-top:0.5rem;display:flex;flex-direction:column;gap:0.35rem;">
                <div style="display:flex;justify-content:space-between;font-size:0.78rem;">
                    <span style="color:var(--text-muted);">Base Salaries</span>
                    <span>NLE {{ number_format($payrollForecast['base'], 0) }}</span>
                </div>
                @if($payrollForecast['overtime'] > 0)
                <div style="display:flex;justify-content:space-between;font-size:0.78rem;">
                    <span style="color:var(--text-muted);">Avg Overtime</span>
                    <span style="color:#f59e0b;">+ NLE {{ number_format($payrollForecast['overtime'], 0) }}</span>
                </div>
                @endif
                @if($payrollForecast['bonus'] > 0)
                <div style="display:flex;justify-content:space-between;font-size:0.78rem;">
                    <span style="color:var(--text-muted);">Avg Bonuses</span>
                    <span style="color:#22c55e;">+ NLE {{ number_format($payrollForecast['bonus'], 0) }}</span>
                </div>
                @endif
            </div>
            @if(count($payrollForecast['history']) > 0)
            <div style="margin-top:0.75rem;border-top:1px solid var(--border-color);padding-top:0.75rem;">
                <div style="font-size:0.7rem;color:var(--text-muted);margin-bottom:0.4rem;text-transform:uppercase;">Last 3 Months</div>
                <div style="display:flex;gap:0.4rem;align-items:flex-end;height:36px;">
                    @php $maxHist = max(1, max(array_column($payrollForecast['history'], 'total'))); @endphp
                    @foreach($payrollForecast['history'] as $h)
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;">
                            <div title="{{ $h['label'] }}: NLE {{ number_format($h['total']) }}"
                                 style="height:{{ $h['total'] > 0 ? max(4, round(($h['total']/$maxHist)*28)) : 2 }}px;width:100%;background:var(--accent);border-radius:3px 3px 0 0;opacity:0.6;"></div>
                            <div style="font-size:0.6rem;color:var(--text-muted);white-space:nowrap;">{{ $h['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Attendance Insights Card --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Attendance Insights
                </h3>
                <span style="font-size:0.72rem;color:var(--text-muted);">AI-detected patterns</span>
            </div>
            @if(empty($attendanceInsights))
                <div class="empty-state" style="padding:1.5rem 0">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="32" cy="32" r="24" stroke="currentColor" stroke-width="2"/>
                        <path d="M22 32l7 7 13-13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p>No attendance anomalies detected.</p>
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                    @foreach($attendanceInsights as $insight)
                        @php
                            $ic = $insight['severity'] === 'warning' ? '#ef4444' : '#00e5ff';
                            $ib = $insight['severity'] === 'warning' ? '#ef444415' : '#00e5ff10';
                        @endphp
                        <div style="display:flex;align-items:flex-start;gap:0.65rem;padding:0.6rem 0.85rem;background:{{ $ib }};border-left:3px solid {{ $ic }};border-radius:0 6px 6px 0;">
                            @if($insight['severity'] === 'warning')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="{{ $ic }}" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            @else
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="{{ $ic }}" stroke-width="2" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            @endif
                            <span style="font-size:0.82rem;color:var(--text-secondary);line-height:1.45;">{{ $insight['message'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Expiring Contracts / Probation Banner --}}
    @if($expiringContracts->isNotEmpty())
    <div style="margin-top:1.5rem">
        <div class="card" style="border-left:4px solid #f59e0b">
            <div class="card-header">
                <h3 class="card-title" style="display:flex;align-items:center;gap:0.5rem">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Contracts / Probation Expiring Within 30 Days
                </h3>
                <span class="badge badge-warning">{{ $expiringContracts->count() }}</span>
            </div>
            <div class="table-wrapper" style="margin:0">
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Contract Type</th>
                            <th>Contract End</th>
                            <th>Probation End</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiringContracts as $emp)
                        <tr>
                            <td style="font-weight:600">{{ $emp->user?->name }}</td>
                            <td style="font-size:0.82rem;color:var(--text-muted)">{{ $emp->department?->name ?? '—' }}</td>
                            <td>
                                @if($emp->contract_type === 'permanent')
                                    <span class="badge badge-success">Permanent</span>
                                @elseif($emp->contract_type === 'fixed-term')
                                    <span class="badge badge-info">Fixed-Term</span>
                                @else
                                    <span class="badge badge-warning">Probation</span>
                                @endif
                            </td>
                            <td style="font-size:0.82rem">
                                @if($emp->contract_end_date)
                                    <span style="{{ $emp->isContractExpiringSoon() ? 'color:#f59e0b;font-weight:600' : '' }}">
                                        {{ $emp->contract_end_date->format('d M Y') }}
                                    </span>
                                    @if($emp->isContractExpiringSoon())
                                        <span style="font-size:0.72rem;color:var(--text-muted)"> ({{ $emp->contractDaysRemaining() }}d)</span>
                                    @endif
                                @else
                                    <span style="color:var(--text-muted)">—</span>
                                @endif
                            </td>
                            <td style="font-size:0.82rem">
                                @if($emp->probation_end_date)
                                    <span style="{{ $emp->isProbationExpiringSoon() ? 'color:#f59e0b;font-weight:600' : '' }}">
                                        {{ $emp->probation_end_date->format('d M Y') }}
                                    </span>
                                    @if($emp->isProbationExpiringSoon())
                                        <span style="font-size:0.72rem;color:var(--text-muted)"> ({{ $emp->probationDaysRemaining() }}d)</span>
                                    @endif
                                @else
                                    <span style="color:var(--text-muted)">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('employees.edit', $emp) }}" class="btn btn-sm btn-secondary">Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Upcoming Events + Activity Feed --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-top:1.5rem">

        {{-- #19 Upcoming Events Widget --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01M12 14h.01"/></svg>
                    Upcoming Events
                </h3>
                <span style="font-size:0.75rem;color:var(--text-muted)">Next 7 days</span>
            </div>

            @if($upcomingEvents->isEmpty())
                <div class="empty-state" style="padding:1.5rem 0">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="8" y="12" width="48" height="40" rx="4" stroke="currentColor" stroke-width="2"/>
                        <line x1="8" y1="24" x2="56" y2="24" stroke="currentColor" stroke-width="2"/>
                        <circle cx="32" cy="42" r="6" stroke="currentColor" stroke-width="2"/>
                        <path d="M32 38v4l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <p>No upcoming events in the next 7 days.</p>
                </div>
            @else
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:0.6rem">
                    @foreach($upcomingEvents as $event)
                        <li style="display:flex;align-items:center;gap:0.75rem;padding:0.55rem 0;border-bottom:1px solid var(--border-color)">
                            @if($event['type'] === 'birthday')
                                <span style="width:30px;height:30px;border-radius:50%;background:rgba(108,99,255,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6c63ff" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </span>
                                <div style="flex:1;min-width:0">
                                    <div style="font-weight:600;font-size:0.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $event['name'] }}</div>
                                    <div style="font-size:0.75rem;color:var(--text-muted)">Birthday — {{ \Carbon\Carbon::parse($event['date'])->format('d M') }}</div>
                                </div>
                                <span class="badge badge-info" style="flex-shrink:0">Birthday</span>
                            @elseif($event['type'] === 'anniversary')
                                <span style="width:30px;height:30px;border-radius:50%;background:rgba(0,230,118,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#00e676" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                </span>
                                <div style="flex:1;min-width:0">
                                    <div style="font-weight:600;font-size:0.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $event['name'] }}</div>
                                    <div style="font-size:0.75rem;color:var(--text-muted)">Work Anniversary — {{ $event['detail'] }} — {{ \Carbon\Carbon::parse($event['date'])->format('d M') }}</div>
                                </div>
                                <span class="badge badge-success" style="flex-shrink:0">Anniv.</span>
                            @else
                                <span style="width:30px;height:30px;border-radius:50%;background:rgba(255,171,0,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ffab00" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                </span>
                                <div style="flex:1;min-width:0">
                                    <div style="font-weight:600;font-size:0.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $event['name'] }}</div>
                                    <div style="font-size:0.75rem;color:var(--text-muted)">{{ $event['detail'] }} expires {{ \Carbon\Carbon::parse($event['date'])->format('d M') }}</div>
                                </div>
                                <span class="badge badge-warning" style="flex-shrink:0">Expiry</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- #20 Activity Feed --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Recent Activity
                </h3>
                <a href="{{ route('audit.index') }}" class="btn btn-sm btn-secondary">View All</a>
            </div>

            @if($recentActivity->isEmpty())
                <div class="empty-state" style="padding:1.5rem 0">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 52V20l20-8 20 8v32" stroke="currentColor" stroke-width="2"/>
                        <circle cx="32" cy="38" r="8" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <p>No recent activity recorded yet.</p>
                </div>
            @else
                <div style="position:relative;padding-left:1.25rem">
                    <div style="position:absolute;left:7px;top:6px;bottom:6px;width:2px;background:var(--border-color);border-radius:2px"></div>
                    @foreach($recentActivity as $activity)
                        @php
                            $eventColor = match($activity->event) {
                                'created' => '#00e676',
                                'updated' => '#6c63ff',
                                'deleted' => '#ff5252',
                                default   => '#aaa',
                            };
                        @endphp
                        <div style="position:relative;padding:0 0 0.85rem 1rem;margin-bottom:0">
                            <div style="position:absolute;left:-5px;top:4px;width:10px;height:10px;border-radius:50%;background:{{ $eventColor }};border:2px solid var(--card-bg)"></div>
                            <div style="font-size:0.82rem;font-weight:600;color:var(--text-primary);line-height:1.3">
                                {{ $activity->causer?->name ?? 'System' }}
                                <span style="font-weight:400;color:var(--text-secondary)">
                                    {{ $activity->event ?? 'logged' }}
                                    {{ class_basename($activity->subject_type ?? '') }}@if($activity->subject_id) #{{ $activity->subject_id }}@endif
                                </span>
                            </div>
                            <div style="font-size:0.72rem;color:var(--text-muted);margin-top:1px">
                                {{ $activity->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Pending Leave Requests --}}
    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Pending Leave Requests</h3>
            <a href="{{ route('leaves.index') }}" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Days</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingLeaveRequests as $leave)
                        <tr>
                            <td>{{ $leave->employee->user->name ?? '—' }}</td>
                            <td>{{ $leave->leaveType->name ?? '—' }}</td>
                            <td>{{ $leave->start_date->format('d M Y') }}</td>
                            <td>{{ $leave->end_date->format('d M Y') }}</td>
                            <td>{{ $leave->total_days }}</td>
                            <td>
                                <div class="btn-group">
                                    <form action="{{ route('leaves.approve', $leave) }}" method="POST" style="margin:0">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <form action="{{ route('leaves.reject', $leave) }}" method="POST" style="margin:0"
                                          data-confirm="Reject leave request for {{ $leave->employee->user->name ?? 'this employee' }}?">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="32" cy="32" r="24" stroke="currentColor" stroke-width="2"/>
                                        <path d="M22 32l7 7 13-13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <p>All caught up — no pending leave requests!</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Employees --}}
    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Recent Employees</h3>
            <a href="{{ route('employees.index') }}" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentEmployees as $emp)
                        <tr>
                            <td class="text-accent">{{ $emp->employee_id }}</td>
                            <td>{{ $emp->user->name }}</td>
                            <td>{{ $emp->department?->name ?? '—' }}</td>
                            <td>{{ $emp->position }}</td>
                            <td>
                                <span class="badge {{ $emp->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                    {{ ucfirst($emp->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="24" cy="20" r="10" stroke="currentColor" stroke-width="2"/>
                                        <path d="M4 52c0-11 9-18 20-18s20 7 20 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <line x1="44" y1="28" x2="60" y2="28" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <line x1="52" y1="20" x2="52" y2="36" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <p>No employees found.</p>
                                    <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm mt-2">Add Employee</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// ── Animated Stat Counters ───────────────────────────────
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

const payrollData    = @json($payrollChartData);
const deptData       = @json($deptData);
const attendanceData = @json($attendanceChartData);

function sliceLast(arr, n) { return arr.slice(-n); }

const chartScales = {
    x: { ticks: { color: '#aaa' }, grid: { color: 'rgba(255,255,255,0.05)' } },
    y: { ticks: { color: '#aaa' }, grid: { color: 'rgba(255,255,255,0.05)' } }
};

// Payroll Trends Chart
let payrollChart = new Chart(document.getElementById('payrollChart'), {
    type: 'line',
    data: {
        labels: sliceLast(payrollData, 6).map(d => d.label),
        datasets: [{
            label: 'Net Salary (NLE)',
            data: sliceLast(payrollData, 6).map(d => d.value),
            borderColor: '#6c63ff',
            backgroundColor: 'rgba(108,99,255,0.1)',
            fill: true,
            tension: 0.4,
        }]
    },
    options: { responsive: true, plugins: { legend: { labels: { color: '#aaa' } } }, scales: chartScales }
});

window.applyPayrollRange = function(n) {
    const slice = sliceLast(payrollData, parseInt(n));
    payrollChart.data.labels = slice.map(d => d.label);
    payrollChart.data.datasets[0].data = slice.map(d => d.value);
    payrollChart.update();
};

// Department Headcount Chart
let deptChart = new Chart(document.getElementById('deptChart'), {
    type: 'doughnut',
    data: {
        labels: deptData.map(d => d.name),
        datasets: [{
            data: deptData.map(d => d.employees_count),
            backgroundColor: ['#6c63ff','#00e676','#ff5252','#ffab00','#29b6f6','#ef5350'],
        }]
    },
    options: { responsive: true, plugins: { legend: { labels: { color: '#aaa', font: { size: 11 } } } } }
});

// Attendance Trends Chart
let attendanceChart = new Chart(document.getElementById('attendanceChart'), {
    type: 'bar',
    data: {
        labels: sliceLast(attendanceData, 6).map(d => d.label),
        datasets: [
            { label: 'Present', data: sliceLast(attendanceData, 6).map(d => d.present), backgroundColor: 'rgba(0,230,118,0.7)' },
            { label: 'Absent',  data: sliceLast(attendanceData, 6).map(d => d.absent),  backgroundColor: 'rgba(255,82,82,0.7)' }
        ]
    },
    options: { responsive: true, plugins: { legend: { labels: { color: '#aaa' } } }, scales: chartScales }
});

window.applyAttendanceRange = function(n) {
    const slice = sliceLast(attendanceData, parseInt(n));
    attendanceChart.data.labels = slice.map(d => d.label);
    attendanceChart.data.datasets[0].data = slice.map(d => d.present);
    attendanceChart.data.datasets[1].data = slice.map(d => d.absent);
    attendanceChart.update();
};

// ── Chart Export (Feature 11) ─────────────────────────────
window.exportChart = function(canvasId, filename) {
    const chart = Chart.getChart(canvasId);
    if (!chart) return;
    const link = document.createElement('a');
    link.download = filename + '.png';
    link.href = chart.toBase64Image('image/png', 1);
    link.click();
};
</script>
@endpush
