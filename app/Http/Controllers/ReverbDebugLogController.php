<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ReverbDebugLogController extends Controller
{
    public function store(Request $request): Response
    {
        abort_unless(config('broadcasting.connections.reverb.debug'), 404);

        $validated = $request->validate([
            'level' => ['required', 'string', 'in:info,warn,error,debug'],
            'message' => ['required', 'string', 'max:500'],
            'context' => ['nullable', 'array'],
        ]);

        $user = $request->user();
        $context = array_merge($validated['context'] ?? [], [
            'user_type' => $user ? $user::class : null,
            'user_id' => $user?->getAuthIdentifier(),
            'ip' => $request->ip(),
        ]);

        Log::channel('reverb')->log($validated['level'], '[client] '.$validated['message'], $context);

        return response()->noContent();
    }
}
