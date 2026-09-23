<?php

namespace App\Http\Middleware;

use App\Services\PlatformSettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformNotInMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        if (! app(PlatformSettingsService::class)->getBool('maintenance_mode')) {
            return $next($request);
        }

        return response()
            ->view('maintenance', [], Response::HTTP_SERVICE_UNAVAILABLE)
            ->header('Retry-After', '3600');
    }

    private function shouldBypass(Request $request): bool
    {
        return $request->is('webhooks/*');
    }
}
