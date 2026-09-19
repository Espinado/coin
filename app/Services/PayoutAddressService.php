<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Support\TronAddressValidator;

class PayoutAddressService
{
    public function __construct(
        private readonly TronAddressValidator $validator,
        private readonly WalletService $wallets,
    ) {}

    public function networkLabel(): string
    {
        return (string) config('coin.wallet.payout_network', 'TRC-20');
    }

    public function saveForUser(User $user, string $address): Wallet
    {
        $normalized = trim($address);
        $this->assertValidOrFail($normalized);

        $wallet = $this->wallets->ensureWallet($user);
        $wallet->update([
            'payout_address' => $normalized,
            'network_label' => $this->networkLabel(),
        ]);

        return $wallet->fresh();
    }

    public function clearForUser(User $user): Wallet
    {
        $wallet = $this->wallets->ensureWallet($user);
        $wallet->update([
            'payout_address' => null,
            'network_label' => null,
        ]);

        return $wallet->fresh();
    }

    public function assertWalletReady(?Wallet $wallet): void
    {
        $address = $wallet?->payout_address;

        if (! is_string($address) || trim($address) === '' || $address === '—') {
            throw new \RuntimeException(__('coin.wallet.payout_address_missing'));
        }

        $this->assertValidOrFail($address);
    }

    private function assertValidOrFail(string $address): void
    {
        if (! $this->validator->isValid($address)) {
            throw new \RuntimeException(__('coin.wallet.payout_address_invalid'));
        }
    }
}
