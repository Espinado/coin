<?php

namespace Tests\Unit;

use App\Support\ProductionPaymentConfigGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionPaymentConfigGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()['env'] = 'production';
    }

    public function test_production_requires_ccapi_driver_and_api_key(): void
    {
        config([
            'coin.payments.driver' => 'mock',
            'coin.payments.ccapi.api_key' => '',
            'coin.payments.ccapi.webhook_ips' => [],
            'coin.trusted_proxies' => [],
        ]);

        $violations = ProductionPaymentConfigGuard::violations();

        $this->assertContains('COIN_PAYMENT_DRIVER must be ccapi in production.', $violations);
        $this->assertContains('CCAPI_API_KEY must be set in production.', $violations);
        $this->assertContains('CCAPI_WEBHOOK_IPS must be set explicitly in production.', $violations);
    }

    public function test_production_rejects_wildcard_trusted_proxies(): void
    {
        config([
            'coin.payments.driver' => 'ccapi',
            'coin.payments.ccapi.api_key' => 'live-key',
            'coin.trusted_proxies' => ['*'],
        ]);

        $violations = ProductionPaymentConfigGuard::violations();

        $this->assertContains(
            'COIN_TRUSTED_PROXIES must not be * in production (webhook IP checks can be bypassed).',
            $violations,
        );
    }

    public function test_valid_production_payment_config_has_no_violations_when_gate_enabled(): void
    {
        config([
            'coin.payments.driver' => 'ccapi',
            'coin.payments.ccapi.api_key' => 'live-key',
            'coin.payments.ccapi.webhook_ips' => ['168.119.158.209'],
            'coin.trusted_proxies' => ['203.0.113.10'],
        ]);

        \App\Models\PlatformSetting::query()->updateOrCreate(
            ['key' => 'payment_gate_enabled'],
            ['value' => '1'],
        );

        $this->assertSame([], ProductionPaymentConfigGuard::violations());
    }
}
