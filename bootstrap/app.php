<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->domain(config('coin.admin_domain'))
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'user.domain' => \App\Http\Middleware\EnsureUserDomain::class,
            'admin.domain' => \App\Http\Middleware\EnsureAdminDomain::class,
            'reject.web.on.admin' => \App\Http\Middleware\RejectWebGuardOnAdmin::class,
            'broadcast.auth' => \App\Http\Middleware\AuthenticateBroadcasting::class,
        ]);

        Authenticate::redirectUsing(function (Request $request) {
            if ($request->getHost() === config('coin.admin_domain')) {
                return route('admin.login');
            }

            return route('login');
        });

        RedirectIfAuthenticated::redirectUsing(function (Request $request) {
            if ($request->getHost() === config('coin.admin_domain')) {
                return route('admin.dashboard');
            }

            return route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
