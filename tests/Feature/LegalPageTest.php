<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\LegalPage;
use Database\Seeders\AdminSeeder;
use Database\Seeders\LegalPageSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPageTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
        ]);

        $this->seed(AdminSeeder::class);
        $this->seed(PlatformSettingsSeeder::class);
        $this->seed(LegalPageSeeder::class);

        $this->admin = Admin::query()->firstOrFail();
    }

    public function test_guest_can_view_published_legal_page(): void
    {
        $page = LegalPage::query()->where('slug', LegalPage::SLUG_TERMS)->firstOrFail();

        $this->get('http://coin.test/legal/terms')
            ->assertOk()
            ->assertSee($page->title, false)
            ->assertSee('CloudFlops', false);
    }

    public function test_guest_cannot_view_unpublished_legal_page(): void
    {
        LegalPage::query()->where('slug', LegalPage::SLUG_PRIVACY)->update(['is_published' => false]);

        $this->get('http://coin.test/legal/privacy')->assertNotFound();
    }

    public function test_admin_can_update_legal_page(): void
    {
        $page = LegalPage::query()->where('slug', LegalPage::SLUG_RISKS)->firstOrFail();

        $response = $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/legal/'.$page->slug, [
                'title' => 'Обновлённые риски',
                'body' => 'Новый текст раскрытия рисков.',
                'is_published' => '1',
            ]);

        $response->assertRedirect(route('admin.legal.index', absolute: false));

        $page->refresh();

        $this->assertSame('Обновлённые риски', $page->title);
        $this->assertSame('Новый текст раскрытия рисков.', $page->body);
        $this->assertTrue($page->is_published);
    }
}
