<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\EmailVerificationAccess;
use App\Services\LoginTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request, LoginTwoFactorService $twoFactor): View|RedirectResponse
    {
        if ($request->boolean('cancel')) {
            $twoFactor->clearChallenge($request);

            if (! $request->session()->has('status')) {
                $request->session()->flash('status', __('coin.auth.two_factor_cancelled'));
            }
        }

        if ($twoFactor->hasPendingChallenge($request)) {
            return redirect()->route('login.two-factor');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(
        LoginRequest $request,
        LoginTwoFactorService $twoFactor,
        EmailVerificationAccess $verificationAccess,
    ): RedirectResponse {
        $user = $request->validateCredentials();

        if (! $user->hasVerifiedEmail()) {
            return $verificationAccess->openVerificationGate(
                $user,
                $request,
                $request->boolean('remember'),
                __('coin.auth.email_not_verified_login_sent'),
            );
        }

        // Login always requires email 2FA (not a user-toggleable preference).
        $twoFactor->beginChallenge($user, $request->boolean('remember'), $request);

        return redirect()->route('login.two-factor');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
