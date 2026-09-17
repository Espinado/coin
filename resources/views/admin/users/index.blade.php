@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.users')]))

@section('content')
    <div class="admin-card">
        <div style="margin-bottom:16px;">
            <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.users') }}</h1>
            <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.users_sub') }}</p>
        </div>
        @include('admin.partials.list-toolbar', [
            'action' => route('admin.users.index'),
            'search' => $search,
            'sort' => $sort,
            'dir' => $dir,
            'perPage' => $perPage,
        ])
    </div>

    <div class="admin-card admin-card--table" style="margin-top:16px;"><div class="admin-table-scroll">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                    @include('admin.partials.sortable-th', ['column' => 'account', 'label' => strtoupper(__('coin.admin.account')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'email', 'label' => strtoupper(__('coin.admin.email')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'kyc', 'label' => strtoupper(__('coin.admin.kyc')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'balance', 'label' => strtoupper(__('coin.balance')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'contracts', 'label' => strtoupper(__('coin.admin.deposits')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'created_at', 'label' => strtoupper(__('coin.admin.joined')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'last_login', 'label' => strtoupper(__('coin.admin.last_login')), 'sort' => $sort, 'dir' => $dir])
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
    </div>

    @include('admin.partials.list-pagination', ['paginator' => $users])
@endsection
