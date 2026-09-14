@extends('layouts.admin')

@section('title', 'Coin Admin — Plans')

@section('content')
    @include('admin.partials.nav')

    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div class="admin-card">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <h1 style="margin:0;font-size:24px;font-weight:600;">Plans</h1>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">Catalog for the user dashboard Plans section. Active plans appear immediately; hidden plans are excluded.</p>
            </div>
            <a href="{{ route('admin.plans.create') }}" class="admin-btn admin-btn-primary">New plan</a>
        </div>
    </div>

    <div class="admin-card" style="margin-top:16px;padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);color:rgba(232,237,245,0.62);font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.1em;">
                    <th style="padding:14px 18px;">PLAN</th>
                    <th style="padding:14px 18px;">TFLOPS</th>
                    <th style="padding:14px 18px;">PRICE</th>
                    <th style="padding:14px 18px;">MULT</th>
                    <th style="padding:14px 18px;">STATUS</th>
                    <th style="padding:14px 18px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($plans as $plan)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;"><a href="{{ route('admin.plans.edit', $plan) }}">{{ $plan->name }}</a></td>
                        <td style="padding:14px 18px;">{{ $plan->formattedTflops() }}</td>
                        <td style="padding:14px 18px;">{{ $plan->price_label }}</td>
                        <td style="padding:14px 18px;">{{ $plan->reward_multiplier }}×</td>
                        <td style="padding:14px 18px;">{{ $plan->is_active ? 'Active' : 'Hidden' }}@if($plan->is_featured) · Featured @endif</td>
                        <td style="padding:14px 18px;text-align:right;">
                            <a href="{{ route('admin.plans.edit', $plan) }}" class="admin-btn">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
