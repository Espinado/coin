<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Services\PlatformSettingsService;
use App\Services\ProfitAccrualService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfitAccrualController extends Controller
{
    public function index(PlatformSettingsService $settings): View
    {
        return view('admin.profit-accrual.index', [
            'recentAccruals' => WalletTransaction::query()
                ->with('user')
                ->where('type', 'Daily profit')
                ->orderByDesc('occurred_at')
                ->orderByDesc('id')
                ->limit(30)
                ->get(),
            'currency' => $settings->tokenSymbol(),
        ]);
    }

    public function run(ProfitAccrualService $accrual): RedirectResponse
    {
        $result = $accrual->accrueDaily(auth('admin')->user());

        return redirect()
            ->route('admin.profit-accrual.index')
            ->with('status', sprintf(
                'Daily accrual completed: %d contract(s), %s total profit, %d matured.',
                $result['contracts_processed'],
                number_format($result['total_profit'], 2, '.', ''),
                $result['contracts_matured'],
            ));
    }
}
