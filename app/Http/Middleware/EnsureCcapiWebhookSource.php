<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCcapiWebhookSource
{
    /** @var list<string> */
    private const DEFAULT_PRODUCTION_IPS = ['168.119.158.209'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('production')) {
            return $next($request);
        }

        $allowedIps = $this->allowedIps();
        $clientIp = (string) $request->ip();

        if (! in_array($clientIp, $allowedIps, true)) {
            return response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
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
