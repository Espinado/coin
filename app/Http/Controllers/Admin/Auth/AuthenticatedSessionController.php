<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Services\AdminLoginTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request, AdminLoginTwoFactorService $twoFactor): View|RedirectResponse
    {
        if ($request->boolean('cancel')) {
            $twoFactor->clearChallenge($request);
        }

        if ($twoFactor->hasPendingChallenge($request)) {
            return redirect()->route('admin.login.two-factor');
        }

        return view('admin.auth.login');
    }

    public function store(LoginRequest $request, AdminLoginTwoFactorService $twoFactor): RedirectResponse
    {
        $admin = $request->validateCredentials();

        $twoFactor->beginChallenge($admin, $request->boolean('remember'), $request);

        return redirect()->route('admin.login.two-factor');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
