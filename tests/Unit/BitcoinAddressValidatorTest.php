<?php

namespace Tests\Unit;

use App\Support\BitcoinAddressValidator;
use Tests\TestCase;

class BitcoinAddressValidatorTest extends TestCase
{
    private BitcoinAddressValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = app(BitcoinAddressValidator::class);
    }

    public function test_accepts_known_valid_bech32_address(): void
    {
        $this->assertTrue($this->validator->isValid('bc1qqhza20mal9tdar863pzrlpjgfx6kdhyfssccpf'));
    }

    public function test_rejects_tron_address_for_bitcoin_wallet(): void
    {
        $this->assertFalse($this->validator->isValid('TMYBKvQ5qFtp2xqiB8jEGy4vsUPmZ7c9GG'));
    }

    public function test_rejects_truncated_evm_style_address(): void
    {
        $this->assertFalse($this->validator->isValid('0x7c4b912a9f8833e2d1b0c8a4f'));
    }

    public function test_rejects_invalid_bech32_checksum(): void
    {
        $this->assertFalse($this->validator->isValid('bc1qqhza20mal9tdar863pzrlpjgfx6kdhyfssccpg'));
    }

    public function test_rejects_empty_address(): void
    {
        $this->assertFalse($this->validator->isValid(''));
        $this->assertFalse($this->validator->isValid(null));
    }
}
