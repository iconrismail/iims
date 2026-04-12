@extends('layouts.app')

@section('title', 'Payroll Details')
@section('page-title', 'Payroll — ' . $payroll->periodLabel())

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">{{ $payroll->periodLabel() }}</h3>
                <p class="text-secondary" style="font-size: 0.82rem; margin-top: 0.25rem;">
                    Generated {{ $payroll->created_at->format('M d, Y \a\t h:i A') }}
                </p>
            </div>
            <div class="btn-group">
                @if($payroll->status === 'processed')
                    <span class="badge badge-info" style="font-size: 0.85rem; padding: 0.4rem 1rem;">Processed</span>
                    <form action="{{ route('payroll.markPaid', $payroll) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-success">Mark as Paid</button>
                    </form>
                @elseif($payroll->status === 'paid')
                    <span class="badge badge-success" style="font-size: 0.85rem; padding: 0.4rem 1rem;">Paid</span>
                @else
                    <span class="badge badge-warning" style="font-size: 0.85rem; padding: 0.4rem 1rem;">Draft</span>
                @endif
                <a href="{{ route('payroll.index') }}" class="btn btn-sm btn-secondary">Back</a>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="stats-grid" style="margin-bottom: 0;">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
                <div class="stat-info">
                    <h3>{{ $payroll->payslips->count() }}</h3>
                    <div class="stat-label">Employees</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <div class="stat-info">
                    <h3>NLE {{ number_format($payroll->payslips->sum('net_salary'), 2) }}</h3>
                    <div class="stat-label">Total Net Payroll</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payslips Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payslips</h3>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Basic Salary</th>
                        <th>Allowances</th>
                        <th>Deductions</th>
                        <th>Days Worked</th>
                        <th>Days Absent</th>
                        <th>Net Salary</th>
                        <th>Flags</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payroll->payslips as $slip)
                        <tr>
                            <td>
                                <div class="font-bold">{{ $slip->employee->user->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem">{{ $slip->employee->employee_id }}</div>
                            </td>
                            <td class="text-secondary">{{ $slip->employee->department->name }}</td>
                            <td>NLE {{ number_format($slip->basic_salary, 2) }}</td>
                            <td class="text-success">+NLE {{ number_format($slip->allowances, 2) }}</td>
                            <td class="text-danger">-NLE {{ number_format($slip->deductions, 2) }}</td>
                            <td>{{ $slip->days_worked }}</td>
                            <td>{{ $slip->days_absent }}</td>
                            <td class="text-accent font-bold">NLE {{ number_format($slip->net_salary, 2) }}</td>
                            <td>
                                @if($slip->anomalies->isNotEmpty())
                                    <div style="display:flex; flex-wrap:wrap; gap:0.25rem;">
                                        @foreach($slip->anomalies as $anomaly)
                                            <span title="{{ $anomaly->description }}"
                                                  style="display:inline-block; padding:0.15rem 0.45rem; border-radius:4px; font-size:0.68rem; font-weight:600; background:{{ $anomaly->severityColor() }}22; color:{{ $anomaly->severityColor() }}; border:1px solid {{ $anomaly->severityColor() }}44; cursor:default; white-space:nowrap;">
                                                {{ $anomaly->typeLabel() }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-secondary" style="font-size:0.75rem;">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('payslips.show', $slip) }}" class="btn btn-sm btn-secondary">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" class="text-right font-bold">Total:</td>
                        <td class="text-accent font-bold">NLE {{ number_format($payroll->payslips->sum('net_salary'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
