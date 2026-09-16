@extends('layouts.admin')

@section('title', \App\Support\PlatformBrand::adminPageTitle($isEdit ? 'Edit '.$plan->name : 'New plan'))

@section('content')
    @include('admin.partials.nav')

    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div class="admin-card">
        <h1 style="margin:0;font-size:24px;font-weight:600;">{{ $isEdit ? 'Edit plan' : 'Create plan' }}</h1>
    </div>

    <div class="admin-card" style="margin-top:16px;">
        <p style="margin:0 0 16px;font-size:13px;line-height:1.6;color:rgba(232,237,245,0.72);">
            Full reference: <code style="font-family:'JetBrains Mono',monospace;font-size:12px;color:#ffb454;">docs/plan-fields.md</code>.
            Must set for every plan: <strong>Minimum purchase</strong>, <strong>Annual return (APR)</strong>, <strong>Lock period</strong>.
        </p>

        @php
            $planFieldConfig = config('plan_fields');
            $sections = $planFieldConfig['sections'] ?? [];
            $fields = $planFieldConfig['fields'] ?? [];
            $flags = $planFieldConfig['flags'] ?? [];
        @endphp

        <form method="POST" action="{{ $isEdit ? route('admin.plans.update', $plan) : route('admin.plans.store') }}" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
            @csrf
            @if($isEdit) @method('PATCH') @endif

            @foreach($sections as $sectionKey => $sectionTitle)
                <div style="grid-column:1/-1;margin-top:{{ $loop->first ? '0' : '8' }};padding-top:{{ $loop->first ? '0' : '12' }};border-top:{{ $loop->first ? '0' : '1px solid rgba(255,255,255,0.08)' }};">
                    <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(255,180,84,0.85);">{{ strtoupper($sectionTitle) }}</div>
                </div>

                @foreach($fields as $field => $meta)
                    @if(($meta['section'] ?? '') !== $sectionKey)
                        @continue
                    @endif
                    @if(($meta['type'] ?? '') === 'hidden')
                        <input type="hidden" name="{{ $field }}" value="{{ old($field, $plan->{$field} ?? config('coin.wallet.base_currency', 'USDT')) }}">
                    @else
                    <div>
                        <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper($meta['label']) }}</label>
                        <input type="{{ $meta['type'] }}" name="{{ $field }}" value="{{ old($field, $plan->{$field}) }}" @if(!empty($meta['step'])) step="{{ $meta['step'] }}" @endif
                            style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                        @if(!empty($meta['help']))
                            <p style="margin:6px 0 0;font-size:11.5px;line-height:1.45;color:rgba(232,237,245,0.55);">{{ $meta['help'] }}</p>
                        @endif
                        @error($field)<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
                    </div>
                    @endif
                @endforeach
            @endforeach

            <div style="grid-column:1/-1;margin-top:8px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.08);">
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(255,180,84,0.85);">VISIBILITY</div>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active))> {{ $flags['is_active']['label'] ?? 'Published' }}
                </label>
                @if(!empty($flags['is_active']['help']))
                    <p style="margin:-4px 0 0;font-size:11.5px;line-height:1.45;color:rgba(232,237,245,0.55);">{{ $flags['is_active']['help'] }}</p>
                @endif
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $plan->is_featured))> {{ $flags['is_featured']['label'] ?? 'Featured' }}
                </label>
                @if(!empty($flags['is_featured']['help']))
                    <p style="margin:-4px 0 0;font-size:11.5px;line-height:1.45;color:rgba(232,237,245,0.55);">{{ $flags['is_featured']['help'] }}</p>
                @endif
            </div>

            <div style="grid-column:1/-1;display:flex;gap:10px;flex-wrap:wrap;">
                <button type="submit" class="admin-btn admin-btn-primary">Save plan</button>
                <a href="{{ route('admin.plans.index') }}" class="admin-btn">Back to list</a>
            </div>
        </form>

        @if($isEdit)
            <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" style="margin-top:16px;" onsubmit="return confirm('Delete this plan?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-btn" style="border-color:rgba(255,143,143,0.45);color:#ff8f8f;">Delete plan</button>
            </form>
        @endif
    </div>
@endsection
