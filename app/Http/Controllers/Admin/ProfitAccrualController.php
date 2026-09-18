<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Services\PlatformSettingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfitAccrualController extends Controller
{
    use AdminListQuery;

    public function index(Request $request, PlatformSettingsService $settings): View
    {
        $search = $this->adminSearchTerm($request);

        $query = WalletTransaction::query()
            ->with('user')
            ->where('type', 'Daily profit')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('source', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('account_slug', 'like', "%{$search}%"));
                });
            });

        $this->adminApplySort($request, $query, [
            'occurred_at' => 'occurred_at',
            'amount' => 'amount',
            'source' => 'source',
        ], 'occurred_at', 'desc', [
            'user' => fn ($transactionQuery, $direction) => $this->adminOrderByRelatedUser($transactionQuery, 'email', $direction),
        ]);

        return view('admin.profit-accrual.index', [
            'accruals' => $this->adminPaginate($query, $request),
            'currency' => $settings->tokenSymbol(),
            'profitAccrualTime' => $settings->profitAccrualTime(),
            'profitAccrualTimezone' => $settings->profitAccrualTimezone(),
            ...$this->adminListState($request),
        ]);
    }
}
