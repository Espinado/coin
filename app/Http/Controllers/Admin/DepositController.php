<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Services\DepositService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepositController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $deposits = Deposit::query()
            ->with(['user', 'confirmedBy'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.deposits.index', [
            'deposits' => $deposits,
            'status' => $status,
            'statuses' => [
                Deposit::STATUS_PENDING => 'Pending',
                Deposit::STATUS_CONFIRMED => 'Confirmed',
                Deposit::STATUS_REJECTED => 'Rejected',
            ],
        ]);
    }

    public function show(Deposit $deposit): View
    {
        $deposit->load(['user.wallet', 'confirmedBy']);

        return view('admin.deposits.show', [
            'deposit' => $deposit,
        ]);
    }

    public function confirm(Deposit $deposit, DepositService $deposits): RedirectResponse
    {
        $deposits->confirm($deposit, auth('admin')->user());

        return redirect()
            ->route('admin.deposits.show', $deposit)
            ->with('status', 'Deposit confirmed and credited.');
    }

    public function reject(Deposit $deposit, DepositService $deposits): RedirectResponse
    {
        $deposits->reject($deposit, auth('admin')->user());

        return redirect()
            ->route('admin.deposits.show', $deposit)
            ->with('status', 'Deposit rejected.');
    }
}
