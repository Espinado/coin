<?php

namespace App\Http\Controllers;

use App\Support\SeoSchema;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SeoHubController extends Controller
{
    public function invest(): Response
    {
        return response()
            ->view('seo.invest', [
                'jsonLd' => SeoSchema::investHub(),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}
