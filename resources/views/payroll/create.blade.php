@extends('layouts.app')

@section('title', 'Process Payroll')
@section('page-title', 'Process Payroll')

@section('content')
    <div class="card" style="max-width: 500px;">
        <div class="card-header">
            <h3 class="card-title">Generate Payroll</h3>
            <a href="{{ route('payroll.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
        </div>

        <div class="alert alert-info" style="margin-bottom: 1.25rem">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            This will calculate and generate payslips for all active employees based on their salary data and attendance for the selected month.
        </div>

        <form action="{{ route('payroll.process') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="month">Month *</label>
                    <select id="month" name="month" class="form-control" required>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="year">Year *</label>
                    <select id="year" name="year" class="form-control" required>
                        @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="mt-2">
                <button type="submit" class="btn btn-primary w-full" style="justify-content: center;"
                        onclick="return confirm('Process payroll for this period? Existing payslips will be recalculated.')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Process Payroll
                </button>
            </div>
        </form>
    </div>
@endsection
