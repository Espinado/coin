@extends('layouts.admin')

@section('title', 'Coin Admin — Epochs')

@section('content')
    @include('admin.partials.nav')

    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div class="admin-card">
        <div style="margin-bottom:16px;">
            <h1 style="margin:0;font-size:24px;font-weight:600;">Epoch engine</h1>
            <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">
                Current epoch {{ $currentEpoch ?: '—' }} · next #{{ $nextEpoch }} · rate {{ $settings['reward_rate'] }} · {{ $settings['epochs_per_day'] }}/day
            </p>
            <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.58);">Manual settlement is disabled. Daily profit runs via cron only.</p>
        </div>
        @include('admin.partials.list-toolbar', [
            'action' => route('admin.epochs.index'),
            'search' => $search,
            'sort' => $sort,
            'dir' => $dir,
            'perPage' => $perPage,
            'showSearch' => false,
        ])
    </div>

    <div class="admin-card admin-card--table" style="margin-top:16px;"><div class="admin-table-scroll">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                    @include('admin.partials.sortable-th', ['column' => 'number', 'label' => 'EPOCH', 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'contracts_settled', 'label' => 'CONTRACTS', 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'total_rewards', 'label' => 'TOTAL REWARDS', 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'reward_rate', 'label' => 'RATE', 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'completed_at', 'label' => 'COMPLETED', 'sort' => $sort, 'dir' => $dir])
                </tr>
            </thead>
            <tbody>
                @forelse($epochs as $epoch)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;"><a href="{{ route('admin.epochs.show', $epoch) }}">#{{ $epoch->number }}</a></td>
                        <td style="padding:14px 18px;">{{ $epoch->contracts_settled }}</td>
                        <td style="padding:14px 18px;font-family:'JetBrains Mono',monospace;">{{ $epoch->formattedTotalRewards() }} {{ $settings['token_symbol'] }}</td>
                        <td style="padding:14px 18px;">{{ $epoch->reward_rate }}</td>
                        <td style="padding:14px 18px;">{{ $epoch->completed_at?->format('M j, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="padding:24px 18px;color:rgba(232,237,245,0.62);">No settlements yet. Run the first epoch.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @include('admin.partials.list-pagination', ['paginator' => $epochs])
@endsection
