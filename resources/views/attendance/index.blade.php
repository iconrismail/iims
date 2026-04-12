@extends('layouts.app')

@section('title', 'Attendance')
@section('page-title', 'Attendance Management')

@section('content')
    {{-- Filter Bar --}}
    <div class="card mb-3">
        <form method="GET" action="{{ route('attendance.index') }}" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0">
                <label class="form-label">Month</label>
                <select name="month" class="form-control" style="width:auto">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="form-group" style="margin:0">
                <label class="form-label">Year</label>
                <select name="year" class="form-control" style="width:auto">
                    @for($y = date('Y')-2; $y <= date('Y')+1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="form-group" style="margin:0;min-width:160px">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-control">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->employee_id }} — {{ $emp->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;min-width:110px">
                <label class="form-label">Status</label>
                <select name="status_filter" class="form-control">
                    <option value="">All</option>
                    <option value="present"  {{ request('status_filter') === 'present'  ? 'selected' : '' }}>Present</option>
                    <option value="absent"   {{ request('status_filter') === 'absent'   ? 'selected' : '' }}>Absent</option>
                    <option value="late"     {{ request('status_filter') === 'late'     ? 'selected' : '' }}>Late</option>
                    <option value="half_day" {{ request('status_filter') === 'half_day' ? 'selected' : '' }}>Half Day</option>
                </select>
            </div>
            <div class="form-group" style="margin:0">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="form-group" style="margin:0">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div style="display:flex;gap:0.5rem;padding-bottom:1px">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if(request()->hasAny(['employee_id','status_filter','date_from','date_to']))
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        {{-- Attendance Records Table --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attendance Records</h3>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Shift</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Late (min)</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $att)
                            <tr>
                                <td>{{ $att->date->format('M d, Y') }}</td>
                                <td>{{ $att->employee->user->name ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($att->status) {
                                            'present'  => 'badge-success',
                                            'absent'   => 'badge-danger',
                                            'late'     => 'badge-warning',
                                            'half_day' => 'badge-info',
                                            default    => 'badge-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_',' ',$att->status)) }}</span>
                                </td>
                                <td style="font-size:.8rem;color:var(--text-muted);">{{ $att->shift ? ucfirst($att->shift) : '—' }}</td>
                                <td style="font-size:.82rem;">{{ $att->time_in ? \Carbon\Carbon::parse($att->time_in)->format('H:i') : '—' }}</td>
                                <td style="font-size:.82rem;">{{ $att->time_out ? \Carbon\Carbon::parse($att->time_out)->format('H:i') : '—' }}</td>
                                <td style="font-size:.82rem;text-align:center;">
                                    @if($att->late_minutes > 0)
                                        <span style="color:#f59e0b;font-weight:600;">{{ $att->late_minutes }}m</span>
                                    @else
                                        <span style="color:var(--text-muted);">—</span>
                                    @endif
                                </td>
                                <td class="text-secondary" style="font-size:.8rem;">{{ $att->remarks ?? '—' }}</td>
                                <td>
                                    <form action="{{ route('attendance.destroy', $att) }}" method="POST"
                                          onsubmit="return confirm('Delete this record?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted" style="padding: 2rem">No attendance records found.</td></tr>
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

        {{-- Record Single Attendance --}}
        <div class="card" style="align-self: start;">
            <div class="card-header">
                <h3 class="card-title">Record Attendance</h3>
            </div>

            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="employee_id">Employee *</label>
                    <select id="employee_id" name="employee_id" class="form-control" required>
                        <option value="">Select Employee</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->employee_id }} — {{ $emp->user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="att_date">Date *</label>
                    <input type="date" id="att_date" name="date" class="form-control"
                           value="{{ $date }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="att_status">Status *</label>
                    <select id="att_status" name="status" class="form-control" required>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                        <option value="half_day">Half Day</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="att_shift">Shift</label>
                    <select id="att_shift" name="shift" class="form-control">
                        <option value="">— Select shift —</option>
                        <option value="morning">Morning (08:00)</option>
                        <option value="afternoon">Afternoon (14:00)</option>
                        <option value="night">Night (22:00)</option>
                    </select>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
                    <div class="form-group">
                        <label class="form-label" for="att_time_in">Time In</label>
                        <input type="time" id="att_time_in" name="time_in" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="att_time_out">Time Out</label>
                        <input type="time" id="att_time_out" name="time_out" class="form-control">
                    </div>
                </div>

                <div id="late-preview" style="display:none;font-size:0.78rem;color:#f59e0b;margin:-0.5rem 0 0.75rem;"></div>

                <div class="form-group">
                    <label class="form-label" for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks" class="form-control" rows="2" placeholder="Optional remarks"></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-full" style="justify-content: center;">
                    Record Attendance
                </button>
            </form>
        </div>
    </div>

    {{-- Bulk Attendance Section --}}
    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Bulk Attendance</h3>
        </div>

        <form action="{{ route('attendance.bulk') }}" method="POST">
            @csrf

            <div class="form-group" style="max-width: 250px;">
                <label class="form-label" for="bulk_date">Date *</label>
                <input type="date" id="bulk_date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th style="width: 180px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $emp)
                            <tr>
                                <td>{{ $emp->employee_id }} — {{ $emp->user->name }}</td>
                                <td class="text-secondary">{{ $emp->department->name ?? '' }}</td>
                                <td>
                                    <select name="attendance[{{ $emp->id }}]" class="form-control" style="padding: 0.4rem 0.6rem; font-size: 0.82rem;">
                                        <option value="present">Present</option>
                                        <option value="absent">Absent</option>
                                        <option value="late">Late</option>
                                        <option value="half_day">Half Day</option>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2">
                <button type="submit" class="btn btn-primary">Save Bulk Attendance</button>
            </div>
        </form>
    </div>

@push('scripts')
<script>
(function () {
    const shiftStarts = { morning: '08:00', afternoon: '14:00', night: '22:00' };
    const shiftSel  = document.getElementById('att_shift');
    const timeInEl  = document.getElementById('att_time_in');
    const statusSel = document.getElementById('att_status');
    const preview   = document.getElementById('late-preview');

    function updateLate() {
        const shift  = shiftSel.value;
        const timeIn = timeInEl.value;
        if (!shift || !timeIn) { preview.style.display = 'none'; return; }

        const [sh, sm] = shiftStarts[shift].split(':').map(Number);
        const [th, tm] = timeIn.split(':').map(Number);
        const late = (th * 60 + tm) - (sh * 60 + sm);

        if (late > 0) {
            preview.textContent = late + ' minute' + (late !== 1 ? 's' : '') + ' late — status will be set to Late automatically.';
            preview.style.display = 'block';
            if (statusSel.value === 'present') statusSel.value = 'late';
        } else {
            preview.style.display = 'none';
        }
    }

    shiftSel.addEventListener('change', updateLate);
    timeInEl.addEventListener('change', updateLate);
})();
</script>
@endpush

@endsection
