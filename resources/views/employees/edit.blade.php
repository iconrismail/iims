@extends('layouts.app')

@section('title', 'Edit Employee')
@section('page-title', 'Edit Employee')

@section('content')
    <div class="card" style="max-width: 800px;">
        <div class="card-header">
            <h3 class="card-title">Edit: {{ $employee->user->name }} ({{ $employee->employee_id }})</h3>
            <a href="{{ route('employees.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
        </div>

        <form action="{{ route('employees.update', $employee) }}" method="POST">
            @csrf @method('PUT')

            <div class="nav-section" style="padding: 0; margin-bottom: 1rem;">Account Details</div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control"
                           value="{{ old('name', $employee->user->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="{{ old('email', $employee->user->email) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">New Password <span class="text-muted">(leave blank to keep current)</span></label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Min 8 characters">
            </div>

            <div class="nav-section" style="padding: 0; margin: 1.5rem 0 1rem;">Employment Details</div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="department_id">Department *</label>
                    <select id="department_id" name="department_id" class="form-control" required>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="position">Position / Job Role *</label>
                    <input type="text" id="position" name="position" class="form-control"
                           value="{{ old('position', $employee->position) }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control"
                           value="{{ old('phone', $employee->phone) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="hire_date">Hire Date *</label>
                    <input type="date" id="hire_date" name="hire_date" class="form-control"
                           value="{{ old('hire_date', $employee->hire_date->format('Y-m-d')) }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control"
                           value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="2">{{ old('address', $employee->address) }}</textarea>
            </div>

            <div class="nav-section" style="padding: 0; margin: 1.5rem 0 1rem;">Contract &amp; Probation</div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="contract_type">Contract Type *</label>
                    <select id="contract_type" name="contract_type" class="form-control" required onchange="toggleContractDate(this.value)">
                        <option value="permanent"  {{ old('contract_type', $employee->contract_type ?? 'permanent') === 'permanent'  ? 'selected' : '' }}>Permanent</option>
                        <option value="fixed-term" {{ old('contract_type', $employee->contract_type) === 'fixed-term' ? 'selected' : '' }}>Fixed-Term</option>
                        <option value="probation"  {{ old('contract_type', $employee->contract_type) === 'probation'  ? 'selected' : '' }}>Probation</option>
                    </select>
                </div>
                <div class="form-group" id="contract_end_group"
                     style="{{ in_array(old('contract_type', $employee->contract_type), ['fixed-term','probation']) ? '' : 'display:none' }}">
                    <label class="form-label" for="contract_end_date">Contract End Date <span id="contract_end_required" style="{{ old('contract_type', $employee->contract_type) === 'fixed-term' ? '' : 'display:none' }}">*</span></label>
                    <input type="date" id="contract_end_date" name="contract_end_date" class="form-control"
                           value="{{ old('contract_end_date', $employee->contract_end_date?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="form-group" style="max-width: calc(50% - 0.5rem);">
                <label class="form-label" for="probation_end_date">Probation End Date</label>
                <input type="date" id="probation_end_date" name="probation_end_date" class="form-control"
                       value="{{ old('probation_end_date', $employee->probation_end_date?->format('Y-m-d')) }}">
                <small class="text-muted" style="font-size:.73rem">Optional — set even for permanent staff if there's a probation window.</small>
            </div>

            <script>
            function toggleContractDate(type) {
                const group    = document.getElementById('contract_end_group');
                const required = document.getElementById('contract_end_required');
                const input    = document.getElementById('contract_end_date');
                group.style.display    = (type === 'fixed-term' || type === 'probation') ? '' : 'none';
                required.style.display = (type === 'fixed-term') ? '' : 'none';
                input.required         = (type === 'fixed-term');
            }
            </script>

            <div class="nav-section" style="padding: 0; margin: 1.5rem 0 1rem;">Salary Details</div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="basic_salary">Basic Salary *</label>
                    <input type="number" id="basic_salary" name="basic_salary" class="form-control"
                           value="{{ old('basic_salary', $employee->basic_salary) }}" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="allowances">Allowances</label>
                    <input type="number" id="allowances" name="allowances" class="form-control"
                           value="{{ old('allowances', $employee->allowances) }}" step="0.01" min="0">
                </div>
            </div>

            <div class="form-group" style="max-width: calc(50% - 0.5rem);">
                <label class="form-label" for="deductions">Deductions</label>
                <input type="number" id="deductions" name="deductions" class="form-control"
                       value="{{ old('deductions', $employee->deductions) }}" step="0.01" min="0">
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Update Employee</button>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary" style="margin-left: 0.5rem;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
