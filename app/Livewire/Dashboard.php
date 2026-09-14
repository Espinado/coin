<?php

namespace App\Livewire;

use App\Models\Deposit;
use App\Models\Plan;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Services\DashboardDataService;
use App\Services\DepositService;
use App\Services\PlanPurchaseService;
use App\Services\PlatformSettingsService;
use App\Services\SupportTicketService;
use App\Services\WithdrawalService;
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
    public Collection $referralCommissions;

    /** @var Collection<int, mixed> */
    public Collection $profitTransactions;

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

    public string $depositAmount = '';

    public string $withdrawAmount = '';

    public ?string $paymentModal = null;

    public string $paymentModalStep = 'review';

    public ?string $paymentModalError = null;

    public ?string $paymentModalReference = null;

    public ?string $actionMessage = null;

    public string $profilePhone = '';

    public string $profileTelegram = '';

    public string $profileCountry = '';

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
        $this->referralCommissions = $payload['referralCommissions'];
        $this->profitTransactions = $payload['profitTransactions'];
        $this->primaryContract = $payload['primaryContract'];
        $this->primaryPlan = $payload['primaryPlan'];
        $this->profilePhone = (string) ($this->user->phone ?? '');
        $this->profileTelegram = (string) ($this->user->telegram ?? '');
        $this->profileCountry = (string) ($this->user->country_code ?? '');
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

    public function buyPlan(int $planId): void
    {
        $plan = $this->plans->firstWhere('id', $planId);

        if (! $plan instanceof Plan) {
            return;
        }

        if ($plan->isCurrentFor($this->primaryPlan)) {
            $this->section = 2;

            return;
        }

        $this->selectPlan($planId);
        $this->openInvestmentPaymentModal();
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
            return __('coin.sections.overview_sub').' · '.$this->activeContractCount.' '.__('coin.invest.active_count');
        }

        return app(DashboardDataService::class)->sectionMeta()[$this->section][1];
    }

    public function getActiveContractCountProperty(): int
    {
        return $this->activeContracts->count();
    }

    public function getTotalAllocatedTflopsProperty(): string
    {
        return $this->totalLockedBalance;
    }

    public function getTotalLockedBalanceProperty(): string
    {
        $total = $this->activeContracts->sum(
            fn ($contract) => (float) ($contract->principal_amount ?? $contract->plan?->price_amount ?? 0)
        );

        if ($total <= 0 && $this->wallet?->locked_balance) {
            $total = (float) $this->wallet->locked_balance;
        }

        return number_format($total, 2, '.', ',').' USDT';
    }

    /** @return array{items: list<array{name: string, percent: int, color: string}>, utilized: int, gradient: string} */
    public function getPlanAllocationProperty(): array
    {
        $colors = [
            'oklch(0.86 0.12 192)',
            'oklch(0.72 0.11 215)',
            'oklch(0.7 0.15 292)',
            'rgba(214,238,248,0.2)',
        ];

        $total = $this->activeContracts->sum(
            fn ($contract) => (float) ($contract->principal_amount ?? $contract->plan?->price_amount ?? 0)
        );

        if ($total <= 0) {
            return [
                'items' => [],
                'utilized' => 0,
                'gradient' => 'rgba(214,238,248,0.16) 100%',
            ];
        }

        $grouped = $this->activeContracts->groupBy(
            fn ($contract) => $contract->plan?->name ?? 'Other'
        );

        $items = [];
        $offset = 0;
        $gradientParts = [];

        foreach ($grouped as $name => $contracts) {
            $amount = $contracts->sum(
                fn ($contract) => (float) ($contract->principal_amount ?? $contract->plan?->price_amount ?? 0)
            );
            $percent = (int) round($amount / $total * 100);
            $color = $colors[count($items) % count($colors)];
            $items[] = ['name' => (string) $name, 'percent' => $percent, 'color' => $color];
            $end = min(100, $offset + max($percent, 1));
            $gradientParts[] = $color.' '.$offset.'% '.$end.'%';
            $offset = $end;
        }

        $walletTotal = (float) ($this->wallet?->balance ?? $total);

        return [
            'items' => $items,
            'utilized' => min(100, (int) round($total / max(1, $walletTotal) * 100)),
            'gradient' => implode(', ', $gradientParts) ?: 'rgba(214,238,248,0.16) 100%',
        ];
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
        $plan = $this->selectedPlan;

        if ($plan?->formattedMinDeposit()) {
            return $plan->formattedMinDeposit();
        }

        return number_format($this->power, 0, '.', ',').' USDT';
    }

    public function getPlanTermProperty(): string
    {
        return $this->selectedPlan?->formattedDuration() ?? '—';
    }

    public function getCalculatorTermLabelProperty(): string
    {
        return $this->selectedPlan?->calculatorTermLabel() ?? 'CONTRACT TERM';
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
        return match ($this->period) {
            0 => 'PER DAY',
            2 => 'PER MONTH',
            default => 'PER WEEK',
        };
    }

    public function getPeriodTotalProperty(): string
    {
        return number_format($this->profitTotalForPeriod($this->period), 2, '.', ',');
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

    public function openTopUpPaymentModal(): void
    {
        $this->resetActionFeedback();
        $this->resetPaymentModal();

        $this->validate([
            'depositAmount' => ['required', 'numeric', 'min:1'],
        ], [], [
            'depositAmount' => 'amount',
        ]);

        $this->paymentModal = 'topup';
        $this->paymentModalStep = 'review';
    }

    public function confirmTopUpPayment(DepositService $deposits): void
    {
        if ($this->paymentModal !== 'topup' || $this->paymentModalStep !== 'review') {
            return;
        }

        $this->paymentModalError = null;
        $this->paymentModalStep = 'processing';

        $amount = (float) $this->depositAmount;

        try {
            sleep(2);

            $deposit = $deposits->createPending($this->user, $amount);

            $this->depositAmount = '';
            $this->reloadPortfolioData();
            $this->paymentModalReference = 'TOP-'.$deposit->id;
            $this->paymentModalStep = 'success';
            $this->actionMessage = $deposit->fresh()->status === Deposit::STATUS_CONFIRMED
                ? __('coin.messages.top_up_credited')
                : __('coin.messages.top_up_pending');
        } catch (\RuntimeException $exception) {
            $this->paymentModalStep = 'error';
            $this->paymentModalError = $exception->getMessage();
            $this->addError('depositAmount', $exception->getMessage());
        }
    }

    public function openPayoutPaymentModal(): void
    {
        $this->resetActionFeedback();
        $this->resetPaymentModal();

        $this->validate([
            'withdrawAmount' => ['required', 'numeric', 'min:1'],
        ], [], [
            'withdrawAmount' => 'amount',
        ]);

        $this->paymentModal = 'payout';
        $this->paymentModalStep = 'review';
    }

    public function confirmPayoutPayment(WithdrawalService $withdrawals): void
    {
        if ($this->paymentModal !== 'payout' || $this->paymentModalStep !== 'review') {
            return;
        }

        $this->paymentModalError = null;
        $this->paymentModalStep = 'processing';

        $amount = (float) $this->withdrawAmount;

        try {
            sleep(2);

            $withdrawal = $withdrawals->createForUser($this->user, $amount);

            $this->withdrawAmount = '';
            $this->reloadPortfolioData();
            $this->paymentModalReference = $withdrawal->reference;
            $this->paymentModalStep = 'success';
            $this->actionMessage = __('coin.messages.payout_submitted');
        } catch (\RuntimeException $exception) {
            $this->paymentModalStep = 'error';
            $this->paymentModalError = $exception->getMessage();
            $this->addError('withdrawAmount', $exception->getMessage());
        }
    }

    public function saveProfile(): void
    {
        $this->resetActionFeedback();

        $validated = $this->validate([
            'profilePhone' => ['nullable', 'string', 'max:32'],
            'profileTelegram' => ['nullable', 'string', 'max:64'],
            'profileCountry' => ['nullable', 'string', 'size:2'],
        ], [], [
            'profilePhone' => 'phone',
            'profileTelegram' => 'telegram',
            'profileCountry' => 'country',
        ]);

        $this->user->update([
            'phone' => $validated['profilePhone'] ?: null,
            'telegram' => $validated['profileTelegram'] ?: null,
            'country_code' => $validated['profileCountry'] ? strtoupper($validated['profileCountry']) : null,
        ]);

        $this->reloadPortfolioData();
        $this->actionMessage = __('coin.messages.profile_saved');
    }

    public function openInvestmentPaymentModal(): void
    {
        $this->resetActionFeedback();
        $this->resetPaymentModal();

        $plan = $this->selectedPlan;

        if (! $plan instanceof Plan) {
            $this->addError('purchase', __('coin.messages.select_plan'));

            return;
        }

        if ($plan->isEnterprise() && $plan->min_deposit === null) {
            $this->addError('purchase', __('coin.invest.contact_sales'));

            return;
        }

        $this->paymentModal = 'investment';
        $this->paymentModalStep = 'review';
    }

    public function confirmInvestmentPayment(PlanPurchaseService $purchases): void
    {
        if ($this->paymentModal !== 'investment' || $this->paymentModalStep !== 'review') {
            return;
        }

        $plan = $this->selectedPlan;

        if (! $plan instanceof Plan) {
            $this->closePaymentModal();
            $this->addError('purchase', __('coin.messages.select_plan'));

            return;
        }

        $this->paymentModalError = null;
        $this->paymentModalStep = 'processing';

        try {
            sleep(2);

            $contract = $purchases->purchase($this->user, $plan, (float) $this->power);

            $this->reloadPortfolioData();
            $this->selectedPlanId = $plan->id;
            $this->paymentModalReference = $contract->code;
            $this->paymentModalStep = 'success';
            $this->actionMessage = __('coin.messages.plan_activated');
        } catch (\RuntimeException $exception) {
            $this->paymentModalStep = 'error';
            $this->paymentModalError = $exception->getMessage();
            $this->addError('purchase', $exception->getMessage());
        }
    }

    public function finishInvestmentPayment(): void
    {
        $this->section = 2;
        $this->closePaymentModal();
    }

    public function closePaymentModal(): void
    {
        if ($this->paymentModalStep === 'processing') {
            return;
        }

        $this->resetPaymentModal();
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
            ->layout('layouts.coin-dashboard', ['title' => 'Coin — '.__('coin.nav.portal')]);
    }

    private function dailyAmount(): float
    {
        $plan = $this->selectedPlan;

        if (! $plan instanceof Plan) {
            return 0;
        }

        $principal = (float) $this->power;
        $apr = (float) ($plan->annual_profit_percent ?? 0);

        if ($principal <= 0 || $apr <= 0) {
            return 0;
        }

        return round($principal * ($apr / 100) / 365, 2);
    }

    private function reloadPortfolioData(): void
    {
        $payload = app(DashboardDataService::class)->forUser($this->user->fresh());

        $this->user = auth()->user()->fresh();
        $this->wallet = $payload['wallet'];
        $this->plans = $payload['plans'];
        $this->activeContracts = $payload['activeContracts'];
        $this->completedContracts = $payload['completedContracts'];
        $this->transactions = $payload['transactions'];
        $this->periodTotals = $payload['periodTotals'];
        $this->referral = $payload['referral'];
        $this->referralAccruals = $payload['referralAccruals'];
        $this->referralCommissions = $payload['referralCommissions'];
        $this->profitTransactions = $payload['profitTransactions'];
        $this->primaryContract = $payload['primaryContract'];
        $this->primaryPlan = $payload['primaryPlan'];
        $this->profilePhone = (string) ($this->user->phone ?? '');
        $this->profileTelegram = (string) ($this->user->telegram ?? '');
        $this->profileCountry = (string) ($this->user->country_code ?? '');
    }

    private function profitTotalForPeriod(int $period): float
    {
        $since = match ($period) {
            0 => now()->startOfDay(),
            2 => now()->startOfMonth(),
            default => now()->startOfWeek(),
        };

        return (float) $this->profitTransactions
            ->filter(function ($transaction) use ($since) {
                $at = $transaction->occurred_at ?? $transaction->created_at;

                return $at && $at >= $since;
            })
            ->sum(fn ($transaction) => max(0, (float) ($transaction->amount ?? 0)));
    }

    private function resetActionFeedback(): void
    {
        $this->actionMessage = null;
        $this->resetErrorBag();
    }

    private function resetPaymentModal(): void
    {
        $this->paymentModal = null;
        $this->paymentModalStep = 'review';
        $this->paymentModalError = null;
        $this->paymentModalReference = null;
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
