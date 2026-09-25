<?php

namespace App\Http\Middleware;

use App\Services\PlatformSettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureCcapiWebhookSource
{
    /** @var list<string> */
    private const DEFAULT_PRODUCTION_IPS = ['168.119.158.209'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldEnforceIpCheck()) {
            return $next($request);
        }

        $allowedIps = $this->allowedIps();
        $clientIp = (string) $request->ip();

        if (! in_array($clientIp, $allowedIps, true)) {
            Log::warning('ccapi.webhook.blocked_ip', [
                'client_ip' => $clientIp,
                'allowed_ips' => $allowedIps,
            ]);

            return response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }

    private function shouldEnforceIpCheck(): bool
    {
        if (app()->environment(['production', 'staging'])) {
            return true;
        }

        return app(PlatformSettingsService::class)->usesLivePaymentGateway()
            && (string) config('coin.payments.driver', 'mock') === 'ccapi';
    }

    /** @return list<string> */
    private function allowedIps(): array
    {
        $configured = config('coin.payments.ccapi.webhook_ips');

        if (is_array($configured) && $configured !== []) {
            return $configured;
        }

        return self::DEFAULT_PRODUCTION_IPS;
    }
}
