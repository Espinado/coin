<?php

namespace Tests;

use App\Models\User;
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
            'phone_country' => 'RU',
            'phone_national' => '9001234567',
            'password' => 'password',
            'password_confirmation' => 'password',
            'accept_terms' => '1',
            'accept_privacy' => '1',
            'accept_risks' => '1',
        ], $overrides);
    }

    protected function approveKyc(User $user): User
    {
        $user->forceFill(['kyc_status' => User::KYC_APPROVED])->save();

        return $user->fresh();
    }
}
