<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two-Factor Authentication — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>

            <h1>Two-Factor Auth</h1>
            <p class="subtitle">Enter the 6-digit code from your authenticator app</p>

            @if($errors->any())
                <div class="alert alert-error" style="margin-bottom: 1.25rem">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('2fa.verify') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="code">Authentication Code</label>
                    <input type="text"
                           id="code"
                           name="code"
                           class="form-control"
                           placeholder="000000"
                           maxlength="6"
                           pattern="\d{6}"
                           autocomplete="one-time-code"
                           required
                           autofocus
                           style="font-size:1.5rem;text-align:center;letter-spacing:0.4rem;">
                </div>

                <button type="submit" class="btn btn-primary w-full" style="justify-content: center; padding: 0.75rem;">
                    Verify
                </button>
            </form>

            <div style="text-align:center;margin-top:1rem;">
                <a href="{{ route('login') }}" style="color:var(--text-muted);font-size:0.85rem;">Back to Login</a>
            </div>

            <div class="brand">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
                </svg>
            </div>
        </div>
    </div>
</body>
</html>
