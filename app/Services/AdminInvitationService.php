<?php

namespace App\Services;

use App\Mail\AdminInvitationMail;
use App\Models\Admin;
use App\Models\AdminInvitation;
use App\Support\AdminRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class AdminInvitationService
{
    public function invite(Admin $inviter, string $email, ?string $name = null): AdminInvitation
    {
        $email = Str::lower(trim($email));

        if ($email === '') {
            throw new RuntimeException(__('coin.admin.admins.email_required'));
        }

        $existingAdmin = Admin::query()->where('email', $email)->first();

        if ($existingAdmin) {
            return $this->invitePasswordReset($inviter, $existingAdmin);
        }

        return $this->createInvitation(
            inviter: $inviter,
            email: $email,
            name: $name,
        );
    }

    public function invitePasswordReset(Admin $inviter, Admin $target): AdminInvitation
    {
        return $this->createInvitation(
            inviter: $inviter,
            email: $target->email,
            name: $target->name,
            targetAdmin: $target,
        );
    }

    public function requestPasswordResetByEmail(string $email): ?AdminInvitation
    {
        $email = Str::lower(trim($email));

        if ($email === '') {
            return null;
        }

        $admin = Admin::query()->where('email', $email)->first();

        if (! $admin) {
            return null;
        }

        return $this->invitePasswordReset($admin, $admin);
    }

    public function resend(AdminInvitation $invitation, Admin $inviter): AdminInvitation
    {
        if (! $invitation->isPending()) {
            throw new RuntimeException(__('coin.admin.admins.invitation_not_pending'));
        }

        if ($invitation->isPasswordReset() && $invitation->admin) {
            return $this->invitePasswordReset($inviter, $invitation->admin);
        }

        return $this->invite($inviter, $invitation->email, $invitation->name);
    }

    public function revoke(AdminInvitation $invitation): void
    {
        if ($invitation->isAccepted()) {
            throw new RuntimeException(__('coin.admin.admins.invitation_already_accepted'));
        }

        $invitation->delete();
    }

    public function findPendingByToken(string $plainToken): ?AdminInvitation
    {
        if (trim($plainToken) === '') {
            return null;
        }

        return AdminInvitation::query()
            ->where('token_hash', $this->hashToken($plainToken))
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function accept(AdminInvitation $invitation, string $name, string $password): Admin
    {
        if (! $invitation->isPending()) {
            throw new RuntimeException(__('coin.admin.admins.invitation_invalid'));
        }

        if ($invitation->isPasswordReset()) {
            return $this->acceptPasswordReset($invitation, $name, $password);
        }

        if (Admin::query()->where('email', $invitation->email)->exists()) {
            throw new RuntimeException(__('coin.admin.admins.email_exists'));
        }

        return DB::transaction(function () use ($invitation, $name, $password) {
            $admin = Admin::query()->create([
                'name' => trim($name),
                'email' => $invitation->email,
                'password' => $password,
                'role' => AdminRole::Operator,
            ]);

            $invitation->update(['accepted_at' => now()]);

            return $admin;
        });
    }

    public function inviteUrl(string $plainToken): string
    {
        return route('admin.invite.show', ['token' => $plainToken], absolute: true);
    }

    private function createInvitation(
        Admin $inviter,
        string $email,
        ?string $name = null,
        ?Admin $targetAdmin = null,
    ): AdminInvitation {
        AdminInvitation::query()
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->delete();

        $plainToken = Str::random(64);

        $invitation = AdminInvitation::query()->create([
            'email' => $email,
            'name' => $name !== null && trim($name) !== '' ? trim($name) : ($targetAdmin?->name),
            'admin_id' => $targetAdmin?->id,
            'token_hash' => $this->hashToken($plainToken),
            'invited_by_admin_id' => $inviter->id,
            'expires_at' => now()->addHours((int) config('coin.admin.invitation_ttl_hours', 72)),
        ]);

        Mail::to($email)->send(new AdminInvitationMail($invitation, $inviter, $plainToken));

        return $invitation;
    }

    private function acceptPasswordReset(AdminInvitation $invitation, string $name, string $password): Admin
    {
        $admin = $invitation->admin;

        if (! $admin || $admin->email !== $invitation->email) {
            throw new RuntimeException(__('coin.admin.admins.invitation_invalid'));
        }

        return DB::transaction(function () use ($invitation, $admin, $name, $password) {
            $admin->update([
                'name' => trim($name),
                'password' => $password,
            ]);

            $invitation->update(['accepted_at' => now()]);

            return $admin->fresh();
        });
    }

    private function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
