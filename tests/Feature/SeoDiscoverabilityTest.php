<?php

namespace Tests\Feature;

use App\Models\LegalPage;
use Database\Seeders\LegalPageSeeder;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoDiscoverabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.user_domain' => 'coin.test',
            'coin.admin_domain' => 'admin.coin.test',
            'coin.seo.indexable_hosts' => [],
        ]);

        $this->seed(PlatformSettingsSeeder::class);
        $this->seed(LegalPageSeeder::class);
    }

    public function test_staging_robots_disallows_all(): void
    {
        $this->get('http://coin.test/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('User-agent: *', false)
            ->assertSee('Disallow: /', false)
            ->assertDontSee('Sitemap:', false);
    }

    public function test_production_robots_disallows_private_paths_and_lists_sitemap(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        config([
            'coin.seo.indexable_hosts' => ['coin.test'],
        ]);

        $response = $this->get('http://coin.test/robots.txt');

        $response->assertOk()
            ->assertSee('Disallow: /dashboard', false)
            ->assertSee('Disallow: /login', false)
            ->assertSee('Disallow: /register', false)
            ->assertSee('Sitemap:', false);

        $this->assertStringNotContainsString("Disallow: /\n", $response->getContent());
    }

    public function test_home_includes_seo_meta_and_json_ld(): void
    {
        $this->get('http://coin.test/')
            ->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('name="robots" content="noindex, nofollow"', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"@type":"WebSite"', false);
    }

    public function test_faq_page_includes_faq_json_ld(): void
    {
        $page = LegalPage::query()->where('slug', LegalPage::SLUG_FAQ)->firstOrFail();

        $this->get('http://coin.test/legal/faq')
            ->assertOk()
            ->assertSee($page->title, false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('name="robots" content="noindex, nofollow"', false);
    }

    public function test_sitemap_lists_home_and_published_legal_pages(): void
    {
        $response = $this->get('http://coin.test/sitemap.xml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>'.route('home').'</loc>', false)
            ->assertSee('<loc>'.route('legal.show', ['legalPage' => 'terms']).'</loc>', false)
            ->assertSee('<loc>'.route('legal.show', ['legalPage' => 'faq']).'</loc>', false);
    }

    public function test_llms_txt_exposes_platform_facts(): void
    {
        $this->get('http://coin.test/llms.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('CloudFlops', false)
            ->assertSee('FAQ:', false)
            ->assertSee(route('home'), false);
    }

    public function test_login_page_is_noindex(): void
    {
        $this->get('http://coin.test/login')
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false);
    }
}
