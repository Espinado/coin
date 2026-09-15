<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Admin\Concerns\RedirectsWithAdminFlash;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlanController extends Controller
{
    use AdminListQuery;
    use RedirectsWithAdminFlash;

    public function index(Request $request): View
    {
        $search = $this->adminSearchTerm($request);

        $query = Plan::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('tier_label', 'like', "%{$search}%");
                });
            });

        $this->adminApplySort($request, $query, [
            'name' => 'name',
            'min_deposit' => 'min_deposit',
            'annual_profit_percent' => 'annual_profit_percent',
            'duration_days' => 'duration_days',
            'visibility' => 'is_active',
            'sort_order' => 'sort_order',
        ], 'sort_order', 'asc');

        return view('admin.plans.index', [
            'plans' => $this->adminPaginate($query, $request),
            ...$this->adminListState($request),
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
        Plan::query()->create($this->validated($request));

        return $this->adminSuccess('coin.admin.flash.plan_created', 'admin.plans.index');
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

        return $this->adminSuccess('coin.admin.flash.plan_saved', 'admin.plans.index');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->contracts()->exists()) {
            return $this->adminError('coin.admin.flash.plan_delete_blocked', 'admin.plans.index');
        }

        $plan->delete();

        return $this->adminSuccess('coin.admin.flash.plan_deleted', 'admin.plans.index');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Plan $plan = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', 'unique:plans,slug,'.($plan?->id ?? 'NULL')],
            'tier_label' => ['nullable', 'string', 'max:80'],
            'price_label' => ['required', 'string', 'max:80'],
            'min_deposit' => ['nullable', 'numeric', 'min:0'],
            'price_amount' => ['nullable', 'numeric', 'min:0'],
            'annual_profit_percent' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
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
