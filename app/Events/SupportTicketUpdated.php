<?php

namespace App\Events;

use App\Models\SupportTicket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportTicketUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SupportTicket $ticket,
    ) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('support.ticket.'.$this->ticket->id),
            new PrivateChannel('support.user.'.$this->ticket->user_id),
            new PrivateChannel('support.admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'SupportTicketUpdated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $this->ticket->loadMissing('messages');

        return [
            'ticket' => [
                'id' => $this->ticket->id,
                'status' => $this->ticket->status,
                'status_label' => $this->ticket->statusLabel(),
                'reference' => $this->ticket->reference,
                'updated_at' => $this->ticket->updated_at?->format('M j, H:i'),
            ],
            'unread_for_admin' => $this->ticket->unreadMessagesForAdmin(),
            'unread_for_user' => $this->ticket->unreadMessagesForUser(),
            'total_unread_for_admin' => SupportTicket::totalUnreadForAdmin(),
        ];
    }
}
