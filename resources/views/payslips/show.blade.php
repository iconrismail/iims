@extends('layouts.app')

@section('title', 'Payslip Details')
@section('page-title', 'Payslip Details')

@section('content')
    <div class="card" style="max-width: 700px;">
        <div class="card-header">
            <h3 class="card-title">Payslip — {{ $payslip->payroll->periodLabel() }}</h3>
            @if($payslip->payroll->status === 'paid')
                <span class="badge badge-success">Paid</span>
            @elseif($payslip->payroll->status === 'processed')
                <span class="badge badge-info">Processed</span>
            @else
                <span class="badge badge-warning">Draft</span>
            @endif
        </div>

        {{-- Employee Info --}}
        <div style="background: var(--bg-secondary); border-radius: var(--radius); padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div>
                    <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase;">Employee</div>
                    <div class="font-bold">{{ $payslip->employee->user->name }}</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase;">Employee ID</div>
                    <div class="text-accent font-bold">{{ $payslip->employee->employee_id }}</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase;">Department</div>
                    <div>{{ $payslip->employee->department->name }}</div>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase;">Position</div>
                    <div>{{ $payslip->employee->position }}</div>
                </div>
            </div>
        </div>

        {{-- Salary Breakdown --}}
        <h4 class="card-title mb-1">Salary Breakdown</h4>

        <div class="payslip-row">
            <span class="label">Basic Salary</span>
            <span class="value">NLE {{ number_format($payslip->basic_salary, 2) }}</span>
        </div>
        <div class="payslip-row">
            <span class="label">Allowances</span>
            <span class="value text-success">+ NLE {{ number_format($payslip->allowances, 2) }}</span>
        </div>
        <div class="payslip-row">
            <span class="label">Deductions</span>
            <span class="value text-danger">- NLE {{ number_format($payslip->deductions, 2) }}</span>
        </div>
        @if(($payslip->overtime_pay ?? 0) > 0)
            <div class="payslip-row">
                <span class="label">Overtime Pay</span>
                <span class="value text-success">+ NLE {{ number_format($payslip->overtime_pay, 2) }}</span>
            </div>
        @endif
        @if(($payslip->bonus ?? 0) > 0)
            <div class="payslip-row">
                <span class="label">Bonus</span>
                <span class="value text-success">+ NLE {{ number_format($payslip->bonus, 2) }}</span>
            </div>
        @endif

        <div class="payslip-total">
            Net Salary: NLE {{ number_format($payslip->net_salary, 2) }}
        </div>

        {{-- Attendance Info --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1.5rem;">
            <div style="background: rgba(0, 230, 118, 0.08); border-radius: var(--radius); padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">{{ $payslip->days_worked }}</div>
                <div class="text-secondary" style="font-size: 0.8rem;">Days Worked</div>
            </div>
            <div style="background: rgba(255, 82, 82, 0.08); border-radius: var(--radius); padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger);">{{ $payslip->days_absent }}</div>
                <div class="text-secondary" style="font-size: 0.8rem;">Days Absent</div>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('payslips.download', $payslip) }}" class="btn btn-primary">&#11015; Download PDF</a>
            <a href="{{ route('payslips.index') }}" class="btn btn-secondary" style="margin-left: 0.5rem;">Back to Payslips</a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('payroll.show', $payslip->payroll) }}" class="btn btn-secondary" style="margin-left: 0.5rem;">View Full Payroll</a>
            @endif
        </div>
    </div>
@endsection
