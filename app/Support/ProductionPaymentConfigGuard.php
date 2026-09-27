<?php

namespace App\Support;

final class ProductionPaymentConfigGuard
{
    /**
     * True when this install must run live CCAPI payments (no mock).
     * Covers: APP_ENV production|staging, COIN_REQUIRE_LIVE_PAYMENTS,
     * and non-local COIN_USER_DOMAIN (e.g. coin.arguss.lv with APP_ENV=local).
     */
    public static function mustEnforceLivePayments(): bool
    {
        if (app()->environment(['production', 'staging'])) {
            return true;
        }

        // Escape hatch for rare local demos against a public-looking hosts entry.
        // Never overrides production/staging above.
        if ((bool) config('coin.payments.allow_mock', false)) {
            return false;
        }

        if ((bool) config('coin.payments.require_live', false)) {
            return true;
        }

        $userDomain = strtolower(trim((string) config('coin.user_domain', '')));

        if ($userDomain !== '' && ! self::isLocalDevHost($userDomain)) {
            return true;
        }

        return false;
    }

    public static function isLocalDevHost(string $host): bool
    {
        $host = strtolower(trim($host));

        if ($host === '' || $host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return true;
        }

        foreach (['.test', '.local', '.localhost', '.invalid'] as $suffix) {
            if (str_ends_with($host, $suffix)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    public static function violations(): array
    {
        if (! self::mustEnforceLivePayments()) {
            return [];
        }

        $errors = [];
        $scope = 'live payment hosts (production, staging, or non-local COIN_USER_DOMAIN)';

        if ((string) config('coin.payments.driver', 'mock') !== 'ccapi') {
            $errors[] = "COIN_PAYMENT_DRIVER must be ccapi on {$scope}.";
        }

        if (! filled((string) config('coin.payments.ccapi.api_key'))) {
            $errors[] = "CCAPI_API_KEY must be set on {$scope}.";
        }

        $webhookIps = config('coin.payments.ccapi.webhook_ips', []);

        if (! is_array($webhookIps) || $webhookIps === []) {
            $errors[] = "CCAPI_WEBHOOK_IPS must be set explicitly on {$scope}.";
        }

        $trustedProxies = config('coin.trusted_proxies', []);

        if (is_array($trustedProxies) && in_array('*', $trustedProxies, true)) {
            $errors[] = "COIN_TRUSTED_PROXIES must not be * on {$scope} (webhook IP checks can be bypassed).";
        }

        if (filter_var(config('coin.deposits.auto_confirm_mock', false), FILTER_VALIDATE_BOOL)) {
            $errors[] = "COIN_DEPOSITS_AUTO_CONFIRM_MOCK must be false on {$scope}.";
        }

        return $errors;
    }

    public static function assertValid(): void
    {
        if (self::shouldSkip()) {
            return;
        }

        $errors = self::violations();

        if ($errors === []) {
            return;
        }

        throw new \RuntimeException(
            'Live payment configuration is unsafe: '.implode(' ', $errors),
        );
    }

    private static function shouldSkip(): bool
    {
        if (! app()->runningInConsole()) {
            return false;
        }

        $command = $_SERVER['argv'][1] ?? '';

        if ($command === '') {
            return true;
        }

        $skipPrefixes = [
            'migrate',
            'db:',
            'key:',
            'about',
            'list',
            'help',
            'env',
            'tinker',
            'config:',
            'package:',
        ];

        foreach ($skipPrefixes as $prefix) {
            if ($command === $prefix || str_starts_with($command, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
