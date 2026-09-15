@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.profit_accrual')]))

@section('content')
    @include('admin.partials.nav')

    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div class="admin-card">
        <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.profit_accrual') }}</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">
            {!! __('coin.admin.profit_accrual_sub', ['command' => '<code style="font-family:\'JetBrains Mono\',monospace;">coin:accrue-daily-profits</code>']) !!}
        </p>
        <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.58);">{{ __('coin.admin.profit_accrual_auto_only') }}</p>
    </div>

    <div class="admin-card" style="margin-top:16px;padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);color:rgba(232,237,245,0.62);font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.1em;">
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.when')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.user')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.source')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.amount')) }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAccruals as $tx)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;">{{ $tx->occurred_at?->format('M j, Y H:i') ?? $tx->occurred_label }}</td>
                        <td style="padding:14px 18px;"><a href="{{ route('admin.users.show', $tx->user) }}">{{ $tx->user?->accountLabel() }}</a></td>
                        <td style="padding:14px 18px;">{{ $tx->source }}</td>
                        <td style="padding:14px 18px;font-family:'JetBrains Mono',monospace;">{{ $tx->amount_label }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="padding:24px 18px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.no_accruals') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
