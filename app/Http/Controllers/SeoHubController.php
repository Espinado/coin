<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use App\Support\SeoSchema;
use Illuminate\Http\Response;

class SeoHubController extends Controller
{
    public function invest(): Response
    {
        $page = LegalPage::query()
            ->where('slug', LegalPage::SLUG_INVEST)
            ->published()
            ->firstOrFail();

        return response()
            ->view('seo.invest', [
                'page' => $page,
                'jsonLd' => SeoSchema::investHub($page),
                'legalNav' => LegalPage::query()
                    ->published()
                    ->ordered()
                    ->whereIn('slug', LegalPage::legalRouteSlugs())
                    ->get(),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}
