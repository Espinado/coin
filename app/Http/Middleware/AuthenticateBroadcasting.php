<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateBroadcasting
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $guard = 'web'): Response
    {
        $user = auth($guard)->user();

        if ($user === null) {
            if (config('broadcasting.connections.reverb.debug')) {
                Log::channel('reverb')->warning('Broadcast auth rejected: unauthenticated.', [
                    'guard' => $guard,
                    'host' => $request->getHost(),
                    'channel_name' => $request->input('channel_name'),
                ]);
            }

            abort(403);
        }

        if (config('broadcasting.connections.reverb.debug')) {
            Log::channel('reverb')->info('Broadcast auth accepted.', [
                'guard' => $guard,
                'host' => $request->getHost(),
                'user_type' => $user::class,
                'user_id' => $user->getAuthIdentifier(),
                'channel_name' => $request->input('channel_name'),
                'socket_id' => $request->input('socket_id'),
            ]);
        }

        $request->setUserResolver(static fn () => $user);

        return $next($request);
    }
}
