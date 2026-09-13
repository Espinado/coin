<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupportTicketService
{
    public function createForUser(User $user, string $subject, string $category, string $body): SupportTicket
    {
        return DB::transaction(function () use ($user, $subject, $category, $body) {
            $ticket = SupportTicket::query()->create([
                'user_id' => $user->id,
                'reference' => $this->nextReference(),
                'subject' => $subject,
                'category' => $category,
                'status' => SupportTicket::STATUS_OPEN,
                'last_reply_at' => now(),
            ]);

            $this->addMessage($ticket, SupportTicketMessage::AUTHOR_USER, $user->id, $body);

            return $ticket->load('messages');
        });
    }

    public function addUserMessage(SupportTicket $ticket, User $user, string $body): SupportTicketMessage
    {
        $this->assertTicketOwner($ticket, $user);

        if ($ticket->status === SupportTicket::STATUS_CLOSED) {
            $ticket->update(['status' => SupportTicket::STATUS_OPEN]);
        }

        return $this->addMessage($ticket, SupportTicketMessage::AUTHOR_USER, $user->id, $body);
    }

    public function addAdminMessage(SupportTicket $ticket, Admin $admin, string $body, ?string $status = null): SupportTicketMessage
    {
        $ticket->update([
            'assigned_admin_id' => $admin->id,
            'status' => $status ?? SupportTicket::STATUS_PENDING,
        ]);

        return $this->addMessage($ticket, SupportTicketMessage::AUTHOR_ADMIN, $admin->id, $body);
    }

    public function updateStatus(SupportTicket $ticket, string $status, Admin $admin): SupportTicket
    {
        $ticket->update([
            'status' => $status,
            'assigned_admin_id' => $ticket->assigned_admin_id ?? $admin->id,
        ]);

        return $ticket->fresh(['user.wallet', 'user.contracts.plan', 'messages', 'assignedAdmin']);
    }

    private function addMessage(SupportTicket $ticket, string $authorType, int $authorId, string $body): SupportTicketMessage
    {
        $message = $ticket->messages()->create([
            'author_type' => $authorType,
            'author_id' => $authorId,
            'body' => $body,
        ]);

        $ticket->update(['last_reply_at' => $message->created_at]);

        return $message;
    }

    private function assertTicketOwner(SupportTicket $ticket, User $user): void
    {
        abort_unless($ticket->user_id === $user->id, 403);
    }

    private function nextReference(): string
    {
        do {
            $reference = 'TKT-'.Str::upper(Str::random(6));
        } while (SupportTicket::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
