<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\User;
use App\Services\PlatformSettingsService;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WithdrawalFeeDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['coin.user_domain' => 'coin.test']);

        $this->seed(PlatformSettingsSeeder::class);
    }

    public function test_wallet_shows_platform_fee_from_settings(): void
    {
        app(PlatformSettingsService::class)->setMany([
            'network_fee' => '1.25',
            'withdrawal_processing_hours' => '24',
        ]);

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('section', 4)
            ->assertSee('1,25 USDT')
            ->assertSee('~24 ч');
    }
}
