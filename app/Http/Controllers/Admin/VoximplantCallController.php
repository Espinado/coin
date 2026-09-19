<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Voximplant\VoximplantCallService;
use App\Services\Voximplant\VoximplantException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VoximplantCallController extends Controller
{

    public function oneTimeKey(Request $request, VoximplantCallService $calls): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:512'],
        ]);

        if (! $calls->isReady()) {
            return response()->json([
                'message' => __('coin.voximplant.not_configured'),
            ], 503);
        }

        return response()->json([
            'hash' => $calls->oneTimeLoginHash($validated['key']),
            'username' => $calls->sdkUsername(),
        ]);
    }

    public function store(User $user, VoximplantCallService $calls): RedirectResponse
    {
        if (! $calls->isReady()) {
            return redirect()
                ->back()
                ->with('status', __('coin.voximplant.not_configured'))
                ->with('status_type', 'error');
        }

        try {
            $calls->startOutboundCall($user);
        } catch (VoximplantException $exception) {
            return redirect()
                ->back()
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        return redirect()
            ->back()
            ->with('status', __('coin.voximplant.api_call_started', [
                'phone' => $calls->normalizeDestination($user) ?? $user->phone,
            ]))
            ->with('status_type', 'success');
    }
}
