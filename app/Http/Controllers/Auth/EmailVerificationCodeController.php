<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailVerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationCodeController extends Controller
{
    public function store(Request $request, EmailVerificationCodeService $verification): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [], [
            'code' => __('coin.auth.verify_email_code'),
        ]);

        $verification->verify($request->user(), $request->string('code')->toString(), $request);

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
