@extends('layouts.app')

@section('title', 'My Payslips')
@section('page-title', 'My Payslips')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payslip History</h3>
        </div>

        @if($payslips instanceof \Illuminate\Pagination\LengthAwarePaginator && $payslips->count() > 0)
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Basic Salary</th>
                            <th>Allowances</th>
                            <th>Deductions</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payslips as $slip)
                            <tr>
                                <td class="font-bold">{{ $slip->payroll->periodLabel() }}</td>
                                <td>NLE {{ number_format($slip->basic_salary, 2) }}</td>
                                <td class="text-success">+NLE {{ number_format($slip->allowances, 2) }}</td>
                                <td class="text-danger">-NLE {{ number_format($slip->deductions, 2) }}</td>
                                <td class="text-accent font-bold">NLE {{ number_format($slip->net_salary, 2) }}</td>
                                <td>
                                    @if($slip->payroll->status === 'paid')
                                        <span class="badge badge-success">Paid</span>
                                    @elseif($slip->payroll->status === 'processed')
                                        <span class="badge badge-info">Processed</span>
                                    @else
                                        <span class="badge badge-warning">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('payslips.show', $slip) }}" class="btn btn-sm btn-secondary">View Details</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($payslips->hasPages())
                <div class="pagination-wrapper">
                    {{ $payslips->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <p>No payslips available yet.</p>
            </div>
        @endif
    </div>
@endsection
