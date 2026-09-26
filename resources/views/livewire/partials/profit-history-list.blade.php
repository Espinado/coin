@php
  $searchProperty = $searchProperty ?? 'profitSearch';
  $sortProperty = $sortProperty ?? 'profitSort';
  $dirProperty = $dirProperty ?? 'profitDir';
  $sortMethod = $sortMethod ?? 'sortProfit';
  $perPageProperty = $perPageProperty ?? 'profitPerPage';
  $pageName = $pageName ?? 'profitPage';
@endphp

<div style="display: flex; align-items: baseline; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
  <span style="font-size: 15px; font-weight: 600;">{{ __('coin.stats.full_history') }}</span>
  <span style="font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: rgba(214,238,248,0.66);">
    {{ __('coin.stats.total_entries', ['count' => $transactions->total()]) }} · {{ $wallet?->currency ?? config('coin.wallet.base_currency', 'USDT') }}
  </span>
</div>

@include('livewire.partials.transaction-list-toolbar', [
  'searchProperty' => $searchProperty,
  'placeholder' => __('coin.stats.search_profit_history'),
])

<div class="coin-data-list coin-data-list--profit">
  <div class="coin-data-list__head">
    <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'occurred_at', 'label' => mb_strtoupper(__('coin.table.date')), 'sortProperty' => $sortProperty, 'dirProperty' => $dirProperty, 'sortMethod' => $sortMethod])</span>
    <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'type', 'label' => mb_strtoupper(__('coin.table.type')), 'sortProperty' => $sortProperty, 'dirProperty' => $dirProperty, 'sortMethod' => $sortMethod])</span>
    <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'source', 'label' => mb_strtoupper(__('coin.table.source')), 'sortProperty' => $sortProperty, 'dirProperty' => $dirProperty, 'sortMethod' => $sortMethod])</span>
    <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'amount', 'label' => mb_strtoupper(__('coin.table.amount')), 'sortProperty' => $sortProperty, 'dirProperty' => $dirProperty, 'sortMethod' => $sortMethod, 'align' => 'right'])</span>
  </div>

  @forelse($transactions as $transaction)
  <article class="coin-data-list__row">
    <span style="color: rgba(214,238,248,0.78);">{{ $transaction->formattedOccurredAt() }}</span>
    <span style="color: rgba(214,238,248,0.78);">{{ $transaction->displayType() }}</span>
    <span style="font-family: 'JetBrains Mono', monospace; color: rgba(214,238,248,0.72);">{{ $transaction->displaySource() }}</span>
    <span style="font-family: 'JetBrains Mono', monospace; text-align: right; color: {{ $transaction->amountColor() }};">{{ $transaction->amount_label }}</span>
  </article>
  @empty
  <div style="padding: 24px 0; font-size: 13px; color: rgba(214,238,248,0.68);">{{ __('coin.stats.no_profit_yet') }}</div>
  @endforelse
</div>

@include('livewire.partials.coin-pagination', [
  'paginator' => $transactions,
  'perPageProperty' => $perPageProperty,
  'perPageOptions' => [10, 20, 50],
])
