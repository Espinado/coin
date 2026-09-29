<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Admin\Concerns\RedirectsWithAdminFlash;
use App\Http\Controllers\Controller;
use App\Models\EarlyUnlockRequest;
use App\Services\EarlyUnlockRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class EarlyUnlockRequestController extends Controller
{
    use AdminListQuery;
    use RedirectsWithAdminFlash;

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = $this->adminSearchTerm($request);

        $query = EarlyUnlockRequest::query()
            ->with(['user', 'contract.plan'])
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
            'fee_amount' => 'fee_amount',
            'credit_amount' => 'credit_amount',
        ], 'created_at', 'desc', [
            'user' => fn ($requestQuery, $direction) => $this->adminOrderByRelatedUser($requestQuery, 'email', $direction),
        ]);

        return view('admin.early-unlocks.index', [
            'requests' => $this->adminPaginate($query, $request),
            'statuses' => EarlyUnlockRequest::statuses(),
            ...$this->adminListState($request),
        ]);
    }

    public function show(EarlyUnlockRequest $earlyUnlock): View
    {
        $earlyUnlock->load(['user.wallet', 'user.contracts.plan', 'contract.plan', 'processedByAdmin']);

        return view('admin.early-unlocks.show', [
            'request' => $earlyUnlock,
        ]);
    }

    public function approve(Request $request, EarlyUnlockRequest $earlyUnlock, EarlyUnlockRequestService $service): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $service->approve($earlyUnlock, $request->user('admin'), $validated['admin_note'] ?? null);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.early-unlocks.show', $earlyUnlock)
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        return $this->adminSuccess('coin.admin.early_unlock_approved_flash', 'admin.early-unlocks.index', [
            'user' => $earlyUnlock->fresh(['user'])->user?->name ?? '',
        ]);
    }

    public function reject(Request $request, EarlyUnlockRequest $earlyUnlock, EarlyUnlockRequestService $service): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $service->reject($earlyUnlock, $request->user('admin'), $validated['admin_note'] ?? null);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.early-unlocks.show', $earlyUnlock)
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        return $this->adminSuccess('coin.admin.early_unlock_rejected_flash', 'admin.early-unlocks.index', [
            'user' => $earlyUnlock->fresh(['user'])->user?->name ?? '',
        ]);
    }
}
