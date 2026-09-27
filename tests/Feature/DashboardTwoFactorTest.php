<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_profile_shows_two_factor_as_required_without_toggle(): void
    {
        $user = User::factory()->create([
            'password' => 'SecretPass1!',
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('setSection', 6)
            ->assertSee(__('coin.profile.two_factor'), false)
            ->assertSee(mb_strtoupper(__('coin.profile.required')), false)
            ->assertDontSee(__('coin.profile.enable_two_factor'), false)
            ->assertDontSee(__('coin.profile.disable_two_factor'), false);

        $this->assertFalse(method_exists(Dashboard::class, 'enableEmailTwoFactor'));
        $this->assertFalse(method_exists(Dashboard::class, 'disableEmailTwoFactor'));
    }

    public function test_password_change_still_works_without_two_factor_toggle(): void
    {
        $user = User::factory()->create([
            'password' => 'SecretPass1!',
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('profileCurrentPassword', 'SecretPass1!')
            ->set('profileNewPassword', 'NewSecret2!')
            ->set('profileNewPasswordConfirmation', 'NewSecret2!')
            ->call('saveProfilePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('NewSecret2!', $user->fresh()->password));
    }

    public function test_user_can_update_display_name(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'phone' => '+37126161034',
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('profileName', 'New Display Name')
            ->set('profilePhone', '+37126161034')
            ->call('saveProfile')
            ->assertHasNoErrors()
            ->assertSet('profileName', 'New Display Name');

        $this->assertSame('New Display Name', $user->fresh()->name);
    }
}
