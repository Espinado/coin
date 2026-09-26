<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->getHost() === config('coin.admin_domain') ? 'ru' : 'en';

        app()->setLocale($locale);

        return $next($request);
    }
}
