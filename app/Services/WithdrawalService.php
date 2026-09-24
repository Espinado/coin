<?php

namespace App\Services;

use App\Events\WithdrawalUpdated;
use App\Support\PaymentStatusReason;
use App\Support\PlatformTerms;
use App\Models\Admin;
use App\Models\PaymentStatusLog;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentSimulatorService;
use App\Services\Payment\PaymentStatusLogService;
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

    /** @return array{payout_usdt: float, payout_amount: float, currency: string, platform_fee: float, total_debit_usdt: float, exchange_rate: float, usdt_per_btc: float} */
    public function quote(float $amount, string $currency): array
    {
        $currency = strtoupper(trim($currency));
        $liveUsdtPerBtc = $this->exchangeRates->fetchLiveUsdtPerBtc();
        $liveBtcPerUsdt = $this->exchangeRates->btcPerUsdtFromUsdtRate($liveUsdtPerBtc);
        $platformFee = $this->settings->platformWithdrawalFee();

        if ($currency === PayoutAddressService::CURRENCY_BTC) {
            $payoutUsdt = $this->exchangeRates->convertToBase($amount, 'BTC', $liveBtcPerUsdt)['amount'];
            $payoutAmount = round($amount, 8);
        } else {
            $payoutUsdt = round($amount, 2);
            $payoutAmount = $payoutUsdt;
            $currency = PayoutAddressService::CURRENCY_USDT;
        }

        return [
            'payout_usdt' => $payoutUsdt,
            'payout_amount' => $payoutAmount,
            'currency' => $currency,
            'platform_fee' => $platformFee,
            'total_debit_usdt' => round($payoutUsdt + $platformFee, 2),
            'exchange_rate' => $liveBtcPerUsdt,
            'usdt_per_btc' => $liveUsdtPerBtc,
        ];
    }

    public function assertCanCreate(User $user, float $amount, string $currency): array
    {
        $wallet = $user->wallet ?? throw new RuntimeException('User has no wallet.');
        $quote = $this->quote($amount, $currency);

        if ($quote['payout_usdt'] < $this->settings->minWithdrawal()) {
            throw new RuntimeException(__('coin.wallet.min_withdrawal_error', [
                'min' => $this->exchangeRates->formatMinWithdrawalLabel($currency),
            ]));
        }

        if ((float) $wallet->available + 0.001 < $quote['total_debit_usdt']) {
            throw new RuntimeException(__('coin.wallet.insufficient_funds'));
        }

        return $quote;
    }

    public function createForUser(User $user, float $amount, string $currency = PayoutAddressService::CURRENCY_USDT, ?string $payoutAddress = null): Withdrawal
    {
        $wallet = $user->wallet ?? throw new RuntimeException('User has no wallet.');
        $currency = strtoupper(trim($currency));

        if ($user->is_blocked) {
            throw new RuntimeException('Account is blocked.');
        }

        if ($this->settings->getBool('kyc_required_for_withdrawal') && $user->kyc_status !== User::KYC_APPROVED) {
            throw new RuntimeException('KYC approval is required before requesting a payout.');
        }

        if ($amount <= 0) {
            throw new RuntimeException('Amount must be greater than zero.');
        }

        $quote = $this->assertCanCreate($user, $amount, $currency);

        $this->payoutAddresses->assertWalletReady($wallet, $quote['currency']);
        $resolved = $this->payoutAddresses->resolveFor($wallet, $quote['currency']);

        return DB::transaction(function () use ($user, $wallet, $quote, $payoutAddress, $resolved) {
            $lockedWallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            $totalDebit = $quote['total_debit_usdt'];

            if ((float) $lockedWallet->available + 0.001 < $totalDebit) {
                throw new RuntimeException(__('coin.wallet.insufficient_funds'));
            }

            $lockedWallet->decrement('available', $totalDebit);
            $lockedWallet->increment('pending', $totalDebit);

            $withdrawal = Withdrawal::query()->create([
                'user_id' => $user->id,
                'reference' => 'WD-'.Str::upper(Str::random(8)),
                'amount' => $quote['payout_amount'],
                'base_amount' => $quote['payout_usdt'],
                'platform_fee' => $quote['platform_fee'],
                'currency' => $quote['currency'],
                'exchange_rate' => $quote['exchange_rate'],
                'usdt_per_btc' => $quote['usdt_per_btc'],
                'withdrawal_type' => 'available_balance',
                'payout_address' => $payoutAddress ?? $resolved['address'],
                'network_label' => $resolved['network_label'],
                'status' => Withdrawal::STATUS_PENDING,
            ]);

            WithdrawalUpdated::dispatch($withdrawal);

            app(PaymentStatusLogService::class)->withdrawalCreated($withdrawal);

            return $withdrawal;
        });
    }

    public function approveAndDispatch(Withdrawal $withdrawal, Admin $admin, ?string $note = null): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal, $admin, $note) {
            $withdrawal->refresh();

            if (! in_array($withdrawal->status, [Withdrawal::STATUS_PENDING, Withdrawal::STATUS_APPROVED], true)) {
                throw new RuntimeException(__('coin.admin.withdrawal_approve_pending_only'));
            }

            if ($note !== null && $note !== '') {
                $withdrawal->update([
                    'admin_note' => $note,
                    'processed_by' => $admin->id,
                ]);
                $withdrawal->refresh();
            }

            return $this->dispatchViaGateway($withdrawal, $admin);
        });
    }

    public function updateStatus(Withdrawal $withdrawal, string $status, Admin $admin, ?string $note = null): Withdrawal
    {
        if (! array_key_exists($status, Withdrawal::adminStatuses())) {
            throw new RuntimeException('Invalid withdrawal status.');
        }

        if ($status === Withdrawal::STATUS_PAID) {
            throw new RuntimeException(__('coin.admin.withdrawal_paid_requires_ipn'));
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

            $wallet = $this->lockWallet(
                $withdrawal->user->wallet ?? throw new RuntimeException('User has no wallet.'),
            );
            $reservedAmount = $withdrawal->totalReservedUsdt();
            $wasPending = $previous === Withdrawal::STATUS_PENDING;
            $wasCommitted = in_array($previous, Withdrawal::committedStatuses(), true);
            $willCommit = in_array($status, Withdrawal::committedStatuses(), true);

            if ($status === Withdrawal::STATUS_REJECTED) {
                if ($previous === Withdrawal::STATUS_PROCESSING
                    && $withdrawal->gateway_request_id !== null
                    && $this->settings->usesLivePaymentGateway()) {
                    throw new RuntimeException(__('coin.admin.withdrawal_reject_after_dispatch_blocked'));
                }

                if ($wasPending) {
                    $this->releasePending($wallet, $reservedAmount);
                } elseif ($wasCommitted) {
                    $this->restoreCommittedFunds($wallet, $reservedAmount);
                }
            } elseif ($willCommit && $wasPending) {
                $this->commitWithdrawalFunds($wallet, $reservedAmount);
            }

            $withdrawal->update([
                'status' => $status,
                'status_reason' => $status === Withdrawal::STATUS_REJECTED
                    ? PaymentStatusReason::WITHDRAWAL_ADMIN_REJECTED
                    : null,
                'processed_by' => $admin->id,
                'admin_note' => $note ?? $withdrawal->admin_note,
                'processed_at' => $status === Withdrawal::STATUS_REJECTED ? now() : $withdrawal->processed_at,
            ]);

            $withdrawal = $withdrawal->fresh(['user.wallet', 'processedByAdmin']);

            WithdrawalUpdated::dispatch($withdrawal);

            app(PaymentStatusLogService::class)->withdrawalAdminStatusChange(
                $withdrawal,
                $previous,
                $status,
            );

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

        $this->settings->assertLivePaymentGatewayReady();

        $autoSimulateIpn ??= $this->shouldAutoCompleteMockPayout();

        return DB::transaction(function () use ($withdrawal, $admin, $autoSimulateIpn) {
            $withdrawal = $this->lockWithdrawal($withdrawal);
            $previousStatus = $withdrawal->status;
            $wasDispatched = false;

            if ($withdrawal->status === Withdrawal::STATUS_PENDING) {
                $wallet = $this->lockWallet(
                    $withdrawal->user->wallet ?? throw new RuntimeException('User has no wallet.'),
                );
                $this->commitWithdrawalFunds($wallet, $withdrawal->totalReservedUsdt());

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
                $wasDispatched = true;
            }

            if ($autoSimulateIpn) {
                app(PaymentSimulatorService::class)->simulateWithdrawalIpn($withdrawal);
            }

            $withdrawal = $withdrawal->fresh(['user.wallet', 'processedByAdmin']);

            WithdrawalUpdated::dispatch($withdrawal);

            if ($wasDispatched) {
                app(PaymentStatusLogService::class)->withdrawalDispatched($withdrawal, $previousStatus);
            }

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

    public function markFailedFromGateway(
        Withdrawal $withdrawal,
        ?string $gatewayState = null,
        ?string $reason = null,
        ?string $statusReason = null,
        string $logSource = PaymentStatusLog::SOURCE_APP,
    ): Withdrawal {
        return DB::transaction(function () use ($withdrawal, $gatewayState, $reason, $statusReason, $logSource) {
            $withdrawal = $this->lockWithdrawal($withdrawal);

            if ($withdrawal->status === Withdrawal::STATUS_REJECTED) {
                return $withdrawal;
            }

            if ($withdrawal->status !== Withdrawal::STATUS_PROCESSING) {
                throw new RuntimeException('Only processing withdrawals can be marked failed from gateway.');
            }

            $wallet = $this->lockWallet(
                $withdrawal->user->wallet ?? throw new RuntimeException('User has no wallet.'),
            );
            $this->restoreCommittedFunds($wallet, $withdrawal->totalReservedUsdt());

            $updates = [
                'status' => Withdrawal::STATUS_REJECTED,
                'status_reason' => $statusReason ?? PaymentStatusReason::WITHDRAWAL_GATEWAY_FAILED,
                'gateway_state' => $gatewayState ?? $withdrawal->gateway_state,
                'processed_at' => now(),
            ];

            if ($reason !== null && trim($reason) !== '') {
                $updates['admin_note'] = trim(($withdrawal->admin_note ? $withdrawal->admin_note."\n" : '').$reason);
            }

            $withdrawal->update($updates);

            $withdrawal = $withdrawal->fresh(['user.wallet', 'processedByAdmin']);

            WithdrawalUpdated::dispatch($withdrawal);

            app(PaymentStatusLogService::class)->withdrawalFailed($withdrawal, $logSource);

            return $withdrawal;
        });
    }

    public function markPaidFromGateway(
        Withdrawal $withdrawal,
        ?string $txid = null,
        ?string $gatewayState = null,
        string $logSource = PaymentStatusLog::SOURCE_APP,
    ): Withdrawal {
        return DB::transaction(function () use ($withdrawal, $txid, $gatewayState, $logSource) {
            $withdrawal = $this->lockWithdrawal($withdrawal);
            $previous = $withdrawal->status;

            if ($previous === Withdrawal::STATUS_PAID) {
                return $withdrawal;
            }

            if ($previous !== Withdrawal::STATUS_PROCESSING) {
                throw new RuntimeException('Only processing withdrawals can be marked paid from gateway.');
            }

            $wallet = $this->lockWallet(
                $withdrawal->user->wallet ?? throw new RuntimeException('User has no wallet.'),
            );

            $this->recordPayoutTransaction($withdrawal, $wallet);
            $this->recordPlatformFeeTransaction($withdrawal, $wallet);

            $withdrawal->update([
                'status' => Withdrawal::STATUS_PAID,
                'txid' => $txid ?? $withdrawal->txid,
                'gateway_state' => $gatewayState ?? $withdrawal->gateway_state,
                'processed_at' => now(),
            ]);

            $withdrawal = $withdrawal->fresh(['user.wallet', 'processedByAdmin']);

            $currency = $withdrawal->currency ?: $this->settings->tokenSymbol();

            $this->notifications->notifyWithdrawalPaid(
                $withdrawal->user,
                $withdrawal,
                $withdrawal->ledgerAmount(),
                $currency,
            );

            WithdrawalUpdated::dispatch($withdrawal);

            app(PaymentStatusLogService::class)->withdrawalPaid($withdrawal, $logSource);

            return $withdrawal;
        });
    }

    private function lockWithdrawal(Withdrawal $withdrawal): Withdrawal
    {
        return Withdrawal::query()
            ->whereKey($withdrawal->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function lockWallet(Wallet $wallet): Wallet
    {
        return Wallet::query()
            ->whereKey($wallet->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function releasePending(Wallet $wallet, float $amount): void
    {
        $wallet->decrement('pending', min($amount, (float) $wallet->pending));
        $wallet->increment('available', $amount);
    }

    private function commitWithdrawalFunds(Wallet $wallet, float $amount): void
    {
        if ((float) $wallet->pending + 0.001 < $amount) {
            throw new RuntimeException('Insufficient pending balance for withdrawal commit.');
        }

        $wallet->decrement('pending', $amount);
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

        $payoutUsdt = $withdrawal->ledgerAmount();
        $symbol = $this->settings->tokenSymbol();
        $sortOrder = (int) WalletTransaction::query()->where('user_id', $withdrawal->user_id)->max('sort_order') + 1;

        WalletTransaction::query()->create([
            'user_id' => $withdrawal->user_id,
            'occurred_label' => now()->format('M j · H:i'),
            'type' => PlatformTerms::TX_PAYOUT,
            'source' => $withdrawal->reference,
            'amount_label' => '-'.number_format($payoutUsdt, 2, '.', '').' '.$symbol,
            'amount_tone' => 'neutral',
            'status_label' => 'COMPLETED',
            'sort_order' => $sortOrder,
        ]);
    }

    private function recordPlatformFeeTransaction(Withdrawal $withdrawal, Wallet $wallet): void
    {
        $fee = $withdrawal->platformFeeAmount();

        if ($fee <= 0) {
            return;
        }

        $source = $withdrawal->reference.':fee';

        if (WalletTransaction::query()
            ->where('user_id', $withdrawal->user_id)
            ->where('source', $source)
            ->where('type', PlatformTerms::TX_PLATFORM_FEE)
            ->exists()) {
            return;
        }

        $symbol = $this->settings->tokenSymbol();
        $sortOrder = (int) WalletTransaction::query()->where('user_id', $withdrawal->user_id)->max('sort_order') + 1;

        WalletTransaction::query()->create([
            'user_id' => $withdrawal->user_id,
            'occurred_label' => now()->format('M j · H:i'),
            'type' => PlatformTerms::TX_PLATFORM_FEE,
            'source' => $source,
            'amount_label' => '-'.number_format($fee, 2, '.', '').' '.$symbol,
            'amount_tone' => 'neutral',
            'status_label' => 'COMPLETED',
            'sort_order' => $sortOrder,
        ]);
    }
}
