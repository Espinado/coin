<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RedirectsWithAdminFlash;
use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use App\Support\LegalFaq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    use RedirectsWithAdminFlash;

    public function index(): View
    {
        return view('admin.legal.index', [
            'pages' => LegalPage::query()->ordered()->get(),
        ]);
    }

    public function edit(LegalPage $legalPage): View
    {
        return view('admin.legal.form', [
            'page' => $legalPage,
        ]);
    }

    public function update(Request $request, LegalPage $legalPage): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['nullable', 'string', 'max:50000'],
            'is_published' => ['sometimes', 'boolean'],
            'faq_items' => ['nullable', 'array', 'max:30'],
            'faq_items.*.question' => ['nullable', 'string', 'max:500'],
            'faq_items.*.answer' => ['nullable', 'string', 'max:5000'],
        ]);

        $body = $validated['body'] ?? null;

        if ($legalPage->isFaq()) {
            $items = collect($validated['faq_items'] ?? [])
                ->map(static fn (array $item): array => [
                    'question' => trim((string) ($item['question'] ?? '')),
                    'answer' => trim((string) ($item['answer'] ?? '')),
                ])
                ->filter(static fn (array $item): bool => $item['question'] !== '' && $item['answer'] !== '')
                ->values()
                ->all();

            $body = $items === [] ? null : LegalFaq::encode($items);
        }

        $legalPage->update([
            'title' => $validated['title'],
            'body' => $body,
            'is_published' => $request->boolean('is_published'),
        ]);

        return $this->adminSuccess('coin.admin.flash.legal_saved', 'admin.legal.index');
    }
}
