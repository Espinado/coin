<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccessTest extends TestCase
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

    public function test_user_routes_are_not_available_on_admin_domain(): void
    {
        $this->get('http://admin.coin.test/login')
            ->assertOk()
            ->assertSee('Admin sign in');

        $this->get('http://admin.coin.test/dashboard')
            ->assertRedirect('http://admin.coin.test/login');

        $this->get('http://admin.coin.test/register')
            ->assertNotFound();
    }

    public function test_admin_routes_are_not_available_on_user_domain(): void
    {
        $this->get('http://coin.test/login')
            ->assertOk()
            ->assertSee('Sign in to your account');

        $this->get('http://coin.test/dashboard')
            ->assertRedirect('http://coin.test/login');

        $this->get('http://coin.test/register')
            ->assertOk();
    }

    public function test_regular_user_cannot_sign_in_to_admin_panel(): void
    {
        User::factory()->create([
            'email' => 'user@coin.test',
            'password' => Hash::make('password'),
        ]);

        $this->post('http://admin.coin.test/login', [
            'email' => 'user@coin.test',
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_can_sign_in_and_access_dashboard(): void
    {
        Admin::query()->create([
            'name' => 'Staff',
            'email' => 'staff@coin.test',
            'password' => Hash::make('secret1234'),
        ]);

        $this->post('http://admin.coin.test/login', [
            'email' => 'staff@coin.test',
            'password' => 'secret1234',
        ])->assertRedirect('http://admin.coin.test/dashboard');

        $this->get('http://admin.coin.test/dashboard')
            ->assertOk()
            ->assertSee('Admin console');
    }
}
