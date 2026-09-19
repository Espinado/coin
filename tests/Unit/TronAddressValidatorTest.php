<?php

namespace Tests\Unit;

use App\Support\TronAddressValidator;
use Tests\TestCase;

class TronAddressValidatorTest extends TestCase
{
    private TronAddressValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = app(TronAddressValidator::class);
    }

    public function test_accepts_known_valid_tron_mainnet_address(): void
    {
        $this->assertTrue($this->validator->isValid('TMYBKvQ5qFtp2xqiB8jEGy4vsUPmZ7c9GG'));
        $this->assertTrue($this->validator->isValid('TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t'));
    }

    public function test_rejects_truncated_evm_style_address(): void
    {
        $this->assertFalse($this->validator->isValid('0x7c4b912a9f8833e2d1b0c8a4f'));
    }

    public function test_rejects_address_with_invalid_checksum(): void
    {
        $this->assertFalse($this->validator->isValid('TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6x'));
    }

    public function test_rejects_empty_and_non_tron_prefix(): void
    {
        $this->assertFalse($this->validator->isValid(''));
        $this->assertFalse($this->validator->isValid('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa'));
    }
}
