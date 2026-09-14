<?php

namespace App\Services;

use App\Events\SupportTicketMessageSent;
use App\Events\SupportTicketUpdated;
use App\Models\Admin;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SupportTicketService
{
    public function createForGuest(string $email, string $subject, string $category, string $body): SupportTicket
    {
        return DB::transaction(function () use ($email, $subject, $category, $body) {
            $ticket = SupportTicket::query()->create([
                'user_id' => null,
                'guest_email' => $email,
                'guest_token' => Str::random(64),
                'reference' => $this->nextReference(),
                'subject' => $subject,
                'category' => $category,
                'status' => SupportTicket::STATUS_OPEN,
                'last_reply_at' => now(),
            ]);

            $this->addMessage($ticket, SupportTicketMessage::AUTHOR_GUEST, 0, $body);

            SupportGuestSession::put($ticket);

            return $ticket->load('messages');
        });
    }

    public function addGuestMessage(SupportTicket $ticket, string $token, string $body): SupportTicketMessage
    {
        $this->assertGuestTicketAccess($ticket, $token);

        if ($ticket->status === SupportTicket::STATUS_CLOSED) {
            $ticket->update(['status' => SupportTicket::STATUS_OPEN]);
        }

        return $this->addMessage($ticket, SupportTicketMessage::AUTHOR_GUEST, 0, $body);
    }

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

        $ticket = $ticket->fresh(['user.wallet', 'user.contracts.plan', 'messages', 'assignedAdmin']);

        $this->broadcastSupportEvent(new SupportTicketUpdated($ticket));

        return $ticket;
    }

    private function addMessage(SupportTicket $ticket, string $authorType, int $authorId, string $body): SupportTicketMessage
    {
        $message = $ticket->messages()->create([
            'author_type' => $authorType,
            'author_id' => $authorId,
            'body' => $body,
        ]);

        $ticket->update(['last_reply_at' => $message->created_at]);

        $this->broadcastSupportEvent(new SupportTicketMessageSent($message->fresh()));

        return $message;
    }

    private function broadcastSupportEvent(object $event): void
    {
        $channels = $event instanceof ShouldBroadcastNow
            ? collect($event->broadcastOn())->map(fn ($channel) => $channel->name)->values()->all()
            : [];

        try {
            event($event);

            if (config('broadcasting.connections.reverb.debug')) {
                Log::channel('reverb')->info('Support broadcast dispatched.', [
                    'event' => $event::class,
                    'channels' => $channels,
                    'driver' => config('broadcasting.default'),
                ]);
            }
        } catch (BroadcastException $exception) {
            Log::channel('reverb')->error('Support realtime broadcast failed.', [
                'event' => $event::class,
                'channels' => $channels,
                'driver' => config('broadcasting.default'),
                'reverb_host' => config('broadcasting.connections.reverb.options.host'),
                'reverb_port' => config('broadcasting.connections.reverb.options.port'),
                'message' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            Log::channel('reverb')->error('Support realtime broadcast error.', [
                'event' => $event::class,
                'channels' => $channels,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function assertTicketOwner(SupportTicket $ticket, User $user): void
    {
        abort_unless($ticket->user_id === $user->id, 403);
    }

    private function assertGuestTicketAccess(SupportTicket $ticket, string $token): void
    {
        abort_unless($ticket->isGuest(), 403);
        abort_unless(hash_equals($ticket->guest_token ?? '', $token), 403);
    }

    private function nextReference(): string
    {
        do {
            $reference = 'TKT-'.Str::upper(Str::random(6));
        } while (SupportTicket::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
