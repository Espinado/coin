<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use App\Models\Plan;
use App\Services\LandingStatsService;
use App\Services\PlatformSettingsService;
use App\Support\LandingPlans;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    public function __invoke(PlatformSettingsService $settings, LandingStatsService $landingStats): Response
    {
        /** @var Collection<int, Plan> $allActive */
        $allActive = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $landingPlans = $allActive->reject(fn (Plan $plan) => $plan->isEnterprise())->values();
        $stats = $landingStats->forLanding($landingPlans, $allActive->count());

        $faqPage = LegalPage::query()
            ->where('slug', LegalPage::SLUG_FAQ)
            ->published()
            ->first();

        return response()
            ->view('home', [
                'plans' => $landingPlans,
                'activePlanCount' => $stats['active_plan_count'],
                'landingStats' => $stats,
                'landingPlansPayload' => LandingPlans::calculatorPayload($landingPlans),
                'legalPages' => LegalPage::query()->published()->ordered()->get(),
                'faqPage' => $faqPage,
                'faqItems' => $faqPage?->faqItems() ?? [],
                'companyLegal' => $settings->legalInfo(),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}
