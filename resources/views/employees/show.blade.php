@extends('layouts.app')

@section('title', 'Employee Details')
@section('page-title', 'Employee Details')

@section('content')
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        {{-- Profile Card --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $employee->user->name }}</h3>
                <span class="badge {{ $employee->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                    {{ ucfirst($employee->status) }}
                </span>
            </div>

            <div class="payslip-row">
                <span class="label">Employee ID</span>
                <span class="value text-accent">{{ $employee->employee_id }}</span>
            </div>
            <div class="payslip-row">
                <span class="label">Email</span>
                <span class="value">{{ $employee->user->email }}</span>
            </div>
            <div class="payslip-row">
                <span class="label">Department</span>
                <span class="value">{{ $employee->department->name }}</span>
            </div>
            <div class="payslip-row">
                <span class="label">Position</span>
                <span class="value">{{ $employee->position }}</span>
            </div>
            <div class="payslip-row">
                <span class="label">Phone</span>
                <span class="value">{{ $employee->phone ?? 'N/A' }}</span>
            </div>
            <div class="payslip-row">
                <span class="label">Address</span>
                <span class="value">{{ $employee->address ?? 'N/A' }}</span>
            </div>
            <div class="payslip-row">
                <span class="label">Date of Birth</span>
                <span class="value">{{ $employee->date_of_birth?->format('M d, Y') ?? 'N/A' }}</span>
            </div>
            <div class="payslip-row">
                <span class="label">Hire Date</span>
                <span class="value">{{ $employee->hire_date->format('M d, Y') }}</span>
            </div>
            <div class="payslip-row">
                <span class="label">Contract Type</span>
                <span class="value">
                    @php $ct = $employee->contract_type ?? 'permanent'; @endphp
                    @if($ct === 'permanent')
                        <span class="badge badge-success">Permanent</span>
                    @elseif($ct === 'fixed-term')
                        <span class="badge badge-info">Fixed-Term</span>
                    @else
                        <span class="badge badge-warning">Probation</span>
                    @endif
                </span>
            </div>
            @if($employee->contract_end_date)
            <div class="payslip-row">
                <span class="label">Contract End Date</span>
                <span class="value" style="color:{{ $employee->isContractExpiringSoon() ? '#f59e0b' : 'inherit' }}">
                    {{ $employee->contract_end_date->format('M d, Y') }}
                    @if($employee->isContractExpiringSoon())
                        <span style="font-size:0.75rem;color:#f59e0b;margin-left:0.4rem">
                            ({{ $employee->contractDaysRemaining() }} day(s) left)
                        </span>
                    @endif
                </span>
            </div>
            @endif
            @if($employee->probation_end_date)
            <div class="payslip-row">
                <span class="label">Probation End Date</span>
                <span class="value" style="color:{{ $employee->isProbationExpiringSoon() ? '#f59e0b' : 'inherit' }}">
                    {{ $employee->probation_end_date->format('M d, Y') }}
                    @if($employee->isProbationExpiringSoon())
                        <span style="font-size:0.75rem;color:#f59e0b;margin-left:0.4rem">
                            ({{ $employee->probationDaysRemaining() }} day(s) left)
                        </span>
                    @endif
                </span>
            </div>
            @endif

            <div class="mt-2 btn-group">
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-primary">Edit</a>
                <a href="{{ route('employees.documents', $employee) }}" class="btn btn-sm btn-secondary">
                    Documents
                    @if($employee->documents->count() > 0)
                        <span class="badge badge-info" style="margin-left: 0.35rem; font-size: 0.7rem;">{{ $employee->documents->count() }}</span>
                    @endif
                </a>
                <a href="{{ route('employees.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
        </div>

        {{-- Salary Card --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Salary Information</h3>
            </div>

            <div class="payslip-row">
                <span class="label">Basic Salary</span>
                <span class="value">NLE {{ number_format($employee->basic_salary, 2) }}</span>
            </div>
            <div class="payslip-row">
                <span class="label">Allowances</span>
                <span class="value text-success">+ NLE {{ number_format($employee->allowances, 2) }}</span>
            </div>
            <div class="payslip-row">
                <span class="label">Deductions</span>
                <span class="value text-danger">- NLE {{ number_format($employee->deductions, 2) }}</span>
            </div>
            <div class="payslip-total">
                Net Salary: NLE {{ number_format($employee->netSalary(), 2) }}
            </div>

            {{-- Recent Payslips --}}
            <h4 class="card-title mt-3 mb-1">Payslip History</h4>
            @if($employee->payslips->count() > 0)
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employee->payslips->take(5) as $slip)
                                <tr>
                                    <td>{{ $slip->payroll->periodLabel() }}</td>
                                    <td class="text-accent font-bold">NLE {{ number_format($slip->net_salary, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $slip->payroll->status === 'paid' ? 'badge-success' : 'badge-info' }}">
                                            {{ ucfirst($slip->payroll->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted" style="font-size: 0.85rem">No payslips generated yet.</p>
            @endif
        </div>
    </div>

    {{-- Churn Risk Card --}}
    @php
        $churnColor = match($churn['level']) { 'high' => '#ef4444', 'medium' => '#f59e0b', default => '#22c55e' };
        $churnBg    = match($churn['level']) { 'high' => '#ef444418', 'medium' => '#f59e0b18', default => '#22c55e18' };
    @endphp
    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Employee Churn Risk</h3>
            <span style="padding:0.3rem 0.9rem; border-radius:20px; font-size:0.8rem; font-weight:700;
                         background:{{ $churnBg }}; color:{{ $churnColor }}; border:1px solid {{ $churnColor }}44;">
                {{ ucfirst($churn['level']) }} Risk
            </span>
        </div>

        <div style="display:flex; align-items:center; gap:2rem; padding:1rem 0;">
            {{-- Score gauge --}}
            <div style="text-align:center; min-width:90px;">
                <div style="font-size:2.5rem; font-weight:800; color:{{ $churnColor }}; line-height:1;">
                    {{ $churn['score'] }}
                </div>
                <div style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em;">
                    / 100
                </div>
            </div>

            {{-- Progress bar --}}
            <div style="flex:1;">
                <div style="background:var(--bg-card); border-radius:8px; height:12px; overflow:hidden; border:1px solid var(--border-color);">
                    <div style="height:100%; width:{{ $churn['score'] }}%; background:{{ $churnColor }}; border-radius:8px; transition:width .4s ease;"></div>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.68rem; color:var(--text-muted); margin-top:0.25rem;">
                    <span>Low</span><span>Medium</span><span>High</span>
                </div>
            </div>
        </div>

        @if(count($churn['factors']) > 0)
            <div style="border-top:1px solid var(--border-color); padding-top:0.75rem; margin-top:0.25rem;">
                <p style="font-size:0.78rem; color:var(--text-muted); margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.05em;">Risk Factors Detected</p>
                <div style="display:flex; flex-wrap:wrap; gap:0.4rem;">
                    @foreach($churn['factors'] as $factor)
                        <span style="padding:0.2rem 0.6rem; border-radius:6px; font-size:0.75rem; background:{{ $churnBg }}; color:{{ $churnColor }}; border:1px solid {{ $churnColor }}44;">
                            {{ $factor['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        @else
            <p style="font-size:0.82rem; color:var(--text-muted); padding-top:0.5rem; border-top:1px solid var(--border-color);">
                No significant risk factors detected.
            </p>
        @endif
    </div>
@endsection
