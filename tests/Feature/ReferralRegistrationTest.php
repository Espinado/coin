<?php

namespace Tests\Feature;

use App\Models\ReferralProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
        ]);
    }

    public function test_referral_link_stores_code_and_redirects_to_register(): void
    {
        $referrer = User::factory()->create();
        ReferralProfile::query()->create([
            'user_id' => $referrer->id,
            'code' => 'COIN-REF01',
        ]);

        $response = $this->get('http://coin.test/r/COIN-REF01');

        $response->assertRedirect(route('register'));
        $response->assertCookie('coin_referral_code', 'COIN-REF01');
    }

    public function test_registration_via_referral_increments_level1_stats(): void
    {
        $referrer = User::factory()->create();
        $profile = ReferralProfile::query()->create([
            'user_id' => $referrer->id,
            'code' => 'COIN-REF02',
            'invited_count' => 0,
            'level1_users' => 0,
            'level2_users' => 0,
        ]);

        $response = $this
            ->withCookie('coin_referral_code', 'COIN-REF02')
            ->post('/register', [
                'name' => 'Invited User',
                'email' => 'invited@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertRedirect(route('verification.notice', absolute: false));

        $invited = User::query()->where('email', 'invited@example.com')->first();

        $this->assertNotNull($invited);
        $this->assertSame($referrer->id, $invited->referred_by_user_id);

        $profile->refresh();
        $this->assertSame(1, $profile->invited_count);
        $this->assertSame(1, $profile->level1_users);
        $this->assertSame(0, $profile->level2_users);

        $this->assertNotNull($invited->referralProfile);
        $this->assertNotSame('COIN-REF02', $invited->referralProfile->code);
    }

    public function test_invalid_referral_code_does_not_affect_registration(): void
    {
        $response = $this
            ->withCookie('coin_referral_code', 'COIN-NOPE')
            ->post('/register', [
                'name' => 'Solo User',
                'email' => 'solo@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertRedirect(route('verification.notice', absolute: false));

        $user = User::query()->where('email', 'solo@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->referred_by_user_id);
        $this->assertNotNull($user->referralProfile);
    }
}
