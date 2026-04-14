@extends('layouts.app')
@section('title', 'Account Settings')
@section('page-title', 'Account Settings')

@section('breadcrumbs')
    <span>Account Settings</span>
@endsection

@section('content')
<div style="max-width:720px">

    {{-- ── Profile Information ────────────────────────────── --}}
    <form action="{{ route('account.update') }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:.5rem;vertical-align:-3px"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Profile Information
                </h3>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label" for="name">Display Name <span style="color:#ef4444">*</span></label>
                    <input type="text" id="name" name="name" class="form-control"
                           value="{{ old('name', $user->name) }}" required maxlength="100" autocomplete="name">
                    @error('name')
                        <div class="form-error" style="color:#ef4444;font-size:.8rem;margin-top:.25rem">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address <span style="color:#ef4444">*</span></label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="{{ old('email', $user->email) }}" required maxlength="150" autocomplete="email">
                    @error('email')
                        <div class="form-error" style="color:#ef4444;font-size:.8rem;margin-top:.25rem">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="margin-top:.5rem">
                <button type="submit" class="btn btn-primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:.4rem"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Profile
                </button>
            </div>
        </div>
    </form>

    {{-- ── Change Password ─────────────────────────────────── --}}
    <form action="{{ route('account.password') }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:.5rem;vertical-align:-3px"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Change Password
                </h3>
            </div>

            <div style="display:grid;gap:1rem;max-width:400px">
                <div class="form-group">
                    <label class="form-label" for="current_password">Current Password <span style="color:#ef4444">*</span></label>
                    <input type="password" id="current_password" name="current_password"
                           class="form-control"
                           required autocomplete="current-password">
                    @error('current_password')
                        <div class="form-error" style="color:#ef4444;font-size:.8rem;margin-top:.25rem">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">New Password <span style="color:#ef4444">*</span></label>
                    <input type="password" id="password" name="password"
                           class="form-control"
                           required autocomplete="new-password">
                    <div class="form-hint">Minimum 8 characters, must include letters and numbers.</div>
                    @error('password')
                        <div class="form-error" style="color:#ef4444;font-size:.8rem;margin-top:.25rem">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirm New Password <span style="color:#ef4444">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-control" required autocomplete="new-password">
                </div>
            </div>

            <div style="margin-top:.5rem">
                <button type="submit" class="btn btn-primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:.4rem"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Update Password
                </button>
            </div>
        </div>
    </form>

    {{-- ── Account Information (read-only) ─────────────────── --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:.5rem;vertical-align:-3px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Account Information
            </h3>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem">
            <div>
                <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-secondary);margin-bottom:.3rem">Role</div>
                @php
                    $roleColors = ['admin' => '#6c63ff', 'hr' => '#29b6f6', 'manager' => '#f59e0b', 'employee' => '#00e676'];
                    $roleColor  = $roleColors[$user->role] ?? '#aaa';
                @endphp
                <span style="display:inline-block;padding:.2rem .65rem;border-radius:99px;font-size:.78rem;font-weight:700;background:{{ $roleColor }}22;color:{{ $roleColor }};border:1px solid {{ $roleColor }}55;">
                    {{ ucfirst($user->role) }}
                </span>
            </div>

            <div>
                <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-secondary);margin-bottom:.3rem">Member Since</div>
                <div style="font-size:.9rem;">{{ $user->created_at->format('d M Y') }}</div>
            </div>

            <div>
                <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-secondary);margin-bottom:.3rem">Last Login</div>
                <div style="font-size:.9rem;">
                    @if($user->last_login_at)
                        {{ $user->last_login_at->format('d M Y, g:i A') }}
                        <div style="font-size:.75rem;color:var(--text-secondary);">{{ $user->last_login_at->diffForHumans() }}</div>
                    @else
                        <span style="color:var(--text-secondary);">—</span>
                    @endif
                </div>
            </div>

            <div>
                <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-secondary);margin-bottom:.3rem">Two-Factor Auth</div>
                @if($user->two_factor_enabled)
                    <span style="display:inline-flex;align-items:center;gap:.3rem;color:#00e676;font-size:.85rem;font-weight:600;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Enabled
                    </span>
                @else
                    <span style="display:inline-flex;align-items:center;gap:.3rem;color:var(--text-secondary);font-size:.85rem;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        Disabled
                    </span>
                    @if($user->isAdmin())
                        <div style="margin-top:.4rem">
                            <a href="{{ route('2fa.setup') }}" class="btn btn-secondary btn-sm" style="font-size:.75rem;">Enable 2FA</a>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
