<?php

namespace App\Services;

use App\Mail\EmailVerificationMail;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class EmailVerificationCodeService
{
    private const CACHE_PREFIX = 'email_verify:';

    private const TTL_MINUTES = 10;

    public function sendCode(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->cacheKey($user), [
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
        ], now()->addMinutes(self::TTL_MINUTES));

        Mail::to($user->email)->send(new EmailVerificationMail($user, $code));
    }

    public function verify(User $user, string $code, Request $request): User
    {
        $this->ensureIsNotRateLimited($request, $user);

        if (! $this->hasPendingCode($user)) {
            throw ValidationException::withMessages([
                'code' => __('coin.auth.verify_email_expired'),
            ]);
        }

        $payload = Cache::get($this->cacheKey($user));

        if (! Hash::check($code, (string) ($payload['code_hash'] ?? ''))) {
            RateLimiter::hit($this->throttleKey($request, $user));

            throw ValidationException::withMessages([
                'code' => __('coin.auth.verify_email_invalid'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request, $user));
        Cache::forget($this->cacheKey($user));

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return $user->fresh();
    }

    public function hasPendingCode(User $user): bool
    {
        $payload = Cache::get($this->cacheKey($user));

        return is_array($payload) && (int) ($payload['user_id'] ?? 0) === $user->id;
    }

    private function cacheKey(User $user): string
    {
        return self::CACHE_PREFIX.$user->id;
    }

    private function throttleKey(Request $request, User $user): string
    {
        return 'email-verify|'.$user->id.'|'.$request->ip();
    }

    private function ensureIsNotRateLimited(Request $request, User $user): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request, $user), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request, $user));

        throw ValidationException::withMessages([
            'code' => __('coin.auth.login_throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }
}
