<?php

namespace App\Support;

use App\Models\LegalPage;
use Illuminate\Support\Carbon;

final class PublicSeoUrls
{
    /**
     * Absolute public URLs that belong in sitemap / IndexNow.
     *
     * @return list<array{loc: string, lastmod: string, changefreq: string, priority: string}>
     */
    public static function sitemapEntries(): array
    {
        $urls = [
            [
                'loc' => route('home'),
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            [
                'loc' => route('seo.invest'),
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
        ];

        $pages = LegalPage::query()->published()->ordered()->get(['slug', 'updated_at']);

        foreach ($pages as $page) {
            // /invest is listed above; /legal/invest only redirects and must not appear twice.
            if ($page->isInvest()) {
                continue;
            }

            $urls[] = [
                'loc' => $page->publicUrl(),
                'lastmod' => optional($page->updated_at)->toAtomString() ?? Carbon::now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => $page->slug === LegalPage::SLUG_ABOUT ? '0.8' : '0.7',
            ];
        }

        return $urls;
    }

    /**
     * @return list<string>
     */
    public static function absoluteUrls(): array
    {
        return array_values(array_map(
            static fn (array $entry): string => $entry['loc'],
            self::sitemapEntries(),
        ));
    }
}
