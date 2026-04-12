<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct() { $this->google2fa = new Google2FA(); }

    public function showChallenge(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('2fa:user:id')) return redirect()->route('login');
        return view('auth.2fa-challenge');
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|digits:6']);
        $userId = $request->session()->get('2fa:user:id');
        $remember = $request->session()->get('2fa:remember', false);

        $user = User::find($userId);
        if (!$user) return redirect()->route('login');

        if (empty($user->two_factor_secret)) {
            $request->session()->forget(['2fa:user:id', '2fa:remember']);
            return redirect()->route('login')->withErrors(['email' => 'Two-factor authentication is not configured. Please contact your administrator.']);
        }

        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);
        if (!$valid) return back()->withErrors(['code' => 'Invalid code. Please try again.']);

        $request->session()->forget(['2fa:user:id','2fa:remember']);
        $request->session()->put('previous_login_at', $user->last_login_at?->toIso8601String());
        $user->updateQuietly(['last_login_at' => now()]);
        Auth::login($user, $remember);
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    public function showSetup(Request $request): View
    {
        $user = $request->user();
        if (!$user->two_factor_secret) {
            $secret = $this->google2fa->generateSecretKey();
            $user->update(['two_factor_secret' => $secret]);
        }
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(config('app.name'), $user->email, $user->two_factor_secret);
        $qrCode = (new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        ));
        $qrSvg = (new \BaconQrCode\Writer($qrCode))->writeString($qrCodeUrl);
        return view('auth.2fa-setup', compact('qrSvg', 'user'));
    }

    public function enableTwoFactor(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|digits:6']);
        $user = $request->user();
        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);
        if (!$valid) return back()->withErrors(['code' => 'Invalid code. 2FA not enabled.']);
        $user->update(['two_factor_enabled' => true]);
        return redirect()->route('dashboard')->with('success', '2FA enabled successfully.');
    }

    public function disableTwoFactor(Request $request): RedirectResponse
    {
        $request->user()->update(['two_factor_enabled' => false, 'two_factor_secret' => null]);
        return redirect()->route('dashboard')->with('success', '2FA disabled.');
    }
}
