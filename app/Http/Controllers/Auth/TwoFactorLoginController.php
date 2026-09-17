<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthAuditLogger;
use App\Services\Auth\AuthFailureStage;
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
            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_NO_PENDING);

            return redirect()->route('login');
        }

        if ($twoFactor->challengeExpired($request)) {
            $twoFactor->clearChallenge($request);

            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_EXPIRED);

            return redirect()->route('login')->with('status', __('coin.auth.two_factor_expired'));
        }

        $user = $twoFactor->pendingUser($request);

        if (! $user) {
            $twoFactor->clearChallenge($request);

            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_SUBJECT_MISSING);

            return redirect()->route('login');
        }

        return view('auth.two-factor-login', [
            'email' => $user->email,
        ]);
    }

    public function store(Request $request, LoginTwoFactorService $twoFactor): RedirectResponse
    {
        if (! $twoFactor->hasPendingChallenge($request)) {
            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_NO_PENDING);

            return redirect()->route('login');
        }

        if ($twoFactor->challengeExpired($request)) {
            $twoFactor->clearChallenge($request);

            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_EXPIRED);

            return redirect()->route('login')->with('status', __('coin.auth.two_factor_expired'));
        }

        try {
            $request->validate([
                'code' => ['required', 'string', 'digits:6'],
            ], [], [
                'code' => __('coin.auth.two_factor_code'),
            ]);
        } catch (ValidationException $exception) {
            $this->logAuthFailure($request, AuthFailureStage::VALIDATION, [
                'validation_errors' => array_keys($exception->errors()),
            ]);

            throw $exception;
        }

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
            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_NO_PENDING);

            return redirect()->route('login');
        }

        if ($twoFactor->challengeExpired($request)) {
            $twoFactor->clearChallenge($request);

            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_EXPIRED);

            return redirect()->route('login')->with('status', __('coin.auth.two_factor_expired'));
        }

        $user = $twoFactor->pendingUser($request);

        if (! $user) {
            $twoFactor->clearChallenge($request);

            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_SUBJECT_MISSING);

            return redirect()->route('login');
        }

        $twoFactor->sendCode($user, $request);

        return back()->with('status', __('coin.auth.two_factor_resent'));
    }

    private function logAuthFailure(Request $request, string $stage, array $context = []): void
    {
        app(AuthAuditLogger::class)->logFailure('web', 'two_factor', $stage, $request, $context);
    }
}
