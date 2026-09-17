<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
        ]);

        $this->seed(PlanSeeder::class);
    }

    public function test_user_can_disable_email_two_factor_with_current_password(): void
    {
        $user = User::factory()->withEmailTwoFactor()->create([
            'password' => 'SecretPass1!',
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('profileTwoFactorPassword', 'SecretPass1!')
            ->call('disableEmailTwoFactor')
            ->assertHasNoErrors()
            ->assertSet('user.email_two_factor_enabled', false);

        $this->assertFalse($user->fresh()->hasEmailTwoFactorEnabled());
    }

    public function test_disable_email_two_factor_rejects_wrong_password(): void
    {
        $user = User::factory()->withEmailTwoFactor()->create([
            'password' => 'SecretPass1!',
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('profileTwoFactorPassword', 'WrongPass1!')
            ->call('disableEmailTwoFactor')
            ->assertHasErrors(['profileTwoFactorPassword' => __('coin.auth.login_password_invalid')]);

        $this->assertTrue($user->fresh()->hasEmailTwoFactorEnabled());
    }

    public function test_disable_email_two_factor_works_after_password_change_in_same_session(): void
    {
        $user = User::factory()->withEmailTwoFactor()->create([
            'password' => 'SecretPass1!',
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('profileCurrentPassword', 'SecretPass1!')
            ->set('profileNewPassword', 'NewSecret2!')
            ->set('profileNewPasswordConfirmation', 'NewSecret2!')
            ->call('saveProfilePassword')
            ->assertHasNoErrors()
            ->set('profileTwoFactorPassword', 'NewSecret2!')
            ->call('disableEmailTwoFactor')
            ->assertHasNoErrors();

        $this->assertFalse($user->fresh()->hasEmailTwoFactorEnabled());
    }
}
