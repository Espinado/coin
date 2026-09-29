@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.private_offer_create')]))

@section('content')
    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:{{ session('status_type') === 'error' ? 'rgba(255,143,143,0.35)' : 'rgba(255,180,84,0.35)' }};">{{ session('status') }}</div>
    @endif

    <div class="admin-card" style="margin-bottom:16px;">
        <p style="margin:0;font-size:13px;"><a href="{{ route('admin.users.show', $user) }}">← {{ $user->accountLabel() }}</a></p>
        <h1 style="margin:12px 0 0;font-size:22px;font-weight:600;">{{ __('coin.admin.private_offer_create') }}</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.private_offer_create_sub', ['user' => $user->name]) }}</p>
    </div>

    <div class="admin-grid-split">
        <div class="admin-card">
            <form method="POST" action="{{ route('admin.users.private-offers.store', $user) }}" id="private-offer-form">
                @csrf
                <div style="display:grid;gap:16px;">
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.private_offer_plan_name')) }}</label>
                        <input type="text" name="name" id="offer-name" value="{{ old('name', __('coin.invest.private_offer_name', ['user' => $user->name])) }}" maxlength="120" required
                               style="width:100%;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                        <p style="margin:8px 0 0;font-size:12px;color:rgba(232,237,245,0.55);">{{ __('coin.admin.private_offer_plan_name_hint') }}</p>
                    </div>
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.private_offer_amount')) }}</label>
                        <input type="number" name="amount" id="offer-amount" value="{{ old('amount', 10000) }}" min="1" step="any" required
                               style="width:100%;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                        <input type="range" id="offer-amount-range" min="1000" max="250000" step="500" value="{{ old('amount', 10000) }}"
                               style="width:100%;margin-top:10px;">
                    </div>
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.private_offer_days')) }}</label>
                        <input type="number" name="duration_days" id="offer-days" value="{{ old('duration_days', 180) }}" min="1" max="3650" step="1" required
                               style="width:100%;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                        <input type="range" id="offer-days-range" min="30" max="730" step="1" value="{{ old('duration_days', 180) }}"
                               style="width:100%;margin-top:10px;">
                    </div>
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.private_offer_apr')) }}</label>
                        <input type="number" name="annual_profit_percent" id="offer-apr" value="{{ old('annual_profit_percent', 18) }}" min="0.01" max="1000" step="any" required
                               style="width:100%;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                        <input type="range" id="offer-apr-range" min="0.01" max="100" step="0.01" value="{{ old('annual_profit_percent', 18) }}"
                               style="width:100%;margin-top:10px;">
                        <p style="margin:8px 0 0;font-size:12px;color:rgba(232,237,245,0.55);">{{ __('coin.admin.private_offer_apr_hint') }}</p>
                    </div>
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper(__('coin.admin.private_offer_expires_at')) }}</label>
                        <input type="datetime-local" name="expires_at" id="offer-expires" value="{{ old('expires_at', $defaultExpiresAt) }}" required
                               style="width:100%;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                        <p style="margin:8px 0 0;font-size:12px;color:rgba(232,237,245,0.55);">{{ __('coin.admin.private_offer_expires_hint') }}</p>
                    </div>
                    <div>
                        <button type="submit" class="admin-btn admin-btn-primary">{{ __('coin.admin.private_offer_submit') }}</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="admin-card" id="offer-preview" style="align-self:start;">
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(255,180,84,0.85);">{{ strtoupper(__('coin.admin.private_offer_live_preview')) }}</div>
            <div style="margin-top:16px;display:grid;gap:12px;font-size:14px;">
                <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.65);">{{ __('coin.admin.private_offer_plan_name') }}</span><strong id="preview-name">—</strong></div>
                <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.65);">{{ __('coin.contract.daily_profit') }}</span><strong id="preview-daily" style="font-family:'JetBrains Mono',monospace;color:oklch(0.9 0.12 192);">—</strong></div>
                <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.65);">{{ __('coin.admin.private_offer_total_profit') }}</span><strong id="preview-total" style="font-family:'JetBrains Mono',monospace;color:#ffd39a;">—</strong></div>
                <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.65);">{{ __('coin.contract.maturity') }}</span><strong id="preview-maturity" style="font-family:'JetBrains Mono',monospace;">—</strong></div>
                <div style="display:flex;justify-content:space-between;gap:12px;"><span style="color:rgba(232,237,245,0.65);">{{ __('coin.admin.private_offer_expires_at') }}</span><strong id="preview-expires" style="font-family:'JetBrains Mono',monospace;">—</strong></div>
            </div>
            <p style="margin:16px 0 0;font-size:12px;line-height:1.5;color:rgba(232,237,245,0.55);">{{ __('coin.admin.private_offer_preview_note') }}</p>
        </div>
    </div>

    @if($pendingOffers->isNotEmpty())
    <div class="admin-card" style="margin-top:16px;">
        <h2 style="margin:0 0 12px;font-size:16px;font-weight:600;">{{ __('coin.admin.private_offers_pending') }}</h2>
        <div style="display:grid;gap:10px;">
            @foreach($pendingOffers as $offer)
                <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:12px;padding:12px;border-radius:10px;border:1px solid rgba(255,180,84,0.25);font-size:13px;">
                    <div>
                        <strong>{{ $offer->plan?->displayName() ?? '—' }}</strong>
                        <div style="margin-top:4px;">{{ $offer->formattedAmount() }} · {{ $offer->duration_days }}d · {{ $offer->formattedApr() }}</div>
                        <div style="margin-top:4px;color:rgba(232,237,245,0.62);">{{ __('coin.invest.offer_expires') }}: {{ $offer->expires_at?->format('M j, Y H:i') }}</div>
                    </div>
                    <form method="POST" action="{{ route('admin.users.private-offers.revoke', [$user, $offer]) }}">
                        @csrf
                        <button type="submit" class="admin-btn" style="border-color:rgba(255,143,143,0.45);color:#ff8f8f;">{{ __('coin.admin.private_offer_revoke') }}</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(($offerArchive ?? collect())->isNotEmpty())
    <div class="admin-card" style="margin-top:16px;">
        <h2 style="margin:0 0 8px;font-size:16px;font-weight:600;">{{ __('coin.admin.private_offers_archive') }}</h2>
        <p style="margin:0 0 12px;font-size:13px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.private_offers_archive_sub') }}</p>
        <div style="display:grid;gap:10px;">
            @foreach($offerArchive as $offer)
                <div style="padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,0.08);font-size:13px;">
                    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:8px;">
                        <strong>{{ $offer->plan?->displayName() ?? '—' }}</strong>
                        <span style="color:rgba(232,237,245,0.72);">{{ $offer->statusLabel() }}</span>
                    </div>
                    <div style="margin-top:4px;color:rgba(232,237,245,0.72);">{{ $offer->formattedAmount() }} · {{ $offer->duration_days }}d · {{ $offer->formattedApr() }}</div>
                    <div style="margin-top:4px;color:rgba(232,237,245,0.55);">{{ __('coin.invest.offer_expires') }}: {{ $offer->expires_at?->format('M j, Y H:i') }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
@endsection

@push('scripts')
<script>
(() => {
  const nameInput = document.getElementById('offer-name');
  const amount = document.getElementById('offer-amount');
  const amountRange = document.getElementById('offer-amount-range');
  const days = document.getElementById('offer-days');
  const daysRange = document.getElementById('offer-days-range');
  const apr = document.getElementById('offer-apr');
  const aprRange = document.getElementById('offer-apr-range');
  const expires = document.getElementById('offer-expires');
  const nameEl = document.getElementById('preview-name');
  const dailyEl = document.getElementById('preview-daily');
  const totalEl = document.getElementById('preview-total');
  const maturityEl = document.getElementById('preview-maturity');
  const expiresEl = document.getElementById('preview-expires');
  const currency = @json(config('coin.wallet.base_currency', 'USDT'));

  const syncPair = (input, range, clampRange = true) => {
    const syncFromInput = () => {
      let v = Number(input.value || 0);
      if (clampRange) {
        const min = Number(range.min);
        const max = Number(range.max);
        range.value = String(Math.min(max, Math.max(min, v)));
      } else {
        range.value = String(v);
      }
      render();
    };
    const syncFromRange = () => {
      input.value = range.value;
      render();
    };
    input.addEventListener('input', syncFromInput);
    range.addEventListener('input', syncFromRange);
  };

  const fmt = (n) => Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + currency;
  const fmtDate = (d) => d.toLocaleString(undefined, { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });

  const render = () => {
    const a = Math.max(0, Number(amount.value || 0));
    const d = Math.max(1, Number(days.value || 1));
    const p = Math.max(0, Number(apr.value || 0));
    const daily = a > 0 && p > 0 ? Math.round((a * (p / 100) / 365) * 100) / 100 : 0;
    const total = Math.round(daily * d * 100) / 100;
    const maturity = new Date();
    maturity.setDate(maturity.getDate() + d);
    nameEl.textContent = (nameInput.value || '').trim() || '—';
    dailyEl.textContent = fmt(daily);
    totalEl.textContent = fmt(total);
    maturityEl.textContent = maturity.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
    if (expires.value) {
      const exp = new Date(expires.value);
      expiresEl.textContent = Number.isNaN(exp.getTime()) ? '—' : fmtDate(exp);
    } else {
      expiresEl.textContent = '—';
    }
  };

  syncPair(amount, amountRange);
  syncPair(days, daysRange);
  syncPair(apr, aprRange);
  nameInput.addEventListener('input', render);
  expires.addEventListener('input', render);
  render();
})();
</script>
@endpush
