@extends('layouts.app')

@section('title', 'Add Bonus')
@section('page-title', 'Add Bonus')

@section('content')
    <div class="card" style="max-width: 560px;">
        <div class="card-header">
            <h3 class="card-title">New Bonus</h3>
        </div>

        <form action="{{ route('bonuses.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-control" required>
                    <option value="">-- Select Employee --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->user->name }} — {{ $emp->employee_id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-control" required>
                        <option value="">-- Month --</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ old('month', date('n')) == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0,0,0,$m,1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-control" required>
                        @for($y = date('Y') + 1; $y >= date('Y') - 2; $y--)
                            <option value="{{ $y }}" {{ old('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Amount (NLE)</label>
                    <input type="number" name="amount" class="form-control"
                        min="0.01" step="0.01" value="{{ old('amount') }}" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Bonus Type</label>
                    <select name="type" class="form-control" required>
                        <option value="">-- Type --</option>
                        <option value="performance" {{ old('type') === 'performance' ? 'selected' : '' }}>Performance</option>
                        <option value="annual" {{ old('type') === 'annual' ? 'selected' : '' }}>Annual</option>
                        <option value="festival" {{ old('type') === 'festival' ? 'selected' : '' }}>Festival</option>
                        <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Reason <span style="color: var(--text-muted)">(optional)</span></label>
                <textarea name="reason" class="form-control" rows="3" placeholder="Reason for this bonus...">{{ old('reason') }}</textarea>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Save Bonus</button>
                <a href="{{ route('bonuses.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
