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
        $name = PlatformBrand::name();
        $url = route('home');
        $logo = PlatformBrand::logoUrl('mark');

        return [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $name,
                'legalName' => PlatformBrand::legalName(),
                'url' => $url,
                'logo' => $logo,
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $name,
                'url' => $url,
            ],
        ];
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
