<?php

namespace App\Livewire;

use App\Models\WalletTransaction;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ProfitHistory extends Component
{
    use WithPagination;

    public int $profitPerPage = 10;

    public function updatedProfitPerPage(): void
    {
        $this->resetPage('profitPage');
    }

    public function render(): View
    {
        return view('livewire.profit-history', [
            'transactions' => WalletTransaction::query()
                ->where('user_id', auth()->id())
                ->profitHistory()
                ->orderByDesc('occurred_at')
                ->orderByDesc('id')
                ->paginate($this->pageSize(), pageName: 'profitPage'),
        ])->layout('layouts.coin-dashboard', [
            'title' => 'Coin — '.__('coin.stats.profit_history'),
        ]);
    }

    private function pageSize(): int
    {
        return in_array($this->profitPerPage, [10, 20, 50], true) ? $this->profitPerPage : 10;
    }
}
