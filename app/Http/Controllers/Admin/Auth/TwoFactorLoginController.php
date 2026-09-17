<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Services\AdminLoginTwoFactorService;
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
            return redirect()->route('admin.login');
        }

        if ($twoFactor->challengeExpired($request)) {
            $twoFactor->clearChallenge($request);

            return redirect()->route('admin.login')->with('status', __('coin.auth.two_factor_expired'));
        }

        $admin = $twoFactor->pendingAdmin($request);

        if (! $admin) {
            $twoFactor->clearChallenge($request);

            return redirect()->route('admin.login');
        }

        return view('admin.auth.two-factor-login', [
            'email' => $admin->email,
        ]);
    }

    public function store(Request $request, AdminLoginTwoFactorService $twoFactor): RedirectResponse
    {
        if (! $twoFactor->hasPendingChallenge($request)) {
            return redirect()->route('admin.login');
        }

        if ($twoFactor->challengeExpired($request)) {
            $twoFactor->clearChallenge($request);

            return redirect()->route('admin.login')->with('status', __('coin.auth.two_factor_expired'));
        }

        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [], [
            'code' => __('coin.auth.two_factor_code'),
        ]);

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
            return redirect()->route('admin.login');
        }

        if ($twoFactor->challengeExpired($request)) {
            $twoFactor->clearChallenge($request);

            return redirect()->route('admin.login')->with('status', __('coin.auth.two_factor_expired'));
        }

        $admin = $twoFactor->pendingAdmin($request);

        if (! $admin) {
            $twoFactor->clearChallenge($request);

            return redirect()->route('admin.login');
        }

        $twoFactor->sendCode($admin, $request);

        return back()->with('status', __('coin.auth.two_factor_resent'));
    }
}
