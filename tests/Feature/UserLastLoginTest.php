<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserLastLoginTest extends TestCase
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

    public function test_registration_does_not_record_last_login_until_email_is_verified(): void
    {
        $this->post('/register', [
            'name' => 'Evgen',
            'email' => 'evgenfit@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('verification.notice', absolute: false));

        $user = User::query()->where('email', 'evgenfit@gmail.com')->firstOrFail();
        $this->assertNull($user->last_login_at);

        $this->get(route('verification.notice', absolute: false))
            ->assertOk();

        $user->refresh();
        $this->assertNull($user->last_login_at);
    }

    public function test_backfill_uses_verified_email_timestamp(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now()->subDay(),
            'last_login_at' => null,
        ]);

        $updated = app(\App\Services\UserLoginRecorder::class)->backfillMissing();

        $user->refresh();

        $this->assertSame(1, $updated);
        $this->assertNotNull($user->last_login_at);
    }
}
