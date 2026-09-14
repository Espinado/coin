<?php

namespace App\Events;

use App\Models\SupportTicket;
use Illuminate\Broadcasting\PrivateChannel;

trait SupportTicketBroadcastChannels
{
    /** @return array<int, PrivateChannel> */
    protected function supportTicketChannels(SupportTicket $ticket): array
    {
        $channels = [
            new PrivateChannel('support.ticket.'.$ticket->id),
            new PrivateChannel('support.admin'),
        ];

        if ($ticket->user_id) {
            $channels[] = new PrivateChannel('support.user.'.$ticket->user_id);
        } else {
            $channels[] = new PrivateChannel('support.guest.'.$ticket->id);
        }

        return $channels;
    }
}
