<?php

namespace App\Services;

use App\Mail\LoginVerificationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginTwoFactorService
{
    public const SESSION_USER_KEY = 'login.two_factor.user_id';

    public const SESSION_REMEMBER_KEY = 'login.two_factor.remember';

    private const CACHE_PREFIX = 'login_2fa:';

    private const TTL_MINUTES = 10;

    public function beginChallenge(User $user, bool $remember, Request $request): void
    {
        $request->session()->put(self::SESSION_USER_KEY, $user->id);
        $request->session()->put(self::SESSION_REMEMBER_KEY, $remember);

        $this->sendCode($user, $request);
    }

    public function sendCode(User $user, Request $request): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->cacheKey($request), [
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
        ], now()->addMinutes(self::TTL_MINUTES));

        Mail::to($user->email)->send(new LoginVerificationMail($user, $code));
    }

    public function verify(string $code, Request $request): User
    {
        $this->ensureIsNotRateLimited($request);

        $userId = (int) $request->session()->get(self::SESSION_USER_KEY);

        if (! $userId) {
            throw ValidationException::withMessages([
                'code' => __('coin.auth.two_factor_invalid'),
            ]);
        }

        if ($this->challengeExpired($request)) {
            $this->clearChallenge($request);

            throw ValidationException::withMessages([
                'code' => __('coin.auth.two_factor_expired'),
            ]);
        }

        $payload = Cache::get($this->cacheKey($request));

        if (! Hash::check($code, (string) ($payload['code_hash'] ?? ''))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'code' => __('coin.auth.two_factor_invalid'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        Cache::forget($this->cacheKey($request));

        return User::query()->findOrFail($userId);
    }

    public function clearChallenge(Request $request): void
    {
        Cache::forget($this->cacheKey($request));
        $request->session()->forget([
            self::SESSION_USER_KEY,
            self::SESSION_REMEMBER_KEY,
        ]);
    }

    public function hasPendingChallenge(Request $request): bool
    {
        return $request->session()->has(self::SESSION_USER_KEY);
    }

    public function challengeExpired(Request $request): bool
    {
        if (! $this->hasPendingChallenge($request)) {
            return false;
        }

        $userId = (int) $request->session()->get(self::SESSION_USER_KEY);
        $payload = Cache::get($this->cacheKey($request));

        return ! is_array($payload) || (int) ($payload['user_id'] ?? 0) !== $userId;
    }

    public function rememberFromSession(Request $request): bool
    {
        return (bool) $request->session()->get(self::SESSION_REMEMBER_KEY, false);
    }

    public function pendingUser(Request $request): ?User
    {
        $userId = $request->session()->get(self::SESSION_USER_KEY);

        if (! $userId) {
            return null;
        }

        return User::query()->find($userId);
    }

    private function cacheKey(Request $request): string
    {
        return self::CACHE_PREFIX.$request->session()->getId();
    }

    private function throttleKey(Request $request): string
    {
        return 'login-two-factor|'.$request->session()->getId();
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'code' => __('coin.auth.login_throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }
}
