<?php

namespace App\Livewire;

use App\Services\DashboardDataService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    public int $section = 0;

    public int $power = 1200;

    public int $period = 1;

    public bool $menuOpen = false;

    public string $symbol = 'COIN';

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

    public function mount(DashboardDataService $data): void
    {
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
    }

    public function setSection(int $section): void
    {
        $this->section = $section;
        $this->menuOpen = false;
    }

    public function setPeriod(int $period): void
    {
        $this->period = $period;
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

    public function getPlanNameProperty(): string
    {
        return $this->currentTier()['name'];
    }

    public function getPlanInfraProperty(): string
    {
        return $this->currentTier()['infra'];
    }

    public function getPlanPriceProperty(): string
    {
        return $this->currentTier()['price'];
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

    public function render(): View
    {
        return view('livewire.dashboard')
            ->layout('layouts.coin-dashboard', ['title' => 'Coin — Dashboard']);
    }

    private function dailyAmount(): float
    {
        $tier = $this->currentTier();
        $rate = app(DashboardDataService::class)->rewardRate();

        return $this->power * $rate * (float) $tier['mult'];
    }

    private function currentTier(): array
    {
        return app(DashboardDataService::class)->tierForPower($this->power);
    }

    private function formatAmount(float $value, int $decimals): string
    {
        return number_format($value, $decimals, '.', ',');
    }
}
