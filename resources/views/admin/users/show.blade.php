@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => $user->accountLabel()]))

@section('content')
    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:16px;align-items:start;">
        <div>
            <div class="admin-card">
                <h1 style="margin:0;font-size:24px;font-weight:600;">{{ $user->name }}</h1>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ $user->email }} · {{ $user->accountLabel() }}</p>
                <div style="margin-top:14px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;font-size:13px;">
                    <div><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.registered') }}</span><div style="margin-top:4px;">{{ $user->created_at?->format('M j, Y H:i') ?? '—' }}</div></div>
                    <div><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.last_login') }}</span><div style="margin-top:4px;">{{ $user->last_login_at?->format('M j, Y H:i') ?? '—' }}</div></div>
                    <div><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.phone') }}</span><div style="margin-top:4px;">{{ $user->phone ?: '—' }}</div></div>
                    <div><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.telegram') }}</span><div style="margin-top:4px;">{{ $user->telegram ?: '—' }}</div></div>
                    <div><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.country') }}</span><div style="margin-top:4px;">{{ $user->country_code ?: '—' }}</div></div>
                    <div><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.referrer') }}</span><div style="margin-top:4px;">@if($user->referrer)<a href="{{ route('admin.users.show', $user->referrer) }}">{{ $user->referrer->accountLabel() }}</a>@else — @endif</div></div>
                </div>
            </div>

            <div class="admin-card" style="margin-top:16px;">
                <h2 style="margin:0 0 16px;font-size:16px;font-weight:600;">{{ __('coin.admin.account_controls') }}</h2>
                <form method="POST" action="{{ route('admin.users.update', $user) }}" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.kyc_status')) }}</label>
                        <select name="kyc_status" style="width:100%;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                            @foreach($kycStatuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('kyc_status', $user->kyc_status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.blocked_label')) }}</label>
                        <label style="display:flex;align-items:center;gap:8px;margin-top:12px;font-size:13px;">
                            <input type="hidden" name="is_blocked" value="0">
                            <input type="checkbox" name="is_blocked" value="1" @checked(old('is_blocked', $user->is_blocked))>
                            {{ __('coin.admin.block_sign_in') }}
                        </label>
                    </div>
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.phone')) }}</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    </div>
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.telegram')) }}</label>
                        <input type="text" name="telegram" value="{{ old('telegram', $user->telegram) }}" style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    </div>
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.country_code')) }}</label>
                        <input type="text" name="country_code" maxlength="2" value="{{ old('country_code', $user->country_code) }}" style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.lead_note')) }}</label>
                        <textarea name="admin_lead_note" rows="4" style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">{{ old('admin_lead_note', $user->admin_lead_note) }}</textarea>
                    </div>
                    <div style="grid-column:1/-1;">
                        <button type="submit" class="admin-btn admin-btn-primary">{{ __('coin.admin.save_changes') }}</button>
                    </div>
                </form>
            </div>

            <div class="admin-card" style="margin-top:16px;">
                <h2 style="margin:0 0 14px;font-size:16px;font-weight:600;">{{ __('coin.admin.investments') }}</h2>
                @forelse($user->contracts as $contract)
                    <div style="padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.06);font-size:13px;">
                        <strong>{{ $contract->code }}</strong> · {{ $contract->plan?->name }} · {{ $contract->formattedPrincipal() }} · {{ $contract->status }}
                        <div style="margin-top:4px;color:rgba(232,237,245,0.62);">
                            {{ $contract->formattedAnnualProfit() ?? '—' }} APR · {{ $contract->formattedDailyProfit() }}{{ __('coin.admin.per_day') }} · {{ $contract->progress_percent }}% · {{ __('coin.admin.profit_label') }} {{ $contract->formattedAccrued() }}
                        </div>
                    </div>
                @empty
                    <p style="margin:0;color:rgba(232,237,245,0.62);">{{ __('coin.admin.no_investments') }}</p>
                @endforelse
            </div>

            <div class="admin-card" style="margin-top:16px;">
                <h2 style="margin:0 0 14px;font-size:16px;font-weight:600;">{{ __('coin.admin.top_ups') }}</h2>
                @forelse($user->deposits as $deposit)
                    <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.06);font-size:13px;">
                        <span><a href="{{ route('admin.deposits.show', $deposit) }}">#{{ $deposit->id }}</a> · {{ ucfirst($deposit->status) }}</span>
                        <span style="font-family:'JetBrains Mono',monospace;">{{ $deposit->formattedAmount() }}</span>
                    </div>
                @empty
                    <p style="margin:0;color:rgba(232,237,245,0.62);">{{ __('coin.admin.no_top_ups') }}</p>
                @endforelse
            </div>

            <div class="admin-card" style="margin-top:16px;">
                <h2 style="margin:0 0 14px;font-size:16px;font-weight:600;">{{ __('coin.admin.payouts') }}</h2>
                @forelse($user->withdrawals as $withdrawal)
                    <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.06);font-size:13px;">
                        <span><a href="{{ route('admin.withdrawals.show', $withdrawal) }}">{{ $withdrawal->reference }}</a> · {{ $withdrawal->statusLabel() }}</span>
                        <span style="font-family:'JetBrains Mono',monospace;">{{ $withdrawal->formattedAmount() }}</span>
                    </div>
                @empty
                    <p style="margin:0;color:rgba(232,237,245,0.62);">{{ __('coin.admin.no_payouts') }}</p>
                @endforelse
            </div>

            <div class="admin-card" style="margin-top:16px;">
                <h2 style="margin:0 0 14px;font-size:16px;font-weight:600;">{{ __('coin.admin.recent_transactions') }}</h2>
                @forelse($user->walletTransactions->sortByDesc('sort_order')->take(8) as $tx)
                    <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.06);font-size:13px;">
                        <span>{{ $tx->type }} · {{ $tx->source }}</span>
                        <span style="font-family:'JetBrains Mono',monospace;">{{ $tx->amount_label }}</span>
                    </div>
                @empty
                    <p style="margin:0;color:rgba(232,237,245,0.62);">{{ __('coin.admin.no_transactions') }}</p>
                @endforelse
            </div>
        </div>

        @include('admin.partials.user-context', [
            'user' => $user,
            'referralVolume' => $referralVolume,
            'referralEarnings' => $referralEarnings,
        ])
    </div>
@endsection
