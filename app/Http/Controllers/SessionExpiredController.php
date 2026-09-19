<?php

namespace App\Http\Controllers;

use App\Support\SessionIdleTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionExpiredController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $guard = SessionIdleTracker::activeGuard($request);

        if ($guard !== null) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return SessionIdleTracker::idleLoginRedirect($guard ?? ($request->getHost() === config('coin.admin_domain') ? 'admin' : 'web'));
    }
}
