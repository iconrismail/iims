@extends('layouts.app')

@section('title', 'Bonuses')
@section('page-title', 'Bonuses')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Bonuses</h3>
            <a href="{{ route('bonuses.create') }}" class="btn btn-primary btn-sm">+ Add Bonus</a>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('bonuses.index') }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
            <select name="employee_id" class="form-control" style="width: auto; min-width: 180px;">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->user->name }}
                    </option>
                @endforeach
            </select>
            <select name="type" class="form-control" style="width: auto;">
                <option value="">All Types</option>
                <option value="performance" {{ request('type') === 'performance' ? 'selected' : '' }}>Performance</option>
                <option value="annual" {{ request('type') === 'annual' ? 'selected' : '' }}>Annual</option>
                <option value="festival" {{ request('type') === 'festival' ? 'selected' : '' }}>Festival</option>
                <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other</option>
            </select>
            <select name="year" class="form-control" style="width: auto;">
                <option value="">All Years</option>
                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <select name="status" class="form-control" style="width: auto;">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            <a href="{{ route('bonuses.index') }}" class="btn btn-secondary btn-sm">Clear</a>
        </form>

        @if($bonuses->count() > 0)
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Period</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bonuses as $bonus)
                            <tr style="{{ $bonus->status === 'pending' ? 'background: rgba(255, 171, 64, 0.05);' : '' }}">
                                <td class="font-bold">{{ $bonus->employee->user->name }}</td>
                                <td>{{ date('F', mktime(0,0,0,$bonus->month,1)) }} {{ $bonus->year }}</td>
                                <td class="text-accent font-bold">NLE {{ number_format($bonus->amount, 2) }}</td>
                                <td>
                                    @php
                                        $typeColors = ['performance' => 'badge-info', 'annual' => 'badge-success', 'festival' => 'badge-warning', 'other' => 'badge-secondary'];
                                    @endphp
                                    <span class="badge {{ $typeColors[$bonus->type] ?? 'badge-info' }}">
                                        {{ ucfirst($bonus->type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($bonus->status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td style="color: var(--text-secondary); font-size: 0.85rem;">{{ Str::limit($bonus->reason, 40) }}</td>
                                <td>
                                    <div class="btn-group">
                                        @if($bonus->status === 'pending')
                                            <form action="{{ route('bonuses.approve', $bonus) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('bonuses.destroy', $bonus) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this bonus?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $bonuses->links() }}
            </div>
        @else
            <div class="empty-state">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.4;">🎁</div>
                <h3>No bonuses found</h3>
                <p>Add bonuses for employee performance and special occasions.</p>
                <a href="{{ route('bonuses.create') }}" class="btn btn-primary" style="margin-top: 1rem;">Add Bonus</a>
            </div>
        @endif
    </div>
@endsection
