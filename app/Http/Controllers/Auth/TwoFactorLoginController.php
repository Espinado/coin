<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorLoginController extends Controller
{
    public function create(Request $request, LoginTwoFactorService $twoFactor): View|RedirectResponse
    {
        if (! $twoFactor->hasPendingChallenge($request)) {
            return redirect()->route('login');
        }

        if ($twoFactor->challengeExpired($request)) {
            $twoFactor->clearChallenge($request);

            return redirect()->route('login')->with('status', __('coin.auth.two_factor_expired'));
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

        if ($twoFactor->challengeExpired($request)) {
            $twoFactor->clearChallenge($request);

            return redirect()->route('login')->with('status', __('coin.auth.two_factor_expired'));
        }

        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [], [
            'code' => __('coin.auth.two_factor_code'),
        ]);

        try {
            $user = $twoFactor->verify($request->string('code')->toString(), $request);
        } catch (ValidationException $exception) {
            if (($exception->errors()['code'][0] ?? null) === __('coin.auth.two_factor_expired')) {
                return redirect()->route('login')->with('status', __('coin.auth.two_factor_expired'));
            }

            throw $exception;
        }

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

        if ($twoFactor->challengeExpired($request)) {
            $twoFactor->clearChallenge($request);

            return redirect()->route('login')->with('status', __('coin.auth.two_factor_expired'));
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
