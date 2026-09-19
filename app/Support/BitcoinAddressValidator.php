<?php

namespace App\Support;

use InvalidArgumentException;

class BitcoinAddressValidator
{
    private const BASE58_ALPHABET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    private const BECH32_CHARSET = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';

    public function isValid(?string $address): bool
    {
        if ($address === null || trim($address) === '') {
            return false;
        }

        $address = trim($address);

        if (str_starts_with(strtolower($address), 'bc1')) {
            return $this->isValidBech32($address, 'bc');
        }

        if (preg_match('/^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$/', $address) !== 1) {
            return false;
        }

        return $this->isValidLegacyBase58Check($address);
    }

    public function assertValid(string $address): void
    {
        if (! $this->isValid($address)) {
            throw new InvalidArgumentException(__('coin.wallet.btc_payout_address_invalid'));
        }
    }

    private function isValidLegacyBase58Check(string $address): bool
    {
        $decoded = $this->base58Decode($address);

        if ($decoded === null || strlen($decoded) !== 25) {
            return false;
        }

        $version = ord($decoded[0]);

        if (! in_array($version, [0x00, 0x05], true)) {
            return false;
        }

        $payload = substr($decoded, 0, 21);
        $checksum = substr($decoded, 21, 4);
        $hash = hash('sha256', hash('sha256', $payload, true), true);

        return substr($hash, 0, 4) === $checksum;
    }

    private function isValidBech32(string $address, string $expectedHrp): bool
    {
        if (strlen($address) < 14 || strlen($address) > 90) {
            return false;
        }

        if ($address !== strtolower($address) && $address !== strtoupper($address)) {
            return false;
        }

        $address = strtolower($address);
        $separator = strrpos($address, '1');

        if ($separator === false || $separator < 1 || $separator + 7 > strlen($address)) {
            return false;
        }

        $hrp = substr($address, 0, $separator);
        $dataPart = substr($address, $separator + 1);

        if ($hrp !== $expectedHrp) {
            return false;
        }

        if (! preg_match('/^[a-z0-9]+$/', $dataPart)) {
            return false;
        }

        $data = [];

        for ($i = 0, $length = strlen($dataPart); $i < $length; $i++) {
            $position = strpos(self::BECH32_CHARSET, $dataPart[$i]);

            if ($position === false) {
                return false;
            }

            $data[] = $position;
        }

        if (count($data) < 6) {
            return false;
        }

        $hrpExpanded = $this->bech32HrpExpand($hrp);

        return $this->bech32Polymod(array_merge($hrpExpanded, $data)) === 1;
    }

    /** @param  list<int>  $values */
    private function bech32HrpExpand(string $hrp): array
    {
        $expanded = [];

        for ($i = 0, $length = strlen($hrp); $i < $length; $i++) {
            $expanded[] = ord($hrp[$i]) >> 5;
        }

        $expanded[] = 0;

        for ($i = 0, $length = strlen($hrp); $i < $length; $i++) {
            $expanded[] = ord($hrp[$i]) & 31;
        }

        return $expanded;
    }

    /** @param  list<int>  $values */
    private function bech32Polymod(array $values): int
    {
        $generator = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];
        $chk = 1;

        foreach ($values as $value) {
            $top = $chk >> 25;
            $chk = (($chk & 0x1ffffff) << 5) ^ $value;

            for ($i = 0; $i < 5; $i++) {
                if ((($top >> $i) & 1) === 1) {
                    $chk ^= $generator[$i];
                }
            }
        }

        return $chk;
    }

    private function base58Decode(string $input): ?string
    {
        $bytes = [0];

        for ($i = 0, $length = strlen($input); $i < $length; $i++) {
            $position = strpos(self::BASE58_ALPHABET, $input[$i]);

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
