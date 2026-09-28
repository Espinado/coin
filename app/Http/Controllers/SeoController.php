<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use App\Support\PlatformBrand;
use App\Support\SeoVisibility;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
        ];

        if (! SeoVisibility::shouldIndexPublicPages()) {
            $lines[] = 'Disallow: /';
            $lines[] = '';

            return response(implode("\n", $lines)."\n", 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        $disallow = [
            '/dashboard',
            '/login',
            '/register',
            '/password',
            '/forgot-password',
            '/reset-password',
            '/verify-email',
            '/confirm-password',
            '/email',
            '/session-expired',
            '/profile',
            '/webhooks',
            '/guest',
            '/reverb-debug',
            '/r/',
        ];

        foreach ($disallow as $path) {
            $lines[] = 'Disallow: '.$path;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.url('/sitemap.xml');
        $lines[] = '';

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(): Response
    {
        $urls = [
            [
                'loc' => route('home'),
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
        ];

        $pages = LegalPage::query()->published()->ordered()->get(['slug', 'updated_at']);

        foreach ($pages as $page) {
            $urls[] = [
                'loc' => route('legal.show', $page),
                'lastmod' => optional($page->updated_at)->toAtomString() ?? Carbon::now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function llms(): Response
    {
        $brand = PlatformBrand::name();
        $legal = PlatformBrand::legalName();
        $home = route('home');
        $faq = route('legal.show', ['legalPage' => LegalPage::SLUG_FAQ]);
        $terms = route('legal.show', ['legalPage' => LegalPage::SLUG_TERMS]);
        $privacy = route('legal.show', ['legalPage' => LegalPage::SLUG_PRIVACY]);
        $risks = route('legal.show', ['legalPage' => LegalPage::SLUG_RISKS]);

        $body = <<<TXT
# {$brand}

> {$brand} is an AI compute investment platform. Users choose a plan, allocate data-center compute power, and track rewards in a personal dashboard.

## Key facts
- Product: AI compute plans with tracked accruals (no hardware to buy or maintain)
- Brand legal name: {$legal}
- Primary currency: USDT (also supports BTC deposits/payouts where configured)
- Public site: {$home}

## Public pages
- Home: {$home}
- FAQ: {$faq}
- Terms: {$terms}
- Privacy: {$privacy}
- Risks: {$risks}

## Notes for assistants
- Do not invent guarantees, APY figures, or regulatory status beyond what public pages state.
- The user dashboard and account areas require authentication and are not public documentation.
TXT;

        return response($body."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
