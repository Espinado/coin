<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Services\AdminInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestPasswordResetController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.forgot-password');
    }

    public function store(Request $request, AdminInvitationService $invitations): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $invitations->requestPasswordResetByEmail($validated['email']);

        return redirect()
            ->route('admin.login')
            ->with('status', __('coin.admin.admins.password_reset_sent'))
            ->with('status_type', 'success');
    }
}
