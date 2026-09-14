<?php

namespace App\Services;

use App\Support\LocaleFormat;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;

class WalletService
{
    public function __construct(
        private PlatformSettingsService $settings,
    ) {}

    public function ensureWallet(User $user): Wallet
    {
        $wallet = $user->wallet;

        if ($wallet instanceof Wallet) {
            return $wallet;
        }

        return Wallet::query()->create([
            'user_id' => $user->id,
            'currency' => 'USDT',
            'balance' => 0,
            'available' => 0,
            'pending' => 0,
            'locked_balance' => 0,
            'min_withdrawal_label' => number_format($this->settings->minWithdrawal(), 2, '.', ''),
        ]);
    }

    public function currencyFor(Wallet $wallet): string
    {
        return $wallet->currency ?: 'USDT';
    }

    public function nextSortOrder(int $userId): int
    {
        return (int) WalletTransaction::query()->where('user_id', $userId)->max('sort_order') + 1;
    }

    public function record(
        User $user,
        string $type,
        string $source,
        float $amount,
        string $currency,
        string $tone = 'neutral',
        string $statusLabel = 'COMPLETED',
        ?Model $reference = null,
    ): WalletTransaction {
        $prefix = $amount >= 0 ? '+' : '';

        return WalletTransaction::query()->create([
            'user_id' => $user->id,
            'occurred_label' => LocaleFormat::shortDateTime(now()),
            'type' => $type,
            'source' => $source,
            'amount_label' => $prefix.number_format(abs($amount), 2, '.', '').' '.$currency,
            'amount_tone' => $tone,
            'status_label' => $statusLabel,
            'sort_order' => $this->nextSortOrder($user->id),
            'amount' => $amount,
            'currency' => $currency,
            'reference_type' => $reference ? $reference->getMorphClass() : null,
            'reference_id' => $reference?->getKey(),
            'occurred_at' => now(),
        ]);
    }
}
