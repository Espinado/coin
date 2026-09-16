<?php

use App\Models\Admin;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportGuestSession;
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
        logSupportChannelAuth('support.ticket.'.$ticketId, $user, true, 'admin');

        return true;
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
    $allowed = $user instanceof Admin;
    logSupportChannelAuth('support.admin', $user, $allowed, 'admin_only');

    return $allowed;
});

Broadcast::channel('admin.withdrawals', function ($user) {
    $allowed = $user instanceof Admin;
    logSupportChannelAuth('admin.withdrawals', $user, $allowed, 'admin_only');

    return $allowed;
});

Broadcast::channel('admin.plan-changes', function ($user) {
    $allowed = $user instanceof Admin;
    logSupportChannelAuth('admin.plan-changes', $user, $allowed, 'admin_only');

    return $allowed;
});

Broadcast::channel('wallet.user.{userId}', function ($user, int $userId) {
    $allowed = $user instanceof User && (int) $user->id === (int) $userId;
    logSupportChannelAuth('wallet.user.'.$userId, $user, $allowed, 'user_self');

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
