<?php

namespace App\Livewire;

use App\Models\Contract;
use App\Models\Deposit;
use App\Models\Plan;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\SupportTicketMessage;
use App\Models\WalletTransaction;
use App\Models\UserNotification;
use App\Services\DashboardDataService;
use App\Services\UserInAppNotificationService;
use App\Services\DepositService;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentSimulatorService;
use App\Services\PlanChangeRequestService;
use App\Services\PlanPurchaseService;
use App\Services\PlatformSettingsService;
use App\Services\ReferralService;
use App\Services\SupportTicketService;
use App\Services\WithdrawalService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    #[Url(as: 'section', history: true, keep: false)]
    public int $section = 0;

    public int $power = 1200;

    public ?int $selectedPlanId = null;

    public int $period = 1;

    /** 0 = 24H, 1 = 14D, 2 = 30D */
    public int $accrualChartPeriod = 1;

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
    public Collection $pendingPlanChanges;

    /** @var Collection<int, mixed> */
    public Collection $transactions;

    /** @var Collection<string, mixed> */
    public Collection $periodTotals;

    /** @var Collection<int, mixed> */
    public Collection $tickets;

    /** @var Collection<int, UserNotification> */
    public Collection $userNotifications;

    public ?int $selectedTicketId = null;

    public ?int $selectedNotificationId = null;

    public bool $showCreateTicket = false;

    public string $newSubject = '';

    public string $newCategory = SupportTicket::CATEGORY_OTHER;

    public string $newBody = '';

    public string $replyBody = '';

    public int $replyFormKey = 0;

    public int $createFormKey = 0;

    public string $depositAmount = '';

    public string $depositCurrency = 'USDT';

    public string $withdrawAmount = '';

    public ?string $paymentModal = null;

    public string $paymentModalStep = 'review';

    public ?string $paymentModalError = null;

    public ?string $paymentModalReference = null;

    public ?int $contractDetailsId = null;

    public ?int $changingContractId = null;

    public ?float $pendingTopUpAmount = null;

    public ?int $pendingDepositId = null;

    public ?string $pendingPaymentAddress = null;

    public ?string $actionMessage = null;

    public ?string $actionMessageTone = null;

    public string $referralInviteEmail = '';

    public string $profileEmail = '';

    public string $profileCurrentPassword = '';

    public string $profileNewPassword = '';

    public string $profileNewPasswordConfirmation = '';

    public string $profileTwoFactorPassword = '';

    public string $profilePhone = '';

    public string $profileTelegram = '';

    public string $profileCountry = '';

    public int $walletPerPage = 10;

    public string $walletSearch = '';

    public string $walletSort = '';

    public string $walletDir = 'desc';

    public function updatedWalletPerPage(): void
    {
        $this->resetPage('walletPage');
    }

    public function updatedWalletSearch(): void
    {
        $this->resetPage('walletPage');
    }

    public function sortWallet(string $column): void
    {
        if ($this->walletSort === $column) {
            $this->walletDir = $this->walletDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->walletSort = $column;
            $this->walletDir = 'desc';
        }

        $this->resetPage('walletPage');
    }

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
        $this->pendingPlanChanges = $payload['pendingPlanChanges'];
        $this->transactions = $payload['transactions'];
        $this->periodTotals = $payload['periodTotals'];
        $this->referral = $payload['referral'];
        $this->referralAccruals = $payload['referralAccruals'];
        $this->referralCommissions = $payload['referralCommissions'];
        $this->profitTransactions = $payload['profitTransactions'];
        $this->primaryContract = $payload['primaryContract'];
        $this->primaryPlan = $payload['primaryPlan'];
        $this->profileEmail = (string) $this->user->email;
        $this->profilePhone = (string) ($this->user->phone ?? '');
        $this->profileTelegram = (string) ($this->user->telegram ?? '');
        $this->profileCountry = (string) ($this->user->country_code ?? '');
        $this->depositCurrency = (string) (config('coin.deposits.currencies')[0] ?? 'USDT');
        $this->selectedPlanId = $this->primaryPlan?->id
            ?? $this->plans->first(fn (Plan $plan) => ! $plan->isEnterprise())?->id;
        $this->power = (int) ($this->primaryPlan?->min_deposit ?? $this->selectedPlan?->calculatorMinAmount() ?? 1200);
        $this->syncPowerToSelectedPlan();

        $this->tickets = $this->user->supportTickets()
            ->with('messages')
            ->orderByDesc('updated_at')
            ->get();

        $this->reloadUserNotifications();

        if ($this->section < 0 || $this->section > 8) {
            $this->section = 0;
        }
    }

    public function setSection(int $section): void
    {
        if ($section !== 1) {
            $this->changingContractId = null;
        }

        $this->section = $section;
        $this->menuOpen = false;
        $this->resetActionFeedback();

        if ($section === 4) {
            $this->wallet = $this->user->fresh(['wallet'])->wallet;
            $this->resetPage('walletPage');
        }

        if ($section === 7) {
            $this->prepareSupportChat();
        } else {
            $this->refreshSupportUnreadState();
        }

        if ($section !== 8) {
            $this->syncNotificationsUnreadBadge();
        }
    }

    public function openNotification(int $notificationId): void
    {
        $notification = app(UserInAppNotificationService::class)->findForUser($notificationId, $this->user);

        if (! $notification instanceof UserNotification) {
            return;
        }

        if (! $notification->isRead()) {
            app(UserInAppNotificationService::class)->markAsRead($notification, $this->user);
            $this->reloadUserNotifications();
            $this->syncNotificationsUnreadBadge();
        }

        $this->selectedNotificationId = $notification->id;
        $this->section = 8;
        $this->menuOpen = false;
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

    public function setAccrualChartPeriod(int $period): void
    {
        $this->accrualChartPeriod = $period;
    }

    public function getAccrualsChartProperty(): array
    {
        return $this->buildAccrualsChart($this->accrualChartPeriod);
    }

    public function selectPlan(int $planId): void
    {
        $plan = $this->plans->firstWhere('id', $planId);

        if (! $plan instanceof Plan) {
            return;
        }

        if ($this->changingContractId) {
            $changing = $this->changingContract;

            if ($changing && (int) $plan->id === (int) $changing->plan_id) {
                return;
            }

            $this->selectedPlanId = $plan->id;

            return;
        }

        $this->selectedPlanId = $plan->id;
        $this->power = max(
            $plan->calculatorMinAmount(),
            min($plan->calculatorMaxAmount(), (int) $this->power)
        );
    }

    public function openChangePlan(int $contractId): void
    {
        $contract = $this->findOwnedContract($contractId);

        abort_unless($contract instanceof Contract && $contract->isActive(), 403);

        if ($this->pendingPlanChanges->has($contract->id)) {
            $this->actionMessage = __('coin.messages.plan_change_pending_exists');
            $this->actionMessageTone = 'warning';
            $this->section = 2;

            return;
        }

        $this->changingContractId = $contract->id;

        $alternative = $this->plans->first(
            fn (Plan $plan) => (int) $plan->id !== (int) $contract->plan_id
        );

        $this->selectedPlanId = $alternative?->id;

        if ($alternative instanceof Plan) {
            $this->power = max(
                $alternative->calculatorMinAmount(),
                min($alternative->calculatorMaxAmount(), (int) $this->power)
            );
        }

        $this->section = 1;
        $this->resetActionFeedback();
    }

    public function cancelChangePlan(): void
    {
        $this->changingContractId = null;
        $this->resetActionFeedback();
    }

    public function getChangingContractProperty(): ?Contract
    {
        if ($this->changingContractId === null) {
            return null;
        }

        $contract = $this->findOwnedContract($this->changingContractId);

        return $contract instanceof Contract && $contract->isActive() ? $contract : null;
    }

    public function getPlanChangeTopUpProperty(): float
    {
        $contract = $this->changingContract;
        $plan = $this->selectedPlan;

        if (! $contract instanceof Contract || ! $plan instanceof Plan) {
            return 0.0;
        }

        if ((int) $contract->plan_id === (int) $plan->id) {
            return 0.0;
        }

        return app(PlanPurchaseService::class)->topUpRequired($contract, $plan);
    }

    public function getPlanChangePrincipalAfterProperty(): float
    {
        $contract = $this->changingContract;

        if (! $contract instanceof Contract) {
            return 0.0;
        }

        return round((float) $contract->principal_amount + $this->planChangeTopUp, 2);
    }

    public function getPlanChangeHasInsufficientFundsProperty(): bool
    {
        return $this->planChangeTopUp > 0.009 && $this->walletAvailableAmount() < $this->planChangeTopUp;
    }

    public function goToWalletTopUp(): void
    {
        $this->closePaymentModal();
        $this->changingContractId = null;
        $this->section = 4;
    }

    public function updatedPower(): void
    {
        $this->syncPowerToSelectedPlan();
    }

    public function setDepositPreset(int|string $amount): void
    {
        $this->depositAmount = number_format(max(0, (float) $amount), 2, '.', '');
    }

    public function setWithdrawMax(): void
    {
        $this->wallet = $this->user->fresh(['wallet'])->wallet;
        $this->withdrawAmount = number_format(max(0, $this->walletAvailableAmount()), 2, '.', '');
    }

    public function getAvailableBalanceFormattedProperty(): string
    {
        $available = (float) ($this->walletAvailableAmount());

        return number_format(max(0, $available), 2, '.', '');
    }

    private function walletAvailableAmount(): float
    {
        $wallet = $this->wallet;

        if (is_array($wallet)) {
            return (float) ($wallet['available'] ?? 0);
        }

        return (float) ($wallet?->available ?? 0);
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

        return number_format($total, 2, '.', ',').' '.$this->walletCurrency;
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

    public function getLifetimeProfitProperty(): string
    {
        $total = (float) $this->activeContracts
            ->merge($this->completedContracts)
            ->sum(fn ($contract) => (float) $contract->accrued_amount);

        return number_format($total, 2, '.', ',').' '.$this->walletCurrency;
    }

    /** @deprecated Use lifetimeProfit */
    public function getLifetimeRewardsProperty(): string
    {
        return $this->lifetimeProfit;
    }

    public function getNextExpiryLabelProperty(): string
    {
        $nearest = $this->activeContracts
            ->filter(fn ($contract) => $contract->ends_at !== null)
            ->sortBy(fn ($contract) => $contract->ends_at)
            ->first();

        if (! $nearest) {
            return '—';
        }

        return $nearest->formattedEndsAt();
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

        return $this->plans->first(fn (Plan $plan) => ! $plan->isEnterprise());
    }

    public function getCalculatorMinProperty(): int
    {
        return $this->selectedPlan?->calculatorMinAmount() ?? 100;
    }

    public function getCalculatorMaxProperty(): int
    {
        return $this->selectedPlan?->calculatorMaxAmount() ?? 10_000;
    }

    public function getCalculatorStepProperty(): int
    {
        return $this->selectedPlan?->calculatorStep() ?? 100;
    }

    /** @return list<string> */
    public function getDepositCurrenciesProperty(): array
    {
        return config('coin.deposits.currencies', ['USDT', 'BTC']);
    }

    public function getDepositCreditPreviewProperty(): ?string
    {
        $amount = (float) str_replace([',', ' '], '', $this->depositAmount);

        if ($amount <= 0) {
            return null;
        }

        try {
            return app(\App\Services\ExchangeRateService::class)->previewLabel($amount, $this->depositCurrency);
        } catch (\Throwable) {
            return null;
        }
    }

    public function getPlanNameProperty(): string
    {
        return $this->selectedPlan?->displayName() ?? '—';
    }

    public function getPlanInfraProperty(): string
    {
        return $this->selectedPlan?->displayInfra() ?? '—';
    }

    public function getWalletCurrencyProperty(): string
    {
        return $this->wallet?->currency ?? (string) config('coin.wallet.base_currency', 'USDT');
    }

    public function getNetworkFeeLabelProperty(): string
    {
        $fee = app(PlatformSettingsService::class)->getFloat('network_fee');

        return number_format($fee, 2, ',', '').' '.$this->walletCurrency;
    }

    public function getPlanPriceProperty(): string
    {
        return $this->selectedPlan?->formattedPriceLabel() ?? '—';
    }

    public function getPlanComputeProperty(): string
    {
        $plan = $this->selectedPlan;

        if ($plan?->formattedMinDeposit()) {
            return $plan->formattedMinDeposit();
        }

        return number_format($this->power, 0, '.', ',').' '.$this->walletCurrency;
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
            0 => __('coin.invest.per_day'),
            2 => __('coin.invest.per_month'),
            default => __('coin.stats.per_week'),
        };
    }

    public function getPeriodTotalProperty(): string
    {
        return number_format($this->profitTotalForPeriod($this->period), 2, '.', ',').' '.$this->walletCurrency;
    }

    public function getProfitTrendChartProperty(): array
    {
        return $this->buildProfitTrendChart($this->period);
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

    public function getUnreadNotificationsCountProperty(): int
    {
        return app(UserInAppNotificationService::class)->unreadCountForUser((int) $this->user->id);
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
        $this->paymentModalStep = 'gateway';
        $this->pendingTopUpAmount = (float) $this->depositAmount;
    }

    public function proceedToTopUpPayment(DepositService $deposits, PaymentGatewayInterface $gateway): void
    {
        if ($this->paymentModal !== 'topup' || $this->paymentModalStep !== 'gateway') {
            return;
        }

        $this->paymentModalError = null;
        $this->paymentModalStep = 'processing';

        $amount = $this->pendingTopUpAmount ?? (float) $this->depositAmount;

        try {
            $deposit = $deposits->initiateWithGateway($this->user, $amount, $this->depositCurrency, $gateway);

            $this->pendingDepositId = $deposit->id;
            $this->pendingPaymentAddress = $deposit->payment_address;
            $this->paymentModalStep = 'payment';
        } catch (\RuntimeException $exception) {
            $this->paymentModalStep = 'error';
            $this->paymentModalError = $exception->getMessage();
            $this->addError('depositAmount', $exception->getMessage());
        }
    }

    public function confirmTopUpPayment(PaymentSimulatorService $simulator): void
    {
        if ($this->paymentModal !== 'topup' || $this->paymentModalStep !== 'payment') {
            return;
        }

        if ((string) config('coin.payments.driver', 'mock') !== 'mock') {
            return;
        }

        $this->paymentModalError = null;
        $this->paymentModalStep = 'processing';

        try {
            $deposit = Deposit::query()
                ->whereKey($this->pendingDepositId)
                ->where('user_id', $this->user->id)
                ->firstOrFail();

            $simulator->simulateDepositIpn($deposit);

            $this->depositAmount = '';
            $this->pendingTopUpAmount = null;
            $this->pendingDepositId = null;
            $this->pendingPaymentAddress = null;
            $this->reloadPortfolioData();
            $this->paymentModalReference = 'TOP-'.$deposit->id;
            $this->paymentModalStep = 'success';
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

    public function enableEmailTwoFactor(): void
    {
        $this->resetActionFeedback();

        if ($this->user->hasEmailTwoFactorEnabled()) {
            return;
        }

        $this->validate([
            'profileTwoFactorPassword' => ['required', 'string'],
        ], [], [
            'profileTwoFactorPassword' => __('coin.profile.two_factor_password'),
        ]);

        $this->assertCurrentUserPassword($this->profileTwoFactorPassword, 'profileTwoFactorPassword');

        $this->user->update(['email_two_factor_enabled' => true]);
        $this->profileTwoFactorPassword = '';
        $this->reloadPortfolioData();
        $this->setActionFeedback(__('coin.messages.two_factor_enabled'), 'success');
    }

    public function toggleNotifyProfitCredit(): void
    {
        $this->toggleNotificationPreference(
            'notify_profit_credit',
            'coin.messages.notify_profit_credit_enabled',
            'coin.messages.notify_profit_credit_disabled',
        );
    }

    public function toggleNotifyContractExpiry(): void
    {
        $this->toggleNotificationPreference(
            'notify_contract_expiry',
            'coin.messages.notify_contract_expiry_enabled',
            'coin.messages.notify_contract_expiry_disabled',
        );
    }

    public function toggleNotifyMaturityAlerts(): void
    {
        $this->toggleNotificationPreference(
            'notify_maturity_alerts',
            'coin.messages.notify_maturity_alerts_enabled',
            'coin.messages.notify_maturity_alerts_disabled',
        );
    }

    public function toggleNotifyReferralActivity(): void
    {
        $this->toggleNotificationPreference(
            'notify_referral_activity',
            'coin.messages.notify_referral_activity_enabled',
            'coin.messages.notify_referral_activity_disabled',
        );
    }

    public function disableEmailTwoFactor(): void
    {
        $this->resetActionFeedback();

        if (! $this->user->hasEmailTwoFactorEnabled()) {
            return;
        }

        $this->validate([
            'profileTwoFactorPassword' => ['required', 'string'],
        ], [], [
            'profileTwoFactorPassword' => __('coin.profile.two_factor_password'),
        ]);

        $this->assertCurrentUserPassword($this->profileTwoFactorPassword, 'profileTwoFactorPassword');

        $this->user->update(['email_two_factor_enabled' => false]);
        $this->profileTwoFactorPassword = '';
        $this->reloadPortfolioData();
        $this->setActionFeedback(__('coin.messages.two_factor_disabled'), 'success');
    }

    public function saveProfileEmail(): void
    {
        $this->resetActionFeedback();

        $validated = $this->validate([
            'profileEmail' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
        ], [], [
            'profileEmail' => __('coin.auth.email'),
        ]);

        $newEmail = $validated['profileEmail'];

        if ($newEmail === $this->user->email) {
            return;
        }

        $this->user->update([
            'email' => $newEmail,
            'email_verified_at' => null,
        ]);

        $this->reloadPortfolioData();
        $this->profileEmail = (string) $this->user->email;
        $this->actionMessage = __('coin.messages.email_updated');
    }

    public function saveProfilePassword(): void
    {
        $this->resetActionFeedback();

        $this->validate([
            'profileCurrentPassword' => ['required', 'string'],
            'profileNewPassword' => ['required', 'string', Password::defaults()],
            'profileNewPasswordConfirmation' => ['required', 'same:profileNewPassword'],
        ], [], [
            'profileCurrentPassword' => __('coin.profile.current_password'),
            'profileNewPassword' => __('coin.profile.new_password'),
            'profileNewPasswordConfirmation' => __('coin.auth.password_confirm'),
        ]);

        $this->assertCurrentUserPassword($this->profileCurrentPassword, 'profileCurrentPassword');

        $this->user->update([
            'password' => $this->profileNewPassword,
        ]);

        auth()->setUser($this->user->fresh());

        $this->reset(['profileCurrentPassword', 'profileNewPassword', 'profileNewPasswordConfirmation']);
        $this->actionMessage = __('coin.messages.password_updated');
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

        $minDeposit = (float) ($plan->min_deposit ?? 0);

        if ($minDeposit > 0 && (float) $this->power < $minDeposit) {
            $this->addError('purchase', __('coin.messages.min_investment', [
                'amount' => number_format($minDeposit, 0, '.', ' ').' '.($plan->currency ?? config('coin.wallet.base_currency', 'USDT')),
            ]));

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
        } catch (\RuntimeException $exception) {
            $this->paymentModalStep = 'error';
            $this->paymentModalError = $exception->getMessage();
            $this->addError('purchase', $exception->getMessage());
        }
    }

    public function finishInvestmentPayment(): void
    {
        $this->section = 2;
        $this->changingContractId = null;
        $this->closePaymentModal();
    }

    public function openPlanChangeModal(): void
    {
        $this->resetActionFeedback();
        $this->resetPaymentModal();

        $contract = $this->changingContract;
        $plan = $this->selectedPlan;

        if (! $contract instanceof Contract || ! $plan instanceof Plan) {
            $this->addError('purchase', __('coin.messages.select_plan'));

            return;
        }

        if ((int) $contract->plan_id === (int) $plan->id) {
            $this->addError('purchase', __('coin.messages.plan_change_same_plan'));

            return;
        }

        if ($plan->isEnterprise() && $plan->min_deposit === null) {
            $this->addError('purchase', __('coin.invest.contact_sales'));

            return;
        }

        if ($this->planChangeHasInsufficientFunds) {
            $this->paymentModal = 'plan_change';
            $this->paymentModalStep = 'insufficient_funds';

            return;
        }

        $this->paymentModal = 'plan_change';
        $this->paymentModalStep = 'review';
    }

    public function confirmPlanChange(PlanChangeRequestService $planChanges): void
    {
        if ($this->paymentModal !== 'plan_change' || $this->paymentModalStep !== 'review') {
            return;
        }

        $contract = $this->changingContract;
        $plan = $this->selectedPlan;

        if (! $contract instanceof Contract || ! $plan instanceof Plan) {
            $this->closePaymentModal();
            $this->addError('purchase', __('coin.messages.select_plan'));

            return;
        }

        $this->paymentModalError = null;
        $this->paymentModalStep = 'processing';

        try {
            sleep(1);

            $request = $planChanges->createRequest($this->user, $contract, $plan);

            $this->reloadPortfolioData();
            $this->paymentModalReference = $request->reference;
            $this->paymentModalStep = 'pending_approval';
        } catch (\RuntimeException $exception) {
            $this->paymentModalStep = 'error';
            $this->paymentModalError = $exception->getMessage();
            $this->addError('purchase', $exception->getMessage());
        }
    }

    public function finishPlanChange(): void
    {
        $this->section = 2;
        $this->changingContractId = null;
        $this->closePaymentModal();
    }

    public function closePaymentModal(): void
    {
        if ($this->paymentModalStep === 'processing') {
            return;
        }

        $wasTopUp = $this->paymentModal === 'topup';

        $this->resetPaymentModal();

        if ($wasTopUp) {
            $this->depositAmount = '';
            $this->pendingTopUpAmount = null;
        }
    }

    public function openContractDetails(int $contractId): void
    {
        $contract = $this->findOwnedContract($contractId);

        abort_unless($contract !== null, 403);

        $this->contractDetailsId = $contract->id;
    }

    public function closeContractDetails(): void
    {
        $this->contractDetailsId = null;
    }

    public function getContractDetailsProperty(): ?Contract
    {
        if ($this->contractDetailsId === null) {
            return null;
        }

        return $this->findOwnedContract($this->contractDetailsId);
    }

    public function copyReferralLink(): void
    {
        $url = $this->referral?->shareUrl();

        abort_unless(filled($url), 404);

        $this->js(sprintf(
            'navigator.clipboard.writeText(%s).then(() => window.showSupportToast?.(%s)).catch(() => window.showSupportToast?.(%s, "error"))',
            json_encode($url, JSON_THROW_ON_ERROR),
            json_encode(__('coin.referrals.link_copied')),
            json_encode(__('coin.referrals.link_copy_failed')),
        ));
    }

    public function sendReferralInvite(ReferralService $referrals): void
    {
        $this->validate([
            'referralInviteEmail' => ['required', 'email'],
        ], [], [
            'referralInviteEmail' => __('coin.referrals.invite_email'),
        ]);

        if (strcasecmp($this->referralInviteEmail, (string) $this->user->email) === 0) {
            $this->addError('referralInviteEmail', __('coin.referrals.invite_self_error'));

            return;
        }

        try {
            $referrals->sendInvitation($this->user, $this->referralInviteEmail);
        } catch (\Throwable) {
            $this->addError('referralInviteEmail', __('coin.referrals.invite_failed'));

            return;
        }

        $this->referralInviteEmail = '';
        $this->resetErrorBag();
        $this->actionMessage = __('coin.referrals.invite_sent');
        $this->js(sprintf(
            'window.showSupportToast?.(%s)',
            json_encode(__('coin.referrals.invite_sent'), JSON_THROW_ON_ERROR),
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

    #[On('echo-private:wallet.user.{user.id},.WithdrawalUpdated')]
    public function onWithdrawalUpdated(mixed $payload = null): void
    {
        $this->reloadPortfolioData();
    }

    #[On('echo-private:wallet.user.{user.id},.ReferralCommissionPaid')]
    public function onReferralCommissionPaid(mixed $payload = null): void
    {
        $this->reloadPortfolioData();

        $message = is_array($payload)
            ? (data_get($payload, 'user_toast') ?? data_get($payload, '0.user_toast'))
            : null;

        if (is_string($message) && $message !== '') {
            $this->actionMessage = $message;
            $this->actionMessageTone = 'success';
        }
    }

    private function planChangePayloadStatus(mixed $payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        return data_get($payload, 'request.status')
            ?? data_get($payload, '0.request.status');
    }

    #[On('echo-private:wallet.user.{user.id},.PlanChangeRequestUpdated')]
    public function onPlanChangeRequestUpdated(mixed $payload = null): void
    {
        $status = $this->planChangePayloadStatus($payload);

        $this->reloadPortfolioData();

        if ($status === 'approved') {
            $this->changingContractId = null;
            $this->closePaymentModal();
            $this->section = 2;
            $this->actionMessage = __('coin.messages.plan_change_confirmed');
            $this->actionMessageTone = 'success';
        } elseif ($status === 'rejected') {
            $this->actionMessage = __('coin.messages.plan_change_rejected');
            $this->actionMessageTone = 'error';
        }
    }

    #[On('echo-private:notifications.user.{user.id},.UserNotificationCreated')]
    public function onUserNotificationCreated(mixed $payload = null): void
    {
        $this->reloadUserNotifications();
        $this->syncNotificationsUnreadBadge();
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
        return view('livewire.dashboard', [
            'walletTransactions' => WalletTransaction::query()
                ->where('user_id', auth()->id())
                ->searchTerm($this->walletSearch)
                ->applyListSort($this->walletSort, $this->walletDir, 'sort_order')
                ->paginate($this->walletPageSize(), pageName: 'walletPage'),
        ])->layout('layouts.coin-dashboard', ['title' => \App\Support\PlatformBrand::pageTitle(__('coin.nav.portal'))]);
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

    private function walletPageSize(): int
    {
        return in_array($this->walletPerPage, [10, 20, 50], true) ? $this->walletPerPage : 10;
    }

    private function assertCurrentUserPassword(string $password, string $field): void
    {
        $storedHash = User::query()
            ->whereKey(auth()->id())
            ->value('password');

        if (! is_string($storedHash) || ! Hash::check($password, $storedHash)) {
            throw ValidationException::withMessages([
                $field => __('coin.auth.login_password_invalid'),
            ]);
        }
    }

    private function reloadPortfolioData(): void
    {
        $payload = app(DashboardDataService::class)->forUser($this->user->fresh());

        $this->user = auth()->user()->fresh();
        $this->wallet = $payload['wallet'];
        $this->plans = $payload['plans'];
        $this->activeContracts = $payload['activeContracts'];
        $this->completedContracts = $payload['completedContracts'];
        $this->pendingPlanChanges = $payload['pendingPlanChanges'];
        $this->transactions = $payload['transactions'];
        $this->resetPage('walletPage');
        $this->periodTotals = $payload['periodTotals'];
        $this->referral = $payload['referral'];
        $this->referralAccruals = $payload['referralAccruals'];
        $this->referralCommissions = $payload['referralCommissions'];
        $this->profitTransactions = $payload['profitTransactions'];
        $this->primaryContract = $payload['primaryContract'];
        $this->primaryPlan = $payload['primaryPlan'];
        $this->profileEmail = (string) $this->user->email;
        $this->profilePhone = (string) ($this->user->phone ?? '');
        $this->profileTelegram = (string) ($this->user->telegram ?? '');
        $this->profileCountry = (string) ($this->user->country_code ?? '');
    }

    private function profitTotalForPeriod(int $period): float
    {
        $since = $this->profitPeriodStart($period);

        return (float) $this->profitTransactionsInPeriod($since)
            ->sum(fn ($transaction) => max(0, (float) ($transaction->amount ?? 0)));
    }

    private function profitPeriodStart(int $period): Carbon
    {
        return match ($period) {
            0 => now()->startOfDay(),
            2 => now()->startOfMonth(),
            default => now()->startOfWeek(),
        };
    }

    /** @return Collection<int, WalletTransaction> */
    private function profitTransactionsInPeriod(Carbon $since): Collection
    {
        return $this->profitTransactions->filter(function ($transaction) use ($since) {
            $at = $transaction->occurred_at ?? $transaction->created_at;

            return $at && $at >= $since;
        });
    }

    private function buildAccrualsChart(int $period): array
    {
        return $this->buildDailyProfitChart(match ($period) {
            0 => ['mode' => 'hourly'],
            2 => ['mode' => 'daily', 'days' => 30, 'labelFormat' => 'date'],
            default => ['mode' => 'daily', 'days' => 14, 'labelFormat' => 'date'],
        });
    }

    /** @return Collection<int, WalletTransaction> */
    private function dailyProfitTransactions(): Collection
    {
        return $this->profitTransactions->filter(
            fn ($transaction) => $transaction->type === 'Daily profit'
        );
    }

    /** @param  array{mode: string, days?: int, labelFormat?: string}  $config */
    private function buildDailyProfitChart(array $config): array
    {
        $transactions = $this->dailyProfitTransactions();

        [$values, $labels] = match ($config['mode']) {
            'hourly' => $this->profitTrendHourlyBuckets(
                $transactions->filter(function ($transaction) {
                    $at = $transaction->occurred_at ?? $transaction->created_at;

                    return $at && $at >= now()->startOfDay();
                })
            ),
            default => $this->accrualDailyBuckets(
                $transactions,
                (int) ($config['days'] ?? 7),
                (string) ($config['labelFormat'] ?? 'date'),
            ),
        };

        return $this->formatProfitTrendChart($values, $labels);
    }

    /** @return array{0: array<int, float>, 1: array<int, string>} */
    private function accrualDailyBuckets(Collection $transactions, int $days, string $labelFormat = 'date'): array
    {
        $start = now()->startOfDay()->subDays($days - 1);
        $end = now()->startOfDay();
        $values = array_fill(0, $days, 0.0);
        $labels = [];

        for ($index = 0; $index < $days; $index++) {
            $day = $start->copy()->addDays($index);
            $labels[] = match ($labelFormat) {
                'weekday' => $day->isoFormat('dd'),
                'day' => (string) $day->day,
                default => strtoupper($day->locale('en')->isoFormat('MMM D')),
            };
        }

        foreach ($transactions as $transaction) {
            $at = $transaction->occurred_at ?? $transaction->created_at;
            if (! $at) {
                continue;
            }

            $atDay = $at->copy()->startOfDay();
            if ($atDay->lt($start) || $atDay->gt($end)) {
                continue;
            }

            $dayIndex = (int) $start->diffInDays($atDay);
            if ($dayIndex >= 0 && $dayIndex < $days) {
                $values[$dayIndex] += max(0, (float) ($transaction->amount ?? 0));
            }
        }

        return [$values, $labels];
    }

    private function buildProfitTrendChart(int $period): array
    {
        return $this->buildDailyProfitChart(match ($period) {
            0 => ['mode' => 'hourly'],
            2 => ['mode' => 'daily', 'days' => now()->day, 'labelFormat' => 'day'],
            default => ['mode' => 'daily', 'days' => 7, 'labelFormat' => 'weekday'],
        });
    }

    /** @return array{0: array<int, float>, 1: array<int, string>} */
    private function profitTrendHourlyBuckets(Collection $transactions): array
    {
        $currentHour = (int) now()->format('G');
        $values = array_fill(0, $currentHour + 1, 0.0);
        $labels = array_map(fn (int $hour) => sprintf('%02d', $hour), range(0, $currentHour));

        foreach ($transactions as $transaction) {
            $at = $transaction->occurred_at ?? $transaction->created_at;
            if (! $at) {
                continue;
            }

            $hour = (int) $at->format('G');
            if ($hour > $currentHour) {
                continue;
            }

            $values[$hour] += max(0, (float) ($transaction->amount ?? 0));
        }

        return [$values, $labels];
    }

    /** @param  array<int, float>  $values
     * @param  array<int, string>  $labels
     */
    private function formatProfitTrendChart(array $values, array $labels): array
    {
        $max = max($values ?: [0]);
        $hasData = $max > 0;
        $lastIndex = count($values) - 1;
        $currency = $this->walletCurrency;
        $width = 1000;
        $height = 210;
        $paddingX = 8;
        $paddingY = 18;
        $baseline = $height - $paddingY;
        $count = count($values);

        $points = [];
        foreach ($values as $index => $value) {
            $ratio = $hasData && $max > 0 ? ($value / $max) : 0;
            $normalized = $value > 0 ? max(0.08, $ratio) : 0.04;
            $x = $count > 1
                ? $paddingX + ($index / ($count - 1)) * ($width - (2 * $paddingX))
                : $width / 2;

            $y = round($baseline - ($normalized * ($height - (2 * $paddingY))), 2);

            $points[] = [
                'x' => round($x, 2),
                'y' => $y,
                'value' => $value,
                'valueLabel' => number_format($value, 2, '.', ''),
                'showLabel' => $value > 0,
                'xPct' => round(($x / $width) * 100, 2),
                'yPct' => round(($y / $height) * 100, 2),
                'tooltip' => number_format($value, 2, '.', ',').' '.$currency,
                'highlight' => $index === $lastIndex,
            ];
        }

        $linePath = $this->profitTrendLinearPath($points);
        $areaPath = $linePath !== ''
            ? $linePath.' L '.($width - $paddingX).' '.$baseline.' L '.$paddingX.' '.$baseline.' Z'
            : '';

        return [
            'linePath' => $linePath,
            'areaPath' => $areaPath,
            'points' => $points,
            'axis' => $this->profitTrendAxisLabels($labels),
            'yMaxLabel' => number_format($max, 2, '.', '').' '.$currency,
            'yMidLabel' => number_format($max / 2, 2, '.', '').' '.$currency,
            'hasData' => $hasData,
        ];
    }

    /** @param  array<int, array{x: float, y: float}>  $points */
    private function profitTrendLinearPath(array $points): string
    {
        $count = count($points);
        if ($count === 0) {
            return '';
        }

        $path = sprintf('M %.2f %.2f', $points[0]['x'], $points[0]['y']);

        for ($index = 1; $index < $count; $index++) {
            $path .= sprintf(' L %.2f %.2f', $points[$index]['x'], $points[$index]['y']);
        }

        return $path;
    }

    /** @param  array<int, string>  $labels */
    private function profitTrendAxisLabels(array $labels, int $tickCount = 5): array
    {
        $count = count($labels);
        if ($count === 0) {
            return [];
        }

        if ($count <= $tickCount) {
            return array_values($labels);
        }

        $ticks = [];
        for ($index = 0; $index < $tickCount; $index++) {
            $labelIndex = (int) round($index * ($count - 1) / max($tickCount - 1, 1));
            $ticks[] = $labels[$labelIndex];
        }

        return $ticks;
    }

    private function toggleNotificationPreference(string $column, string $enabledMessageKey, string $disabledMessageKey): void
    {
        $allowed = [
            'notify_profit_credit',
            'notify_contract_expiry',
            'notify_maturity_alerts',
            'notify_referral_activity',
        ];

        if (! in_array($column, $allowed, true)) {
            return;
        }

        $this->resetActionFeedback();

        $enabled = ! (bool) $this->user->{$column};

        $this->user->update([
            $column => $enabled,
        ]);

        $this->reloadPortfolioData();

        $this->setActionFeedback(
            __($enabled ? $enabledMessageKey : $disabledMessageKey),
            $enabled ? 'success' : 'error',
        );
    }

    private function setActionFeedback(string $message, string $tone = 'info'): void
    {
        $this->actionMessage = $message;
        $this->actionMessageTone = $tone;
    }

    private function resetActionFeedback(): void
    {
        $this->actionMessage = null;
        $this->actionMessageTone = null;
        $this->resetErrorBag();
    }

    private function findOwnedContract(int $contractId): ?Contract
    {
        return $this->activeContracts->firstWhere('id', $contractId)
            ?? $this->completedContracts->firstWhere('id', $contractId);
    }

    private function resetPaymentModal(): void
    {
        $this->paymentModal = null;
        $this->paymentModalStep = 'review';
        $this->paymentModalError = null;
        $this->paymentModalReference = null;
        $this->pendingTopUpAmount = null;
        $this->pendingDepositId = null;
        $this->pendingPaymentAddress = null;
    }

    private function formatAmount(float $value, int $decimals): string
    {
        return number_format($value, $decimals, '.', ',').' '.$this->walletCurrency;
    }

    private function syncPowerToSelectedPlan(): void
    {
        $this->power = $this->clampPowerToSelectedPlan((int) $this->power);
    }

    private function clampPowerToSelectedPlan(int $value): int
    {
        $plan = $this->selectedPlan;

        if (! $plan instanceof Plan) {
            return max(1, $value);
        }

        return max(
            $plan->calculatorMinAmount(),
            min($plan->calculatorMaxAmount(), $value)
        );
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

    private function reloadUserNotifications(): void
    {
        $this->userNotifications = app(UserInAppNotificationService::class)->listForUser($this->user);
    }

    private function syncNotificationsUnreadBadge(): void
    {
        $this->dispatch('notifications-unread-updated', count: $this->unreadNotificationsCount);
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
