<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Services\PlatformSettingsService;
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
}
