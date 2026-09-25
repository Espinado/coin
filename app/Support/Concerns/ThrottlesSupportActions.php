<?php

namespace App\Support\Concerns;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

trait ThrottlesSupportActions
{
    protected function throttleSupportAction(string $action, int $maxAttempts = 5, int $decaySeconds = 60): void
    {
        $key = sprintf('support:%s:%s', $action, $this->supportThrottleKey());

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'rate_limit' => __('coin.support.too_many_requests', ['seconds' => $seconds]),
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    protected function supportThrottleKey(): string
    {
        if (auth()->check()) {
            return 'user:'.auth()->id();
        }

        return 'ip:'.request()->ip();
    }
}
