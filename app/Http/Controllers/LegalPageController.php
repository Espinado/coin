<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function show(Request $request, LegalPage $legalPage): View
    {
        abort_unless($legalPage->is_published, 404);

        return view('legal.show', [
            'page' => $legalPage,
            'legalNav' => LegalPage::query()->published()->ordered()->get(),
        ]);
    }
}
