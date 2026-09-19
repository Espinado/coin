<?php

namespace App\Http\Middleware;

use App\Support\SessionIdleTracker;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceIdleSessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = SessionIdleTracker::activeGuard($request);

        if ($guard === null) {
            return $next($request);
        }

        if (SessionIdleTracker::isExpired($request)) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->headers->has('X-Livewire')) {
                return response('', 401);
            }

            return redirect(SessionIdleTracker::logoutRouteForGuard($guard))
                ->with('status', __('coin.auth.idle_logout', [
                    'minutes' => SessionIdleTracker::idleMinutes(),
                ]));
        }

        if (SessionIdleTracker::shouldTouch($request)) {
            SessionIdleTracker::markNow($request);
        }

        return $next($request);
    }
}
