<?php

namespace App\Services;

use App\Events\DepositUpdated;
use App\Support\PlatformTerms;
use App\Models\Admin;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Support\CcapiUserMessage;
use App\Support\CryptoAmountFormat;
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
        $prepared = $this->exchangeRates->prepareGatewayDeposit($amount, $currency);

        $method ??= (string) config('coin.payments.driver', 'mock');

        $deposit = DB::transaction(function () use ($user, $prepared, $method) {
            return Deposit::query()->create([
                'user_id' => $user->id,
                'amount' => $prepared['pay_amount'],
                'currency' => $prepared['pay_currency'],
                'input_amount' => $prepared['input_amount'],
                'input_currency' => $prepared['input_currency'],
                'exchange_rate' => $prepared['exchange_rate'],
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

        try {
            $intent = $gateway->createDepositIntent($deposit);
            $this->applyDepositIntent($deposit, $intent);
        } catch (\Throwable $exception) {
            try {
                $this->reject(
                    $deposit->fresh(),
                    null,
                    PaymentStatusReason::DEPOSIT_GATEWAY_FAILED,
                    logSource: PaymentStatusLog::SOURCE_APP,
                );
            } catch (RuntimeException $rejectException) {
                Log::warning('Failed to reject deposit after gateway error.', [
                    'deposit_id' => $deposit->id,
                    'gateway_error' => $exception->getMessage(),
                    'reject_error' => $rejectException->getMessage(),
                ]);
            }

            throw new RuntimeException(CcapiUserMessage::fromThrowable($exception), 0, $exception);
        }

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
            $wallet = Wallet::query()
                ->whereKey($wallet->id)
                ->lockForUpdate()
                ->firstOrFail();
            $paymentAmount = (float) $deposit->amount;
            $paymentCurrency = strtoupper((string) ($deposit->currency ?: 'USDT'));
            $inputCurrency = strtoupper((string) ($deposit->input_currency ?? ''));
            $walletCurrency = $this->wallets->currencyFor($wallet);
            $isLiveDeposit = $deposit->method === 'ccapi';

            if ($paymentCurrency === 'BTC') {
                $lockedBtcPerUsdt = $deposit->exchange_rate
                    ? (float) $deposit->exchange_rate
                    : null;
                $conversion = $lockedBtcPerUsdt !== null
                    ? $this->exchangeRates->convertToBase($paymentAmount, $paymentCurrency, $lockedBtcPerUsdt)
                    : $this->exchangeRates->convertToBaseAtLiveRate($paymentAmount, $paymentCurrency);
                $creditedAmount = $conversion['amount'];
                $sourceKey = $isLiveDeposit ? 'live_top_up_converted' : 'mock_top_up_converted';
                $source = __(
                    'coin.tx_sources.'.$sourceKey,
                    [
                        'paid' => rtrim(rtrim(number_format($paymentAmount, 8, '.', ''), '0'), '.').' BTC',
                        'rate' => number_format((float) ($conversion['rate'] ?? 1), 8, '.', ''),
                    ],
                );
            } elseif ($deposit->hasInputConversion() && $inputCurrency === 'BTC') {
                $creditedAmount = $paymentAmount;
                $conversion = [
                    'amount' => $creditedAmount,
                    'rate' => $deposit->exchange_rate,
                ];
                $sourceKey = $isLiveDeposit ? 'live_top_up_converted' : 'mock_top_up_converted';
                $source = __(
                    'coin.tx_sources.'.$sourceKey,
                    [
                        'paid' => CryptoAmountFormat::amountWithSymbol((float) $deposit->input_amount, 'BTC'),
                        'rate' => number_format((float) ($deposit->exchange_rate ?? 0), 8, '.', ''),
                    ],
                );
            } else {
                $creditedAmount = $paymentAmount;
                $conversion = [
                    'amount' => $creditedAmount,
                    'rate' => null,
                ];
                $sourceKey = $isLiveDeposit ? 'live_top_up' : 'mock_top_up';
                $source = __('coin.tx_sources.'.$sourceKey);
            }

            $wallet->increment('available', $creditedAmount);
            $wallet->increment('balance', $creditedAmount);

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
                'exchange_rate' => $conversion['rate'] ?? $deposit->exchange_rate,
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

    public function discardOrphanPending(Deposit $deposit, string $logSource = PaymentStatusLog::SOURCE_APP): Deposit
    {
        $this->assertOrphanPending($deposit);

        return $this->reject($deposit, null, PaymentStatusReason::DEPOSIT_GATEWAY_FAILED, logSource: $logSource);
    }

    public function deleteOrphanPending(Deposit $deposit): void
    {
        $this->assertOrphanPending($deposit);

        DB::transaction(function () use ($deposit): void {
            $deposit = Deposit::query()
                ->whereKey($deposit->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertOrphanPending($deposit);

            $deposit->delete();
        });
    }

    private function assertOrphanPending(Deposit $deposit): void
    {
        $deposit = $deposit->fresh();

        if ($deposit->status !== Deposit::STATUS_PENDING) {
            throw new RuntimeException('Only pending top-ups can be discarded.');
        }

        if (filled($deposit->payment_address)) {
            throw new RuntimeException('Deposit already has a payment address.');
        }

        if (filled($deposit->txid)) {
            throw new RuntimeException('Deposit already has a blockchain transaction.');
        }

        $walletTransactionExists = WalletTransaction::query()
            ->where('reference_type', $deposit->getMorphClass())
            ->where('reference_id', $deposit->id)
            ->exists();

        if ($walletTransactionExists) {
            throw new RuntimeException('Deposit already has a wallet transaction.');
        }
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
