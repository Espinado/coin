<?php

namespace App\Services\Payment;

/**
 * CCAPI IPN signatures use SHA-1 per gateway protocol (not chosen by this app).
 * Security relies on a secret API key, HTTPS, and webhook source IP allowlisting.
 */
class CcapiIpnVerifier
{
    public function verify(array $payload, string $apiKey): bool
    {
        $receivedSign = (string) ($payload['sign'] ?? '');

        if ($receivedSign === '') {
            return false;
        }

        return hash_equals($receivedSign, $this->sign($payload, $apiKey));
    }

    public function sign(array $payload, string $apiKey): string
    {
        unset($payload['sign']);

        ksort($payload, SORT_STRING);

        $values = [];

        foreach ($payload as $value) {
            $values[] = $this->stringifyValue($value);
        }

        $signData = implode(':', $values).':'.md5($apiKey);

        return sha1($signData);
    }

    private function stringifyValue(mixed $value): string
    {
        if (is_array($value)) {
            return 'Array';
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }
}
