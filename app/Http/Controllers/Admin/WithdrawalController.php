<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $withdrawals = Withdrawal::query()
            ->with(['user', 'processedByAdmin'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.withdrawals.index', [
            'withdrawals' => $withdrawals,
            'status' => $status,
            'statuses' => Withdrawal::statuses(),
        ]);
    }

    public function show(Withdrawal $withdrawal): View
    {
        $withdrawal->load(['user.wallet', 'user.contracts.plan', 'processedByAdmin']);

        return view('admin.withdrawals.show', [
            'withdrawal' => $withdrawal,
            'statuses' => Withdrawal::statuses(),
        ]);
    }

    public function updateStatus(Request $request, Withdrawal $withdrawal, WithdrawalService $withdrawals): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(Withdrawal::statuses()))],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $withdrawals->updateStatus(
            $withdrawal,
            $validated['status'],
            $request->user('admin'),
            $validated['admin_note'] ?? null,
        );

        return redirect()
            ->route('admin.withdrawals.show', $withdrawal)
            ->with('status', 'Payout status updated.');
    }
}
