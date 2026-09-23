<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use App\Services\AdminLoginTwoFactorService;
use App\Services\LoginTwoFactorService;
use App\Support\SessionIdleTracker;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        // env() only — config is not loaded yet during middleware registration.
        $trustedProxies = array_values(array_filter(array_map(
            trim(...),
            explode(',', (string) env('COIN_TRUSTED_PROXIES', '')),
        )));
        if ($trustedProxies === ['*']) {
            $middleware->trustProxies(at: '*');
        } elseif ($trustedProxies !== []) {
            $middleware->trustProxies(at: $trustedProxies);
        }

        $middleware->validateCsrfTokens(except: [
            'webhooks/ccapi',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\EnforceIdleSessionTimeout::class,
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
            'platform.maintenance' => \App\Http\Middleware\EnsurePlatformNotInMaintenance::class,
            'ccapi.webhook.source' => \App\Http\Middleware\EnsureCcapiWebhookSource::class,
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
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->isMethod('POST')) {
                return null;
            }

            $isCsrfMismatch = $e instanceof TokenMismatchException
                || ($e instanceof HttpException && $e->getStatusCode() === 419);

            if (! $isCsrfMismatch) {
                return null;
            }

            $host = $request->getHost();
            $isAdminHost = $host === config('coin.admin_domain');
            $isUserHost = $host === config('coin.user_domain');

            if (! $isAdminHost && ! $isUserHost) {
                return null;
            }

            $loginRoute = $isAdminHost ? 'admin.login' : 'login';
            $idleMessage = __('coin.auth.idle_logout', [
                'minutes' => SessionIdleTracker::idleMinutes(),
            ]);
            $sessionExpiredMessage = __('coin.auth.session_expired');

            $isLoginAttempt = $request->is('login*')
                || $request->routeIs(
                    'admin.login',
                    'admin.login.store',
                    'admin.login.two-factor',
                    'admin.login.two-factor.store',
                    'admin.login.two-factor.resend',
                    'login',
                    'login.store',
                    'login.two-factor',
                    'login.two-factor.store',
                    'login.two-factor.resend',
                );

            if ($isLoginAttempt) {
                if ($isAdminHost) {
                    app(AdminLoginTwoFactorService::class)->clearChallenge($request);
                } else {
                    app(LoginTwoFactorService::class)->clearChallenge($request);
                }

                return redirect()
                    ->route($loginRoute, ['cancel' => 1])
                    ->with('status', $sessionExpiredMessage);
            }

            if ($request->is('logout') || $request->headers->has('X-Livewire')) {
                return redirect()
                    ->route($loginRoute, ['idle' => 1])
                    ->with('status', $idleMessage);
            }

            return redirect()
                ->route($loginRoute, ['idle' => 1])
                ->with('status', $idleMessage);
        });
    })->create();
