@extends('layouts.app')

@section('title', 'Payroll Report')
@section('page-title', 'Payroll Report')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payroll Summary — {{ $year }}</h3>
            <form method="GET" style="display:flex;gap:0.5rem;align-items:center;">
                <select name="year" class="form-control" style="width:auto;">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            </form>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Employees</th>
                        <th>Gross Total</th>
                        <th>Net Total</th>
                        <th>Status</th>
                        <th>Export</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $row)
                        <tr>
                            <td class="font-bold">{{ $row['label'] }}</td>
                            <td>{{ $row['employee_count'] }}</td>
                            <td>NLE {{ number_format($row['total_gross'], 2) }}</td>
                            <td>NLE {{ number_format($row['total_net'], 2) }}</td>
                            <td>
                                @if($row['status'] === 'paid')
                                    <span class="badge badge-success">Paid</span>
                                @elseif($row['status'] === 'processed')
                                    <span class="badge badge-info">Processed</span>
                                @else
                                    <span class="badge badge-warning">Draft</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('reports.export.payroll', ['month' => $row['month'], 'year' => $year]) }}"
                                   class="btn btn-sm btn-secondary">Export</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted" style="padding:2rem">No payroll data for {{ $year }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back to Reports</a>
        </div>
    </div>
@endsection
