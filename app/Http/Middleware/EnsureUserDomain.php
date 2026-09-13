<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getHost() !== config('coin.user_domain')) {
            abort(404);
        }

        return $next($request);
    }
}
