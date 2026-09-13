@extends('layouts.admin')

@section('title', 'Coin Admin — Epoch #'.$epoch->number)

@section('content')
    @include('admin.partials.nav')

    <div class="admin-card">
        <h1 style="margin:0;font-size:24px;font-weight:600;">Epoch #{{ $epoch->number }}</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">
            {{ $epoch->contracts_settled }} contracts · {{ $epoch->formattedTotalRewards() }} COIN · rate {{ $epoch->reward_rate }} · {{ $epoch->completed_at?->format('M j, Y H:i') }}
        </p>
        @if($epoch->triggeredByAdmin)
            <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.62);">Triggered by {{ $epoch->triggeredByAdmin->name }}</p>
        @endif
    </div>

    <div class="admin-card" style="margin-top:16px;padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);color:rgba(232,237,245,0.62);font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.1em;">
                    <th style="padding:14px 18px;">USER</th>
                    <th style="padding:14px 18px;">CONTRACT</th>
                    <th style="padding:14px 18px;">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @forelse($epoch->rewards as $reward)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;"><a href="{{ route('admin.users.show', $reward->user) }}">{{ $reward->user->accountLabel() }}</a></td>
                        <td style="padding:14px 18px;">{{ $reward->contract?->code ?? '—' }}</td>
                        <td style="padding:14px 18px;font-family:'JetBrains Mono',monospace;">{{ number_format((float) $reward->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="padding:24px 18px;color:rgba(232,237,245,0.62);">No rewards in this epoch.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
