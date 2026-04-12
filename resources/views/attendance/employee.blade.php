@extends('layouts.app')

@section('title', 'My Attendance')
@section('page-title', 'My Attendance')

@section('content')
    {{-- Filter --}}
    <div class="card mb-3">
        <form method="GET" action="{{ route('attendance.index') }}" class="flex items-center gap-2" style="flex-wrap: wrap;">
            <div class="form-group" style="margin: 0;">
                <select name="month" class="form-control" style="width: auto;">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <select name="year" class="form-control" style="width: auto;">
                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Attendance Records</h3>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $att)
                        <tr>
                            <td>{{ $att->date->format('M d, Y') }}</td>
                            <td class="text-secondary">{{ $att->date->format('l') }}</td>
                            <td>
                                <span class="badge {{ $att->status === 'present' ? 'badge-success' : 'badge-danger' }}">
                                    {{ ucfirst($att->status) }}
                                </span>
                            </td>
                            <td class="text-secondary">{{ $att->remarks ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted" style="padding: 2rem">No attendance records for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($attendances, 'hasPages') && $attendances->hasPages())
            <div class="pagination-wrapper">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
@endsection
