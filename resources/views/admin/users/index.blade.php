@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.users')]))

@section('content')
    @include('admin.partials.nav')

    <div class="admin-card">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.users') }}</h1>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.users_sub') }}</p>
            </div>
            <form method="GET" action="{{ route('admin.users.index') }}" style="display:flex;gap:8px;">
                <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('coin.admin.search_placeholder') }}"
                    style="padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;min-width:240px;">
                <button type="submit" class="admin-btn">{{ __('coin.admin.search') }}</button>
            </form>
        </div>
    </div>

    <div class="admin-card" style="margin-top:16px;padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);color:rgba(232,237,245,0.62);font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.1em;">
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.account')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.email')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.kyc')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.balance')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.deposits')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.joined')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.last_login')) }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;"><a href="{{ route('admin.users.show', $user) }}">{{ $user->accountLabel() }}</a></td>
                        <td style="padding:14px 18px;">{{ $user->email }}</td>
                        <td style="padding:14px 18px;">{{ $user->kycLabel() }}@if($user->is_blocked) · <span style="color:#ff8f8f;">{{ __('coin.admin.blocked') }}</span>@endif</td>
                        <td style="padding:14px 18px;font-family:'JetBrains Mono',monospace;">{{ $user->wallet?->formattedBalance() ?? '—' }}</td>
                        <td style="padding:14px 18px;">{{ $user->contracts_count }}</td>
                        <td style="padding:14px 18px;">{{ $user->created_at?->format('M j, Y') }}</td>
                        <td style="padding:14px 18px;">{{ $user->last_login_at?->format('M j, Y H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="padding:24px 18px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.no_users_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $users->links() }}</div>
@endsection
