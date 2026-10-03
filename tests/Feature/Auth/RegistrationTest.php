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

        $response->assertStatus(200)
            ->assertSee('name="accept_terms"', false)
            ->assertSee('name="accept_privacy"', false)
            ->assertSee('name="accept_risks"', false)
            ->assertSee(route('legal.show', ['legalPage' => 'terms']), false)
            ->assertSee(route('legal.show', ['legalPage' => 'privacy']), false)
            ->assertSee(route('legal.show', ['legalPage' => 'risks']), false);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', $this->validRegistrationPayload());

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice', absolute: false));
        $response->assertSessionHas('status', __('coin.auth.verify_email_registration_sent'));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'phone' => '+79001234567',
            'email_two_factor_enabled' => true,
        ]);
    }

    public function test_users_can_register_with_mixed_case_email(): void
    {
        $response = $this->post('/register', $this->validRegistrationPayload([
            'email' => 'MixedCase@Example.com',
        ]));

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'mixedcase@example.com',
        ]);
    }

    public function test_registration_rejects_duplicate_email_ignoring_case(): void
    {
        $this->post('/register', $this->validRegistrationPayload())
            ->assertRedirect(route('verification.notice', absolute: false));

        auth()->logout();

        $response = $this->post('/register', $this->validRegistrationPayload([
            'name' => 'Second User',
            'email' => 'TEST@example.com',
            'phone' => '+79007654321',
        ]));

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_new_users_cannot_access_dashboard_before_email_verification(): void
    {
        $this->post('/register', $this->validRegistrationPayload());

        $this->get(route('dashboard', absolute: false))
            ->assertRedirect(route('verification.notice', absolute: false));
    }

    public function test_registration_requires_phone(): void
    {
        $response = $this->post('/register', $this->validRegistrationPayload([
            'phone_national' => null,
        ]));

        $response->assertSessionHasErrors(['phone_national', 'phone']);
        $this->assertGuest();
    }

    public function test_registration_rejects_invalid_phone(): void
    {
        $response = $this->post('/register', $this->validRegistrationPayload([
            'phone_national' => '123',
        ]));

        $response->assertSessionHasErrors(['phone_national', 'phone']);
        $this->assertGuest();
    }

    public function test_registration_rejects_national_number_with_country_code(): void
    {
        $response = $this->from('/register')->post('/register', $this->validRegistrationPayload([
            'phone_country' => 'LV',
            'phone_national' => '37126161034',
        ]));

        $response->assertRedirect('/register')
            ->assertSessionHasErrors([
                'phone_national' => __('coin.auth.phone_national_no_country_code'),
            ]);
        $this->assertGuest();
    }

    public function test_registration_screen_shows_country_code_select(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('name="phone_country"', false)
            ->assertSee('name="phone_national"', false)
            ->assertSee('data-phone-country', false)
            ->assertSee('flagcdn.com/w40/lv.png', false)
            ->assertSee('+371', false)
            ->assertSee('Latvia', false);
    }

    public function test_registration_requires_legal_acceptances(): void
    {
        $response = $this->from('/register')->post('/register', $this->validRegistrationPayload([
            'accept_terms' => null,
            'accept_privacy' => null,
            'accept_risks' => null,
        ]));

        $response->assertRedirect('/register')
            ->assertSessionHasErrors([
                'accept_terms' => __('coin.auth.accept_terms_required'),
                'accept_privacy' => __('coin.auth.accept_privacy_required'),
                'accept_risks' => __('coin.auth.accept_risks_required'),
            ]);
        $this->assertGuest();

        $this->get('/register')
            ->assertOk()
            ->assertSee(__('coin.auth.accept_terms_required'), false)
            ->assertSee(__('coin.auth.accept_privacy_required'), false)
            ->assertSee(__('coin.auth.accept_risks_required'), false);
    }

    public function test_registration_rejects_disposable_email_domains(): void
    {
        $response = $this->from('/register')->post('/register', $this->validRegistrationPayload([
            'email' => 'bot@mx-mailsrv.com',
        ]));

        $response->assertRedirect('/register')
            ->assertSessionHasErrors([
                'email' => __('coin.auth.email_disposable'),
            ]);
        $this->assertGuest();
    }

    public function test_registration_rejects_honeypot_fill(): void
    {
        $response = $this->from('/register')->post('/register', $this->validRegistrationPayload([
            'website' => 'https://spam.example',
        ]));

        $response->assertRedirect('/register')
            ->assertSessionHasErrors('website');
        $this->assertGuest();
    }
}
