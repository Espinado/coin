<?php

namespace App\Services;

use App\Mail\PayoutAddressVerificationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PayoutAddressTwoFactorService
{
    public const ACTION_SAVE = 'save';

    public const ACTION_DISCONNECT = 'disconnect';

    private const CACHE_PREFIX = 'payout_address_2fa:';

    private const TTL_MINUTES = 10;

    /**
     * @return array{action: string, currency: string, address: string, previous_address: ?string}
     */
    public function beginChallenge(
        User $user,
        string $action,
        string $currency,
        ?string $address,
        ?string $previousAddress,
        Request $request,
    ): array {
        $normalized = $this->normalizeIntent($action, $currency, $address, $previousAddress);

        $this->storeChallenge($request, $user, $normalized);
        $this->sendCode($user, $request);

        return $normalized;
    }

    public function sendCode(User $user, Request $request): void
    {
        $this->ensureResendIsNotRateLimited($request, $user);

        if (! $this->hasPendingChallenge($request, $user)) {
            throw ValidationException::withMessages([
                'payoutAddressVerificationCode' => __('coin.profile.payout_address_verify_expired'),
            ]);
        }

        $payload = Cache::get($this->cacheKey($request));

        if (! is_array($payload)) {
            throw ValidationException::withMessages([
                'payoutAddressVerificationCode' => __('coin.profile.payout_address_verify_expired'),
            ]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $payload['code_hash'] = Hash::make($code);

        Cache::put($this->cacheKey($request), $payload, now()->addMinutes(self::TTL_MINUTES));

        Mail::to($user->email)->send(new PayoutAddressVerificationMail(
            user: $user,
            code: $code,
            action: (string) $payload['action'],
            currency: (string) $payload['currency'],
            address: (string) ($payload['address'] ?? ''),
            previousAddress: isset($payload['previous_address']) && $payload['previous_address'] !== ''
                ? (string) $payload['previous_address']
                : null,
        ));

        RateLimiter::hit($this->resendThrottleKey($request, $user), 60);
    }

    /**
     * @return array{action: string, currency: string, address: string, previous_address: ?string}
     */
    public function verify(string $code, User $user, Request $request): array
    {
        $this->ensureIsNotRateLimited($request, $user);

        if (! $this->hasPendingChallenge($request, $user)) {
            $this->clearChallenge($request);

            throw ValidationException::withMessages([
                'payoutAddressVerificationCode' => __('coin.profile.payout_address_verify_expired'),
            ]);
        }

        $payload = Cache::get($this->cacheKey($request));

        if (! is_array($payload) || (int) ($payload['user_id'] ?? 0) !== $user->id) {
            throw ValidationException::withMessages([
                'payoutAddressVerificationCode' => __('coin.auth.two_factor_invalid'),
            ]);
        }

        if (! Hash::check($code, (string) ($payload['code_hash'] ?? ''))) {
            RateLimiter::hit($this->throttleKey($request, $user));

            throw ValidationException::withMessages([
                'payoutAddressVerificationCode' => __('coin.auth.two_factor_invalid'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request, $user));
        Cache::forget($this->cacheKey($request));

        return [
            'action' => (string) $payload['action'],
            'currency' => (string) $payload['currency'],
            'address' => (string) ($payload['address'] ?? ''),
            'previous_address' => isset($payload['previous_address']) && $payload['previous_address'] !== ''
                ? (string) $payload['previous_address']
                : null,
        ];
    }

    public function clearChallenge(Request $request): void
    {
        if (! $request->hasSession()) {
            return;
        }

        Cache::forget($this->cacheKey($request));
    }

    public function hasPendingChallenge(Request $request, User $user): bool
    {
        $payload = Cache::get($this->cacheKey($request));

        return is_array($payload) && (int) ($payload['user_id'] ?? 0) === $user->id;
    }

    /**
     * @return array{action: string, currency: string, address: string, previous_address: ?string}
     */
    private function normalizeIntent(
        string $action,
        string $currency,
        ?string $address,
        ?string $previousAddress,
    ): array {
        $action = $action === self::ACTION_DISCONNECT ? self::ACTION_DISCONNECT : self::ACTION_SAVE;
        $currency = strtoupper(trim($currency));
        $address = trim((string) $address);
        $previous = trim((string) $previousAddress);

        return [
            'action' => $action,
            'currency' => $currency,
            'address' => $action === self::ACTION_SAVE ? $address : '',
            'previous_address' => $previous !== '' ? $previous : null,
        ];
    }

    /** @param array{action: string, currency: string, address: string, previous_address: ?string} $intent */
    private function storeChallenge(Request $request, User $user, array $intent): void
    {
        Cache::put($this->cacheKey($request), [
            'user_id' => $user->id,
            'action' => $intent['action'],
            'currency' => $intent['currency'],
            'address' => $intent['address'],
            'previous_address' => $intent['previous_address'],
            'code_hash' => '',
        ], now()->addMinutes(self::TTL_MINUTES));
    }

    private function cacheKey(Request $request): string
    {
        $this->ensureRequestSession($request);

        return self::CACHE_PREFIX.$request->session()->getId();
    }

    private function ensureRequestSession(Request $request): void
    {
        if ($request->hasSession()) {
            return;
        }

        $sessionManager = app('session');
        $store = $sessionManager->driver();

        if (! $store->isStarted()) {
            $store->start();
        }

        $request->setLaravelSession($store);
    }

    private function throttleKey(Request $request, User $user): string
    {
        $this->ensureRequestSession($request);

        return 'payout-address-two-factor|'.$user->id.'|'.$request->session()->getId();
    }

    private function resendThrottleKey(Request $request, User $user): string
    {
        $this->ensureRequestSession($request);

        return 'payout-address-two-factor-resend|'.$user->id.'|'.$request->session()->getId();
    }

    private function ensureResendIsNotRateLimited(Request $request, User $user): void
    {
        if (! RateLimiter::tooManyAttempts($this->resendThrottleKey($request, $user), 3)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->resendThrottleKey($request, $user));

        throw ValidationException::withMessages([
            'payoutAddressVerificationCode' => __('coin.auth.login_throttle', [
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
            'payoutAddressVerificationCode' => __('coin.auth.login_throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }
}
