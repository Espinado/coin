<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Support\SessionIdleTracker;
use App\Services\AdminLoginTwoFactorService;
use App\Services\Auth\AuthAuditLogger;
use App\Services\Auth\AuthFailureStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorLoginController extends Controller
{
    public function create(Request $request, AdminLoginTwoFactorService $twoFactor): View|RedirectResponse
    {
        if (! $twoFactor->hasPendingChallenge($request)) {
            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_NO_PENDING);

            return redirect()->route('admin.login');
        }

        if ($twoFactor->challengeExpired($request)) {
            $twoFactor->clearChallenge($request);

            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_EXPIRED);

            return redirect()->route('admin.login')->with('status', __('coin.auth.two_factor_expired'));
        }

        $admin = $twoFactor->pendingAdmin($request);

        if (! $admin) {
            $twoFactor->clearChallenge($request);

            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_SUBJECT_MISSING);

            return redirect()->route('admin.login');
        }

        return view('admin.auth.two-factor-login', [
            'email' => $admin->email,
        ]);
    }

    public function store(Request $request, AdminLoginTwoFactorService $twoFactor): RedirectResponse
    {
        if (! $twoFactor->hasPendingChallenge($request)) {
            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_NO_PENDING);

            return redirect()->route('admin.login');
        }

        if ($twoFactor->challengeExpired($request)) {
            $twoFactor->clearChallenge($request);

            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_EXPIRED);

            return redirect()->route('admin.login')->with('status', __('coin.auth.two_factor_expired'));
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
            $admin = $twoFactor->verify($request->string('code')->toString(), $request);
        } catch (ValidationException $exception) {
            if (($exception->errors()['code'][0] ?? null) === __('coin.auth.two_factor_expired')) {
                return redirect()->route('admin.login')->with('status', __('coin.auth.two_factor_expired'));
            }

            throw $exception;
        }

        $remember = $twoFactor->rememberFromSession($request);
        $postLoginMessageKey = $twoFactor->pullPostLoginMessageKey($request);

        $twoFactor->clearChallenge($request);

        Auth::guard('admin')->login($admin, $remember);
        $request->session()->regenerate();
        SessionIdleTracker::markNow($request);

        $redirect = redirect()->intended(route('admin.dashboard', absolute: false));

        if ($postLoginMessageKey !== null) {
            return $redirect
                ->with('status', __($postLoginMessageKey))
                ->with('status_type', 'success');
        }

        return $redirect;
    }

    public function resend(Request $request, AdminLoginTwoFactorService $twoFactor): RedirectResponse
    {
        if (! $twoFactor->hasPendingChallenge($request)) {
            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_NO_PENDING);

            return redirect()->route('admin.login');
        }

        if ($twoFactor->challengeExpired($request)) {
            $twoFactor->clearChallenge($request);

            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_EXPIRED);

            return redirect()->route('admin.login')->with('status', __('coin.auth.two_factor_expired'));
        }

        $admin = $twoFactor->pendingAdmin($request);

        if (! $admin) {
            $twoFactor->clearChallenge($request);

            $this->logAuthFailure($request, AuthFailureStage::TWO_FACTOR_SUBJECT_MISSING);

            return redirect()->route('admin.login');
        }

        $twoFactor->sendCode($admin, $request);

        return back()->with('status', __('coin.auth.two_factor_resent'));
    }

    private function logAuthFailure(Request $request, string $stage, array $context = []): void
    {
        app(AuthAuditLogger::class)->logFailure('admin', 'two_factor', $stage, $request, $context);
    }
}
