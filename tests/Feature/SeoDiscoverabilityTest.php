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
            ->assertSee('# AI:', false)
            ->assertSee('/llms.txt', false)
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
            ->assertSee('Sitemap:', false)
            ->assertSee('# AI:', false)
            ->assertSee('/llms.txt', false);

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
            ->assertSee('twitter:card" content="summary_large_image"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('Investment plans with daily APR accruals', false)
            ->assertDontSee('AI COMPUTE PLATFORM', false);
    }

    public function test_invest_hub_is_public_and_separate_from_gpu_marketplace_claims(): void
    {
        $this->get('http://coin.test/invest')
            ->assertOk()
            ->assertSee('AI infrastructure investment', false)
            ->assertSee('name="robots" content="noindex, nofollow"', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee('"@type":"WebPage"', false)
            ->assertSee('not a GPU cloud provider', false)
            ->assertDontSee('rent GPU servers', false);
    }

    public function test_unpublished_invest_hub_is_not_found(): void
    {
        LegalPage::query()->where('slug', LegalPage::SLUG_INVEST)->update(['is_published' => false]);

        $this->get('http://coin.test/invest')->assertNotFound();
    }

    public function test_about_page_is_public_and_describes_platform(): void
    {
        $this->get('http://coin.test/legal/about')
            ->assertOk()
            ->assertSee('investment-plan platform', false)
            ->assertSee('CudaFlops LLC', false)
            ->assertSee('name="robots" content="noindex, nofollow"', false)
            ->assertSee('"@type":"Organization"', false);
    }

    public function test_legacy_about_path_redirects_to_legal_about(): void
    {
        $this->get('http://coin.test/about')
            ->assertRedirect('/legal/about');
    }

    public function test_faq_page_includes_faq_json_ld(): void
    {
        $page = LegalPage::query()->where('slug', LegalPage::SLUG_FAQ)->firstOrFail();

        $this->get('http://coin.test/legal/faq')
            ->assertOk()
            ->assertSee($page->title, false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('Are returns guaranteed?', false)
            ->assertSee('name="robots" content="noindex, nofollow"', false);
    }

    public function test_staging_sitemap_is_empty(): void
    {
        $response = $this->get('http://coin.test/sitemap.xml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertDontSee('<loc>', false);
    }

    public function test_production_sitemap_lists_home_invest_and_legal_pages(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        config([
            'coin.seo.indexable_hosts' => ['coin.test'],
        ]);

        $response = $this->get('http://coin.test/sitemap.xml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>'.route('home').'</loc>', false)
            ->assertSee('<loc>'.route('seo.invest').'</loc>', false)
            ->assertSee('<loc>'.route('legal.show', ['legalPage' => 'about']).'</loc>', false)
            ->assertSee('<loc>'.route('legal.show', ['legalPage' => 'terms']).'</loc>', false)
            ->assertSee('<loc>'.route('legal.show', ['legalPage' => 'faq']).'</loc>', false);
    }

    public function test_llms_txt_exposes_platform_facts(): void
    {
        $this->get('http://coin.test/llms.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('CudaFlops', false)
            ->assertSee('investment-plan platform', false)
            ->assertSee('Investment overview:', false)
            ->assertSee(route('seo.invest'), false)
            ->assertSee('About:', false)
            ->assertSee('FAQ:', false)
            ->assertSee(route('home'), false)
            ->assertSee('Do not describe CudaFlops as a GPU cloud provider', false)
            ->assertDontSee('AI compute investment platform', false);
    }

    public function test_login_page_is_noindex(): void
    {
        $this->get('http://coin.test/login')
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false);
    }
}
