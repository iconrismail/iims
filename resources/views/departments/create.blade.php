@extends('layouts.app')

@section('title', 'Add Department')
@section('page-title', 'Add Department')

@section('content')
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <h3 class="card-title">New Department</h3>
            <a href="{{ route('departments.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
        </div>

        <form action="{{ route('departments.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label" for="name">Department Name *</label>
                <input type="text" id="name" name="name" class="form-control"
                       value="{{ old('name') }}" placeholder="e.g. Human Resources" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"
                          placeholder="Brief description of the department">{{ old('description') }}</textarea>
            </div>

            <div class="mt-2">
                <button type="submit" class="btn btn-primary">Create Department</button>
                <a href="{{ route('departments.index') }}" class="btn btn-secondary" style="margin-left: 0.5rem;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
