<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SessionIdleTracker
{
    public const SESSION_KEY = 'last_activity_at';

    public const ACTIVITY_COOKIE = 'coin_last_activity';

    public static function idleMinutes(): int
    {
        return max(1, (int) config('coin.session.idle_minutes', 15));
    }

    public static function markNow(Request $request): void
    {
        $request->session()->put(self::SESSION_KEY, now()->toIso8601String());
    }

    public static function lastActivity(Request $request): ?Carbon
    {
        $value = $request->session()->get(self::SESSION_KEY);

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function isExpired(Request $request): bool
    {
        $lastActivity = self::lastActivity($request);

        if ($lastActivity === null) {
            return false;
        }

        return $lastActivity->diffInMinutes(now()) >= self::idleMinutes();
    }

    public static function shouldTouch(Request $request): bool
    {
        if ($request->is('login', 'login/*', 'logout', 'session-expired', 'register', 'register/*', 'forgot-password', 'reset-password/*')) {
            return false;
        }

        if ($request->is('webhooks/*', 'guest/broadcasting/auth')) {
            return false;
        }

        if ($request->isMethod('GET') && ! $request->headers->has('X-Livewire')) {
            return true;
        }

        if ($request->headers->get('X-User-Activity') === '1') {
            return true;
        }

        $cookie = $request->cookie(self::ACTIVITY_COOKIE);

        if (! is_string($cookie) || ! ctype_digit($cookie)) {
            return false;
        }

        return (int) floor(microtime(true) * 1000) - (int) $cookie <= 120_000;
    }

    public static function activeGuard(Request $request): ?string
    {
        if (Auth::guard('admin')->check()) {
            return 'admin';
        }

        if (Auth::guard('web')->check()) {
            return 'web';
        }

        return null;
    }

    public static function logoutRouteForGuard(?string $guard): string
    {
        return $guard === 'admin' ? route('admin.login') : route('login');
    }

    public static function idleLoginRedirect(?string $guard): \Illuminate\Http\RedirectResponse
    {
        return redirect(self::idleLoginRoute($guard))
            ->with('status', __('coin.auth.idle_logout', [
                'minutes' => self::idleMinutes(),
            ]));
    }

    public static function idleLoginRoute(?string $guard): string
    {
        return $guard === 'admin'
            ? route('admin.login', ['idle' => 1])
            : route('login', ['idle' => 1]);
    }
}
