<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Plan;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardPlansTest extends TestCase
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

    public function test_dashboard_plans_section_renders_seeded_catalog(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('setSection', 1)
            ->assertSee('Node')
            ->assertSee('Core')
            ->assertSee('Cluster')
            ->assertSee('Enterprise')
            ->assertSee('3,400 USDT')
            ->assertSee('Dedicated pods');
    }

    public function test_dashboard_plans_reflect_admin_updates(): void
    {
        $user = User::factory()->create();
        $plan = Plan::query()->where('slug', 'node')->firstOrFail();
        $plan->update(['name' => 'Node Plus', 'price_label' => '299 USDT', 'min_deposit' => 299]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('setSection', 1)
            ->assertSee('Node Plus')
            ->assertSee('299 USDT')
            ->assertDontSee('250 USDT');
    }

    public function test_inactive_plans_are_hidden_from_dashboard(): void
    {
        $user = User::factory()->create();
        Plan::query()->where('slug', 'enterprise')->update(['is_active' => false]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('setSection', 1)
            ->assertDontSee('Enterprise');
    }
}
