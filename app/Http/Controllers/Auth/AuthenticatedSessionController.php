<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\LoginTwoFactorService;
use App\Services\UserLoginRecorder;
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
        }

        if ($twoFactor->hasPendingChallenge($request)) {
            return redirect()->route('login.two-factor');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, LoginTwoFactorService $twoFactor, UserLoginRecorder $loginRecorder): RedirectResponse
    {
        $user = $request->validateCredentials();

        if ($user->hasEmailTwoFactorEnabled()) {
            $twoFactor->beginChallenge($user, $request->boolean('remember'), $request);

            return redirect()->route('login.two-factor');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $loginRecorder->record($user, $request);

        return redirect()->intended(route('dashboard', absolute: false));
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
