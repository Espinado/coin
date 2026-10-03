<?php

namespace App\Livewire;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Rules\NotDisposableEmail;
use App\Services\SupportGuestSession;
use App\Services\SupportTicketService;
use App\Services\TurnstileVerifier;
use App\Support\Concerns\ThrottlesSupportActions;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class GuestSupportChat extends Component
{
    use ThrottlesSupportActions;

    public bool $isOpen = false;

    public ?int $ticketId = null;

    public string $guestEmail = '';

    public string $newSubject = '';

    public string $newCategory = SupportTicket::CATEGORY_OTHER;

    public string $newBody = '';

    public string $turnstileToken = '';

    public int $turnstileWidgetKey = 0;

    /** pending|checking|passed|failed|expired */
    public string $turnstileStatus = 'pending';

    public bool $turnstileCleared = false;

    public string $replyBody = '';

    public int $replyFormKey = 0;

    public int $lastSeenMessageId = 0;

    public function mount(): void
    {
        $ticket = SupportGuestSession::current();

        if ($ticket) {
            $this->ticketId = $ticket->id;
            $this->guestEmail = $ticket->guest_email ?? '';
            $this->lastSeenMessageId = (int) ($ticket->messages->max('id') ?? 0);
            $this->markTicketRead($ticket);
        }

        if (! $this->turnstileEnabled) {
            $this->turnstileCleared = true;
            $this->turnstileStatus = 'passed';
        }
    }

    #[On('open-guest-support')]
    public function openChat(): void
    {
        $this->isOpen = true;

        if ($this->ticketId) {
            $this->markTicketRead($this->selectedTicket);
            $this->bootGuestRealtime();

            return;
        }

        if (! $this->turnstileEnabled) {
            $this->turnstileCleared = true;
            $this->turnstileStatus = 'passed';

            return;
        }

        $this->turnstileCleared = false;
        $this->turnstileStatus = 'checking';
        $this->resetTurnstileWidget(dispatchReset: true);
        $this->js('window.setTimeout(function () { window.renderGuestTurnstile && window.renderGuestTurnstile(true); }, 80)');
    }

    public function closeChat(): void
    {
        $this->isOpen = false;

        if (! $this->ticketId && $this->turnstileEnabled) {
            $this->turnstileCleared = false;
            $this->turnstileStatus = 'pending';
            $this->turnstileToken = '';
        }
    }

    public function markTurnstileChecking(): void
    {
        if ($this->turnstileCleared) {
            return;
        }

        $this->turnstileStatus = 'checking';
    }

    public function markTurnstilePassed(string $token): void
    {
        $token = trim($token);

        if ($token === '') {
            $this->markTurnstileFailed();

            return;
        }

        $this->turnstileToken = $token;
        $this->turnstileStatus = 'passed';
        $this->turnstileCleared = true;
        $this->resetErrorBag('turnstileToken');
    }

    public function markTurnstileFailed(): void
    {
        $this->turnstileToken = '';
        $this->turnstileCleared = false;
        $this->turnstileStatus = 'failed';
        $this->resetTurnstileWidget(dispatchReset: true);
    }

    public function markTurnstileExpired(): void
    {
        $this->turnstileToken = '';
        $this->turnstileCleared = false;
        $this->turnstileStatus = 'expired';
        $this->resetTurnstileWidget(dispatchReset: true);
    }

    public function pollMessages(): void
    {
        if (! $this->isOpen || ! $this->ticketId) {
            return;
        }

        $ticket = SupportGuestSession::current()?->loadMissing('messages');

        if (! $ticket || $ticket->id !== $this->ticketId) {
            return;
        }

        $newAdminMessages = $ticket->messages
            ->filter(fn (SupportTicketMessage $message) => $message->id > $this->lastSeenMessageId && $message->isFromAdmin())
            ->values();

        if ($newAdminMessages->isEmpty()) {
            return;
        }

        $this->lastSeenMessageId = (int) $newAdminMessages->max('id');
        $this->markTicketRead($ticket);

        $this->dispatch('guest-support-new-messages', messages: $newAdminMessages
            ->map(fn (SupportTicketMessage $message) => $this->formatMessageForBroadcast($message))
            ->all());
    }

    public function createTicket(SupportTicketService $support, TurnstileVerifier $turnstile): void
    {
        $this->throttleSupportAction('guest-create-ticket');

        if ($turnstile->enabled() && ! $this->turnstileCleared) {
            throw ValidationException::withMessages([
                'turnstileToken' => __('coin.support.captcha_required'),
            ]);
        }

        $this->guestEmail = strtolower(trim($this->guestEmail));
        $this->newSubject = trim($this->newSubject);
        $this->newBody = trim($this->newBody);

        $validated = $this->validate([
            'guestEmail' => ['required', 'email', 'max:255', new NotDisposableEmail],
            'newSubject' => ['required', 'string', 'min:3', 'max:120'],
            'newCategory' => ['required', 'in:'.implode(',', array_keys(SupportTicket::categories()))],
            'newBody' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'guestEmail.email' => __('coin.auth.email_invalid'),
        ], [
            'guestEmail' => 'email',
            'newSubject' => 'subject',
            'newCategory' => 'category',
            'newBody' => 'message',
        ]);

        if ($turnstile->enabled()) {
            if (trim($this->turnstileToken) === '') {
                $this->turnstileCleared = false;
                $this->turnstileStatus = 'pending';
                $this->resetTurnstileWidget(dispatchReset: true);

                throw ValidationException::withMessages([
                    'turnstileToken' => __('coin.support.captcha_required'),
                ]);
            }

            if (! $turnstile->verify($this->turnstileToken, request()->ip())) {
                $this->turnstileCleared = false;
                $this->turnstileStatus = 'failed';
                $this->resetTurnstileWidget(dispatchReset: true);

                throw ValidationException::withMessages([
                    'turnstileToken' => __('coin.support.captcha_failed'),
                ]);
            }
        }

        $ticket = $support->createForGuest(
            $validated['guestEmail'],
            $validated['newSubject'],
            $validated['newCategory'],
            $validated['newBody'],
        );

        $this->ticketId = $ticket->id;
        $this->lastSeenMessageId = (int) ($ticket->messages->max('id') ?? 0);
        $this->newSubject = '';
        $this->newBody = '';
        $this->newCategory = SupportTicket::CATEGORY_OTHER;
        $this->turnstileToken = '';
        $this->markTicketRead($ticket);
        $this->bootGuestRealtime($ticket);
        $this->dispatch('support-thread-scroll');
        $this->dispatch('support-message-sent');
    }

    public function sendReply(SupportTicketService $support): void
    {
        $this->throttleSupportAction('guest-reply', 20);

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
        $this->lastSeenMessageId = max($this->lastSeenMessageId, $message->id);
        $this->dispatch('support-append-message', message: $this->formatMessageForBroadcast($message));
        $this->dispatch('support-thread-scroll');
        $this->dispatch('support-message-sent');
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

    public function getTurnstileEnabledProperty(): bool
    {
        return app(TurnstileVerifier::class)->enabled();
    }

    public function getTurnstileSiteKeyProperty(): string
    {
        return app(TurnstileVerifier::class)->siteKey();
    }

    public function render(): View
    {
        return view('livewire.guest-support-chat');
    }

    private function resetTurnstileWidget(bool $dispatchReset = true): void
    {
        $this->turnstileToken = '';
        $this->turnstileWidgetKey++;

        if ($dispatchReset) {
            $this->dispatch('guest-turnstile-reset');
        }
    }

    private function bootGuestRealtime(?SupportTicket $ticket = null): void
    {
        $ticket ??= $this->selectedTicket;

        if (! $ticket) {
            return;
        }

        $this->dispatch('guest-support-opened', ticketId: $ticket->id, guestToken: $ticket->guest_token);

        $this->js(sprintf(
            'window.coinReverb = Object.assign(window.coinReverb || {}, %s); window.bootGuestSupportRealtime?.(%d);',
            json_encode([
                'guestTicketId' => $ticket->id,
                'guestToken' => $ticket->guest_token,
            ], JSON_THROW_ON_ERROR),
            $ticket->id,
        ));
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
