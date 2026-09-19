<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice', absolute: false));
        $response->assertSessionHas('status', __('coin.auth.verify_email_registration_sent'));
    }

    public function test_users_can_register_with_mixed_case_email(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'MixedCase@Example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'mixedcase@example.com',
        ]);
    }

    public function test_registration_rejects_duplicate_email_ignoring_case(): void
    {
        $this->post('/register', [
            'name' => 'First User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('verification.notice', absolute: false));

        auth()->logout();

        $response = $this->post('/register', [
            'name' => 'Second User',
            'email' => 'TEST@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_new_users_cannot_access_dashboard_before_email_verification(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->get(route('dashboard', absolute: false))
            ->assertRedirect(route('verification.notice', absolute: false));
    }
}
