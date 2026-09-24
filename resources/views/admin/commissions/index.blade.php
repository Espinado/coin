@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.commissions')]))

@section('content')
    @include('admin.partials.finance-tabs', ['active' => 'commissions'])

    <div class="admin-card">
        <div style="margin-bottom:16px;">
            <h2 style="margin:0;font-size:18px;font-weight:600;">{{ __('coin.admin.commissions') }}</h2>
            <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.commissions_sub') }}</p>
        </div>

        <div style="margin-bottom:18px;padding:16px 18px;border-radius:10px;background:rgba(150,235,250,0.06);border:1px solid rgba(150,235,250,0.14);">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.commissions_total')) }}</div>
            <div style="margin-top:8px;font-size:28px;font-weight:600;">{{ number_format($totalCommission, 2, '.', ',') }} {{ $currency }}</div>
        </div>

        <form method="GET" action="{{ route('admin.commissions.index') }}" class="admin-list-toolbar">
            <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('coin.admin.search_placeholder_commissions') }}">

            <select name="period">
                <option value="">{{ __('coin.admin.period_all') }}</option>
                <option value="today" @selected($period === 'today')>{{ __('coin.admin.period_today') }}</option>
                <option value="week" @selected($period === 'week')>{{ __('coin.admin.period_week') }}</option>
                <option value="month" @selected($period === 'month')>{{ __('coin.admin.period_month') }}</option>
                <option value="custom" @selected($period === 'custom')>{{ __('coin.admin.period_custom') }}</option>
            </select>

            <input type="date" name="from" value="{{ $from }}" aria-label="{{ __('coin.admin.period_from') }}">
            <input type="date" name="to" value="{{ $to }}" aria-label="{{ __('coin.admin.period_to') }}">

            @if($sort !== '')
                <input type="hidden" name="sort" value="{{ $sort }}">
            @endif
            @if($dir !== '')
                <input type="hidden" name="dir" value="{{ $dir }}">
            @endif

            <label>
                <span>{{ __('coin.pagination.per_page') }}</span>
                <select name="per_page">
                    @foreach([10, 20, 50, 100] as $option)
                        <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </label>

            <button type="submit" class="admin-btn">{{ __('coin.admin.apply') }}</button>
        </form>
    </div>

    <div class="admin-card admin-card--table" style="margin-top:16px;"><div class="admin-table-scroll">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                    @include('admin.partials.sortable-th', ['column' => 'processed_at', 'label' => strtoupper(__('coin.admin.time')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'reference', 'label' => strtoupper(__('coin.admin.reference')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'user', 'label' => strtoupper(__('coin.user')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'amount', 'label' => strtoupper(__('coin.admin.payout_amount')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'platform_fee', 'label' => strtoupper(__('coin.admin.commission_amount')), 'sort' => $sort, 'dir' => $dir])
                </tr>
            </thead>
            <tbody>
                @forelse($commissions as $withdrawal)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;white-space:nowrap;">{{ \App\Support\LocaleFormat::dateTimeLocal($withdrawal->processed_at) }}</td>
                        <td style="padding:14px 18px;"><a href="{{ route('admin.withdrawals.show', $withdrawal) }}">{{ $withdrawal->reference }}</a></td>
                        <td style="padding:14px 18px;">{{ $withdrawal->user?->email }}</td>
                        <td style="padding:14px 18px;">{{ $withdrawal->formattedAmount() }}</td>
                        <td style="padding:14px 18px;">{{ number_format((float) $withdrawal->platform_fee, 2, '.', ',') }} {{ $currency }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="padding:18px;color:rgba(232,237,245,0.65);">{{ __('coin.admin.no_commissions') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @include('admin.partials.list-pagination', ['paginator' => $commissions])
@endsection
