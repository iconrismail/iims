@extends('layouts.app')

@section('title', 'Tax & Deductions')
@section('page-title', 'Tax & Deductions')

@section('content')
    {{-- Tax Brackets --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Tax Brackets</h3>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Min Salary (NLE)</th>
                        <th>Max Salary (NLE)</th>
                        <th>Rate (%)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brackets as $bracket)
                        <tr>
                            <td class="font-bold">{{ $bracket->name }}</td>
                            <td>{{ number_format($bracket->min_salary, 2) }}</td>
                            <td>{{ $bracket->max_salary ? number_format($bracket->max_salary, 2) : '<span class="text-muted">No limit</span>' }}</td>
                            <td><span class="badge badge-info">{{ $bracket->rate }}%</span></td>
                            <td>
                                <form method="POST" action="{{ route('tax.brackets.destroy', $bracket) }}"
                                      onsubmit="return confirm('Delete this bracket?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted" style="padding:2rem">No tax brackets configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Add Bracket Form --}}
        <div style="border-top:1px solid var(--border-color);padding-top:1.25rem;margin-top:1rem;">
            <h4 class="card-title" style="margin-bottom:1rem;">Add Tax Bracket</h4>
            <form method="POST" action="{{ route('tax.brackets.store') }}">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;align-items:end;">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Basic Rate" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Min Salary</label>
                        <input type="number" name="min_salary" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Salary (blank = no limit)</label>
                        <input type="number" name="max_salary" class="form-control" step="0.01" min="0" placeholder="Leave blank for no limit">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rate (%)</label>
                        <input type="number" name="rate" class="form-control" step="0.01" min="0" max="100" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Add Bracket</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Deduction Rules --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Deduction Rules</h3>
            <span class="text-secondary" style="font-size:0.8rem;">Applied automatically during payroll processing</span>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                        <tr>
                            <td class="font-bold">{{ $rule->name }}</td>
                            <td>
                                <span class="badge {{ $rule->type === 'percentage' ? 'badge-info' : 'badge-warning' }}">
                                    {{ ucfirst($rule->type) }}
                                </span>
                            </td>
                            <td>
                                @if($rule->type === 'percentage')
                                    {{ $rule->value }}%
                                @else
                                    NLE {{ number_format($rule->value, 2) }}
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $rule->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <form method="POST" action="{{ route('tax.rules.toggle', $rule) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-secondary">
                                            {{ $rule->is_active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('tax.rules.destroy', $rule) }}"
                                          onsubmit="return confirm('Delete this rule?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted" style="padding:2rem">No deduction rules configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Add Rule Form --}}
        <div style="border-top:1px solid var(--border-color);padding-top:1.25rem;margin-top:1rem;">
            <h4 class="card-title" style="margin-bottom:1rem;">Add Deduction Rule</h4>
            <form method="POST" action="{{ route('tax.rules.store') }}">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;align-items:end;">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. NASSIT Contribution" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control" required>
                            <option value="percentage">Percentage of Gross</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Value</label>
                        <input type="number" name="value" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Add Rule</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
