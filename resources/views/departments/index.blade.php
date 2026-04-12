@extends('layouts.app')

@section('title', 'Departments')
@section('page-title', 'Departments')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Departments</h3>
            <a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Department
            </a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Employees</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td class="font-bold">{{ $dept->name }}</td>
                            <td class="text-secondary">{{ Str::limit($dept->description, 60) ?? '—' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $dept->employees_count }}</span>
                            </td>
                            <td class="text-secondary">{{ $dept->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('departments.edit', $dept) }}" class="btn btn-sm btn-secondary">Edit</a>
                                    <form action="{{ route('departments.destroy', $dept) }}" method="POST"
                                          onsubmit="return confirm('Delete this department?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted" style="padding: 2rem">No departments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($departments->hasPages())
            <div class="pagination-wrapper">
                {{ $departments->links() }}
            </div>
        @endif
    </div>
@endsection
