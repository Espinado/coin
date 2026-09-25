<?php

namespace App\Services;

use App\Events\DepositUpdated;
use App\Support\PlatformTerms;
use App\Models\Admin;
use App\Models\Deposit;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Support\PaymentStatusReason;
use App\Models\PaymentStatusLog;
use App\Jobs\ExpirePendingDepositJob;
use App\Services\Payment\Dtos\DepositIntentDto;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentStatusLogService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DepositService
{
    public function __construct(
        private WalletService $wallets,
        private ExchangeRateService $exchangeRates,
        private PlatformSettingsService $settings,
    ) {}

    public function createPending(User $user, float $amount, string $currency = 'USDT', ?string $method = null): Deposit
    {
        $currency = strtoupper(trim($currency));
        $allowed = config('coin.deposits.currencies', ['USDT', 'BTC']);

        if (! in_array($currency, $allowed, true)) {
            throw new RuntimeException('Unsupported top-up currency.');
        }

        $lockedBtcPerUsdt = null;
        $lockedUsdtPerBtc = null;

        if ($currency === 'BTC') {
            $lockedUsdtPerBtc = $this->exchangeRates->fetchLiveUsdtPerBtc();
            $lockedBtcPerUsdt = $this->exchangeRates->btcPerUsdtFromUsdtRate($lockedUsdtPerBtc);
            $this->exchangeRates->assertMinDeposit($amount, $currency, $lockedBtcPerUsdt);
        } else {
            $this->exchangeRates->assertMinDeposit($amount, $currency);
        }

        $method ??= (string) config('coin.payments.driver', 'mock');

        $deposit = DB::transaction(function () use ($user, $amount, $currency, $method, $lockedBtcPerUsdt) {
            return Deposit::query()->create([
                'user_id' => $user->id,
                'amount' => $amount,
                'currency' => $currency,
                'exchange_rate' => $lockedBtcPerUsdt,
                'status' => Deposit::STATUS_PENDING,
                'method' => $method,
            ]);
        });

        if ($this->shouldAutoConfirmMock()) {
            return $this->confirm($deposit, null);
        }

        app(PaymentStatusLogService::class)->depositCreated($deposit);

        return $deposit;
    }

    public function initiateWithGateway(User $user, float $amount, string $currency, PaymentGatewayInterface $gateway): Deposit
    {
        $this->settings->assertLivePaymentGatewayReady();

        $driver = $this->settings->paymentGateEnabled()
            ? (string) config('coin.payments.driver', 'mock')
            : 'mock';
        $deposit = $this->createPending($user, $amount, $currency, $driver);

        $intent = $gateway->createDepositIntent($deposit);
        $this->applyDepositIntent($deposit, $intent);

        return $deposit->fresh(['user']);
    }

    public function applyDepositIntent(Deposit $deposit, DepositIntentDto $intent): Deposit
    {
        $deposit->update([
            'payment_address' => $intent->paymentAddress,
            'gateway_uniq_id' => $intent->gatewayUniqId,
            'gateway_network' => $intent->gatewayNetwork,
            'expires_at' => $intent->expiresAt,
            'external_reference' => $intent->gatewayUniqId,
        ]);

        $deposit = $deposit->fresh(['user']);

        if ($deposit->method === 'ccapi' && $intent->expiresAt !== null) {
            ExpirePendingDepositJob::dispatch($deposit->id)->delay($intent->expiresAt);
        }

        return $deposit;
    }

    public function expireIfDue(Deposit $deposit, string $logSource = PaymentStatusLog::SOURCE_APP): ?Deposit
    {
        $deposit = $deposit->fresh();

        if ($deposit->status !== Deposit::STATUS_PENDING) {
            return null;
        }

        if ($deposit->method !== 'ccapi') {
            return null;
        }

        if ($deposit->expires_at === null || $deposit->expires_at->isFuture()) {
            return null;
        }

        try {
            return $this->reject($deposit, null, PaymentStatusReason::DEPOSIT_EXPIRED, logSource: $logSource);
        } catch (RuntimeException) {
            return null;
        }
    }

    public function expireAllDuePending(string $logSource = PaymentStatusLog::SOURCE_POLL): int
    {
        $expired = 0;

        Deposit::query()
            ->where('status', Deposit::STATUS_PENDING)
            ->where('method', 'ccapi')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->each(function (Deposit $deposit) use (&$expired, $logSource): void {
                if ($this->expireIfDue($deposit, $logSource) !== null) {
                    $expired++;
                }
            });

        return $expired;
    }

    private function shouldAutoConfirmMock(): bool
    {
        if (! config('coin.deposits.auto_confirm_mock')) {
            return false;
        }

        return app()->environment(['local', 'testing']);
    }

    public function confirm(Deposit $deposit, ?Admin $admin = null, string $logSource = PaymentStatusLog::SOURCE_APP): Deposit
    {
        return DB::transaction(function () use ($deposit, $admin, $logSource) {
            $deposit = Deposit::query()
                ->whereKey($deposit->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($deposit->status === Deposit::STATUS_CONFIRMED) {
                return $deposit->fresh(['user.wallet']);
            }

            if ($deposit->status !== Deposit::STATUS_PENDING) {
                throw new RuntimeException('Only pending top-ups can be confirmed.');
            }

            if ($admin !== null) {
                throw new RuntimeException(__('coin.admin.deposit_manual_action_blocked'));
            }

            $walletTransactionExists = WalletTransaction::query()
                ->where('reference_type', $deposit->getMorphClass())
                ->where('reference_id', $deposit->id)
                ->where('type', PlatformTerms::TX_TOP_UP)
                ->exists();

            if ($walletTransactionExists) {
                throw new RuntimeException('Deposit wallet transaction already exists but deposit is still pending.');
            }

            $user = $deposit->user;
            $wallet = $this->wallets->ensureWallet($user);
            $paymentAmount = (float) $deposit->amount;
            $paymentCurrency = strtoupper((string) ($deposit->currency ?: 'USDT'));
            $lockedBtcPerUsdt = $paymentCurrency === 'BTC' && $deposit->exchange_rate
                ? (float) $deposit->exchange_rate
                : null;

            $conversion = $lockedBtcPerUsdt !== null
                ? $this->exchangeRates->convertToBase($paymentAmount, $paymentCurrency, $lockedBtcPerUsdt)
                : $this->exchangeRates->convertToBaseAtLiveRate($paymentAmount, $paymentCurrency);
            $creditedAmount = $conversion['amount'];
            $walletCurrency = $this->wallets->currencyFor($wallet);

            $wallet->increment('available', $creditedAmount);
            $wallet->increment('balance', $creditedAmount);

            $isLiveDeposit = $deposit->method === 'ccapi';
            $sourceKey = $paymentCurrency === $walletCurrency
                ? ($isLiveDeposit ? 'live_top_up' : 'mock_top_up')
                : ($isLiveDeposit ? 'live_top_up_converted' : 'mock_top_up_converted');

            $source = __(
                'coin.tx_sources.'.$sourceKey,
                $sourceKey === 'mock_top_up' || $sourceKey === 'live_top_up'
                    ? []
                    : [
                        'paid' => number_format($paymentAmount, 2, '.', '').' '.$paymentCurrency,
                        'rate' => number_format((float) ($conversion['rate'] ?? 1), 4, '.', ''),
                    ],
            );

            try {
                $this->wallets->record(
                    $user,
                    PlatformTerms::TX_TOP_UP,
                    $source,
                    $creditedAmount,
                    $walletCurrency,
                    'positive',
                    'COMPLETED',
                    $deposit,
                );
            } catch (UniqueConstraintViolationException $exception) {
                Log::warning('Duplicate deposit wallet transaction ignored during confirm.', [
                    'deposit_id' => $deposit->id,
                ]);

                $deposit->refresh();

                if ($deposit->status === Deposit::STATUS_CONFIRMED) {
                    return $deposit->fresh(['user.wallet']);
                }

                throw $exception;
            }

            $deposit->update([
                'credited_amount' => $creditedAmount,
                'credited_currency' => $walletCurrency,
                'exchange_rate' => $conversion['rate'],
                'status' => Deposit::STATUS_CONFIRMED,
                'confirmed_by' => $admin?->id,
                'confirmed_at' => now(),
            ]);

            $deposit = $deposit->fresh(['user.wallet']);
            DepositUpdated::dispatch($deposit);
            app(PaymentStatusLogService::class)->depositConfirmed($deposit, $logSource);

            return $deposit;
        });
    }

    public function reject(
        Deposit $deposit,
        ?Admin $admin = null,
        ?string $statusReason = null,
        ?float $receivedAmount = null,
        string $logSource = PaymentStatusLog::SOURCE_APP,
    ): Deposit {
        return DB::transaction(function () use ($deposit, $admin, $statusReason, $receivedAmount, $logSource) {
            $deposit = Deposit::query()
                ->whereKey($deposit->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($deposit->status !== Deposit::STATUS_PENDING) {
                throw new RuntimeException('Only pending top-ups can be rejected.');
            }

            if ($admin !== null) {
                throw new RuntimeException(__('coin.admin.deposit_manual_action_blocked'));
            }

            $updates = [
                'status' => Deposit::STATUS_REJECTED,
                'status_reason' => $statusReason ?? PaymentStatusReason::DEPOSIT_GENERIC,
                'confirmed_by' => $admin?->id,
                'confirmed_at' => now(),
            ];

            if ($receivedAmount !== null) {
                $updates['received_amount'] = $receivedAmount;
            }

            $deposit->update($updates);

            $deposit = $deposit->fresh(['user']);
            DepositUpdated::dispatch($deposit);
            app(PaymentStatusLogService::class)->depositRejected($deposit, $logSource);

            return $deposit;
        });
    }
}
