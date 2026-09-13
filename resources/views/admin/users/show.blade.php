@extends('layouts.admin')

@section('title', 'Coin Admin — '.$user->accountLabel())

@section('content')
    @include('admin.partials.nav')

    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:16px;align-items:start;">
        <div>
            <div class="admin-card">
                <h1 style="margin:0;font-size:24px;font-weight:600;">{{ $user->name }}</h1>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ $user->email }} · {{ $user->accountLabel() }}</p>
            </div>

            <div class="admin-card" style="margin-top:16px;">
                <h2 style="margin:0 0 16px;font-size:16px;font-weight:600;">Account controls</h2>
                <form method="POST" action="{{ route('admin.users.update', $user) }}" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">KYC STATUS</label>
                        <select name="kyc_status" style="width:100%;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                            @foreach($kycStatuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('kyc_status', $user->kyc_status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">BLOCKED</label>
                        <label style="display:flex;align-items:center;gap:8px;margin-top:12px;font-size:13px;">
                            <input type="hidden" name="is_blocked" value="0">
                            <input type="checkbox" name="is_blocked" value="1" @checked(old('is_blocked', $user->is_blocked))>
                            Block sign-in and withdrawals
                        </label>
                    </div>
                    <div style="grid-column:1/-1;">
                        <button type="submit" class="admin-btn admin-btn-primary">Save changes</button>
                    </div>
                </form>
            </div>

            <div class="admin-card" style="margin-top:16px;">
                <h2 style="margin:0 0 14px;font-size:16px;font-weight:600;">Contracts</h2>
                @forelse($user->contracts as $contract)
                    <div style="padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.06);font-size:13px;">
                        <strong>{{ $contract->code }}</strong> · {{ $contract->plan?->name }} · {{ $contract->formattedTflops() }} TF · {{ $contract->status }}
                        <div style="margin-top:4px;color:rgba(232,237,245,0.62);">Accrued {{ $contract->formattedAccrued() }} COIN · {{ $contract->progress_percent }}%</div>
                    </div>
                @empty
                    <p style="margin:0;color:rgba(232,237,245,0.62);">No contracts.</p>
                @endforelse
            </div>

            <div class="admin-card" style="margin-top:16px;">
                <h2 style="margin:0 0 14px;font-size:16px;font-weight:600;">Recent transactions</h2>
                @forelse($user->walletTransactions->take(8) as $tx)
                    <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.06);font-size:13px;">
                        <span>{{ $tx->type }} · {{ $tx->source }}</span>
                        <span style="font-family:'JetBrains Mono',monospace;">{{ $tx->amount_label }}</span>
                    </div>
                @empty
                    <p style="margin:0;color:rgba(232,237,245,0.62);">No transactions.</p>
                @endforelse
            </div>
        </div>

        @include('admin.partials.user-context', ['user' => $user])
    </div>
@endsection
