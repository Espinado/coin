<?php

namespace App\Http\Controllers;

use App\Services\PlatformSettingsService;
use Illuminate\Http\Response;

class AboutController extends Controller
{
    public function __invoke(PlatformSettingsService $settings): Response
    {
        return response()
            ->view('about', [
                'companyLegal' => $settings->legalInfo(),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}
