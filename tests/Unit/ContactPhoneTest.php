<?php

namespace Tests\Unit;

use App\Rules\ContactPhone;
use Tests\TestCase;

class ContactPhoneTest extends TestCase
{
    public function test_accepts_russian_mobile_number(): void
    {
        $rule = new ContactPhone;
        $failed = false;

        $rule->validate('phone', '+7 900 123-45-67', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_rejects_too_short_number(): void
    {
        $rule = new ContactPhone;
        $failed = false;

        $rule->validate('phone', '12345', function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    public function test_normalize_trims_whitespace(): void
    {
        $this->assertSame('+79001234567', ContactPhone::normalize('  +79001234567  '));
    }
}
