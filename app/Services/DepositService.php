<?php

namespace App\Services;

use App\Support\PlatformTerms;

use App\Models\Admin;
use App\Models\Deposit;
use App\Models\User;
use App\Services\Payment\Dtos\DepositIntentDto;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DepositService
{
    public function __construct(
        private WalletService $wallets,
        private ExchangeRateService $exchangeRates,
    ) {}

    public function createPending(User $user, float $amount, string $currency = 'USDT', ?string $method = null): Deposit
    {
        if ($amount <= 0) {
            throw new RuntimeException('Top-up amount must be greater than zero.');
        }

        $currency = strtoupper(trim($currency));
        $allowed = config('coin.deposits.currencies', ['USDT', 'BTC']);

        if (! in_array($currency, $allowed, true)) {
            throw new RuntimeException('Unsupported top-up currency.');
        }

        $method ??= (string) config('coin.payments.driver', 'mock');

        $deposit = DB::transaction(function () use ($user, $amount, $currency, $method) {
            return Deposit::query()->create([
                'user_id' => $user->id,
                'amount' => $amount,
                'currency' => $currency,
                'status' => Deposit::STATUS_PENDING,
                'method' => $method,
            ]);
        });

        if ($this->shouldAutoConfirmMock()) {
            return $this->confirm($deposit, null);
        }

        return $deposit;
    }

    public function initiateWithGateway(User $user, float $amount, string $currency, PaymentGatewayInterface $gateway): Deposit
    {
        $driver = (string) config('coin.payments.driver', 'mock');
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

        return $deposit->fresh(['user']);
    }

    private function shouldAutoConfirmMock(): bool
    {
        if (! config('coin.deposits.auto_confirm_mock')) {
            return false;
        }

        return app()->environment(['local', 'testing']);
    }

    public function confirm(Deposit $deposit, ?Admin $admin = null): Deposit
    {
        if ($deposit->status !== Deposit::STATUS_PENDING) {
            throw new RuntimeException('Only pending top-ups can be confirmed.');
        }

        return DB::transaction(function () use ($deposit, $admin) {
            $deposit->refresh();
            $user = $deposit->user;
            $wallet = $this->wallets->ensureWallet($user);
            $paymentAmount = (float) $deposit->amount;
            $paymentCurrency = strtoupper((string) ($deposit->currency ?: 'USDT'));
            $conversion = $this->exchangeRates->convertToBase($paymentAmount, $paymentCurrency);
            $creditedAmount = $conversion['amount'];
            $walletCurrency = $this->wallets->currencyFor($wallet);

            $wallet->increment('available', $creditedAmount);
            $wallet->increment('balance', $creditedAmount);

            $source = $paymentCurrency === $walletCurrency
                ? __('coin.tx_sources.mock_top_up')
                : __('coin.tx_sources.mock_top_up_converted', [
                    'paid' => number_format($paymentAmount, 2, '.', '').' '.$paymentCurrency,
                    'rate' => number_format((float) ($conversion['rate'] ?? 1), 4, '.', ''),
                ]);

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

            $deposit->update([
                'credited_amount' => $creditedAmount,
                'credited_currency' => $walletCurrency,
                'exchange_rate' => $conversion['rate'],
                'status' => Deposit::STATUS_CONFIRMED,
                'confirmed_by' => $admin?->id,
                'confirmed_at' => now(),
            ]);

            return $deposit->fresh(['user.wallet']);
        });
    }

    public function reject(Deposit $deposit, ?Admin $admin = null): Deposit
    {
        if ($deposit->status !== Deposit::STATUS_PENDING) {
            throw new RuntimeException('Only pending top-ups can be rejected.');
        }

        $deposit->update([
            'status' => Deposit::STATUS_REJECTED,
            'confirmed_by' => $admin?->id,
            'confirmed_at' => now(),
        ]);

        return $deposit->fresh(['user']);
    }
}
