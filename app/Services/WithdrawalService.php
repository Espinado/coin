<?php

namespace App\Services;

use App\Events\WithdrawalUpdated;
use App\Support\PlatformTerms;
use App\Models\Admin;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentSimulatorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class WithdrawalService
{
    public function __construct(
        private PlatformSettingsService $settings,
        private UserNotificationService $notifications,
        private ExchangeRateService $exchangeRates,
        private PayoutAddressService $payoutAddresses,
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

        $this->payoutAddresses->assertWalletReady($wallet);

        $liveUsdtPerBtc = $this->exchangeRates->fetchLiveUsdtPerBtc();
        $liveBtcPerUsdt = $this->exchangeRates->btcPerUsdtFromUsdtRate($liveUsdtPerBtc);

        return DB::transaction(function () use ($user, $wallet, $amount, $payoutAddress, $liveBtcPerUsdt, $liveUsdtPerBtc) {
            $wallet->decrement('available', $amount);
            $wallet->increment('pending', $amount);

            $withdrawal = Withdrawal::query()->create([
                'user_id' => $user->id,
                'reference' => 'WD-'.Str::upper(Str::random(8)),
                'amount' => $amount,
                'currency' => $wallet->currency ?: (string) config('coin.wallet.base_currency', 'USDT'),
                'exchange_rate' => $liveBtcPerUsdt,
                'usdt_per_btc' => $liveUsdtPerBtc,
                'withdrawal_type' => 'available_balance',
                'payout_address' => $payoutAddress ?? $wallet->payout_address ?? '—',
                'network_label' => $wallet->network_label,
                'status' => Withdrawal::STATUS_PENDING,
            ]);

            WithdrawalUpdated::dispatch($withdrawal);

            return $withdrawal;
        });
    }

    public function updateStatus(Withdrawal $withdrawal, string $status, Admin $admin, ?string $note = null): Withdrawal
    {
        if (! array_key_exists($status, Withdrawal::adminStatuses())) {
            throw new RuntimeException('Invalid withdrawal status.');
        }

        return DB::transaction(function () use ($withdrawal, $status, $admin, $note) {
            $withdrawal->refresh();
            $previous = $withdrawal->status;

            if ($previous === $status) {
                return $withdrawal;
            }

            if (in_array($previous, Withdrawal::closedStatuses(), true)) {
                throw new RuntimeException(__('coin.admin.withdrawal_closed'));
            }

            if (! Withdrawal::canTransition($previous, $status)) {
                throw new RuntimeException(__('coin.admin.withdrawal_invalid_transition', [
                    'from' => $withdrawal->statusLabel(),
                    'to' => Withdrawal::adminStatuses()[$status] ?? $status,
                ]));
            }

            $wallet = $withdrawal->user->wallet ?? throw new RuntimeException('User has no wallet.');
            $amount = (float) $withdrawal->amount;
            $wasPending = $previous === Withdrawal::STATUS_PENDING;
            $wasCommitted = in_array($previous, Withdrawal::committedStatuses(), true);
            $willCommit = in_array($status, Withdrawal::committedStatuses(), true);

            if ($status === Withdrawal::STATUS_REJECTED) {
                if ($wasPending) {
                    $this->releasePending($wallet, $amount);
                } elseif ($wasCommitted) {
                    $this->restoreCommittedFunds($wallet, $amount);
                }
            } elseif ($willCommit && $wasPending) {
                $this->commitWithdrawalFunds($wallet, $amount);
            }

            if ($status === Withdrawal::STATUS_PAID && $previous !== Withdrawal::STATUS_PAID) {
                $this->recordPayoutTransaction($withdrawal, $wallet);
            }

            $withdrawal->update([
                'status' => $status,
                'processed_by' => $admin->id,
                'admin_note' => $note ?? $withdrawal->admin_note,
                'processed_at' => in_array($status, [Withdrawal::STATUS_PAID, Withdrawal::STATUS_REJECTED], true) ? now() : $withdrawal->processed_at,
            ]);

            $withdrawal = $withdrawal->fresh(['user.wallet', 'processedByAdmin']);

            if ($status === Withdrawal::STATUS_PAID && $previous !== Withdrawal::STATUS_PAID) {
                $fee = $this->settings->getFloat('network_fee');
                $net = max(0, $amount - $fee);
                $currency = $withdrawal->currency ?: $this->settings->tokenSymbol();

                $this->notifications->notifyWithdrawalPaid(
                    $withdrawal->user,
                    $withdrawal,
                    $net,
                    $currency,
                );
            }

            WithdrawalUpdated::dispatch($withdrawal);

            return $withdrawal;
        });
    }

    public function simulatePayoutViaGateway(Withdrawal $withdrawal): Withdrawal
    {
        if ((string) config('coin.payments.driver', 'mock') !== 'mock') {
            throw new RuntimeException('Gateway payout simulation is only available for the mock driver.');
        }

        return $this->dispatchViaGateway($withdrawal, null, true);
    }

    public function dispatchViaGateway(Withdrawal $withdrawal, ?Admin $admin = null, ?bool $autoSimulateIpn = null): Withdrawal
    {
        if (! $this->usesPaymentGateway()) {
            return $withdrawal;
        }

        $autoSimulateIpn ??= $this->shouldAutoCompleteMockPayout();

        return DB::transaction(function () use ($withdrawal, $admin, $autoSimulateIpn) {
            $withdrawal->refresh();

            if ($withdrawal->status === Withdrawal::STATUS_PENDING) {
                $wallet = $withdrawal->user->wallet ?? throw new RuntimeException('User has no wallet.');
                $this->commitWithdrawalFunds($wallet, (float) $withdrawal->amount);

                $withdrawal->update([
                    'status' => Withdrawal::STATUS_PROCESSING,
                    'processed_by' => $admin?->id ?? $withdrawal->processed_by,
                ]);

                $withdrawal->refresh();
            }

            if ($withdrawal->status !== Withdrawal::STATUS_PROCESSING) {
                throw new RuntimeException('Only pending or processing withdrawals can be sent via gateway.');
            }

            if ($withdrawal->gateway_request_id === null) {
                $gateway = app(PaymentGatewayInterface::class);
                $payout = $gateway->sendPayout($withdrawal);

                $withdrawal->update([
                    'gateway_request_id' => $payout->gatewayRequestId,
                    'sent_at' => now(),
                    'processed_by' => $admin?->id ?? $withdrawal->processed_by,
                ]);

                $withdrawal->refresh();
            }

            if ($autoSimulateIpn) {
                app(PaymentSimulatorService::class)->simulateWithdrawalIpn($withdrawal);
            }

            $withdrawal = $withdrawal->fresh(['user.wallet', 'processedByAdmin']);

            WithdrawalUpdated::dispatch($withdrawal);

            return $withdrawal;
        });
    }

    private function usesPaymentGateway(): bool
    {
        return in_array((string) config('coin.payments.driver', 'mock'), ['mock', 'ccapi'], true);
    }

    private function shouldAutoCompleteMockPayout(): bool
    {
        if ((string) config('coin.payments.driver', 'mock') !== 'mock') {
            return false;
        }

        return (bool) config('coin.payments.mock.auto_complete_payout', true);
    }

    public function markPaidFromGateway(Withdrawal $withdrawal, ?string $txid = null, ?string $gatewayState = null): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal, $txid, $gatewayState) {
            $withdrawal->refresh();
            $previous = $withdrawal->status;

            if ($previous === Withdrawal::STATUS_PAID) {
                return $withdrawal;
            }

            if ($previous !== Withdrawal::STATUS_PROCESSING) {
                throw new RuntimeException('Only processing withdrawals can be marked paid from gateway.');
            }

            $wallet = $withdrawal->user->wallet ?? throw new RuntimeException('User has no wallet.');

            $this->recordPayoutTransaction($withdrawal, $wallet);

            $withdrawal->update([
                'status' => Withdrawal::STATUS_PAID,
                'txid' => $txid ?? $withdrawal->txid,
                'gateway_state' => $gatewayState ?? $withdrawal->gateway_state,
                'processed_at' => now(),
            ]);

            $withdrawal = $withdrawal->fresh(['user.wallet', 'processedByAdmin']);

            $fee = $this->settings->getFloat('network_fee');
            $net = max(0, (float) $withdrawal->amount - $fee);
            $currency = $withdrawal->currency ?: $this->settings->tokenSymbol();

            $this->notifications->notifyWithdrawalPaid(
                $withdrawal->user,
                $withdrawal,
                $net,
                $currency,
            );

            WithdrawalUpdated::dispatch($withdrawal);

            return $withdrawal;
        });
    }

    private function releasePending(Wallet $wallet, float $amount): void
    {
        $wallet->decrement('pending', min($amount, (float) $wallet->pending));
        $wallet->increment('available', $amount);
    }

    private function commitWithdrawalFunds(Wallet $wallet, float $amount): void
    {
        $wallet->decrement('pending', min($amount, (float) $wallet->pending));
        $wallet->decrement('balance', $amount);
    }

    private function restoreCommittedFunds(Wallet $wallet, float $amount): void
    {
        $wallet->increment('balance', $amount);
        $wallet->increment('available', $amount);
    }

    private function recordPayoutTransaction(Withdrawal $withdrawal, Wallet $wallet): void
    {
        if (WalletTransaction::query()
            ->where('user_id', $withdrawal->user_id)
            ->where('source', $withdrawal->reference)
            ->where('type', PlatformTerms::TX_PAYOUT)
            ->exists()) {
            return;
        }

        $amount = (float) $withdrawal->amount;
        $fee = $this->settings->getFloat('network_fee');
        $net = max(0, $amount - $fee);
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
