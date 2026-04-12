<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid credentials. Please try again.']);
        }

        $user = Auth::user();

        if ($user->isAdmin() && $user->two_factor_enabled && $user->two_factor_secret) {
            Auth::logout();
            $request->session()->put('2fa:user:id', $user->id);
            $request->session()->put('2fa:remember', $remember);
            $request->session()->regenerate();
            return redirect()->route('2fa.challenge');
        }

        // Store previous login time in session before overwriting
        $request->session()->put('previous_login_at', $user->last_login_at?->toIso8601String());
        $user->updateQuietly(['last_login_at' => now()]);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
