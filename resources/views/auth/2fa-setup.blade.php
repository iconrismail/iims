@extends('layouts.app')

@section('title', '2FA Security Setup')
@section('page-title', '2FA Security Setup')

@section('content')
    <div class="card" style="max-width:500px;">
        <div class="card-header">
            <h3 class="card-title">Two-Factor Authentication</h3>
            @if($user->two_factor_enabled)
                <span class="badge badge-success">Enabled</span>
            @else
                <span class="badge badge-warning">Disabled</span>
            @endif
        </div>

        @if(!$user->two_factor_enabled)
            <p class="text-secondary" style="margin-bottom:1rem;">
                Scan the QR code below with your authenticator app (Google Authenticator, Authy, etc.), then enter the 6-digit code to enable 2FA.
            </p>

            <div style="text-align:center;margin:1.5rem 0;background:white;padding:1rem;border-radius:var(--radius);display:inline-block;">
                {!! $qrSvg !!}
            </div>

            <div style="background:var(--bg-secondary);border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1.5rem;text-align:center;">
                <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;margin-bottom:0.25rem;">Secret Key (manual entry)</div>
                <code style="font-size:0.9rem;letter-spacing:0.15rem;">{{ $user->two_factor_secret }}</code>
            </div>

            <form method="POST" action="{{ route('2fa.enable') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="code">Verification Code</label>
                    <input type="text" id="code" name="code" class="form-control"
                           placeholder="000000" maxlength="6" pattern="\d{6}"
                           autocomplete="one-time-code" required
                           style="font-size:1.25rem;text-align:center;letter-spacing:0.3rem;">
                    @error('code')
                        <div style="color:var(--danger);font-size:0.82rem;margin-top:0.25rem;">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Enable 2FA</button>
            </form>
        @else
            <p class="text-secondary" style="margin-bottom:1.5rem;">
                Two-factor authentication is currently <strong>enabled</strong> on your account. You will be required to enter a code from your authenticator app each time you log in.
            </p>

            <div style="background:rgba(255,82,82,0.08);border-radius:var(--radius);padding:1rem;margin-bottom:1.5rem;">
                <p style="color:var(--danger);margin:0;font-size:0.9rem;">
                    <strong>Warning:</strong> Disabling 2FA will reduce the security of your admin account.
                </p>
            </div>

            <form method="POST" action="{{ route('2fa.disable') }}"
                  onsubmit="return confirm('Are you sure you want to disable 2FA?')">
                @csrf
                <button type="submit" class="btn btn-danger">Disable 2FA</button>
            </form>
        @endif

        <div style="margin-top:1.5rem;">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
@endsection
