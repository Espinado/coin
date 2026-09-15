<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorLoginController extends Controller
{
    public function create(Request $request, LoginTwoFactorService $twoFactor): View|RedirectResponse
    {
        if (! $twoFactor->hasPendingChallenge($request)) {
            return redirect()->route('login');
        }

        $user = $twoFactor->pendingUser($request);

        if (! $user) {
            $twoFactor->clearChallenge($request);

            return redirect()->route('login');
        }

        return view('auth.two-factor-login', [
            'email' => $user->email,
        ]);
    }

    public function store(Request $request, LoginTwoFactorService $twoFactor): RedirectResponse
    {
        if (! $twoFactor->hasPendingChallenge($request)) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [], [
            'code' => __('coin.auth.two_factor_code'),
        ]);

        $user = $twoFactor->verify($request->string('code')->toString(), $request);
        $remember = $twoFactor->rememberFromSession($request);

        $twoFactor->clearChallenge($request);

        Auth::login($user, $remember);
        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function resend(Request $request, LoginTwoFactorService $twoFactor): RedirectResponse
    {
        if (! $twoFactor->hasPendingChallenge($request)) {
            return redirect()->route('login');
        }

        $user = $twoFactor->pendingUser($request);

        if (! $user) {
            $twoFactor->clearChallenge($request);

            return redirect()->route('login');
        }

        $twoFactor->sendCode($user, $request);

        return back()->with('status', __('coin.auth.two_factor_resent'));
    }
}
