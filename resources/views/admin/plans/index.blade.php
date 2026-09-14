@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.plans')]))

@section('content')
    @include('admin.partials.nav')

    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:rgba(255,180,84,0.35);">{{ session('status') }}</div>
    @endif

    <div class="admin-card">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.plans') }}</h1>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.plans_sub') }}</p>
            </div>
            <a href="{{ route('admin.plans.create') }}" class="admin-btn admin-btn-primary">{{ __('coin.admin.new_plan') }}</a>
        </div>
    </div>

    <div class="admin-card" style="margin-top:16px;padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);color:rgba(232,237,245,0.62);font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.1em;">
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.plan')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.min_purchase')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.apr')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.lock_period')) }}</th>
                    <th style="padding:14px 18px;">{{ strtoupper(__('coin.admin.visibility')) }}</th>
                    <th style="padding:14px 18px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($plans as $plan)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;"><a href="{{ route('admin.plans.edit', $plan) }}">{{ $plan->name }}</a></td>
                        <td style="padding:14px 18px;">{{ $plan->formattedMinDeposit() ?? $plan->price_label }}</td>
                        <td style="padding:14px 18px;">{{ $plan->formattedAnnualProfit() ?? '—' }}</td>
                        <td style="padding:14px 18px;">{{ $plan->formattedDuration() }}</td>
                        <td style="padding:14px 18px;">{{ $plan->is_active ? __('coin.admin.published') : __('coin.admin.hidden') }}@if($plan->is_featured) · {{ __('coin.admin.highlighted') }} @endif</td>
                        <td style="padding:14px 18px;text-align:right;">
                            <a href="{{ route('admin.plans.edit', $plan) }}" class="admin-btn">{{ __('coin.edit') }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
