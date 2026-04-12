@extends('layouts.app')
@section('title', 'HR Analytics')
@section('page-title', 'HR Analytics')

@section('breadcrumbs')
    <a href="{{ route('reports.index') }}">Reports</a>
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Analytics</span>
@endsection

@section('content')

{{-- Year filter --}}
<div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap">
    <span style="font-size:0.82rem;font-weight:600;color:var(--text-secondary)">Year:</span>
    @foreach($availableYears as $y)
        <a href="{{ request()->fullUrlWithQuery(['year' => $y]) }}"
           class="btn btn-sm {{ $year == $y ? 'btn-primary' : 'btn-secondary' }}">{{ $y }}</a>
    @endforeach
    <span style="font-size:0.8rem;color:var(--text-muted);margin-left:0.25rem">Showing data for <strong>{{ $year }}</strong></span>
</div>

{{-- ── Row 1: YTD Summary Cards ── --}}
<div class="stats-grid" style="margin-bottom:1.5rem">
    <div class="stat-card">
        <div class="stat-icon green">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-info">
            <h3>{{ number_format($activeCount) }}</h3>
            <div class="stat-label">Active Employees</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="stat-info">
            <h3>NLE {{ number_format($ytdGross?->net ?? 0, 0) }}</h3>
            <div class="stat-label">YTD Net Payroll</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="stat-info">
            <h3>NLE {{ number_format($ytdGross?->gross ?? 0, 0) }}</h3>
            <div class="stat-label">YTD Gross Payroll</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg>
        </div>
        <div class="stat-info">
            <h3>{{ $newHires }}</h3>
            <div class="stat-label">New Hires ({{ $year }})</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div class="stat-info">
            <h3>{{ $inactiveCount }}</h3>
            <div class="stat-label">Inactive / Separated</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="stat-info">
            <h3>NLE {{ number_format($ytdGross?->deductions ?? 0, 0) }}</h3>
            <div class="stat-label">YTD Deductions</div>
        </div>
    </div>
</div>

{{-- ── Row 2: Monthly Payroll Trend + Dept Cost ── --}}
<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:1.5rem;margin-bottom:1.5rem">

    {{-- Monthly Payroll Trend --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Monthly Payroll Trend ({{ $year }})</h3>
        </div>
        @if($monthlyPayroll->isEmpty())
            <div class="empty-state"><p>No payroll data for {{ $year }}.</p></div>
        @else
        @php $maxNet = $monthlyPayroll->max('net_total') ?: 1; @endphp
        <div style="display:flex;flex-direction:column;gap:0.6rem">
            @foreach($monthlyPayroll as $row)
            @php $pct = round($row['net_total'] / $maxNet * 100); @endphp
            <div style="display:grid;grid-template-columns:36px 1fr 110px;align-items:center;gap:0.5rem;font-size:0.8rem">
                <span style="color:var(--text-muted);text-align:right">{{ $row['label'] }}</span>
                <div style="height:10px;background:var(--input-bg);border-radius:5px;overflow:hidden">
                    <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#6c63ff,#00e5ff);border-radius:5px"></div>
                </div>
                <span style="font-weight:600;text-align:right;color:var(--text-primary)">NLE {{ number_format($row['net_total'], 0) }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Dept Payroll Cost --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dept Payroll Cost ({{ $year }})</h3>
        </div>
        @if($deptCosts->isEmpty())
            <div class="empty-state"><p>No data available.</p></div>
        @else
        @php $maxCost = $deptCosts->max('total') ?: 1; @endphp
        <div style="display:flex;flex-direction:column;gap:0.6rem">
            @foreach($deptCosts as $row)
            @php $pct = round($row['total'] / $maxCost * 100); @endphp
            <div style="font-size:0.8rem">
                <div style="display:flex;justify-content:space-between;margin-bottom:3px">
                    <span style="font-weight:600;color:var(--text-primary)">{{ $row['name'] }}</span>
                    <span style="color:var(--text-muted)">{{ $row['headcount'] }} staff &nbsp;·&nbsp; NLE {{ number_format($row['total'], 0) }}</span>
                </div>
                <div style="height:8px;background:var(--input-bg);border-radius:4px;overflow:hidden">
                    <div style="height:100%;width:{{ $pct }}%;background:#6c63ff;border-radius:4px"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ── Row 3: Headcount by Dept + Leave Utilisation ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

    {{-- Headcount by Department --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Headcount by Department</h3>
            <span style="font-size:0.75rem;color:var(--text-muted)">Active only</span>
        </div>
        @if($headcount->isEmpty())
            <div class="empty-state"><p>No active employees.</p></div>
        @else
        @php $maxHC = $headcount->max('count') ?: 1; @endphp
        <div style="display:flex;flex-direction:column;gap:0.55rem">
            @foreach($headcount as $row)
            @php $pct = round($row['count'] / $maxHC * 100); @endphp
            <div style="display:grid;grid-template-columns:1fr 80px;align-items:center;gap:0.5rem;font-size:0.82rem">
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:3px">
                        <span style="font-weight:600;color:var(--text-primary)">{{ $row['name'] }}</span>
                        <span style="color:var(--text-muted)">{{ $row['count'] }}</span>
                    </div>
                    <div style="height:7px;background:var(--input-bg);border-radius:3px;overflow:hidden">
                        <div style="height:100%;width:{{ $pct }}%;background:#00e676;border-radius:3px"></div>
                    </div>
                </div>
                <div style="text-align:right;font-size:0.75rem;color:var(--text-muted)">
                    {{ $activeCount > 0 ? round($row['count'] / $activeCount * 100) : 0 }}%
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Leave Utilisation --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Leave Utilisation ({{ $year }})</h3>
            <span style="font-size:0.75rem;color:var(--text-muted)">Approved only</span>
        </div>
        @if($leaveUtil->isEmpty())
            <div class="empty-state"><p>No approved leave this year.</p></div>
        @else
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th style="text-align:center">Requests</th>
                        <th style="text-align:right">Total Days</th>
                        <th style="text-align:right">Avg Days</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaveUtil as $row)
                    <tr>
                        <td style="font-weight:600">{{ $row['name'] }}</td>
                        <td style="text-align:center">{{ $row['requests'] }}</td>
                        <td style="text-align:right;font-weight:600">{{ $row['total_days'] }}</td>
                        <td style="text-align:right;color:var(--text-muted)">
                            {{ $row['requests'] > 0 ? round($row['total_days'] / $row['requests'], 1) : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid var(--border-color)">
                        <td style="font-weight:700">Total</td>
                        <td style="text-align:center;font-weight:700">{{ $leaveUtil->sum('requests') }}</td>
                        <td style="text-align:right;font-weight:700">{{ $leaveUtil->sum('total_days') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>
</div>

@endsection
