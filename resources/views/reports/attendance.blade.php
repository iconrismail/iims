@extends('layouts.app')

@section('title', 'Attendance Report')
@section('page-title', 'Attendance Report')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Attendance — {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}</h3>
            <form method="GET" style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                <select name="month" class="form-control" style="width:auto;">
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
                <input type="number" name="year" class="form-control" value="{{ $year }}" min="2020" max="2099" style="width:90px;">
                <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                <a href="{{ route('reports.export.attendance', ['month' => $month, 'year' => $year]) }}"
                   class="btn btn-primary btn-sm">Export Excel</a>
            </form>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Employee ID</th>
                        <th>Days Present</th>
                        <th>Days Absent</th>
                        <th>Total Recorded</th>
                        <th>Attendance %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $row)
                        <tr>
                            <td>{{ $row['employee']->user->name }}</td>
                            <td class="text-accent">{{ $row['employee']->employee_id }}</td>
                            <td class="text-success">{{ $row['present'] }}</td>
                            <td class="text-danger">{{ $row['absent'] }}</td>
                            <td>{{ $row['total'] }}</td>
                            <td>
                                @if($row['total'] > 0)
                                    {{ round($row['present'] / $row['total'] * 100, 1) }}%
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted" style="padding:2rem">No active employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Back to Reports</a>
        </div>
    </div>
@endsection
