<?php

namespace App\Http\Controllers;

use App\Services\SupportGuestSession;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Symfony\Component\HttpFoundation\Response;

class GuestBroadcastAuthController extends Controller
{
    public function store(Request $request): Response
    {
        $channelName = (string) $request->input('channel_name');
        $socketId = (string) $request->input('socket_id');

        abort_unless(
            preg_match('/^private-support\.guest\.(\d+)$/', $channelName, $matches) === 1,
            403,
        );

        abort_unless($socketId !== '', 403);
        abort_unless(SupportGuestSession::canAccessTicket((int) $matches[1]), 403);

        $driver = Broadcast::driver();

        // PusherBroadcaster (also used by the Reverb driver) rejects guarded channels when
        // Auth::user() is null — before channel callbacks run. Guest support is authorized
        // via session/token instead, so sign the private channel response directly.
        if ($driver instanceof PusherBroadcaster) {
            return response()->json(
                $driver->validAuthenticationResponse($request, true)
            );
        }

        return Broadcast::auth($request);
    }
}
