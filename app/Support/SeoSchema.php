<?php

namespace App\Support;

use App\Models\LegalPage;

final class SeoSchema
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function landing(): array
    {
        return [
            self::organization(),
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => PlatformBrand::name(),
                'url' => route('home'),
                'description' => __('coin.seo.meta_description', ['brand' => PlatformBrand::name()]),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function about(): array
    {
        return [self::organization()];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function investHub(?LegalPage $page = null): array
    {
        $brand = PlatformBrand::name();
        $title = $page?->title ?: __('coin.seo_hubs.invest.title');

        return [
            self::organization(),
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $title,
                'url' => route('seo.invest'),
                'description' => __('coin.seo_hubs.invest.meta_description', ['brand' => $brand]),
                'isPartOf' => [
                    '@type' => 'WebSite',
                    'name' => $brand,
                    'url' => route('home'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        $name = PlatformBrand::name();
        $url = route('home');
        $logo = PlatformBrand::logoUrl('mark');
        $email = PlatformBrand::contactEmail();
        $legalName = PlatformBrand::legalName();

        $org = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $name,
            'legalName' => $legalName,
            'url' => $url,
            'logo' => $logo,
            'description' => __('coin.seo.meta_description', ['brand' => $name]),
        ];

        if ($email !== '') {
            $org['email'] = $email;
        }

        return $org;
    }

    /**
     * @param  list<array{question: string, answer: string}>  $items
     * @return list<array<string, mixed>>
     */
    public static function faqPage(LegalPage $page, array $items): array
    {
        $entities = [];

        foreach ($items as $item) {
            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));

            if ($question === '' || $answer === '') {
                continue;
            }

            $entities[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $answer,
                ],
            ];
        }

        if ($entities === []) {
            return [];
        }

        return [
            [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $entities,
                'url' => route('legal.show', $page),
                'name' => $page->title,
            ],
        ];
    }

}
