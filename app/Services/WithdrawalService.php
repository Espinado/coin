<?php

namespace App\Services;

use App\Support\PlatformTerms;

use App\Models\Admin;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class WithdrawalService
{
    public function __construct(
        private PlatformSettingsService $settings,
    ) {}

    public function createForUser(User $user, float $amount, ?string $payoutAddress = null): Withdrawal
    {
        $wallet = $user->wallet ?? throw new RuntimeException('User has no wallet.');

        if ($user->is_blocked) {
            throw new RuntimeException('Account is blocked.');
        }

        if ($this->settings->getBool('kyc_required_for_withdrawal') && $user->kyc_status !== User::KYC_APPROVED) {
            throw new RuntimeException('KYC approval is required before requesting a payout.');
        }

        $min = $this->settings->minWithdrawal();

        if ($amount < $min) {
            throw new RuntimeException("Minimum payout is {$min}.");
        }

        if ((float) $wallet->available < $amount) {
            throw new RuntimeException('Insufficient available balance.');
        }

        return DB::transaction(function () use ($user, $wallet, $amount, $payoutAddress) {
            $wallet->decrement('available', $amount);
            $wallet->increment('pending', $amount);

            return Withdrawal::query()->create([
                'user_id' => $user->id,
                'reference' => 'WD-'.Str::upper(Str::random(8)),
                'amount' => $amount,
                'currency' => $wallet->currency ?: 'USDT',
                'withdrawal_type' => 'available_balance',
                'payout_address' => $payoutAddress ?? $wallet->payout_address ?? '—',
                'network_label' => $wallet->network_label,
                'status' => Withdrawal::STATUS_PENDING,
            ]);
        });
    }

    public function updateStatus(Withdrawal $withdrawal, string $status, Admin $admin, ?string $note = null): Withdrawal
    {
        if (! array_key_exists($status, Withdrawal::statuses())) {
            throw new RuntimeException('Invalid withdrawal status.');
        }

        return DB::transaction(function () use ($withdrawal, $status, $admin, $note) {
            $withdrawal->refresh();
            $previous = $withdrawal->status;

            if ($previous === $status) {
                return $withdrawal;
            }

            $wallet = $withdrawal->user->wallet ?? throw new RuntimeException('User has no wallet.');

            if ($status === Withdrawal::STATUS_REJECTED && in_array($previous, [Withdrawal::STATUS_PENDING, Withdrawal::STATUS_APPROVED, Withdrawal::STATUS_PROCESSING], true)) {
                $this->releasePending($wallet, (float) $withdrawal->amount);
            }

            if ($status === Withdrawal::STATUS_PAID && $previous !== Withdrawal::STATUS_PAID) {
                $this->finalizePaid($withdrawal, $wallet, $admin);
            }

            $withdrawal->update([
                'status' => $status,
                'processed_by' => $admin->id,
                'admin_note' => $note ?? $withdrawal->admin_note,
                'processed_at' => in_array($status, [Withdrawal::STATUS_PAID, Withdrawal::STATUS_REJECTED], true) ? now() : $withdrawal->processed_at,
            ]);

            return $withdrawal->fresh(['user.wallet', 'processedByAdmin']);
        });
    }

    private function releasePending(Wallet $wallet, float $amount): void
    {
        $wallet->decrement('pending', min($amount, (float) $wallet->pending));
        $wallet->increment('available', $amount);
    }

    private function finalizePaid(Withdrawal $withdrawal, Wallet $wallet, Admin $admin): void
    {
        $amount = (float) $withdrawal->amount;
        $fee = $this->settings->getFloat('network_fee');
        $net = max(0, $amount - $fee);

        $wallet->decrement('pending', min($amount, (float) $wallet->pending));
        $wallet->decrement('balance', $amount);

        $symbol = $this->settings->tokenSymbol();
        $sortOrder = (int) WalletTransaction::query()->where('user_id', $withdrawal->user_id)->max('sort_order') + 1;

        WalletTransaction::query()->create([
            'user_id' => $withdrawal->user_id,
            'occurred_label' => now()->format('M j · H:i'),
            'type' => PlatformTerms::TX_PAYOUT,
            'source' => $withdrawal->reference,
            'amount_label' => '-'.number_format($net, 2, '.', '').' '.$symbol,
            'amount_tone' => 'neutral',
            'status_label' => 'COMPLETED',
            'sort_order' => $sortOrder,
        ]);
    }
}
