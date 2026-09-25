<?php

namespace Tests\Unit;

use App\Support\CryptoAmountFormat;
use Tests\TestCase;

class CryptoAmountFormatTest extends TestCase
{
    public function test_btc_amounts_use_eight_decimals(): void
    {
        $this->assertSame('0.0001189', CryptoAmountFormat::formatPlain(0.0001189, 'BTC'));
        $this->assertSame('0.00000000', CryptoAmountFormat::placeholder('BTC'));
    }

    public function test_usdt_amounts_use_two_decimals(): void
    {
        $this->assertSame('10.05', CryptoAmountFormat::formatPlain(10.05, 'USDT'));
        $this->assertSame('0.00', CryptoAmountFormat::placeholder('USDT'));
    }
}
