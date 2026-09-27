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

        config([
            'coin.user_domain' => 'coin.example.com',
            'coin.payments.require_live' => false,
            'coin.payments.allow_mock' => false,
            'coin.deposits.auto_confirm_mock' => false,
        ]);
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

        $this->assertTrue(
            collect($violations)->contains(fn (string $v) => str_contains($v, 'COIN_PAYMENT_DRIVER must be ccapi')),
        );
        $this->assertTrue(
            collect($violations)->contains(fn (string $v) => str_contains($v, 'CCAPI_API_KEY must be set')),
        );
        $this->assertTrue(
            collect($violations)->contains(fn (string $v) => str_contains($v, 'CCAPI_WEBHOOK_IPS must be set')),
        );
    }

    public function test_production_rejects_wildcard_trusted_proxies(): void
    {
        config([
            'coin.payments.driver' => 'ccapi',
            'coin.payments.ccapi.api_key' => 'live-key',
            'coin.payments.ccapi.webhook_ips' => ['168.119.158.209'],
            'coin.trusted_proxies' => ['*'],
        ]);

        $violations = ProductionPaymentConfigGuard::violations();

        $this->assertTrue(
            collect($violations)->contains(fn (string $v) => str_contains($v, 'COIN_TRUSTED_PROXIES must not be *')),
        );
    }

    public function test_staging_requires_same_payment_config_as_production(): void
    {
        app()['env'] = 'staging';

        config([
            'coin.payments.driver' => 'mock',
            'coin.payments.ccapi.api_key' => '',
            'coin.payments.ccapi.webhook_ips' => [],
            'coin.trusted_proxies' => [],
        ]);

        $violations = ProductionPaymentConfigGuard::violations();

        $this->assertTrue(
            collect($violations)->contains(fn (string $v) => str_contains($v, 'COIN_PAYMENT_DRIVER must be ccapi')),
        );
        $this->assertTrue(
            collect($violations)->contains(fn (string $v) => str_contains($v, 'CCAPI_WEBHOOK_IPS must be set')),
        );
    }

    public function test_public_user_domain_enforces_live_even_when_app_env_is_local(): void
    {
        app()['env'] = 'local';

        config([
            'coin.user_domain' => 'coin.arguss.lv',
            'coin.payments.driver' => 'mock',
            'coin.payments.ccapi.api_key' => '',
            'coin.payments.ccapi.webhook_ips' => [],
            'coin.trusted_proxies' => [],
            'coin.payments.allow_mock' => false,
        ]);

        $this->assertTrue(ProductionPaymentConfigGuard::mustEnforceLivePayments());
        $this->assertTrue(
            collect(ProductionPaymentConfigGuard::violations())
                ->contains(fn (string $v) => str_contains($v, 'COIN_PAYMENT_DRIVER must be ccapi')),
        );
    }

    public function test_local_dev_domain_allows_mock_when_app_env_is_local(): void
    {
        app()['env'] = 'local';

        config([
            'coin.user_domain' => 'coin.test',
            'coin.payments.driver' => 'mock',
            'coin.payments.ccapi.api_key' => '',
            'coin.payments.ccapi.webhook_ips' => [],
            'coin.trusted_proxies' => [],
            'coin.payments.require_live' => false,
            'coin.payments.allow_mock' => false,
        ]);

        $this->assertFalse(ProductionPaymentConfigGuard::mustEnforceLivePayments());
        $this->assertSame([], ProductionPaymentConfigGuard::violations());
    }

    public function test_require_live_flag_forces_enforcement_on_local_domain(): void
    {
        app()['env'] = 'local';

        config([
            'coin.user_domain' => 'coin.test',
            'coin.payments.require_live' => true,
            'coin.payments.driver' => 'mock',
            'coin.payments.ccapi.api_key' => 'key',
            'coin.payments.ccapi.webhook_ips' => ['1.2.3.4'],
            'coin.trusted_proxies' => [],
        ]);

        $this->assertTrue(ProductionPaymentConfigGuard::mustEnforceLivePayments());
        $this->assertTrue(
            collect(ProductionPaymentConfigGuard::violations())
                ->contains(fn (string $v) => str_contains($v, 'COIN_PAYMENT_DRIVER must be ccapi')),
        );
    }

    public function test_auto_confirm_mock_is_forbidden_when_live_required(): void
    {
        config([
            'coin.payments.driver' => 'ccapi',
            'coin.payments.ccapi.api_key' => 'live-key',
            'coin.payments.ccapi.webhook_ips' => ['168.119.158.209'],
            'coin.trusted_proxies' => ['203.0.113.10'],
            'coin.deposits.auto_confirm_mock' => true,
        ]);

        $this->assertTrue(
            collect(ProductionPaymentConfigGuard::violations())
                ->contains(fn (string $v) => str_contains($v, 'COIN_DEPOSITS_AUTO_CONFIRM_MOCK must be false')),
        );
    }

    public function test_valid_production_payment_config_has_no_violations(): void
    {
        config([
            'coin.payments.driver' => 'ccapi',
            'coin.payments.ccapi.api_key' => 'live-key',
            'coin.payments.ccapi.webhook_ips' => ['168.119.158.209'],
            'coin.trusted_proxies' => ['203.0.113.10'],
            'coin.deposits.auto_confirm_mock' => false,
        ]);

        $this->assertSame([], ProductionPaymentConfigGuard::violations());
    }
}
