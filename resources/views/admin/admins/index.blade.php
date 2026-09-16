@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.admins.title')]))

@section('content')
    @include('admin.partials.nav')

    <div class="admin-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:16px;">
            <div>
                <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.admins.title') }}</h1>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.admins.sub') }}</p>
            </div>
            <a href="{{ route('admin.admins.invite') }}" class="admin-btn admin-btn-primary">{{ __('coin.admin.admins.invite') }}</a>
        </div>
        @include('admin.partials.list-toolbar', [
            'action' => route('admin.admins.index'),
            'search' => $search,
            'sort' => $sort,
            'dir' => $dir,
            'perPage' => $perPage,
            'searchPlaceholder' => __('coin.admin.admins.search_placeholder'),
        ])
    </div>

    @if($pendingInvitations->isNotEmpty())
        <div class="admin-card" style="margin-top:16px;padding:0;overflow:hidden;">
            <div style="padding:16px 18px;border-bottom:1px solid rgba(255,255,255,0.08);">
                <h2 style="margin:0;font-size:16px;font-weight:600;">{{ __('coin.admin.admins.pending_invitations') }}</h2>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                        <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.email')) }}</th>
                        <th style="padding:14px 18px;">{{ strtoupper(__('coin.auth.name')) }}</th>
                        <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.admins.invited_by')) }}</th>
                        <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.admins.expires_at')) }}</th>
                        <th style="padding:14px 18px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingInvitations as $invitation)
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                            <td style="padding:14px 18px;">
                                {{ $invitation->email }}
                                @if($invitation->isPasswordReset())
                                    <span style="margin-left:8px;font-family:'JetBrains Mono',monospace;font-size:10px;color:rgba(232,237,245,0.55);">{{ __('coin.admin.admins.reset_password') }}</span>
                                @endif
                            </td>
                            <td style="padding:14px 18px;">{{ $invitation->name ?? '—' }}</td>
                            <td style="padding:14px 18px;">{{ $invitation->invitedBy?->name ?? '—' }}</td>
                            <td style="padding:14px 18px;">{{ $invitation->expires_at->timezone(config('coin.profit_accrual.schedule_timezone', 'Europe/Riga'))->format('d.m.Y H:i') }}</td>
                            <td style="padding:14px 18px;text-align:right;white-space:nowrap;">
                                <form method="POST" action="{{ route('admin.admins.invitations.resend', $invitation) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="admin-btn">{{ __('coin.admin.admins.resend') }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.admins.invitations.destroy', $invitation) }}" style="display:inline;margin-left:8px;" onsubmit="return confirm(@json(__('coin.admin.admins.revoke_confirm')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn" style="border-color:rgba(255,143,143,0.35);color:#ff8f8f;">{{ __('coin.admin.admins.revoke') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="admin-card" style="margin-top:16px;padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                    @include('admin.partials.sortable-th', ['column' => 'name', 'label' => strtoupper(__('coin.auth.name')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'email', 'label' => strtoupper(__('coin.admin.email')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'created_at', 'label' => strtoupper(__('coin.admin.joined')), 'sort' => $sort, 'dir' => $dir])
                    <th style="padding:14px 18px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;">
                            {{ $admin->name }}
                            @if(auth('admin')->id() === $admin->id)
                                <span style="margin-left:8px;font-family:'JetBrains Mono',monospace;font-size:10px;color:#ffb454;">{{ __('coin.admin.admins.you') }}</span>
                            @endif
                        </td>
                        <td style="padding:14px 18px;">{{ $admin->email }}</td>
                        <td style="padding:14px 18px;">{{ $admin->created_at?->format('d.m.Y H:i') }}</td>
                        <td style="padding:14px 18px;text-align:right;white-space:nowrap;">
                            @if(auth('admin')->id() !== $admin->id)
                                <form method="POST" action="{{ route('admin.admins.reset-password', $admin) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="admin-btn">{{ __('coin.admin.admins.reset_password') }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}" style="display:inline;margin-left:8px;" onsubmit="return confirm(@json(__('coin.admin.admins.delete_confirm')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn" style="border-color:rgba(255,143,143,0.35);color:#ff8f8f;">{{ __('coin.delete') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="padding:24px 18px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.admins.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.partials.list-pagination', ['paginator' => $admins])
@endsection
