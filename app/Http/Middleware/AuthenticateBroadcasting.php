<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateBroadcasting
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $guard = 'web'): Response
    {
        $user = auth($guard)->user();

        abort_unless($user !== null, 403);

        $request->setUserResolver(static fn () => $user);

        return $next($request);
    }
}
