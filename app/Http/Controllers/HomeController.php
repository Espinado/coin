<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use App\Models\Plan;
use App\Support\LandingPlans;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        /** @var Collection<int, Plan> $allActive */
        $allActive = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $landingPlans = $allActive->reject(fn (Plan $plan) => $plan->isEnterprise())->values();

        return view('home', [
            'plans' => $landingPlans,
            'activePlanCount' => $allActive->count(),
            'landingPlansPayload' => LandingPlans::calculatorPayload($landingPlans),
            'legalPages' => LegalPage::query()->published()->ordered()->get(),
        ]);
    }
}
