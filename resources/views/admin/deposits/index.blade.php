@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.top_ups')]))

@section('content')
    @include('admin.partials.nav')

    <div class="admin-card">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.top_ups') }}</h1>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.top_ups_sub') }}</p>
            </div>
            <form method="GET" action="{{ route('admin.deposits.index') }}" style="display:flex;gap:8px;">
                <select name="status" style="padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    <option value="">{{ __('coin.admin.all_statuses') }}</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn">{{ __('coin.admin.filter') }}</button>
            </form>
        </div>
    </div>

    <div class="admin-card" style="margin-top:16px;padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);color:rgba(232,237,245,0.62);font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.1em;">
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.id')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.user')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.amount')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.status')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.requested')) }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deposits as $deposit)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;"><a href="{{ route('admin.deposits.show', $deposit) }}">#{{ $deposit->id }}</a></td>
                        <td style="padding:14px 18px;">{{ $deposit->user?->email }}</td>
                        <td style="padding:14px 18px;">{{ $deposit->formattedAmount() }}</td>
                        <td style="padding:14px 18px;">{{ ucfirst($deposit->status) }}</td>
                        <td style="padding:14px 18px;">{{ $deposit->created_at?->format('M j, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="padding:18px;color:rgba(232,237,245,0.65);">{{ __('coin.admin.no_top_ups') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $deposits->links() }}</div>
@endsection
