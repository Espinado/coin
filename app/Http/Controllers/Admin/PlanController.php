<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        return view('admin.plans.index', [
            'plans' => Plan::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.plans.form', [
            'plan' => new Plan(['is_active' => true, 'is_featured' => false, 'sort_order' => 99]),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $plan = Plan::query()->create($this->validated($request));

        return redirect()
            ->route('admin.plans.edit', $plan)
            ->with('status', 'Plan created.');
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plans.form', [
            'plan' => $plan,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $plan->update($this->validated($request, $plan));

        return redirect()
            ->route('admin.plans.edit', $plan)
            ->with('status', 'Plan saved.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->contracts()->exists()) {
            return redirect()
                ->route('admin.plans.index')
                ->with('status', 'Cannot delete a plan with contracts. Deactivate it instead.');
        }

        $plan->delete();

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'Plan deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Plan $plan = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', 'unique:plans,slug,'.($plan?->id ?? 'NULL')],
            'tier_label' => ['nullable', 'string', 'max:80'],
            'price_label' => ['required', 'string', 'max:80'],
            'tflops' => ['required', 'integer', 'min:1'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'infra' => ['required', 'string', 'max:120'],
            'reward_multiplier' => ['required', 'numeric', 'min:0'],
            'daily_estimate' => ['nullable', 'numeric', 'min:0'],
            'max_tflops' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'capacity_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
