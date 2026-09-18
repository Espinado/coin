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

<div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1.4fr) minmax(0, 0.8fr); padding: 16px 0 12px; border-bottom: 1px solid rgba(150,235,250,0.1); font-family: 'JetBrains Mono', monospace; font-size: 9.5px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">
  <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'occurred_at', 'label' => mb_strtoupper(__('coin.table.date')), 'sortProperty' => $sortProperty, 'dirProperty' => $dirProperty, 'sortMethod' => $sortMethod])</span>
  <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'type', 'label' => mb_strtoupper(__('coin.table.type')), 'sortProperty' => $sortProperty, 'dirProperty' => $dirProperty, 'sortMethod' => $sortMethod])</span>
  <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'source', 'label' => mb_strtoupper(__('coin.table.source')), 'sortProperty' => $sortProperty, 'dirProperty' => $dirProperty, 'sortMethod' => $sortMethod])</span>
  <span>@include('livewire.partials.sortable-transaction-column', ['column' => 'amount', 'label' => mb_strtoupper(__('coin.table.amount')), 'sortProperty' => $sortProperty, 'dirProperty' => $dirProperty, 'sortMethod' => $sortMethod, 'align' => 'right'])</span>
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
  'perPageProperty' => $perPageProperty,
  'perPageOptions' => [10, 20, 50],
])
