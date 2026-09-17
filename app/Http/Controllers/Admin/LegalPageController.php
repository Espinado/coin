<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RedirectsWithAdminFlash;
use App\Http\Controllers\Controller;
use App\Models\LegalPage;
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
        ]);

        $legalPage->update([
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
            'is_published' => $request->boolean('is_published'),
        ]);

        return $this->adminSuccess('coin.admin.flash.legal_saved', 'admin.legal.index');
    }
}
