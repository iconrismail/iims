@extends('layouts.app')

@section('title', 'Request Leave')
@section('page-title', 'Request Leave')

@section('content')
    <div class="card" style="max-width:640px;">
        <div class="card-header">
            <h3 class="card-title">New Leave Request</h3>
        </div>

        {{-- Leave Balance Summary --}}
        @if($balances->isNotEmpty())
        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;padding:0.75rem 0 1rem;border-bottom:1px solid var(--border-color);margin-bottom:1rem;">
            @foreach($balances as $bal)
            <div style="display:flex;align-items:center;gap:0.4rem;padding:0.3rem 0.7rem;border-radius:99px;background:{{ $bal->remaining() > 0 ? 'var(--accent)18' : '#ef444418' }};border:1px solid {{ $bal->remaining() > 0 ? 'var(--accent)44' : '#ef444444' }};">
                <span style="font-size:0.75rem;font-weight:600;color:{{ $bal->remaining() > 0 ? 'var(--accent)' : '#ef4444' }};">
                    {{ $bal->leaveType->name }}
                </span>
                <span style="font-size:0.72rem;color:var(--text-muted);">
                    {{ $bal->remaining() }}/{{ $bal->entitled_days + $bal->carried_forward }} days
                </span>
            </div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('leaves.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="leave_type_id">Leave Type</label>
                <select id="leave_type_id" name="leave_type_id" class="form-control" required>
                    <option value="">Select leave type...</option>
                    @foreach($leaveTypes as $type)
                        @php $bal = $balances->get($type->id); @endphp
                        <option value="{{ $type->id }}"
                                data-remaining="{{ $bal?->remaining() ?? ($type->is_paid ? 0 : 999) }}"
                                data-paid="{{ $type->is_paid ? 1 : 0 }}"
                                {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                            @if($type->is_paid && $bal)
                                — {{ $bal->remaining() }} days remaining
                            @else
                                ({{ $type->days_per_year }} days/year{{ !$type->is_paid ? ', Unpaid' : '' }})
                            @endif
                        </option>
                    @endforeach
                </select>
                <div id="balance-warning" style="display:none;margin-top:0.4rem;font-size:0.78rem;color:#f59e0b;"></div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label" for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control"
                           value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control"
                           value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div id="days-preview" style="display:none;margin:-0.5rem 0 1rem;font-size:0.8rem;color:var(--text-muted);"></div>

            <div class="form-group">
                <label class="form-label" for="reason">Reason (Optional)</label>
                <textarea id="reason" name="reason" class="form-control" rows="3"
                          placeholder="Briefly describe your reason...">{{ old('reason') }}</textarea>
            </div>

            <div style="display:flex;gap:0.75rem;">
                <button type="submit" class="btn btn-primary">Submit Request</button>
                <a href="{{ route('leaves.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

@push('scripts')
<script>
(function () {
    const typeSelect = document.getElementById('leave_type_id');
    const startInput = document.getElementById('start_date');
    const endInput   = document.getElementById('end_date');
    const preview    = document.getElementById('days-preview');
    const warning    = document.getElementById('balance-warning');

    function countWeekdays(start, end) {
        let count = 0, cur = new Date(start);
        const last = new Date(end);
        while (cur <= last) { if (cur.getDay() !== 0 && cur.getDay() !== 6) count++; cur.setDate(cur.getDate() + 1); }
        return count;
    }

    function update() {
        const opt = typeSelect.selectedOptions[0];
        if (!opt || !startInput.value || !endInput.value) { preview.style.display = 'none'; warning.style.display = 'none'; return; }

        const days = Math.max(1, countWeekdays(startInput.value, endInput.value));
        const remaining = parseInt(opt.dataset.remaining) || 0;
        const isPaid    = opt.dataset.paid === '1';

        preview.textContent = days + ' working day' + (days !== 1 ? 's' : '') + ' requested.';
        preview.style.display = 'block';

        if (isPaid && days > remaining) {
            warning.textContent = 'Warning: you only have ' + remaining + ' day(s) remaining. Submitting may be rejected.';
            warning.style.display = 'block';
        } else {
            warning.style.display = 'none';
        }
    }

    typeSelect.addEventListener('change', update);
    startInput.addEventListener('change', update);
    endInput.addEventListener('change', update);
    update();
})();
</script>
@endpush

@endsection
