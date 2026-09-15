@php
    $user = auth()->user();
    $wallet = $user->wallet;
@endphp
<div class="coin-dashboard" style="display: flex; min-height: 100vh; width: 100%; max-width: 1440px; margin: 0 auto; background: #061423; color: #e6f4fa; font-family: 'Sora', 'Helvetica Neue', Helvetica, sans-serif;">
  <main style="flex: 1; min-width: 0; display: flex; flex-direction: column;">
    <header class="coin-dash-header" style="display: flex; align-items: center; gap: 20px; padding: 20px 32px; border-bottom: 1px solid rgba(150,235,250,0.1); background: rgba(4,16,28,0.4);">
      <a href="{{ route('dashboard', ['section' => 3]) }}" wire:navigate style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 14px; border-radius: 10px; border: 1px solid rgba(150,235,250,0.2); background: rgba(150,235,250,0.06); color: #e6f4fa; font-size: 13px; text-decoration: none;">
        ← {{ __('coin.stats.back_to_stats') }}
      </a>
      <div style="min-width: 0; flex: 1;">
        <div style="font-size: 20px; font-weight: 600; letter-spacing: -0.02em;">{{ __('coin.stats.profit_history') }}</div>
        <div style="margin-top: 4px; font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ __('coin.stats.profit_history_sub') }}</div>
      </div>
      <div style="display: flex; align-items: center; gap: 10px; padding: 6px 12px 6px 6px; border-radius: 999px; border: 1px solid rgba(150,235,250,0.14); background: rgba(150,235,250,0.04);">
        <span style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(145deg, oklch(0.7 0.13 198), oklch(0.5 0.15 285)); display: grid; place-items: center; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #04121f;">{{ $user->avatarInitial() }}</span>
        <span style="font-size: 13px;">{{ $user->accountLabel() }}</span>
      </div>
    </header>

    <section style="padding: 28px 32px 40px; display: flex; flex-direction: column; gap: 16px;">
      <div style="padding: 24px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
        <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
          <span style="font-size: 15px; font-weight: 600;">{{ __('coin.stats.full_history') }}</span>
          <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.66);">
            {{ __('coin.stats.total_entries', ['count' => $transactions->total()]) }} · {{ $wallet?->currency ?? 'USDT' }}
          </span>
        </div>

        @include('livewire.partials.transaction-list-toolbar', [
          'searchProperty' => 'profitSearch',
          'placeholder' => __('coin.stats.search_profit_history'),
        ])

        <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1.4fr) minmax(0, 0.8fr); padding: 16px 0 12px; border-bottom: 1px solid rgba(150,235,250,0.1); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">
          <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'occurred_at', 'label' => mb_strtoupper(__('coin.table.date')), 'sortProperty' => 'profitSort', 'dirProperty' => 'profitDir', 'sortMethod' => 'sortProfit'])</span>
          <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'type', 'label' => mb_strtoupper(__('coin.table.type')), 'sortProperty' => 'profitSort', 'dirProperty' => 'profitDir', 'sortMethod' => 'sortProfit'])</span>
          <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'source', 'label' => mb_strtoupper(__('coin.table.source')), 'sortProperty' => 'profitSort', 'dirProperty' => 'profitDir', 'sortMethod' => 'sortProfit'])</span>
          <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'amount', 'label' => mb_strtoupper(__('coin.table.amount')), 'sortProperty' => 'profitSort', 'dirProperty' => 'profitDir', 'sortMethod' => 'sortProfit', 'align' => 'right'])</span>
        </div>

        @forelse($transactions as $transaction)
        <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1.4fr) minmax(0, 0.8fr); padding: 13px 0;@if(!$loop->last) border-bottom: 1px solid rgba(150,235,250,0.07);@endif font-size: 13px; align-items: center;">
          <span style="color: rgba(214,238,248,0.78);">{{ $transaction->formattedOccurredAt() }}</span>
          <span style="color: rgba(214,238,248,0.78);">{{ $transaction->displayType() }}</span>
          <span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.72);">{{ $transaction->displaySource() }}</span>
          <span style="font-family: 'JetBrains Mono', monospace; text-align: right; color: {{ $transaction->amountColor() }};">{{ $transaction->amount_label }}</span>
        </div>
        @empty
        <div style="padding: 24px 0; font-size: 13px; color: rgba(214,238,248,0.68);">{{ __('coin.stats.no_profit_yet') }}</div>
        @endforelse

        @include('livewire.partials.coin-pagination', [
          'paginator' => $transactions,
          'perPageProperty' => 'profitPerPage',
          'perPageOptions' => [10, 20, 50],
        ])
      </div>
    </section>
  </main>
</div>
