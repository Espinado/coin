<?php

namespace App\Http\Middleware;

use App\Services\AdminAuthorization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAbility
{
    public function __construct(
        private AdminAuthorization $authorization,
    ) {}

    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $admin = $request->user('admin');

        if (! $admin || ! $this->authorization->allows($admin, $ability)) {
            abort(403, __('coin.admin.forbidden'));
        }

        return $next($request);
    }
}
