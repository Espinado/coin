<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use App\Services\AdminLoginTwoFactorService;
use App\Services\LoginTwoFactorService;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
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

        $middleware->validateCsrfTokens(except: [
            'webhooks/ccapi',
        ]);

        $middleware->alias([
            'user.domain' => \App\Http\Middleware\EnsureUserDomain::class,
            'admin.domain' => \App\Http\Middleware\EnsureAdminDomain::class,
            'reject.web.on.admin' => \App\Http\Middleware\RejectWebGuardOnAdmin::class,
            'broadcast.auth' => \App\Http\Middleware\AuthenticateBroadcasting::class,
            'user.not-blocked' => \App\Http\Middleware\EnsureUserNotBlocked::class,
            'record.user.login' => \App\Http\Middleware\RecordUserLogin::class,
            'admin.ability' => \App\Http\Middleware\EnsureAdminAbility::class,
            'auth.page.no-cache' => \App\Http\Middleware\PreventAuthPageCache::class,
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
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if (! $request->isMethod('POST')) {
                return null;
            }

            if (! $request->is('login', 'login/two-factor', 'login/two-factor/resend')) {
                return null;
            }

            $isAdminHost = $request->getHost() === config('coin.admin_domain');

            if ($isAdminHost) {
                app(AdminLoginTwoFactorService::class)->clearChallenge($request);

                return redirect()
                    ->route('admin.login', ['cancel' => 1])
                    ->with('status', __('coin.auth.session_expired'));
            }

            app(LoginTwoFactorService::class)->clearChallenge($request);

            return redirect()
                ->route('login', ['cancel' => 1])
                ->with('status', __('coin.auth.session_expired'));
        });
    })->create();
