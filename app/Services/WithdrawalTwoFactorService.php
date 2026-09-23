<?php

namespace App\Services;

use App\Mail\WithdrawalVerificationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class WithdrawalTwoFactorService
{
    private const CACHE_PREFIX = 'withdrawal_2fa:';

    private const TTL_MINUTES = 10;

    /**
     * @return array{amount: float, currency: string}
     */
    public function beginChallenge(User $user, float $amount, string $currency, Request $request): array
    {
        $normalized = $this->normalizeIntent($amount, $currency);

        $this->storeChallenge($request, $user, $normalized['amount'], $normalized['currency']);
        $this->sendCode($user, $request);

        return $normalized;
    }

    public function sendCode(User $user, Request $request): void
    {
        $this->ensureResendIsNotRateLimited($request, $user);

        if ($this->challengeExpired($request, $user)) {
            throw ValidationException::withMessages([
                'payoutVerificationCode' => __('coin.payment_modal.payout_verify_expired'),
            ]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $payload = Cache::get($this->cacheKey($request));

        if (! is_array($payload)) {
            throw ValidationException::withMessages([
                'payoutVerificationCode' => __('coin.payment_modal.payout_verify_expired'),
            ]);
        }

        $payload['code_hash'] = Hash::make($code);

        Cache::put($this->cacheKey($request), $payload, now()->addMinutes(self::TTL_MINUTES));

        Mail::to($user->email)->send(new WithdrawalVerificationMail($user, $code));

        RateLimiter::hit($this->resendThrottleKey($request, $user), 60);
    }

    /**
     * @return array{amount: float, currency: string}
     */
    public function verify(string $code, User $user, Request $request): array
    {
        $this->ensureIsNotRateLimited($request, $user);

        if ($this->challengeExpired($request, $user)) {
            $this->clearChallenge($request);

            throw ValidationException::withMessages([
                'payoutVerificationCode' => __('coin.payment_modal.payout_verify_expired'),
            ]);
        }

        $payload = Cache::get($this->cacheKey($request));

        if (! is_array($payload) || (int) ($payload['user_id'] ?? 0) !== $user->id) {
            throw ValidationException::withMessages([
                'payoutVerificationCode' => __('coin.auth.two_factor_invalid'),
            ]);
        }

        if (! Hash::check($code, (string) ($payload['code_hash'] ?? ''))) {
            RateLimiter::hit($this->throttleKey($request, $user));

            throw ValidationException::withMessages([
                'payoutVerificationCode' => __('coin.auth.two_factor_invalid'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request, $user));
        Cache::forget($this->cacheKey($request));

        return [
            'amount' => (float) $payload['amount'],
            'currency' => (string) $payload['currency'],
        ];
    }

    public function clearChallenge(Request $request): void
    {
        Cache::forget($this->cacheKey($request));
    }

    public function hasPendingChallenge(Request $request, User $user): bool
    {
        $payload = Cache::get($this->cacheKey($request));

        return is_array($payload) && (int) ($payload['user_id'] ?? 0) === $user->id;
    }

    public function challengeExpired(Request $request, User $user): bool
    {
        if (! $this->hasPendingChallenge($request, $user)) {
            return true;
        }

        $payload = Cache::get($this->cacheKey($request));

        return ! is_array($payload) || (int) ($payload['user_id'] ?? 0) !== $user->id;
    }

    /**
     * @return array{amount: float, currency: string}
     */
    private function normalizeIntent(float $amount, string $currency): array
    {
        $decimals = $currency === 'BTC' ? 8 : 2;

        return [
            'amount' => (float) number_format($amount, $decimals, '.', ''),
            'currency' => $currency,
        ];
    }

    private function storeChallenge(Request $request, User $user, float $amount, string $currency): void
    {
        Cache::put($this->cacheKey($request), [
            'user_id' => $user->id,
            'amount' => number_format($amount, $currency === 'BTC' ? 8 : 2, '.', ''),
            'currency' => $currency,
            'code_hash' => '',
        ], now()->addMinutes(self::TTL_MINUTES));
    }

    private function cacheKey(Request $request): string
    {
        return self::CACHE_PREFIX.$request->session()->getId();
    }

    private function throttleKey(Request $request, User $user): string
    {
        return 'withdrawal-two-factor|'.$user->id.'|'.$request->session()->getId();
    }

    private function resendThrottleKey(Request $request, User $user): string
    {
        return 'withdrawal-two-factor-resend|'.$user->id.'|'.$request->session()->getId();
    }

    private function ensureResendIsNotRateLimited(Request $request, User $user): void
    {
        if (! RateLimiter::tooManyAttempts($this->resendThrottleKey($request, $user), 3)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->resendThrottleKey($request, $user));

        throw ValidationException::withMessages([
            'payoutVerificationCode' => __('coin.auth.login_throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function ensureIsNotRateLimited(Request $request, User $user): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request, $user), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request, $user));

        throw ValidationException::withMessages([
            'payoutVerificationCode' => __('coin.auth.login_throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }
}
