<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Admin\Concerns\RedirectsWithAdminFlash;
use App\Http\Controllers\Controller;
use App\Models\PlanChangeRequest;
use App\Services\PlanChangeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PlanChangeRequestController extends Controller
{
    use AdminListQuery;
    use RedirectsWithAdminFlash;

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = $this->adminSearchTerm($request);

        $query = PlanChangeRequest::query()
            ->with(['user', 'contract', 'fromPlan', 'toPlan'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('reference', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('account_slug', 'like', "%{$search}%"))
                        ->orWhereHas('contract', fn ($contractQuery) => $contractQuery
                            ->where('code', 'like', "%{$search}%"));
                });
            });

        $this->adminApplySort($request, $query, [
            'reference' => 'reference',
            'status' => 'status',
            'created_at' => 'created_at',
        ], 'created_at', 'desc', [
            'user' => fn ($requestQuery, $direction) => $this->adminOrderByRelatedUser($requestQuery, 'email', $direction),
        ]);

        return view('admin.plan-changes.index', [
            'requests' => $this->adminPaginate($query, $request),
            'statuses' => PlanChangeRequest::statuses(),
            ...$this->adminListState($request),
        ]);
    }

    public function show(PlanChangeRequest $planChange): View
    {
        $planChange->load(['user.wallet', 'user.contracts.plan', 'contract.plan', 'fromPlan', 'toPlan', 'processedByAdmin']);

        return view('admin.plan-changes.show', [
            'request' => $planChange,
        ]);
    }

    public function approve(Request $request, PlanChangeRequest $planChange, PlanChangeRequestService $service): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $service->approve($planChange, $request->user('admin'), $validated['admin_note'] ?? null);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.plan-changes.show', $planChange)
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        return $this->adminSuccess('coin.admin.plan_change_approved_flash', 'admin.plan-changes.index', [
            'user' => $planChange->fresh(['user'])->user?->name ?? '',
        ]);
    }

    public function reject(Request $request, PlanChangeRequest $planChange, PlanChangeRequestService $service): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $service->reject($planChange, $request->user('admin'), $validated['admin_note'] ?? null);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.plan-changes.show', $planChange)
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        return $this->adminSuccess('coin.admin.plan_change_rejected_flash', 'admin.plan-changes.index', [
            'user' => $planChange->fresh(['user'])->user?->name ?? '',
        ]);
    }
}
