<?php

namespace App\Livewire;

use App\Models\WalletTransaction;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ProfitHistory extends Component
{
    use WithPagination;

    public function render(): View
    {
        return view('livewire.profit-history', [
            'transactions' => WalletTransaction::query()
                ->where('user_id', auth()->id())
                ->profitHistory()
                ->orderByDesc('occurred_at')
                ->orderByDesc('id')
                ->paginate(20),
        ])->layout('layouts.coin-dashboard', [
            'title' => 'Coin — '.__('coin.stats.profit_history'),
        ]);
    }
}
