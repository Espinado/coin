<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailVerificationCodeService;
use App\Services\UserLoginRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationCodeController extends Controller
{
    public function store(
        Request $request,
        EmailVerificationCodeService $verification,
        UserLoginRecorder $loginRecorder,
    ): RedirectResponse {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [], [
            'code' => __('coin.auth.verify_email_code'),
        ]);

        $verification->verify($user, $request->string('code')->toString(), $request);
        $loginRecorder->record($user->fresh(), $request);

        return redirect()
            ->intended(route('dashboard', absolute: false))
            ->with('status', __('coin.auth.verify_email_confirmed_redirect'));
    }
}
