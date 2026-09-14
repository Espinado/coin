@extends('layouts.admin')

@section('title', 'Coin Admin — '.($isEdit ? 'Edit '.$plan->name : 'New plan'))

@section('content')
    @include('admin.partials.nav')

    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div class="admin-card">
        <h1 style="margin:0;font-size:24px;font-weight:600;">{{ $isEdit ? 'Edit plan' : 'Create plan' }}</h1>
    </div>

    <div class="admin-card" style="margin-top:16px;">
        <form method="POST" action="{{ $isEdit ? route('admin.plans.update', $plan) : route('admin.plans.store') }}" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
            @csrf
            @if($isEdit) @method('PATCH') @endif

            @foreach([
                ['name', 'Name', 'text', null],
                ['slug', 'Slug', 'text', null],
                ['tier_label', 'Tier label', 'text', null],
                ['price_label', 'Price label', 'text', null],
                ['min_deposit', 'Min deposit', 'number', '0.01'],
                ['price_amount', 'Default deposit amount', 'number', '0.01'],
                ['annual_profit_percent', 'Annual profit %', 'number', '0.01'],
                ['currency', 'Currency', 'text', null],
                ['tflops', 'TFLOPS (legacy calculator)', 'number', null],
                ['duration_days', 'Duration days', 'number', null],
                ['infra', 'Infrastructure', 'text', null],
                ['reward_multiplier', 'Reward multiplier', 'number', '0.01'],
                ['daily_estimate', 'Daily estimate', 'number', '0.01'],
                ['max_tflops', 'Max TFLOPS (calculator)', 'number', null],
                ['sort_order', 'Sort order', 'number', null],
                ['capacity_percent', 'Capacity %', 'number', null],
            ] as [$field, $label, $type, $step])
                <div>
                    <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ strtoupper($label) }}</label>
                    <input type="{{ $type }}" name="{{ $field }}" value="{{ old($field, $plan->{$field}) }}" @if($step) step="{{ $step }}" @endif
                        style="width:100%;box-sizing:border-box;margin-top:8px;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    @error($field)<div style="margin-top:6px;font-size:12px;color:#ff8f8f;">{{ $message }}</div>@enderror
                </div>
            @endforeach

            <div style="display:flex;flex-direction:column;gap:10px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active))> Active (visible for purchase)
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $plan->is_featured))> Featured
                </label>
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
