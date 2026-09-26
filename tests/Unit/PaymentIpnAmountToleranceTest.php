<?php

namespace Tests\Unit;

use App\Services\Payment\PaymentIpnService;
use Tests\TestCase;

class PaymentIpnAmountToleranceTest extends TestCase
{
    public function test_btc_deposit_accepts_small_wallet_rounding_within_default_tolerance(): void
    {
        config([
            'coin.payments.ccapi.amount_tolerance' => 0,
            'coin.payments.ccapi.amount_tolerance_btc' => 0.000005,
        ]);

        $this->assertTrue(PaymentIpnService::receivedAmountMatchesDepositAmount(
            0.00012042,
            0.00011901,
            currency: 'BTC',
        ));
    }

    public function test_btc_deposit_rejects_amount_outside_tolerance(): void
    {
        config([
            'coin.payments.ccapi.amount_tolerance' => 0,
            'coin.payments.ccapi.amount_tolerance_btc' => 0.000005,
        ]);

        $this->assertFalse(PaymentIpnService::receivedAmountMatchesDepositAmount(
            0.00013000,
            0.00011901,
            currency: 'BTC',
        ));
    }

    public function test_usdt_deposit_still_requires_exact_amount_by_default(): void
    {
        config([
            'coin.payments.ccapi.amount_tolerance' => 0,
            'coin.payments.ccapi.amount_tolerance_btc' => 0,
        ]);

        $this->assertFalse(PaymentIpnService::receivedAmountMatchesDepositAmount(
            10.01,
            10.00,
            currency: 'USDT',
        ));
    }
}
