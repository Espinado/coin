<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SiteVisitUnique;
use App\Support\AdminRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteVisitStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
            'app.key' => 'base64:'.base64_encode(str_repeat('a', 32)),
        ]);

        Cache::flush();
    }

    public function test_public_get_records_unique_ip_once_per_day(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10']);

        $this->get('http://coin.test/')
            ->assertOk();

        $this->assertDatabaseCount('site_visit_uniques', 1);

        $this->get('http://coin.test/')
            ->assertOk();

        $this->assertDatabaseCount('site_visit_uniques', 1);
        $this->assertGreaterThanOrEqual(1, (int) SiteVisitUnique::query()->value('hits'));
    }

    public function test_bots_and_seo_paths_are_not_recorded(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.20'])
            ->withHeaders(['User-Agent' => 'Googlebot/2.1'])
            ->get('http://coin.test/')
            ->assertOk();

        $this->assertDatabaseCount('site_visit_uniques', 0);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.21'])
            ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
            ->get('http://coin.test/robots.txt')
            ->assertOk();

        $this->assertDatabaseCount('site_visit_uniques', 0);
    }

    public function test_operator_can_view_visit_stats(): void
    {
        SiteVisitUnique::query()->create([
            'visit_date' => now()->toDateString(),
            'ip_hash' => hash('sha256', 'demo'),
            'hits' => 3,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);

        $admin = Admin::query()->create([
            'name' => 'Ops',
            'email' => 'ops-visits@coin.test',
            'password' => 'password',
            'role' => AdminRole::Operator,
        ]);

        $this->actingAs($admin, 'admin')
            ->get('http://admin.coin.test/visits')
            ->assertOk()
            ->assertSee(__('coin.admin.visits'));
    }

    public function test_viewer_cannot_access_visit_stats(): void
    {
        $viewer = Admin::query()->create([
            'name' => 'Viewer',
            'email' => 'viewer-visits@coin.test',
            'password' => 'password',
            'role' => AdminRole::Viewer,
        ]);

        $this->actingAs($viewer, 'admin')
            ->get('http://admin.coin.test/visits')
            ->assertForbidden();
    }
}
