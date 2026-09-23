<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Services\PlatformSettingsService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
        ]);

        $this->seed(PlatformSettingsSeeder::class);
    }

    public function test_user_site_returns_maintenance_page_when_enabled(): void
    {
        app(PlatformSettingsService::class)->setMany(['maintenance_mode' => true]);

        $this->get('http://coin.test/')
            ->assertServiceUnavailable()
            ->assertSee(__('coin.maintenance.title'))
            ->assertSee(__('coin.maintenance.body'));
    }

    public function test_user_site_works_when_maintenance_disabled(): void
    {
        app(PlatformSettingsService::class)->setMany(['maintenance_mode' => false]);

        $this->get('http://coin.test/')
            ->assertOk();
    }

    public function test_ccapi_webhook_stays_available_during_maintenance(): void
    {
        app(PlatformSettingsService::class)->setMany(['maintenance_mode' => true]);

        $this->postJson('http://coin.test/webhooks/ccapi', [])
            ->assertForbidden();
    }

    public function test_admin_site_stays_available_during_maintenance(): void
    {
        $this->seed(AdminSeeder::class);
        $admin = Admin::query()->firstOrFail();

        app(PlatformSettingsService::class)->setMany(['maintenance_mode' => true]);

        $this->actingAs($admin, 'admin')
            ->get('http://admin.coin.test/settings')
            ->assertOk();
    }
}
