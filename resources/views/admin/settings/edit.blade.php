@extends('layouts.admin')

@section('title', 'Coin Admin — Settings')

@section('content')
    @include('admin.partials.nav')

    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div class="admin-card">
        <h1 style="margin:0;font-size:24px;font-weight:600;">Platform settings</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">Global parameters for rewards, withdrawals, and referrals.</p>
    </div>

    <div class="admin-card" style="margin-top:16px;">
        <form method="POST" action="{{ route('admin.settings.update') }}" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
            @csrf
            @method('PATCH')

            @foreach($definitions as $key => $definition)
                <div>
                    <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper($definition['label']) }}</label>
                    @if($definition['type'] === 'boolean')
                        <label style="display:flex;align-items:center;gap:8px;margin-top:12px;font-size:13px;">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" value="1" @checked(old($key, filter_var($values[$key] ?? $definition['default'], FILTER_VALIDATE_BOOL)))>
                            Enabled
                        </label>
                    @elseif($key === 'token_symbol')
                        <input type="text" name="{{ $key }}" value="{{ old($key, $values[$key] ?? $definition['default']) }}"
                            style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    @else
                        <input type="number" name="{{ $key }}" value="{{ old($key, $values[$key] ?? $definition['default']) }}" step="{{ in_array($key, ['reward_rate', 'min_withdrawal', 'network_fee'], true) ? '0.0001' : '1' }}"
                            style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    @endif
                    @error($key)<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
                </div>
            @endforeach

            <div style="grid-column:1/-1;">
                <button type="submit" class="admin-btn admin-btn-primary">Save settings</button>
            </div>
        </form>
    </div>
@endsection
