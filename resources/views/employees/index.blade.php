@extends('layouts.app')

@section('title', 'Employees')
@section('page-title', 'Employees')

@section('content')
    {{-- Search & Filter Bar --}}
    <div class="card mb-3">
        <form method="GET" action="{{ route('employees.index') }}" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:1;min-width:180px">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, email, ID, position…" value="{{ request('search') }}">
            </div>
            <div class="form-group" style="margin:0;min-width:160px">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-control">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;min-width:120px">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div style="display:flex;gap:0.5rem;padding-bottom:1px">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if(request()->hasAny(['search','department_id','status']))
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Employees
                @if($employees->total() > 0)
                    <span class="badge badge-info" style="margin-left:0.5rem">{{ $employees->total() }}</span>
                @endif
            </h3>
            <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Employee
            </a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        <tr>
                            <td class="text-accent font-bold">{{ $emp->employee_id }}</td>
                            <td>{{ $emp->user->name }}</td>
                            <td class="text-secondary">{{ $emp->user->email }}</td>
                            <td>{{ $emp->department->name }}</td>
                            <td>{{ $emp->position }}</td>
                            <td>NLE {{ number_format($emp->basic_salary, 2) }}</td>
                            <td>
                                <span class="badge {{ $emp->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                    {{ ucfirst($emp->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('employees.show', $emp) }}" class="btn btn-sm btn-secondary">View</a>
                                    <a href="{{ route('employees.edit', $emp) }}" class="btn btn-sm btn-secondary">Edit</a>
                                    <form action="{{ route('employees.destroy', $emp) }}" method="POST"
                                          onsubmit="return confirm('Delete this employee? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted" style="padding: 2rem">No employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div class="pagination-wrapper">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
@endsection
