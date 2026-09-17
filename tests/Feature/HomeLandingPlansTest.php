<?php

namespace Tests\Feature;

use App\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeLandingPlansTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_active_plans_and_calculator_payload(): void
    {
        $this->seed(PlanSeeder::class);

        $response = $this->get('/');

        $response->assertOk();

        $core = Plan::query()->where('slug', 'core')->firstOrFail();

        $response->assertSee($core->displayName(), false);
        $response->assertSee('id="landing-plans-data"', false);
        $response->assertSee('"slug":"core"', false);
        $response->assertSee('data-landing-slider', false);
        $response->assertDontSee('placeholder', false);
    }
}
