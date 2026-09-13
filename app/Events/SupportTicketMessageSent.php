<?php

namespace App\Events;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportTicketMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SupportTicketMessage $message,
    ) {
        $this->message->loadMissing('ticket.user');
    }

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        $ticket = $this->message->ticket;

        return [
            new PrivateChannel('support.ticket.'.$ticket->id),
            new PrivateChannel('support.user.'.$ticket->user_id),
            new PrivateChannel('support.admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'SupportTicketMessageSent';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $ticket = $this->message->ticket;
        $ticket->loadMissing('messages');

        return [
            'message' => [
                'id' => $this->message->id,
                'ticket_id' => $this->message->support_ticket_id,
                'author_type' => $this->message->author_type,
                'author_label' => $this->message->authorLabelForBroadcast(),
                'body' => $this->message->body,
                'created_at' => $this->message->created_at?->format('M j, Y H:i'),
                'is_from_admin' => $this->message->isFromAdmin(),
            ],
            'ticket' => [
                'id' => $ticket->id,
                'status' => $ticket->status,
                'status_label' => $ticket->statusLabel(),
                'reference' => $ticket->reference,
                'updated_at' => $ticket->updated_at?->format('M j, H:i'),
            ],
            'unread_for_admin' => $ticket->unreadMessagesForAdmin(),
            'unread_for_user' => $ticket->unreadMessagesForUser(),
            'total_unread_for_admin' => SupportTicket::totalUnreadForAdmin(),
        ];
    }
}
