@extends('layouts.app')

@section('title', 'Add Overtime Record')
@section('page-title', 'Add Overtime Record')

@section('content')
    <div class="card" style="max-width: 640px;">
        <div class="card-header">
            <h3 class="card-title">New Overtime Record</h3>
        </div>

        <form action="{{ route('overtime.store') }}" method="POST" id="overtime-form">
            @csrf

            <div class="form-group">
                <label class="form-label">Employee</label>
                <select name="employee_id" id="employee_id" class="form-control" required>
                    <option value="">-- Select Employee --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}"
                            data-salary="{{ $emp->basic_salary }}"
                            {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->user->name }} — {{ $emp->employee_id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Date</label>
                <input type="date" name="date" id="ot_date" class="form-control"
                    value="{{ old('date', date('Y-m-d')) }}" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Hours Worked</label>
                    <input type="number" name="hours" id="hours" class="form-control"
                        min="0.5" max="24" step="0.5" value="{{ old('hours', 1) }}" required>
                    <span class="form-hint">Min 0.5, Max 24 hours</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Rate Multiplier</label>
                    <select name="rate_multiplier" id="rate_multiplier" class="form-control" required>
                        <option value="1.0" {{ old('rate_multiplier') == '1.0' ? 'selected' : '' }}>1.0x — Regular</option>
                        <option value="1.5" {{ old('rate_multiplier', '1.5') == '1.5' ? 'selected' : '' }}>1.5x — Time & Half</option>
                        <option value="2.0" {{ old('rate_multiplier') == '2.0' ? 'selected' : '' }}>2.0x — Double Time</option>
                        <option value="2.5" {{ old('rate_multiplier') == '2.5' ? 'selected' : '' }}>2.5x — Custom</option>
                        <option value="3.0" {{ old('rate_multiplier') == '3.0' ? 'selected' : '' }}>3.0x — Triple Time</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes <span style="color: var(--text-muted)">(optional)</span></label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Reason for overtime...">{{ old('notes') }}</textarea>
            </div>

            {{-- Amount Preview --}}
            <div id="amount-preview" style="background: var(--bg-secondary); border-radius: var(--radius); padding: 1rem 1.25rem; margin-bottom: 1.25rem; display: none;">
                <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Estimated Amount</div>
                <div id="preview-amount" style="font-size: 1.75rem; font-weight: 700; color: var(--accent); margin-top: 0.25rem;">NLE 0.00</div>
                <div id="preview-breakdown" style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;"></div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Save Overtime Record</button>
                <a href="{{ route('overtime.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    const employeeSelect = document.getElementById('employee_id');
    const dateInput = document.getElementById('ot_date');
    const hoursInput = document.getElementById('hours');
    const rateSelect = document.getElementById('rate_multiplier');
    const preview = document.getElementById('amount-preview');
    const previewAmount = document.getElementById('preview-amount');
    const previewBreakdown = document.getElementById('preview-breakdown');

    function getWorkingDays(year, month) {
        let count = 0;
        const daysInMonth = new Date(year, month, 0).getDate();
        for (let d = 1; d <= daysInMonth; d++) {
            const day = new Date(year, month - 1, d).getDay();
            if (day !== 0 && day !== 6) count++;
        }
        return count;
    }

    function updatePreview() {
        const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
        const salary = parseFloat(selectedOption?.dataset?.salary || 0);
        const hours = parseFloat(hoursInput.value || 0);
        const rate = parseFloat(rateSelect.value || 1.5);
        const dateVal = dateInput.value;

        if (!salary || !hours || !dateVal || !employeeSelect.value) {
            preview.style.display = 'none';
            return;
        }

        const parts = dateVal.split('-');
        const year = parseInt(parts[0]);
        const month = parseInt(parts[1]);
        const workingDays = getWorkingDays(year, month);
        const hourlyRate = workingDays > 0 ? salary / workingDays / 8 : 0;
        const amount = hourlyRate * hours * rate;

        preview.style.display = 'block';
        previewAmount.textContent = 'NLE ' + amount.toFixed(2);
        previewBreakdown.textContent = `Hourly rate: NLE ${hourlyRate.toFixed(2)} × ${hours}h × ${rate}x rate`;
    }

    [employeeSelect, dateInput, hoursInput, rateSelect].forEach(el => {
        el.addEventListener('change', updatePreview);
        el.addEventListener('input', updatePreview);
    });

    updatePreview();
</script>
@endpush
