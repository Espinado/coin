<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use App\Support\LegalFaq;
use Illuminate\Database\Seeder;
use RuntimeException;

class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => LegalPage::SLUG_TERMS,
                'title' => 'Terms of Use',
                'body' => $this->legalBody('terms.en.txt'),
                'sort_order' => 10,
            ],
            [
                'slug' => LegalPage::SLUG_PRIVACY,
                'title' => 'Privacy Policy',
                'body' => $this->legalBody('privacy.en.txt'),
                'sort_order' => 20,
            ],
            [
                'slug' => LegalPage::SLUG_RISKS,
                'title' => 'Risk Disclosure',
                'body' => $this->legalBody('risks.en.txt'),
                'sort_order' => 30,
            ],
            [
                'slug' => LegalPage::SLUG_FAQ,
                'title' => 'Frequently asked questions',
                'body' => LegalFaq::encode(LegalFaq::defaultItems()),
                'sort_order' => 40,
            ],
        ];

        foreach ($pages as $page) {
            LegalPage::query()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'body' => $page['body'],
                    'is_published' => true,
                    'sort_order' => $page['sort_order'],
                ],
            );
        }
    }

    private function legalBody(string $filename): string
    {
        $path = database_path('seeders/data/legal/'.$filename);

        if (! is_file($path)) {
            throw new RuntimeException("Legal seed file missing: {$filename}");
        }

        $body = file_get_contents($path);

        if ($body === false || trim($body) === '') {
            throw new RuntimeException("Legal seed file empty: {$filename}");
        }

        return trim($body)."\n";
    }
}
