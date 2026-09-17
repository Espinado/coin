<?php

use App\Models\LegalPage;
use App\Support\LegalFaq;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $page = LegalPage::query()->where('slug', LegalPage::SLUG_FAQ)->first();

        if ($page === null) {
            return;
        }

        $raw = trim((string) $page->body);

        if ($raw !== '' && str_starts_with($raw, '[')) {
            $items = LegalFaq::decode($raw);
        } else {
            $items = LegalFaq::defaultItems();
        }

        if ($items === []) {
            $items = LegalFaq::defaultItems();
        }

        $page->update([
            'body' => LegalFaq::encode($items),
        ]);
    }

    public function down(): void
    {
        $page = LegalPage::query()->where('slug', LegalPage::SLUG_FAQ)->first();

        if ($page === null) {
            return;
        }

        $page->update([
            'body' => LegalFaq::bodyAsPlainText(LegalFaq::decode($page->body)),
        ]);
    }
};
