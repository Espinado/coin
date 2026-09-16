<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Services\AdminLoginTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorLoginController extends Controller
{
    public function create(Request $request, AdminLoginTwoFactorService $twoFactor): View|RedirectResponse
    {
        if (! $twoFactor->hasPendingChallenge($request)) {
            return redirect()->route('admin.login');
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

        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [], [
            'code' => __('coin.auth.two_factor_code'),
        ]);

        $admin = $twoFactor->verify($request->string('code')->toString(), $request);
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

        $admin = $twoFactor->pendingAdmin($request);

        if (! $admin) {
            $twoFactor->clearChallenge($request);

            return redirect()->route('admin.login');
        }

        $twoFactor->sendCode($admin, $request);

        return back()->with('status', __('coin.auth.two_factor_resent'));
    }
}
