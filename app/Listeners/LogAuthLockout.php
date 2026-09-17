<?php

namespace App\Listeners;

use App\Services\Auth\AuthAuditLogger;
use App\Services\Auth\AuthFailureStage;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\RateLimiter;

class LogAuthLockout
{
    public function __construct(
        private readonly AuthAuditLogger $authAuditLogger,
    ) {}

    public function handle(Lockout $event): void
    {
        $request = $event->request;
        $guard = $request->routeIs('admin.*') ? 'admin' : 'web';

        $retryAfter = null;
        if (method_exists($request, 'throttleKey')) {
            $retryAfter = RateLimiter::availableIn($request->throttleKey());
        }

        $this->authAuditLogger->logFailure(
            $guard,
            'credentials',
            AuthFailureStage::LOCKOUT,
            $request,
            array_filter([
                'retry_after_seconds' => $retryAfter,
            ], fn ($value) => $value !== null),
        );
    }
}
