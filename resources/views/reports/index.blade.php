@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')

@section('content')
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Payroll Summary</h3>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <p class="text-secondary" style="margin-bottom:1rem;">View monthly payroll totals, employee counts, and export data to Excel.</p>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <a href="{{ route('reports.payroll') }}" class="btn btn-primary btn-sm">View Report</a>
                <a href="{{ route('reports.export.payroll', ['month' => now()->month, 'year' => now()->year]) }}" class="btn btn-secondary btn-sm">Export This Month</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attendance Summary</h3>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M9 14l2 2 4-4"/></svg>
            </div>
            <p class="text-secondary" style="margin-bottom:1rem;">View monthly attendance per employee and export data to Excel.</p>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <a href="{{ route('reports.attendance') }}" class="btn btn-primary btn-sm">View Report</a>
                <a href="{{ route('reports.export.attendance', ['month' => now()->month, 'year' => now()->year]) }}" class="btn btn-secondary btn-sm">Export This Month</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Employee Export</h3>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <p class="text-secondary" style="margin-bottom:1rem;">Export all active employee records including salary and department info.</p>
            <a href="{{ route('reports.export.employees') }}" class="btn btn-primary btn-sm">Export Employees</a>
        </div>

        <div class="card" style="border-top:3px solid #6c63ff">
            <div class="card-header">
                <h3 class="card-title">HR Analytics</h3>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
            <p class="text-secondary" style="margin-bottom:1rem;">
                Department cost breakdown, headcount distribution, monthly payroll trend, leave utilisation, and year-to-date summaries.
            </p>
            <a href="{{ route('reports.analytics') }}" class="btn btn-primary btn-sm">View Analytics</a>
        </div>

    </div>
@endsection
