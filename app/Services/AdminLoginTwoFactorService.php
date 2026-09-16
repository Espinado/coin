<?php

namespace App\Services;

use App\Mail\AdminLoginVerificationMail;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AdminLoginTwoFactorService
{
    public const SESSION_ADMIN_KEY = 'admin.login.two_factor.admin_id';

    public const SESSION_REMEMBER_KEY = 'admin.login.two_factor.remember';

    public const SESSION_POST_LOGIN_MESSAGE = 'admin.login.two_factor.post_message';

    private const CACHE_PREFIX = 'admin_login_2fa:';

    private const TTL_MINUTES = 10;

    public function beginChallenge(Admin $admin, bool $remember, Request $request, ?string $postLoginMessageKey = null): void
    {
        $request->session()->put(self::SESSION_ADMIN_KEY, $admin->id);
        $request->session()->put(self::SESSION_REMEMBER_KEY, $remember);

        if ($postLoginMessageKey !== null) {
            $request->session()->put(self::SESSION_POST_LOGIN_MESSAGE, $postLoginMessageKey);
        }

        $this->sendCode($admin, $request);
    }

    public function sendCode(Admin $admin, Request $request): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->cacheKey($request), [
            'admin_id' => $admin->id,
            'code_hash' => Hash::make($code),
        ], now()->addMinutes(self::TTL_MINUTES));

        Mail::to($admin->email)->send(new AdminLoginVerificationMail($admin, $code));
    }

    public function verify(string $code, Request $request): Admin
    {
        $this->ensureIsNotRateLimited($request);

        $adminId = (int) $request->session()->get(self::SESSION_ADMIN_KEY);
        $payload = Cache::get($this->cacheKey($request));

        if (! $adminId || ! is_array($payload) || (int) ($payload['admin_id'] ?? 0) !== $adminId) {
            throw ValidationException::withMessages([
                'code' => __('coin.auth.two_factor_invalid'),
            ]);
        }

        if (! Hash::check($code, (string) ($payload['code_hash'] ?? ''))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'code' => __('coin.auth.two_factor_invalid'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        Cache::forget($this->cacheKey($request));

        return Admin::query()->findOrFail($adminId);
    }

    public function clearChallenge(Request $request): void
    {
        Cache::forget($this->cacheKey($request));
        $request->session()->forget([
            self::SESSION_ADMIN_KEY,
            self::SESSION_REMEMBER_KEY,
            self::SESSION_POST_LOGIN_MESSAGE,
        ]);
    }

    public function hasPendingChallenge(Request $request): bool
    {
        return $request->session()->has(self::SESSION_ADMIN_KEY);
    }

    public function rememberFromSession(Request $request): bool
    {
        return (bool) $request->session()->get(self::SESSION_REMEMBER_KEY, false);
    }

    public function pullPostLoginMessageKey(Request $request): ?string
    {
        $key = $request->session()->get(self::SESSION_POST_LOGIN_MESSAGE);

        $request->session()->forget(self::SESSION_POST_LOGIN_MESSAGE);

        return is_string($key) && $key !== '' ? $key : null;
    }

    public function pendingAdmin(Request $request): ?Admin
    {
        $adminId = $request->session()->get(self::SESSION_ADMIN_KEY);

        if (! $adminId) {
            return null;
        }

        return Admin::query()->find($adminId);
    }

    private function cacheKey(Request $request): string
    {
        return self::CACHE_PREFIX.$request->session()->getId();
    }

    private function throttleKey(Request $request): string
    {
        return 'admin-login-two-factor|'.$request->session()->getId();
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
