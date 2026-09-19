<?php

namespace App\Support;

use InvalidArgumentException;

class TronAddressValidator
{
    private const ALPHABET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    private const MAINNET_PREFIX = 0x41;

    private const ADDRESS_LENGTH = 34;

    public function isValid(?string $address): bool
    {
        if ($address === null || trim($address) === '') {
            return false;
        }

        $address = trim($address);

        if (strlen($address) !== self::ADDRESS_LENGTH || ! str_starts_with($address, 'T')) {
            return false;
        }

        if (! preg_match('/^T[1-9A-HJ-NP-Za-km-z]{33}$/', $address)) {
            return false;
        }

        $decoded = $this->base58Decode($address);

        if ($decoded === null || strlen($decoded) !== 25) {
            return false;
        }

        if (ord($decoded[0]) !== self::MAINNET_PREFIX) {
            return false;
        }

        $payload = substr($decoded, 0, 21);
        $checksum = substr($decoded, 21, 4);
        $hash = hash('sha256', hash('sha256', $payload, true), true);

        return substr($hash, 0, 4) === $checksum;
    }

    public function assertValid(string $address): void
    {
        if (! $this->isValid($address)) {
            throw new InvalidArgumentException(__('coin.wallet.payout_address_invalid'));
        }
    }

    private function base58Decode(string $input): ?string
    {
        $bytes = [0];

        for ($i = 0, $length = strlen($input); $i < $length; $i++) {
            $position = strpos(self::ALPHABET, $input[$i]);

            if ($position === false) {
                return null;
            }

            $carry = $position;

            for ($j = 0, $count = count($bytes); $j < $count; $j++) {
                $carry += $bytes[$j] * 58;
                $bytes[$j] = $carry & 0xff;
                $carry >>= 8;
            }

            while ($carry > 0) {
                $bytes[] = $carry & 0xff;
                $carry >>= 8;
            }
        }

        for ($i = 0, $length = strlen($input); $i < $length && $input[$i] === '1'; $i++) {
            array_unshift($bytes, 0);
        }

        return pack('C*', ...array_reverse($bytes));
    }
}
