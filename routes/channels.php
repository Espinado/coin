<?php

use App\Models\Admin;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('support.ticket.{ticketId}', function ($user, int $ticketId) {
    $ticket = SupportTicket::query()->find($ticketId);

    if (! $ticket) {
        return false;
    }

    if ($user instanceof User) {
        return (int) $ticket->user_id === (int) $user->id;
    }

    if ($user instanceof Admin) {
        return true;
    }

    return false;
});

Broadcast::channel('support.user.{userId}', function ($user, int $userId) {
    return $user instanceof User && (int) $user->id === (int) $userId;
});

Broadcast::channel('support.admin', function ($user) {
    return $user instanceof Admin;
});
