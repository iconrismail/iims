@extends('layouts.app')
@section('title', 'Update My Profile')
@section('page-title', 'Update Profile')

@section('breadcrumbs')
    <span>Update Profile</span>
@endsection

@section('content')
<div style="max-width:680px;margin:0 auto;">

    @if($pending)
    <div style="background:rgba(245,158,11,0.10);border:1px solid #f59e0b;border-radius:8px;padding:0.85rem 1.1rem;margin-bottom:1.5rem;display:flex;align-items:flex-start;gap:0.75rem">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <div style="font-size:0.85rem">
            <strong style="color:#f59e0b">Pending request in review.</strong>
            You already have a profile update request submitted on
            <strong>{{ $pending->created_at->format('d M Y') }}</strong> that is awaiting admin approval.
            Submitting a new request will <em>cancel</em> the current one.
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Profile Update Request</h3>
            <span style="font-size:0.75rem;color:var(--text-muted)">Changes require admin approval</span>
        </div>

        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom:1rem">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom:1rem">
                <ul style="margin:0;padding-left:1.25rem">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile-updates.store') }}">
            @csrf

            <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1.25rem;">
                Only fields you change will be included in the request. Leave a field blank or unchanged to keep its current value.
            </p>

            {{-- Contact --}}
            <div style="margin-bottom:1.5rem">
                <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-muted);margin-bottom:0.75rem;padding-bottom:0.4rem;border-bottom:1px solid var(--border-color)">Contact Information</div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $employee->phone) }}" placeholder="e.g. +232 76 123 456" maxlength="30">
                    <div style="font-size:0.72rem;color:var(--text-muted);margin-top:3px">Current: <strong>{{ $employee->phone ?: '—' }}</strong></div>
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Home Address</label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                              rows="2" maxlength="500" placeholder="Your current home address">{{ old('address', $employee->address) }}</textarea>
                    <div style="font-size:0.72rem;color:var(--text-muted);margin-top:3px">Current: <strong>{{ $employee->address ?: '—' }}</strong></div>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Emergency Contact</label>
                    <input type="text" name="emergency_contact" class="form-control @error('emergency_contact') is-invalid @enderror"
                           value="{{ old('emergency_contact', $employee->emergency_contact) }}"
                           placeholder="Name, relationship, phone number" maxlength="200">
                    <div style="font-size:0.72rem;color:var(--text-muted);margin-top:3px">Current: <strong>{{ $employee->emergency_contact ?: '—' }}</strong></div>
                    @error('emergency_contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Bank Details --}}
            <div style="margin-bottom:1.5rem">
                <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-muted);margin-bottom:0.75rem;padding-bottom:0.4rem;border-bottom:1px solid var(--border-color)">Bank Details</div>

                <div class="form-group">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                           value="{{ old('bank_name', $employee->bank_name) }}" placeholder="e.g. Sierra Leone Commercial Bank" maxlength="100">
                    <div style="font-size:0.72rem;color:var(--text-muted);margin-top:3px">Current: <strong>{{ $employee->bank_name ?: '—' }}</strong></div>
                    @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Bank Account Number</label>
                    <input type="text" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror"
                           value="{{ old('bank_account', $employee->bank_account) }}" placeholder="Account number" maxlength="50">
                    <div style="font-size:0.72rem;color:var(--text-muted);margin-top:3px">Current: <strong>{{ $employee->bank_account ?: '—' }}</strong></div>
                    @error('bank_account')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div style="display:flex;gap:0.75rem;margin-top:1rem">
                <button type="submit" class="btn btn-primary">Submit Update Request</button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    {{-- Pending request details --}}
    @if($pending)
    <div class="card" style="margin-top:1.25rem">
        <div class="card-header">
            <h3 class="card-title" style="font-size:0.9rem">Your Pending Request — {{ $pending->created_at->format('d M Y') }}</h3>
            <span class="badge badge-warning">Pending Review</span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
            @foreach($pending->requested_fields as $field => $value)
            <div style="padding:0.6rem 0.75rem;background:var(--input-bg);border-radius:6px;border:1px solid var(--border-color)">
                <div style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:2px">
                    {{ \App\Models\ProfileUpdateRequest::FIELD_LABELS[$field] ?? $field }}
                </div>
                <div style="font-size:0.85rem;color:var(--text-primary)">{{ $value }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
