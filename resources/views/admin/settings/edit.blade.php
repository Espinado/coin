@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.settings')]))

@section('content')
    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div class="admin-card">
        <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.platform_settings') }}</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.settings_sub') }}</p>
    </div>

    @if($cmcRateSyncEnabled ?? false)
        <form id="refresh-btc-rate-form" method="POST" action="{{ route('admin.settings.refresh-btc-rate') }}" hidden>
            @csrf
        </form>
    @endif

    <div class="admin-card" style="margin-top:16px;">
        <form method="POST" action="{{ route('admin.settings.update') }}" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;align-items:start;">
            @csrf
            @method('PATCH')

            @foreach($definitions as $key => $definition)
                <div>
                    <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper($definition['label']) }}</label>
                    @if($definition['type'] === 'boolean')
                        <label style="display:flex;align-items:center;gap:8px;margin-top:12px;font-size:13px;">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" value="1" @checked(old($key, filter_var($values[$key] ?? $definition['default'], FILTER_VALIDATE_BOOL)))>
                            {{ __('coin.admin.enabled') }}
                        </label>
                        @if($key === 'payment_gate_enabled')
                        <p style="margin:6px 0 0;font-size:12px;color:rgba(232,237,245,0.55);">{{ __('coin.settings.payment_gate_hint') }}</p>
                        @elseif($key === 'maintenance_mode')
                        <p style="margin:6px 0 0;font-size:12px;color:rgba(232,237,245,0.55);">{{ __('coin.settings.maintenance_hint') }}</p>
                        @endif
                    @elseif($definition['type'] === 'time')
                        <input type="time" name="{{ $key }}" value="{{ old($key, $values[$key] ?? $definition['default']) }}"
                            style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                        <p style="margin:6px 0 0;font-size:12px;color:rgba(232,237,245,0.55);">{{ __('coin.settings.profit_accrual_time_hint', ['timezone' => config('coin.profit_accrual.schedule_timezone', 'Europe/Riga')]) }}</p>
                    @elseif($key === 'token_symbol')
                        <input type="text" name="{{ $key }}" value="{{ old($key, $values[$key] ?? $definition['default']) }}"
                            style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    @elseif($definition['type'] === 'readonly_decimal')
                        @if($key === 'usdt_per_btc')
                        <div style="display:flex;gap:8px;align-items:stretch;margin-top:8px;">
                            <input type="text" readonly tabindex="-1" value="{{ $values[$key] ?? $definition['default'] }}"
                                style="flex:1;min-width:0;box-sizing:border-box;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);color:rgba(232,237,245,0.72);cursor:default;">
                            @if($cmcRateSyncEnabled ?? false)
                            <button type="submit" form="refresh-btc-rate-form" class="admin-btn" style="height:100%;white-space:nowrap;">{{ __('coin.settings.refresh_btc_rate') }}</button>
                            @endif
                        </div>
                        <p style="margin:6px 0 0;font-size:12px;color:rgba(232,237,245,0.55);">{{ __('coin.settings.usdt_per_btc_hint') }}</p>
                        @if(filled($values['btc_rate_updated_at'] ?? null))
                            <p style="margin:4px 0 0;font-size:12px;color:rgba(232,237,245,0.45);">
                                {{ __('coin.settings.btc_rate_meta', [
                                    'source' => __('coin.settings.btc_rate_source.'.($values['btc_rate_source'] ?? 'coinmarketcap')),
                                    'updated_at' => \Illuminate\Support\Carbon::parse($values['btc_rate_updated_at'])->timezone(config('app.timezone'))->format('d.m.Y H:i'),
                                ]) }}
                            </p>
                        @endif
                        @else
                        <input type="text" readonly tabindex="-1" value="{{ $values[$key] ?? $definition['default'] }}"
                            style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);color:rgba(232,237,245,0.72);cursor:default;">
                        <p style="margin:6px 0 0;font-size:12px;color:rgba(232,237,245,0.55);">{{ __('coin.settings.btc_per_usdt_hint') }}</p>
                        @endif
                    @else
                        <input type="number" name="{{ $key }}" value="{{ old($key, $values[$key] ?? $definition['default']) }}" step="{{ in_array($key, ['reward_rate', 'min_deposit', 'min_withdrawal', 'network_fee'], true) ? '0.0001' : '1' }}"
                            style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    @endif
                    @error($key)<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
                </div>
            @endforeach

            <div style="grid-column:1/-1;">
                <button type="submit" class="admin-btn admin-btn-primary">{{ __('coin.admin.save_settings') }}</button>
            </div>
        </form>
    </div>

    <div class="admin-card" style="margin-top:16px;">
        <h2 style="margin:0;font-size:20px;font-weight:600;">{{ __('coin.admin.legal_info.title') }}</h2>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.legal_info.sub') }}</p>
    </div>

    <div class="admin-card" style="margin-top:16px;">
        <form method="POST" action="{{ route('admin.settings.legal.update') }}" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
            @csrf
            @method('PATCH')

            @foreach($legalDefinitions as $key => $definition)
                <div @if($definition['type'] === 'textarea') style="grid-column:1/-1;" @endif>
                    <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper($definition['label']) }}</label>
                    @if($definition['type'] === 'textarea')
                        <textarea name="{{ $key }}" rows="3"
                            style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;resize:vertical;">{{ old($key, $values[$key] ?? $definition['default']) }}</textarea>
                    @else
                        <input type="{{ $definition['type'] === 'email' ? 'email' : 'text' }}" name="{{ $key }}" value="{{ old($key, $values[$key] ?? $definition['default']) }}"
                            style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    @endif
                    @error($key)<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
                </div>
            @endforeach

            <div style="grid-column:1/-1;">
                <button type="submit" class="admin-btn admin-btn-primary">{{ __('coin.admin.legal_info.save') }}</button>
            </div>
        </form>
    </div>
@endsection
