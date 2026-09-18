<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use App\Models\Plan;
use App\Support\LandingPlans;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        /** @var Collection<int, Plan> $allActive */
        $allActive = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $landingPlans = $allActive->reject(fn (Plan $plan) => $plan->isEnterprise())->values();

        $faqPage = LegalPage::query()
            ->where('slug', LegalPage::SLUG_FAQ)
            ->published()
            ->first();

        return response()
            ->view('home', [
                'plans' => $landingPlans,
                'activePlanCount' => $allActive->count(),
                'landingPlansPayload' => LandingPlans::calculatorPayload($landingPlans),
                'legalPages' => LegalPage::query()->published()->ordered()->get(),
                'faqPage' => $faqPage,
                'faqItems' => $faqPage?->faqItems() ?? [],
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}
