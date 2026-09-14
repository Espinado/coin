<?php

namespace App\Services;

use App\Support\PlatformTerms;

use App\Models\Admin;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DepositService
{
    public function __construct(
        private WalletService $wallets,
    ) {}

    public function createPending(User $user, float $amount, string $currency = 'USDT'): Deposit
    {
        if ($amount <= 0) {
            throw new RuntimeException('Top-up amount must be greater than zero.');
        }

        $deposit = DB::transaction(function () use ($user, $amount, $currency) {
            return Deposit::query()->create([
                'user_id' => $user->id,
                'amount' => $amount,
                'currency' => $currency,
                'status' => Deposit::STATUS_PENDING,
                'method' => 'mock',
            ]);
        });

        if (config('coin.deposits.auto_confirm_mock')) {
            return $this->confirm($deposit, null);
        }

        return $deposit;
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
            $amount = (float) $deposit->amount;
            $currency = $deposit->currency ?: $this->wallets->currencyFor($wallet);

            $wallet->increment('available', $amount);
            $wallet->increment('balance', $amount);

            $this->wallets->record(
                $user,
                PlatformTerms::TX_TOP_UP,
                'Mock top-up',
                $amount,
                $currency,
                'positive',
                'COMPLETED',
                $deposit,
            );

            $deposit->update([
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
