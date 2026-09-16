<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Services\AdminInvitationService;
use App\Services\AdminLoginTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use RuntimeException;

class AcceptInvitationController extends Controller
{
    public function create(string $token, AdminInvitationService $invitations): View|RedirectResponse
    {
        $invitation = $invitations->findPendingByToken($token);

        if (! $invitation) {
            return redirect()
                ->route('admin.login')
                ->with('status', __('coin.admin.admins.invitation_invalid'))
                ->with('status_type', 'error');
        }

        return view('admin.auth.accept-invite', [
            'invitation' => $invitation,
            'token' => $token,
            'isPasswordReset' => $invitation->isPasswordReset(),
        ]);
    }

    public function store(string $token, Request $request, AdminInvitationService $invitations, AdminLoginTwoFactorService $twoFactor): RedirectResponse
    {
        $invitation = $invitations->findPendingByToken($token);

        if (! $invitation) {
            return redirect()
                ->route('admin.login')
                ->with('status', __('coin.admin.admins.invitation_invalid'))
                ->with('status_type', 'error');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $admin = $invitations->accept($invitation, $validated['name'], $validated['password']);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.invite.show', ['token' => $token])
                ->withInput()
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        $messageKey = $invitation->isPasswordReset()
            ? 'coin.admin.admins.password_reset_completed'
            : 'coin.admin.admins.invite_accepted';

        $twoFactor->beginChallenge($admin, false, $request, $messageKey);

        return redirect()->route('admin.login.two-factor');
    }
}
