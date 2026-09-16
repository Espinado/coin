<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Admin\Concerns\RedirectsWithAdminFlash;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminInvitation;
use App\Services\AdminInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AdminStaffController extends Controller
{
    use AdminListQuery;
    use RedirectsWithAdminFlash;

    public function index(Request $request): View
    {
        $search = $this->adminSearchTerm($request);

        $query = Admin::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            });

        $this->adminApplySort($request, $query, [
            'name' => 'name',
            'email' => 'email',
            'created_at' => 'created_at',
        ], 'created_at', 'desc');

        return view('admin.admins.index', [
            'admins' => $this->adminPaginate($query, $request),
            'pendingInvitations' => AdminInvitation::query()
                ->pending()
                ->with('invitedBy')
                ->orderByDesc('created_at')
                ->get(),
            ...$this->adminListState($request),
        ]);
    }

    public function create(): View
    {
        return view('admin.admins.invite');
    }

    public function store(Request $request, AdminInvitationService $invitations): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $existingAdmin = Admin::query()->where('email', $validated['email'])->first();

        try {
            $invitations->invite(
                $request->user('admin'),
                $validated['email'],
                $validated['name'] ?? null,
            );
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.admins.invite')
                ->withInput()
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        $messageKey = $existingAdmin
            ? 'coin.admin.flash.admin_password_reset_sent'
            : 'coin.admin.flash.admin_invited';

        return $this->adminSuccess($messageKey, 'admin.admins.index');
    }

    public function resetPassword(Admin $admin, AdminInvitationService $invitations, Request $request): RedirectResponse
    {
        if ($request->user('admin')->is($admin)) {
            return $this->adminError('coin.admin.flash.admin_password_reset_self', 'admin.admins.index');
        }

        try {
            $invitations->invitePasswordReset($request->user('admin'), $admin);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.admins.index')
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        return $this->adminSuccess('coin.admin.flash.admin_password_reset_sent', 'admin.admins.index');
    }

    public function destroy(Request $request, Admin $admin): RedirectResponse
    {
        if ($request->user('admin')->is($admin)) {
            return $this->adminError('coin.admin.flash.admin_delete_self', 'admin.admins.index');
        }

        if (Admin::query()->count() <= 1) {
            return $this->adminError('coin.admin.flash.admin_delete_last', 'admin.admins.index');
        }

        $admin->delete();

        return $this->adminSuccess('coin.admin.flash.admin_deleted', 'admin.admins.index');
    }

    public function destroyInvitation(AdminInvitation $invitation, AdminInvitationService $invitations): RedirectResponse
    {
        try {
            $invitations->revoke($invitation);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.admins.index')
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        return $this->adminSuccess('coin.admin.flash.admin_invitation_revoked', 'admin.admins.index');
    }

    public function resendInvitation(AdminInvitation $invitation, AdminInvitationService $invitations, Request $request): RedirectResponse
    {
        try {
            $invitations->resend($invitation, $request->user('admin'));
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.admins.index')
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        return $this->adminSuccess('coin.admin.flash.admin_invitation_resent', 'admin.admins.index');
    }
}
