@extends('layouts.app')

@section('title', 'Overtime Records')
@section('page-title', 'Overtime Records')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Overtime Records</h3>
            <a href="{{ route('overtime.create') }}" class="btn btn-primary btn-sm">+ Add Overtime</a>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('overtime.index') }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
            <select name="employee_id" class="form-control" style="width: auto; min-width: 180px;">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->user->name }}
                    </option>
                @endforeach
            </select>
            <select name="month" class="form-control" style="width: auto;">
                <option value="">All Months</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0,0,0,$m,1)) }}
                    </option>
                @endfor
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
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            <a href="{{ route('overtime.index') }}" class="btn btn-secondary btn-sm">Clear</a>
        </form>

        @if($overtimes->count() > 0)
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Hours</th>
                            <th>Rate</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overtimes as $ot)
                            <tr style="{{ $ot->status === 'pending' ? 'background: rgba(255, 171, 64, 0.05);' : '' }}">
                                <td>{{ $ot->date->format('M d, Y') }}</td>
                                <td class="font-bold">{{ $ot->employee->user->name }}</td>
                                <td>{{ $ot->hours }}h</td>
                                <td>{{ $ot->rate_multiplier }}x</td>
                                <td class="text-accent font-bold">NLE {{ number_format($ot->amount, 2) }}</td>
                                <td>
                                    @if($ot->status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($ot->status === 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td style="color: var(--text-secondary); font-size: 0.85rem;">{{ Str::limit($ot->notes, 40) }}</td>
                                <td>
                                    <div class="btn-group">
                                        @if($ot->status === 'pending')
                                            <form action="{{ route('overtime.approve', $ot) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form action="{{ route('overtime.reject', $ot) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-secondary">Reject</button>
                                            </form>
                                        @endif
                                        @if(auth()->user()->isAdmin())
                                        <form action="{{ route('overtime.destroy', $ot) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $overtimes->links() }}
            </div>
        @else
            <div class="empty-state">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.4;">⏱</div>
                <h3>No overtime records found</h3>
                <p>Add overtime records to track extra hours worked.</p>
                <a href="{{ route('overtime.create') }}" class="btn btn-primary" style="margin-top: 1rem;">Add Overtime</a>
            </div>
        @endif
    </div>
@endsection
