<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Support\SessionIdleTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SessionIdleTimeoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
            'coin.session.idle_minutes' => 15,
        ]);
    }

    public function test_idle_user_is_logged_out_on_next_request(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession([
                SessionIdleTracker::SESSION_KEY => now()->subMinutes(20)->toIso8601String(),
            ])
            ->get('http://coin.test/dashboard')
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', __('coin.auth.idle_logout', ['minutes' => 15]));

        $this->assertGuest();
    }

    public function test_active_user_session_is_preserved_on_page_load(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession([
                SessionIdleTracker::SESSION_KEY => now()->subMinutes(5)->toIso8601String(),
            ])
            ->get('http://coin.test/dashboard')
            ->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_idle_admin_is_logged_out_on_next_request(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Staff',
            'email' => 'staff@coin.test',
            'password' => Hash::make('secret1234'),
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession([
                SessionIdleTracker::SESSION_KEY => now()->subMinutes(20)->toIso8601String(),
            ])
            ->get('http://admin.coin.test/dashboard')
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('status', __('coin.auth.idle_logout', ['minutes' => 15]));

        $this->assertGuest('admin');
    }
}
