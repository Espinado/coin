<?php

use App\Models\Admin;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\AdminAuthorization;
use App\Services\SupportGuestSession;
use App\Support\AdminAbility;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('support.ticket.{ticketId}', function ($user, int $ticketId) {
    $ticket = SupportTicket::query()->find($ticketId);

    if (! $ticket) {
        logSupportChannelAuth('support.ticket.'.$ticketId, $user, false, 'ticket_not_found');

        return false;
    }

    if ($user instanceof User) {
        $allowed = (int) $ticket->user_id === (int) $user->id;
        logSupportChannelAuth('support.ticket.'.$ticketId, $user, $allowed, 'user_owner_check');

        return $allowed;
    }

    if ($user instanceof Admin) {
        $allowed = app(AdminAuthorization::class)->allows($user, AdminAbility::ManageSupport);
        logSupportChannelAuth('support.ticket.'.$ticketId, $user, $allowed, 'admin_support_ability');

        return $allowed;
    }

    logSupportChannelAuth('support.ticket.'.$ticketId, $user, false, 'unknown_user_type');

    return false;
});

Broadcast::channel('support.user.{userId}', function ($user, int $userId) {
    $allowed = $user instanceof User && (int) $user->id === (int) $userId;
    logSupportChannelAuth('support.user.'.$userId, $user, $allowed, 'user_self');

    return $allowed;
});

Broadcast::channel('support.admin', function ($user) {
    $allowed = $user instanceof Admin
        && app(AdminAuthorization::class)->allows($user, AdminAbility::ManageSupport);
    logSupportChannelAuth('support.admin', $user, $allowed, 'admin_support_ability');

    return $allowed;
});

Broadcast::channel('admin.withdrawals', function ($user) {
    $allowed = $user instanceof Admin
        && app(AdminAuthorization::class)->allows($user, AdminAbility::ManageWithdrawals);
    logSupportChannelAuth('admin.withdrawals', $user, $allowed, 'admin_withdrawals_ability');

    return $allowed;
});

Broadcast::channel('admin.deposits', function ($user) {
    $allowed = $user instanceof Admin
        && app(AdminAuthorization::class)->allows($user, AdminAbility::ManageDeposits);
    logSupportChannelAuth('admin.deposits', $user, $allowed, 'admin_deposits_ability');

    return $allowed;
});

Broadcast::channel('admin.plan-changes', function ($user) {
    $allowed = $user instanceof Admin
        && app(AdminAuthorization::class)->allows($user, AdminAbility::ManagePlanChanges);
    logSupportChannelAuth('admin.plan-changes', $user, $allowed, 'admin_plan_changes_ability');

    return $allowed;
});

Broadcast::channel('wallet.user.{userId}', function ($user, int $userId) {
    $allowed = $user instanceof User && (int) $user->id === (int) $userId;
    logSupportChannelAuth('wallet.user.'.$userId, $user, $allowed, 'user_self');

    return $allowed;
});

Broadcast::channel('notifications.user.{userId}', function ($user, int $userId) {
    $allowed = $user instanceof User && (int) $user->id === (int) $userId;
    logSupportChannelAuth('notifications.user.'.$userId, $user, $allowed, 'user_self');

    return $allowed;
});

Broadcast::channel('support.guest.{ticketId}', function ($user, int $ticketId) {
    $allowed = SupportGuestSession::canAccessTicket($ticketId);
    logSupportChannelAuth('support.guest.'.$ticketId, $user, $allowed, 'guest_session');

    return $allowed;
});

if (! function_exists('logSupportChannelAuth')) {
    function logSupportChannelAuth(string $channel, mixed $user, bool $allowed, string $reason): void
    {
        if (! config('broadcasting.connections.reverb.debug')) {
            return;
        }

        Log::channel('reverb')->info('Channel auth evaluated.', [
            'channel' => $channel,
            'allowed' => $allowed,
            'reason' => $reason,
            'user_type' => is_object($user) ? $user::class : null,
            'user_id' => is_object($user) && method_exists($user, 'getAuthIdentifier') ? $user->getAuthIdentifier() : null,
        ]);
    }
}
