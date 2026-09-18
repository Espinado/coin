<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\EmailVerificationAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailVerificationResumeController extends Controller
{
    public function create(): View
    {
        return view('auth.verify-email-resume');
    }

    public function store(LoginRequest $request, EmailVerificationAccess $verificationAccess): RedirectResponse
    {
        $user = $request->validateCredentials();

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('login')
                ->with('status', __('coin.auth.email_already_verified'));
        }

        return $verificationAccess->openVerificationGate(
            $user,
            $request,
            $request->boolean('remember'),
            __('coin.auth.email_not_verified_login_sent'),
        );
    }
}
