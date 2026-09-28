<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validRegistrationPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+79001234567',
            'password' => 'password',
            'password_confirmation' => 'password',
            'accept_terms' => '1',
            'accept_privacy' => '1',
            'accept_risks' => '1',
        ], $overrides);
    }
}
