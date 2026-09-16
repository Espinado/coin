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

    public string $profitSearch = '';

    public string $profitSort = '';

    public string $profitDir = 'desc';

    public function updatedProfitPerPage(): void
    {
        $this->resetPage('profitPage');
    }

    public function updatedProfitSearch(): void
    {
        $this->resetPage('profitPage');
    }

    public function sortProfit(string $column): void
    {
        if ($this->profitSort === $column) {
            $this->profitDir = $this->profitDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->profitSort = $column;
            $this->profitDir = 'desc';
        }

        $this->resetPage('profitPage');
    }

    public function render(): View
    {
        return view('livewire.profit-history', [
            'transactions' => WalletTransaction::query()
                ->where('user_id', auth()->id())
                ->profitHistory()
                ->searchTerm($this->profitSearch)
                ->applyListSort($this->profitSort, $this->profitDir, 'occurred_at')
                ->paginate($this->pageSize(), pageName: 'profitPage'),
        ])->layout('layouts.coin-dashboard', [
            'title' => \App\Support\PlatformBrand::pageTitle(__('coin.stats.profit_history')),
        ]);
    }

    private function pageSize(): int
    {
        return in_array($this->profitPerPage, [10, 20, 50], true) ? $this->profitPerPage : 10;
    }
}
