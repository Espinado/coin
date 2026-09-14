@extends('layouts.admin')

@section('title', 'Coin Admin — Deposit #'.$deposit->id)

@section('content')
    @include('admin.partials.nav')

    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:16px;align-items:start;">
        <div>
            <div class="admin-card">
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">DEPOSIT #{{ $deposit->id }}</div>
                <h1 style="margin:10px 0 0;font-size:22px;font-weight:600;">{{ $deposit->formattedAmount() }}</h1>
                <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.72);">{{ ucfirst($deposit->status) }} · {{ $deposit->created_at?->format('M j, Y H:i') }}</p>
                <div style="margin-top:16px;font-size:13px;line-height:1.7;">
                    <div><strong>Method:</strong> {{ $deposit->method }}</div>
                    @if($deposit->confirmed_at)<div><strong>Processed:</strong> {{ $deposit->confirmed_at->format('M j, Y H:i') }}</div>@endif
                </div>
            </div>

            @if($deposit->status === \App\Models\Deposit::STATUS_PENDING)
            <div class="admin-card" style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;">
                <form method="POST" action="{{ route('admin.deposits.confirm', $deposit) }}">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn-primary">Confirm & credit</button>
                </form>
                <form method="POST" action="{{ route('admin.deposits.reject', $deposit) }}">
                    @csrf
                    <button type="submit" class="admin-btn" style="border-color:rgba(255,143,143,0.45);color:#ff8f8f;">Reject</button>
                </form>
            </div>
            @endif
        </div>

        @include('admin.partials.user-context', ['user' => $deposit->user])
    </div>
@endsection
