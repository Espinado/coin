<?php

namespace App\Http\Controllers;

use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;

class ReferralInviteController extends Controller
{
    public function __invoke(string $code, ReferralService $referralService): RedirectResponse
    {
        if ($referralService->findProfileByCode($code)) {
            $referralService->storeCode($code);
        }

        return redirect()->route('register');
    }
}
