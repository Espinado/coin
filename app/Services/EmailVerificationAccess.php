<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationAccess
{
    public function __construct(
        private readonly EmailVerificationCodeService $verification,
    ) {}

    /**
     * Opens a limited session for email confirmation and sends a code when needed.
     */
    public function openVerificationGate(
        User $user,
        Request $request,
        bool $remember = false,
        ?string $statusMessage = null,
    ): RedirectResponse {
        Auth::login($user, $remember);
        $request->session()->regenerate();
        $this->sendCodeIfNeeded($user);

        return redirect()
            ->route('verification.notice')
            ->with('status', $statusMessage ?? __('coin.auth.verify_email_code_sent'));
    }

    public function sendCodeIfNeeded(User $user): void
    {
        if ($user->hasVerifiedEmail() || $this->verification->hasPendingCode($user)) {
            return;
        }

        $this->verification->sendCode($user);
    }
}
