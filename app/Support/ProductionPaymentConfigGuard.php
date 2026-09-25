<?php

namespace App\Support;

use App\Services\PlatformSettingsService;
use Illuminate\Support\Facades\Schema;

final class ProductionPaymentConfigGuard
{
    /** @return list<string> */
    public static function violations(): array
    {
        if (! app()->environment(['production', 'staging'])) {
            return [];
        }

        $errors = [];

        if ((string) config('coin.payments.driver', 'mock') !== 'ccapi') {
            $errors[] = 'COIN_PAYMENT_DRIVER must be ccapi in production and staging.';
        }

        if (! filled((string) config('coin.payments.ccapi.api_key'))) {
            $errors[] = 'CCAPI_API_KEY must be set in production and staging.';
        }

        $webhookIps = config('coin.payments.ccapi.webhook_ips', []);

        if (! is_array($webhookIps) || $webhookIps === []) {
            $errors[] = 'CCAPI_WEBHOOK_IPS must be set explicitly in production and staging.';
        }

        $trustedProxies = config('coin.trusted_proxies', []);

        if (is_array($trustedProxies) && in_array('*', $trustedProxies, true)) {
            $errors[] = 'COIN_TRUSTED_PROXIES must not be * in production and staging (webhook IP checks can be bypassed).';
        }

        if (self::platformSettingsAvailable()) {
            $settings = app(PlatformSettingsService::class);

            if (! $settings->paymentGateEnabled()) {
                $errors[] = 'payment_gate_enabled must be enabled in production and staging platform settings.';
            }
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

    private static function platformSettingsAvailable(): bool
    {
        try {
            return Schema::hasTable('platform_settings');
        } catch (\Throwable) {
            return false;
        }
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
        ];

        foreach ($skipPrefixes as $prefix) {
            if ($command === $prefix || str_starts_with($command, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
