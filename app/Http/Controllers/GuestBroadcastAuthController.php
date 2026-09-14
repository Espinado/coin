<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Symfony\Component\HttpFoundation\Response;

class GuestBroadcastAuthController extends Controller
{
    public function store(Request $request): Response
    {
        $channelName = (string) $request->input('channel_name');

        abort_unless(
            preg_match('/^private-support\.guest\.\d+$/', $channelName) === 1,
            403,
        );

        return Broadcast::auth($request);
    }
}
