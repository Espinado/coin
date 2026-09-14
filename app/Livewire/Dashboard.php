<?php

namespace App\Livewire;

use App\Models\Plan;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Services\DashboardDataService;
use App\Services\PlatformSettingsService;
use App\Services\SupportTicketService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class Dashboard extends Component
{
    public int $section = 0;

    public int $power = 1200;

    public ?int $selectedPlanId = null;

    public int $period = 1;

    public bool $menuOpen = false;

    public string $symbol = 'COIN';

    public int $epochsPerDay = 3;

    public $wallet;

    public $user;

    public $primaryContract;

    public $primaryPlan;

    public $referral;

    /** @var Collection<int, mixed> */
    public Collection $referralAccruals;

    /** @var Collection<int, mixed> */
    public Collection $plans;

    /** @var Collection<int, mixed> */
    public Collection $activeContracts;

    /** @var Collection<int, mixed> */
    public Collection $completedContracts;

    /** @var Collection<int, mixed> */
    public Collection $transactions;

    /** @var Collection<string, mixed> */
    public Collection $periodTotals;

    /** @var Collection<int, mixed> */
    public Collection $tickets;

    public ?int $selectedTicketId = null;

    public bool $showCreateTicket = false;

    public string $newSubject = '';

    public string $newCategory = SupportTicket::CATEGORY_OTHER;

    public string $newBody = '';

    public string $replyBody = '';

    public int $replyFormKey = 0;

    public int $createFormKey = 0;

    public function mount(DashboardDataService $data, PlatformSettingsService $settings): void
    {
        $this->symbol = $settings->tokenSymbol();
        $this->epochsPerDay = $settings->epochsPerDay();

        $payload = $data->forUser(auth()->user());

        $this->user = auth()->user();
        $this->wallet = $payload['wallet'];
        $this->plans = $payload['plans'];
        $this->activeContracts = $payload['activeContracts'];
        $this->completedContracts = $payload['completedContracts'];
        $this->transactions = $payload['transactions'];
        $this->periodTotals = $payload['periodTotals'];
        $this->referral = $payload['referral'];
        $this->referralAccruals = $payload['referralAccruals'];
        $this->primaryContract = $payload['primaryContract'];
        $this->primaryPlan = $payload['primaryPlan'];
        $this->power = (int) ($this->user->active_tflops ?: 1200);
        $this->selectedPlanId = $this->primaryPlan?->id
            ?? $data->planForPower($this->power)?->id;
        $this->tickets = $this->user->supportTickets()
            ->with('messages')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function setSection(int $section): void
    {
        $this->section = $section;
        $this->menuOpen = false;

        if ($section === 7) {
            $this->prepareSupportChat();
        } else {
            $this->refreshSupportUnreadState();
        }
    }

    public function openSupport(): void
    {
        $this->section = 7;
        $this->menuOpen = false;
        $this->prepareSupportChat();

        if ($this->selectedTicketId) {
            $this->markTicketRead($this->selectedTicketId);
        }

        $this->syncSupportUnreadBadge();
    }

    public function setPeriod(int $period): void
    {
        $this->period = $period;
    }

    public function selectPlan(int $planId): void
    {
        $plan = $this->plans->firstWhere('id', $planId);

        if (! $plan instanceof Plan) {
            return;
        }

        $this->selectedPlanId = $plan->id;
        $this->power = (int) $plan->tflops;
    }

    public function updatedPower(): void
    {
        $plan = app(DashboardDataService::class)->planForPower($this->power);

        if ($plan instanceof Plan) {
            $this->selectedPlanId = $plan->id;
        }
    }

    public function toggleMenu(): void
    {
        $this->menuOpen = ! $this->menuOpen;
    }

    public function closeMenu(): void
    {
        $this->menuOpen = false;
    }

    public function getTitleProperty(): string
    {
        return app(DashboardDataService::class)->sectionMeta()[$this->section][0];
    }

    public function getSubtitleProperty(): string
    {
        if ($this->section === 0) {
            return 'Account overview · epoch '.($this->user->epoch_label ?? '—');
        }

        return app(DashboardDataService::class)->sectionMeta()[$this->section][1];
    }

    public function getActiveContractCountProperty(): int
    {
        return $this->activeContracts->count();
    }

    public function getTotalAllocatedTflopsProperty(): string
    {
        $total = $this->activeContracts->sum('tflops');

        return number_format($total, 0, '.', ',').' TF';
    }

    public function getLifetimeRewardsProperty(): string
    {
        $total = $this->activeContracts->merge($this->completedContracts)->sum('accrued_amount');

        return number_format((float) $total, 2, '.', ',');
    }

    public function getNextExpiryLabelProperty(): string
    {
        return $this->user->next_expiry_label ?? '—';
    }

    public function getNextSettlementLabelProperty(): string
    {
        $parts = explode('·', (string) $this->user->epoch_label);

        return trim($parts[1] ?? '02:14:38');
    }

    public function getPowerLabelProperty(): string
    {
        return number_format($this->power, 0, '.', ',');
    }

    public function getSelectedPlanProperty(): ?Plan
    {
        if ($this->selectedPlanId !== null) {
            $selected = $this->plans->firstWhere('id', $this->selectedPlanId);

            if ($selected instanceof Plan) {
                return $selected;
            }
        }

        return app(DashboardDataService::class)->planForPower($this->power);
    }

    public function getPlanNameProperty(): string
    {
        return $this->selectedPlan?->name ?? '—';
    }

    public function getPlanInfraProperty(): string
    {
        return $this->selectedPlan?->infra ?? '—';
    }

    public function getPlanPriceProperty(): string
    {
        return $this->selectedPlan?->price_label ?? '—';
    }

    public function getPlanComputeProperty(): string
    {
        return number_format($this->power, 0, '.', ',').' TFLOPS';
    }

    public function getPlanTermProperty(): string
    {
        return $this->selectedPlan?->formattedDuration() ?? '—';
    }

    public function getDailyProperty(): string
    {
        return $this->formatAmount($this->dailyAmount(), 2);
    }

    public function getMonthlyProperty(): string
    {
        return $this->formatAmount($this->dailyAmount() * 30, 1);
    }

    public function getYearlyProperty(): string
    {
        return $this->formatAmount($this->dailyAmount() * 365, 0);
    }

    public function getPeriodLabelProperty(): string
    {
        $keys = ['day', 'week', 'month'];

        return $this->periodTotals[$keys[$this->period]]->period_label ?? 'PER WEEK';
    }

    public function getPeriodTotalProperty(): string
    {
        $keys = ['day', 'week', 'month'];

        return $this->periodTotals[$keys[$this->period]]->total_label ?? '35.28';
    }

    public function getSelectedTicketProperty(): ?SupportTicket
    {
        if (! $this->selectedTicketId) {
            return null;
        }

        return $this->tickets->firstWhere('id', $this->selectedTicketId);
    }

    public function getUnreadSupportCountProperty(): int
    {
        return $this->unreadSupportTotalForUser();
    }

    public function getTicketCategoriesProperty(): array
    {
        return SupportTicket::categories();
    }

    public function copyReferralLink(): void
    {
        $url = $this->referral?->shareUrl();

        abort_unless(filled($url), 404);

        $this->js(sprintf(
            'navigator.clipboard.writeText(%s).then(() => window.showSupportToast?.(%s)).catch(() => window.showSupportToast?.(%s, "error"))',
            json_encode($url, JSON_THROW_ON_ERROR),
            json_encode('Link copied'),
            json_encode('Could not copy link'),
        ));
    }

    public function openCreateTicket(): void
    {
        $this->showCreateTicket = true;
        $this->selectedTicketId = null;
        $this->resetValidation();
    }

    public function cancelCreateTicket(): void
    {
        $this->showCreateTicket = false;
        $this->newSubject = '';
        $this->newCategory = SupportTicket::CATEGORY_OTHER;
        $this->newBody = '';
        $this->resetValidation();
    }

    public function selectTicket(int $ticketId): void
    {
        abort_unless(
            $this->tickets->contains('id', $ticketId),
            403
        );

        $this->selectedTicketId = $ticketId;
        $this->showCreateTicket = false;
        $this->replyBody = '';
        $this->markTicketRead($ticketId);
        $this->syncSupportUnreadBadge();
    }

    public function createTicket(SupportTicketService $support): void
    {
        $this->newSubject = trim($this->newSubject);
        $this->newBody = trim($this->newBody);

        $validated = $this->validate([
            'newSubject' => ['required', 'string', 'min:3', 'max:120'],
            'newCategory' => ['required', 'in:'.implode(',', array_keys(SupportTicket::categories()))],
            'newBody' => ['required', 'string', 'min:10', 'max:5000'],
        ], [], [
            'newSubject' => 'subject',
            'newCategory' => 'category',
            'newBody' => 'message',
        ]);

        $ticket = $support->createForUser(
            $this->user,
            $validated['newSubject'],
            $validated['newCategory'],
            $validated['newBody'],
        );

        $this->reloadTickets();
        $this->selectedTicketId = $ticket->id;
        $this->showCreateTicket = false;
        $this->newSubject = '';
        $this->newCategory = SupportTicket::CATEGORY_OTHER;
        $this->newBody = '';
        $this->createFormKey++;
        $this->markTicketRead($ticket->id);
        $this->syncSupportUnreadBadge();
        $this->dispatch('support-thread-scroll');
        $this->dispatch('support-message-sent', message: 'Message sent');
    }

    #[On('echo-private:support.user.{user.id},.SupportTicketMessageSent')]
    #[On('echo-private:support.user.{user.id},.SupportTicketUpdated')]
    public function onSupportTicketRealtime(mixed $payload = null): void
    {
        $ticketId = is_int($payload)
            ? $payload
            : (is_array($payload) ? ($payload['message']['ticket_id'] ?? $payload['ticket']['id'] ?? null) : null);

        $this->reloadTickets();

        $viewingTicket = $this->section === 7 && $this->selectedTicketId;

        if ($viewingTicket) {
            if ($ticketId === null || $ticketId === $this->selectedTicketId) {
                $this->markTicketRead($this->selectedTicketId);
            }
        }

        if (is_array($payload) && isset($payload['message'])) {
            $message = $payload['message'];
            $isFromAdmin = $message['is_from_admin'] ?? false;
            $messageTicketId = $message['ticket_id'] ?? null;

            if ($viewingTicket && ($messageTicketId === null || $messageTicketId === $this->selectedTicketId)) {
                $this->dispatch('support-append-message', message: $message);
                $this->dispatch('support-thread-scroll');
            }

            if ($isFromAdmin && $this->section !== 7) {
                $this->dispatch('support-message-received', incomingMessage: $message);
            }
        }

        if ($this->section !== 7) {
            $this->syncSupportUnreadBadge();
        }
    }

    public function sendTicketReply(SupportTicketService $support): void
    {
        $ticket = $this->selectedTicket;

        abort_unless($ticket !== null, 403);

        $this->replyBody = trim($this->replyBody);

        $validated = $this->validate([
            'replyBody' => ['required', 'string', 'min:2', 'max:5000'],
        ], [], [
            'replyBody' => 'message',
        ]);

        $message = $support->addUserMessage($ticket, $this->user, $validated['replyBody']);

        $this->replyBody = '';
        $this->replyFormKey++;
        $this->reloadTickets();
        $this->selectedTicketId = $ticket->id;
        $this->dispatch('support-append-message', message: $this->formatMessageForBroadcast($message));
        $this->dispatch('support-thread-scroll');
        $this->dispatch('support-message-sent', message: 'Message sent');
        $this->syncSupportUnreadBadge();
    }

    public function render(): View
    {
        return view('livewire.dashboard')
            ->layout('layouts.coin-dashboard', ['title' => 'Coin — Dashboard']);
    }

    private function dailyAmount(): float
    {
        $plan = $this->selectedPlan;
        $rate = app(DashboardDataService::class)->rewardRate();

        if (! $plan instanceof Plan) {
            return 0;
        }

        return $this->power * $rate * (float) $plan->reward_multiplier;
    }

    private function formatAmount(float $value, int $decimals): string
    {
        return number_format($value, $decimals, '.', ',');
    }

    private function reloadTickets(): void
    {
        $this->tickets = $this->user->supportTickets()
            ->with('messages')
            ->orderByDesc('updated_at')
            ->get();
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

    private function prepareSupportChat(): void
    {
        $this->showCreateTicket = false;

        if ($this->tickets->isEmpty()) {
            $this->selectedTicketId = null;
            $this->showCreateTicket = true;

            return;
        }

        $preferred = $this->tickets->first(
            fn (SupportTicket $ticket) => $ticket->unreadMessagesForUser() > 0
        ) ?? $this->tickets->first();

        $this->selectedTicketId = $preferred?->id;
    }

    private function refreshSupportUnreadState(): void
    {
        $this->reloadTickets();
        $this->syncSupportUnreadBadge();
    }

    private function unreadSupportTotalForUser(): int
    {
        return (int) SupportTicket::totalUnreadForUser($this->user->id);
    }

    private function dispatchUnreadSupportBadge(): void
    {
        $this->dispatch('support-unread-updated', count: $this->unreadSupportTotalForUser());
    }

    private function syncSupportUnreadBadge(): void
    {
        $this->dispatchUnreadSupportBadge();
    }

    private function markTicketRead(?int $ticketId): void
    {
        if (! $ticketId) {
            return;
        }

        $ticket = $this->tickets->firstWhere('id', $ticketId);

        if (! $ticket) {
            return;
        }

        $now = now();

        SupportTicket::query()
            ->whereKey($ticketId)
            ->where('user_id', $this->user->id)
            ->update(['user_last_read_at' => $now]);

        $ticket->user_last_read_at = $now;
    }
}
