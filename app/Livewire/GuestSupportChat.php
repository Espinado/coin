<?php

namespace App\Livewire;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Services\SupportGuestSession;
use App\Services\SupportTicketService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class GuestSupportChat extends Component
{
    public bool $isOpen = false;

    public ?int $ticketId = null;

    public string $guestEmail = '';

    public string $newSubject = '';

    public string $newCategory = SupportTicket::CATEGORY_OTHER;

    public string $newBody = '';

    public string $replyBody = '';

    public int $replyFormKey = 0;

    public function mount(): void
    {
        $ticket = SupportGuestSession::current();

        if ($ticket) {
            $this->ticketId = $ticket->id;
            $this->guestEmail = $ticket->guest_email ?? '';
            $this->markTicketRead($ticket);
        }
    }

    #[On('open-guest-support')]
    public function openChat(): void
    {
        $this->isOpen = true;

        if ($this->ticketId) {
            $this->markTicketRead($this->selectedTicket);
            $this->dispatch('guest-support-opened', ticketId: $this->ticketId);
        }
    }

    public function closeChat(): void
    {
        $this->isOpen = false;
    }

    public function createTicket(SupportTicketService $support): void
    {
        $this->guestEmail = strtolower(trim($this->guestEmail));
        $this->newSubject = trim($this->newSubject);
        $this->newBody = trim($this->newBody);

        $validated = $this->validate([
            'guestEmail' => ['required', 'email', 'max:255'],
            'newSubject' => ['required', 'string', 'min:3', 'max:120'],
            'newCategory' => ['required', 'in:'.implode(',', array_keys(SupportTicket::categories()))],
            'newBody' => ['required', 'string', 'min:10', 'max:5000'],
        ], [], [
            'guestEmail' => 'email',
            'newSubject' => 'subject',
            'newCategory' => 'category',
            'newBody' => 'message',
        ]);

        $ticket = $support->createForGuest(
            $validated['guestEmail'],
            $validated['newSubject'],
            $validated['newCategory'],
            $validated['newBody'],
        );

        $this->ticketId = $ticket->id;
        $this->newSubject = '';
        $this->newBody = '';
        $this->newCategory = SupportTicket::CATEGORY_OTHER;
        $this->markTicketRead($ticket);
        $this->dispatch('guest-support-opened', ticketId: $ticket->id);
        $this->dispatch('support-thread-scroll');
        $this->dispatch('support-message-sent', message: 'Message sent');
    }

    public function sendReply(SupportTicketService $support): void
    {
        $ticket = $this->selectedTicket;

        abort_unless($ticket !== null, 403);

        $this->replyBody = trim($this->replyBody);

        $validated = $this->validate([
            'replyBody' => ['required', 'string', 'min:2', 'max:5000'],
        ], [], [
            'replyBody' => 'message',
        ]);

        $token = SupportGuestSession::token();

        abort_unless($token !== null, 403);

        $message = $support->addGuestMessage($ticket, $token, $validated['replyBody']);

        $this->replyBody = '';
        $this->replyFormKey++;
        $this->ticketId = $ticket->id;
        $this->dispatch('support-append-message', message: $this->formatMessageForBroadcast($message));
        $this->dispatch('support-thread-scroll');
        $this->dispatch('support-message-sent', message: 'Message sent');
    }

    #[On('guest-support-realtime')]
    public function onSupportTicketRealtime(mixed $payload = null): void
    {
        if (! $this->ticketId) {
            return;
        }

        $ticket = SupportGuestSession::current();

        if (! $ticket || $ticket->id !== $this->ticketId) {
            return;
        }

        if ($this->isOpen) {
            $this->markTicketRead($ticket);
        }

        if (is_array($payload) && isset($payload['message'])) {
            $message = $payload['message'];

            if ($message['is_from_admin'] ?? false) {
                if ($this->isOpen) {
                    $this->dispatch('support-append-message', message: $message);
                    $this->dispatch('support-thread-scroll');
                } else {
                    $this->dispatch('support-message-received', incomingMessage: $message);
                }
            }
        }
    }

    public function getSelectedTicketProperty(): ?SupportTicket
    {
        if (! $this->ticketId) {
            return null;
        }

        $ticket = SupportGuestSession::current();

        if (! $ticket || $ticket->id !== $this->ticketId) {
            return null;
        }

        return $ticket->loadMissing('messages');
    }

    public function getTicketCategoriesProperty(): array
    {
        return SupportTicket::categories();
    }

    public function render(): View
    {
        return view('livewire.guest-support-chat');
    }

    private function markTicketRead(?SupportTicket $ticket): void
    {
        if (! $ticket) {
            return;
        }

        $now = now();
        $ticket->forceFill(['user_last_read_at' => $now])->save();
        $ticket->user_last_read_at = $now;
    }

    /** @return array<string, mixed> */
    private function formatMessageForBroadcast(SupportTicketMessage $message): array
    {
        return [
            'id' => $message->id,
            'ticket_id' => $message->support_ticket_id,
            'author_type' => $message->author_type,
            'author_label' => $message->authorLabelForBroadcast(),
            'body' => $message->body,
            'created_at' => $message->created_at?->format('M j, Y H:i'),
            'is_from_admin' => $message->isFromAdmin(),
        ];
    }
}
