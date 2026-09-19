<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Support\BitcoinAddressValidator;
use App\Support\TronAddressValidator;
use RuntimeException;

class PayoutAddressService
{
    public const CURRENCY_USDT = 'USDT';

    public const CURRENCY_BTC = 'BTC';

    public function __construct(
        private readonly TronAddressValidator $tronValidator,
        private readonly BitcoinAddressValidator $bitcoinValidator,
        private readonly WalletService $wallets,
    ) {}

    public function networkLabelFor(string $currency): string
    {
        return match ($this->normalizeCurrency($currency)) {
            self::CURRENCY_BTC => (string) config('coin.wallet.btc_payout_network', 'Bitcoin'),
            self::CURRENCY_USDT => (string) config('coin.wallet.payout_network', 'TRC-20'),
        };
    }

    /** @deprecated Use networkLabelFor('USDT') */
    public function networkLabel(): string
    {
        return $this->networkLabelFor(self::CURRENCY_USDT);
    }

    public function saveForUser(User $user, string $address, string $currency = self::CURRENCY_USDT): Wallet
    {
        $normalized = trim($address);
        $currency = $this->normalizeCurrency($currency);
        $this->assertValidOrFail($normalized, $currency);

        $wallet = $this->wallets->ensureWallet($user);

        if ($currency === self::CURRENCY_BTC) {
            $wallet->update([
                'btc_payout_address' => $normalized,
                'btc_network_label' => $this->networkLabelFor($currency),
            ]);
        } else {
            $wallet->update([
                'payout_address' => $normalized,
                'network_label' => $this->networkLabelFor($currency),
            ]);
        }

        return $wallet->fresh();
    }

    public function clearForUser(User $user, string $currency = self::CURRENCY_USDT): Wallet
    {
        $currency = $this->normalizeCurrency($currency);
        $wallet = $this->wallets->ensureWallet($user);

        if ($currency === self::CURRENCY_BTC) {
            $wallet->update([
                'btc_payout_address' => null,
                'btc_network_label' => null,
            ]);
        } else {
            $wallet->update([
                'payout_address' => null,
                'network_label' => null,
            ]);
        }

        return $wallet->fresh();
    }

    public function addressFor(?Wallet $wallet, string $currency): ?string
    {
        if ($wallet === null) {
            return null;
        }

        return $this->normalizeCurrency($currency) === self::CURRENCY_BTC
            ? $wallet->btc_payout_address
            : $wallet->payout_address;
    }

    /** @return array{address: string, network_label: string} */
    public function resolveFor(Wallet $wallet, string $currency): array
    {
        $currency = $this->normalizeCurrency($currency);
        $address = $this->addressFor($wallet, $currency);

        if (! is_string($address) || trim($address) === '' || $address === '—') {
            throw new RuntimeException($this->missingMessageFor($currency));
        }

        $this->assertValidOrFail($address, $currency);

        return [
            'address' => $address,
            'network_label' => $currency === self::CURRENCY_BTC
                ? ($wallet->btc_network_label ?: $this->networkLabelFor($currency))
                : ($wallet->network_label ?: $this->networkLabelFor($currency)),
        ];
    }

    public function assertWalletReady(?Wallet $wallet, string $currency = self::CURRENCY_USDT): void
    {
        $currency = $this->normalizeCurrency($currency);
        $address = $this->addressFor($wallet, $currency);

        if (! is_string($address) || trim($address) === '' || $address === '—') {
            throw new RuntimeException($this->missingMessageFor($currency));
        }

        $this->assertValidOrFail($address, $currency);
    }

    public function isValidForCurrency(?string $address, string $currency): bool
    {
        if (! is_string($address) || trim($address) === '') {
            return false;
        }

        return $this->normalizeCurrency($currency) === self::CURRENCY_BTC
            ? $this->bitcoinValidator->isValid($address)
            : $this->tronValidator->isValid($address);
    }

    private function assertValidOrFail(string $address, string $currency): void
    {
        if (! $this->isValidForCurrency($address, $currency)) {
            throw new RuntimeException($this->invalidMessageFor($currency));
        }
    }

    private function missingMessageFor(string $currency): string
    {
        return $currency === self::CURRENCY_BTC
            ? __('coin.wallet.btc_payout_address_missing')
            : __('coin.wallet.payout_address_missing');
    }

    private function invalidMessageFor(string $currency): string
    {
        return $currency === self::CURRENCY_BTC
            ? __('coin.wallet.btc_payout_address_invalid')
            : __('coin.wallet.payout_address_invalid');
    }

    private function normalizeCurrency(string $currency): string
    {
        $normalized = strtoupper(trim($currency));

        if (! in_array($normalized, [self::CURRENCY_USDT, self::CURRENCY_BTC], true)) {
            throw new RuntimeException("Unsupported payout currency: {$currency}");
        }

        return $normalized;
    }
}
