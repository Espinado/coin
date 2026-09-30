@php
    /** @var \App\Models\User $user */
    /** @var \Illuminate\Support\Collection<int, \App\Models\PlanOffer> $pendingOffers */
    $defaultExpiresAt = $defaultExpiresAt ?? now()->addHours(\App\Services\PrivateOfferService::DEFAULT_TTL_HOURS)->format('Y-m-d\TH:i');
    $ticketId = $ticketId ?? null;
    $formAction = route('admin.users.private-offers.store', $user);
    $inputStyle = 'width:100%;margin-top:6px;padding:9px 11px;border-radius:9px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;box-sizing:border-box;';
    $labelStyle = 'display:block;font-family:\'JetBrains Mono\',monospace;font-size:9.5px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);';
@endphp

<div class="admin-card" style="margin-top:16px;" id="ticket-offer-composer" data-ticket-offer-composer>
    <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start;flex-wrap:wrap;">
        <div>
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(255,180,84,0.85);">{{ strtoupper(__('coin.admin.private_offer_composer_title')) }}</div>
            <p style="margin:8px 0 0;font-size:12.5px;line-height:1.45;color:rgba(232,237,245,0.62);">{{ __('coin.admin.private_offer_composer_sub') }}</p>
        </div>
        <a href="{{ route('admin.users.private-offers.create', $user) }}" style="font-size:12px;white-space:nowrap;">{{ __('coin.admin.private_offer_open_full') }}</a>
    </div>

    <form method="POST" action="{{ $formAction }}" style="margin-top:14px;display:grid;gap:12px;">
        @csrf
        @if($ticketId)
            <input type="hidden" name="ticket_id" value="{{ $ticketId }}">
        @endif

        <div>
            <label style="{{ $labelStyle }}">{{ strtoupper(__('coin.admin.private_offer_plan_name')) }}</label>
            <input type="text" name="name" id="ticket-offer-name" value="{{ old('name', __('coin.invest.private_offer_name', ['user' => $user->name])) }}" maxlength="120" required style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">{{ strtoupper(__('coin.admin.private_offer_amount')) }}</label>
            <input type="number" name="amount" id="ticket-offer-amount" value="{{ old('amount', 10000) }}" min="1" step="any" required style="{{ $inputStyle }}">
            <input type="range" id="ticket-offer-amount-range" min="1000" max="250000" step="500" value="{{ old('amount', 10000) }}" style="width:100%;margin-top:8px;">
        </div>
        <div>
            <label style="{{ $labelStyle }}">{{ strtoupper(__('coin.admin.private_offer_days')) }}</label>
            <input type="number" name="duration_days" id="ticket-offer-days" value="{{ old('duration_days', 180) }}" min="1" max="3650" step="1" required style="{{ $inputStyle }}">
            <input type="range" id="ticket-offer-days-range" min="30" max="730" step="1" value="{{ old('duration_days', 180) }}" style="width:100%;margin-top:8px;">
        </div>
        <div>
            <label style="{{ $labelStyle }}">{{ strtoupper(__('coin.admin.private_offer_apr')) }}</label>
            <input type="number" name="annual_profit_percent" id="ticket-offer-apr" value="{{ old('annual_profit_percent', 18) }}" min="0.01" max="1000" step="any" required style="{{ $inputStyle }}">
            <input type="range" id="ticket-offer-apr-range" min="0.01" max="100" step="0.01" value="{{ old('annual_profit_percent', 18) }}" style="width:100%;margin-top:8px;">
        </div>
        <div>
            <label style="{{ $labelStyle }}">{{ strtoupper(__('coin.admin.private_offer_expires_at')) }}</label>
            <input type="datetime-local" name="expires_at" id="ticket-offer-expires" value="{{ old('expires_at', $defaultExpiresAt) }}" required style="{{ $inputStyle }}">
        </div>

        <div style="padding:12px;border-radius:10px;border:1px solid rgba(255,180,84,0.2);background:rgba(255,180,84,0.05);display:grid;gap:8px;font-size:12.5px;">
            <div style="display:flex;justify-content:space-between;gap:10px;"><span style="color:rgba(232,237,245,0.62);">{{ __('coin.contract.daily_profit') }}</span><strong id="ticket-offer-preview-daily" style="font-family:'JetBrains Mono',monospace;color:oklch(0.9 0.12 192);">—</strong></div>
            <div style="display:flex;justify-content:space-between;gap:10px;"><span style="color:rgba(232,237,245,0.62);">{{ __('coin.admin.private_offer_total_profit') }}</span><strong id="ticket-offer-preview-total" style="font-family:'JetBrains Mono',monospace;color:#ffd39a;">—</strong></div>
        </div>

        <button type="submit" class="admin-btn admin-btn-primary" style="width:100%;">{{ __('coin.admin.private_offer_submit') }}</button>
    </form>

    @if(($pendingOffers ?? collect())->isNotEmpty())
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid rgba(255,255,255,0.08);">
            <div style="font-size:12.5px;font-weight:600;margin-bottom:10px;">{{ __('coin.admin.private_offers_pending') }}</div>
            <div style="display:grid;gap:8px;">
                @foreach($pendingOffers as $offer)
                    <div style="padding:10px 12px;border-radius:10px;border:1px solid rgba(255,180,84,0.22);font-size:12px;">
                        <strong>{{ $offer->plan?->displayName() ?? '—' }}</strong>
                        <div style="margin-top:4px;color:rgba(232,237,245,0.72);">{{ $offer->formattedAmount() }} · {{ $offer->duration_days }}d · {{ $offer->formattedApr() }}</div>
                        <form method="POST" action="{{ route('admin.users.private-offers.revoke', [$user, $offer]) }}" style="margin-top:8px;">
                            @csrf
                            @if($ticketId)
                                <input type="hidden" name="ticket_id" value="{{ $ticketId }}">
                            @endif
                            <button type="submit" class="admin-btn" style="padding:7px 10px;font-size:12px;border-color:rgba(255,143,143,0.45);color:#ff8f8f;">{{ __('coin.admin.private_offer_revoke') }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@once
    @push('scripts')
        <script>
        (() => {
          const root = document.getElementById('ticket-offer-composer');
          if (!root) return;

          const nameInput = document.getElementById('ticket-offer-name');
          const amount = document.getElementById('ticket-offer-amount');
          const amountRange = document.getElementById('ticket-offer-amount-range');
          const days = document.getElementById('ticket-offer-days');
          const daysRange = document.getElementById('ticket-offer-days-range');
          const apr = document.getElementById('ticket-offer-apr');
          const aprRange = document.getElementById('ticket-offer-apr-range');
          const dailyEl = document.getElementById('ticket-offer-preview-daily');
          const totalEl = document.getElementById('ticket-offer-preview-total');
          const currency = @json(config('coin.wallet.base_currency', 'USDT'));

          const syncPair = (input, range) => {
            const fromInput = () => {
              const v = Number(input.value || 0);
              const min = Number(range.min);
              const max = Number(range.max);
              range.value = String(Math.min(max, Math.max(min, v)));
              render();
            };
            const fromRange = () => {
              input.value = range.value;
              render();
            };
            input.addEventListener('input', fromInput);
            range.addEventListener('input', fromRange);
          };

          const fmt = (n) => Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + currency;

          const render = () => {
            const a = Math.max(0, Number(amount.value || 0));
            const d = Math.max(1, Number(days.value || 1));
            const p = Math.max(0, Number(apr.value || 0));
            const daily = a > 0 && p > 0 ? Math.round((a * (p / 100) / 365) * 100) / 100 : 0;
            const total = Math.round(daily * d * 100) / 100;
            dailyEl.textContent = fmt(daily);
            totalEl.textContent = fmt(total);
          };

          syncPair(amount, amountRange);
          syncPair(days, daysRange);
          syncPair(apr, aprRange);
          nameInput.addEventListener('input', render);
          render();
        })();
        </script>
    @endpush
@endonce
