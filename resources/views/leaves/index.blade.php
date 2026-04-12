@extends('layouts.app')

@section('title', 'Leave Requests')
@section('page-title', 'Leave Requests')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Leave Requests</h3>
            <a href="{{ route('leaves.create') }}" class="btn btn-primary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Request
            </a>
        </div>

        {{-- Filters --}}
        <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
            <select name="status" class="form-control" style="width:auto;">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            @if(auth()->user()->isAdminOrManager())
                <select name="employee_id" class="form-control" style="width:auto;">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->user->name }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="{{ route('leaves.index') }}" class="btn btn-secondary">Reset</a>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        @if(auth()->user()->isAdminOrManager())<th>Employee</th>@endif
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                        <tr>
                            @if(auth()->user()->isAdminOrManager())
                                <td>{{ $leave->employee->user->name }}</td>
                            @endif
                            <td>{{ $leave->leaveType->name }}</td>
                            <td>{{ $leave->start_date->format('d M Y') }}</td>
                            <td>{{ $leave->end_date->format('d M Y') }}</td>
                            <td>{{ $leave->total_days }}</td>
                            <td>
                                @if($leave->status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($leave->status === 'rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td class="text-secondary">{{ $leave->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('leaves.show', $leave) }}" class="btn btn-sm btn-secondary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ auth()->user()->isAdminOrManager() ? 8 : 7 }}" class="text-center text-muted" style="padding:2rem">No leave requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaves instanceof \Illuminate\Pagination\LengthAwarePaginator && $leaves->hasPages())
            <div class="pagination-wrapper">{{ $leaves->links() }}</div>
        @endif
    </div>
@endsection
