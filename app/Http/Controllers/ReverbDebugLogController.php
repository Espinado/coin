<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ReverbDebugLogController extends Controller
{
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'level' => ['required', 'string', 'in:info,warn,error,debug'],
            'message' => ['required', 'string', 'max:500'],
            'context' => ['nullable', 'array'],
        ]);

        $isMonitor = str_starts_with($validated['message'], '[ws_state]')
            || ($validated['context']['monitor'] ?? false);

        $debugEnabled = (bool) config('broadcasting.connections.reverb.debug');
        $monitorEnabled = (bool) config('broadcasting.connections.reverb.connection_monitor');

        abort_unless($debugEnabled || ($isMonitor && $monitorEnabled), 404);

        $user = $request->user();
        $context = array_merge($validated['context'] ?? [], [
            'user_type' => $user ? $user::class : null,
            'user_id' => $user?->getAuthIdentifier(),
            'ip' => $request->ip(),
        ]);

        if ($isMonitor) {
            Log::channel('reverb_connection')->log($validated['level'], '[client] '.$validated['message'], $context);
        } else {
            Log::channel('reverb')->log($validated['level'], '[client] '.$validated['message'], $context);
        }

        return response()->noContent();
    }
}
