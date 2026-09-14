@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.payout_detail', ['reference' => $withdrawal->reference])]))

@section('content')
    @include('admin.partials.nav')

    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:16px;align-items:start;">
        <div>
            <div class="admin-card">
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ $withdrawal->reference }}</div>
                <h1 style="margin:10px 0 0;font-size:22px;font-weight:600;">{{ $withdrawal->formattedAmount() }} COIN</h1>
                <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.72);">{{ $withdrawal->statusLabel() }} · {{ $withdrawal->created_at?->format('M j, Y H:i') }}</p>
                <div style="margin-top:16px;font-size:13px;line-height:1.7;">
                    <div><strong>{{ __('coin.admin.payout_address') }}:</strong> {{ $withdrawal->payout_address }}</div>
                    @if($withdrawal->network_label)<div><strong>{{ __('coin.admin.network') }}:</strong> {{ $withdrawal->network_label }}</div>@endif
                    @if($withdrawal->admin_note)<div style="margin-top:10px;"><strong>{{ __('coin.admin.admin_note') }}:</strong> {{ $withdrawal->admin_note }}</div>@endif
                </div>
            </div>

            <div class="admin-card" style="margin-top:16px;">
                <h2 style="margin:0 0 14px;font-size:16px;font-weight:600;">{{ __('coin.admin.update_status') }}</h2>
                <form method="POST" action="{{ route('admin.withdrawals.status', $withdrawal) }}" style="display:flex;flex-direction:column;gap:12px;">
                    @csrf
                    @method('PATCH')
                    <select name="status" style="padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($withdrawal->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <textarea name="admin_note" rows="3" placeholder="{{ __('coin.admin.audit_note_placeholder') }}"
                        style="width:100%;box-sizing:border-box;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">{{ old('admin_note', $withdrawal->admin_note) }}</textarea>
                    <button type="submit" class="admin-btn admin-btn-primary" style="align-self:flex-start;">{{ __('coin.admin.save_status') }}</button>
                </form>
            </div>
        </div>

        @include('admin.partials.user-context', ['user' => $withdrawal->user])
    </div>
@endsection
