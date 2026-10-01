<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\LegalPage;
use App\Services\PlatformSettingsService;
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
            ->assertSee('CudaFlops', false);
    }

    public function test_guest_cannot_view_unpublished_legal_page(): void
    {
        LegalPage::query()->where('slug', LegalPage::SLUG_PRIVACY)->update(['is_published' => false]);

        $this->get('http://coin.test/legal/privacy')->assertNotFound();
    }

    public function test_admin_can_update_faq_items_as_json(): void
    {
        $page = LegalPage::query()->where('slug', LegalPage::SLUG_FAQ)->firstOrFail();

        $response = $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/legal/'.$page->slug, [
                'title' => 'FAQ updated',
                'is_published' => '1',
                'faq_items' => [
                    ['question' => 'Test question?', 'answer' => 'Test answer.'],
                ],
            ]);

        $response->assertRedirect(route('admin.legal.index', absolute: false));

        $page->refresh();

        $this->assertSame('FAQ updated', $page->title);
        $this->assertSame([
            ['question' => 'Test question?', 'answer' => 'Test answer.'],
        ], $page->decodedFaqItems());
    }

    public function test_landing_renders_company_legal_info_in_footer(): void
    {
        app(PlatformSettingsService::class)->setLegalMany([
            'company_name' => 'CloudFlops LLC',
            'company_legal_address' => 'Rīga, Brīvības iela 1',
            'company_physical_address' => 'Rīga, Brīvības iela 1',
            'company_registration_number' => '40103123456',
            'company_license_number' => 'LV-12345',
            'company_phone' => '+371 20000000',
            'company_email' => 'legal@cloudflops.example',
        ]);

        $this->get('http://coin.test/')
            ->assertOk()
            ->assertSee('CloudFlops LLC', false)
            ->assertSee('40103123456', false)
            ->assertSee('LV-12345', false)
            ->assertSee('Rīga, Brīvības iela 1', false)
            ->assertSee('+371 20000000', false)
            ->assertSee('legal@cloudflops.example', false);
    }

    public function test_landing_renders_published_faq_items(): void
    {
        LegalPage::query()->where('slug', LegalPage::SLUG_FAQ)->update([
            'title' => 'Landing FAQ',
            'body' => json_encode([
                ['question' => 'Dynamic question?', 'answer' => 'Dynamic answer.'],
            ], JSON_UNESCAPED_UNICODE),
            'is_published' => true,
        ]);

        $this->get('http://coin.test/')
            ->assertOk()
            ->assertSee('Dynamic question?', false)
            ->assertSee('Dynamic answer.', false)
            ->assertSee('landing-faq', false);
    }

    public function test_admin_can_update_about_page(): void
    {
        $page = LegalPage::query()->where('slug', LegalPage::SLUG_ABOUT)->firstOrFail();

        $response = $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/legal/'.$page->slug, [
                'title' => 'About updated',
                'body' => 'Updated about body for CloudFlops.',
                'is_published' => '1',
            ]);

        $response->assertRedirect(route('admin.legal.index', absolute: false));

        $page->refresh();

        $this->assertSame('About updated', $page->title);
        $this->assertSame('Updated about body for CloudFlops.', $page->body);
        $this->assertTrue($page->is_published);

        $this->get('http://coin.test/legal/about')
            ->assertOk()
            ->assertSee('Updated about body for CloudFlops.', false);
    }

    public function test_admin_can_update_invest_page_and_it_appears_on_public_hub(): void
    {
        $page = LegalPage::query()->where('slug', LegalPage::SLUG_INVEST)->firstOrFail();

        $this->actingAs($this->admin, 'admin')
            ->patch('http://admin.coin.test/legal/'.$page->slug, [
                'title' => 'Invest hub updated',
                'body' => "Custom invest body.\n\nEdited from Legal CRUD.",
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.legal.index', absolute: false));

        $page->refresh();
        $this->assertSame('Invest hub updated', $page->title);
        $this->assertSame(route('seo.invest'), $page->publicUrl());

        $this->get('http://coin.test/invest')
            ->assertOk()
            ->assertSee('Invest hub updated', false)
            ->assertSee('Custom invest body.', false);
    }

    public function test_admin_legal_index_lists_about_and_invest(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('http://admin.coin.test/legal')
            ->assertOk()
            ->assertSee(__('coin.legal.slugs.about'), false)
            ->assertSee(__('coin.legal.slugs.invest'), false)
            ->assertSee('About CudaFlops', false)
            ->assertSee('AI infrastructure investment', false);
    }
}
