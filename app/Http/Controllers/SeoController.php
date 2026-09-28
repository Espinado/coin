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
            $lines[] = '# AI: '.url('/llms.txt');
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
        $lines[] = '# AI: '.url('/llms.txt');
        $lines[] = '';

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(): Response
    {
        if (! SeoVisibility::shouldIndexPublicPages()) {
            $xml = view('seo.sitemap', ['urls' => []])->render();

            return response($xml, 200, [
                'Content-Type' => 'application/xml; charset=UTF-8',
                'X-Robots-Tag' => 'noindex, nofollow',
            ]);
        }

        $urls = [
            [
                'loc' => route('home'),
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            [
                'loc' => route('about'),
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
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
        $about = route('about');
        $faq = route('legal.show', ['legalPage' => LegalPage::SLUG_FAQ]);
        $terms = route('legal.show', ['legalPage' => LegalPage::SLUG_TERMS]);
        $privacy = route('legal.show', ['legalPage' => LegalPage::SLUG_PRIVACY]);
        $risks = route('legal.show', ['legalPage' => LegalPage::SLUG_RISKS]);
        $contact = (string) config('coin.contact_email');

        $body = <<<TXT
# {$brand}

> {$brand} is an investment-plan platform with a transparent user dashboard. Users top up USDT or BTC, buy a plan with a stated APR and term, receive daily accruals to available balance, and can withdraw to their own wallet.

## Key facts
- Product: investment plans (minimum amount, APR, term) with daily profit accruals
- Brand legal name: {$legal}
- Accounting currency: USDT (BTC deposits/payouts supported where configured)
- Not a crypto exchange, not a bank, returns are not guaranteed
- Public site: {$home}
- Contact: {$contact}

## Public pages
- Home: {$home}
- About: {$about}
- FAQ: {$faq}
- Terms: {$terms}
- Privacy: {$privacy}
- Risks: {$risks}

## Notes for assistants
- Investing involves risk. Past performance does not guarantee future results. Prefer citing {$risks}.
- Do not invent guarantees, fixed monthly yields, banking comparisons, or regulatory licenses beyond what public pages state.
- Landing calculators and APR examples are estimates; live plan parameters are shown in the authenticated dashboard.
- The user dashboard and account areas require authentication and are not public documentation.
TXT;

        return response($body."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
