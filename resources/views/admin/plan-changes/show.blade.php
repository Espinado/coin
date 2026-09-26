@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.plan_change_detail', ['id' => $request->id])]))

@section('content')
    @if (session('status'))
        <div class="admin-card" style="margin-bottom:16px;border-color:{{ session('status_type') === 'error' ? 'rgba(255,143,143,0.35)' : 'rgba(255,180,84,0.35)' }};">{{ session('status') }}</div>
    @endif

    <div class="admin-grid-split">
        <div>
            <div class="admin-card" data-plan-change-detail="{{ $request->id }}">
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.62);">{{ strtoupper(__('coin.admin.plan_change_detail', ['id' => $request->id])) }}</div>
                <h1 style="margin:10px 0 0;font-size:22px;font-weight:600;">{{ $request->reference }}</h1>
                <p style="margin:8px 0 0;font-size:13px;color:rgba(232,237,245,0.72);"><span data-plan-change-status>{{ $request->statusLabel() }}</span> · {{ $request->created_at?->format('M j, Y H:i') }}</p>
                <div style="margin-top:16px;font-size:13px;line-height:1.7;">
                    <div><strong>{{ __('coin.admin.contract') }}:</strong> {{ $request->contract?->code }}</div>
                    <div><strong>{{ __('coin.admin.from_plan') }}:</strong> {{ $request->fromPlan?->displayName() }}</div>
                    <div><strong>{{ __('coin.admin.to_plan') }}:</strong> {{ $request->toPlan?->displayName() }}</div>
                    <div><strong>{{ __('coin.admin.top_up') }}:</strong> {{ $request->formattedTopUp() }}</div>
                    <div><strong>{{ __('coin.admin.principal_after') }}:</strong> {{ $request->formattedPrincipalAfter() }}</div>
                    @if($request->processed_at)
                    <div><strong>{{ __('coin.admin.processed') }}:</strong> {{ $request->processed_at->format('M j, Y H:i') }}</div>
                    @endif
                    @if($request->admin_note)
                    <div><strong>{{ __('coin.admin.admin_note') }}:</strong> {{ $request->admin_note }}</div>
                    @endif
                </div>
            </div>

            @if($request->isPending())
            <div class="admin-card" style="margin-top:16px;" data-plan-change-actions>
                <form method="POST" action="{{ route('admin.plan-changes.approve', $request) }}" style="margin-bottom:12px;">
                    @csrf
                    <label style="display:block;font-size:12px;color:rgba(232,237,245,0.72);margin-bottom:6px;">{{ __('coin.admin.admin_note') }}</label>
                    <textarea name="admin_note" rows="2" placeholder="{{ __('coin.admin.audit_note_placeholder') }}" style="width:100%;margin-bottom:10px;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.2);color:#e8edf5;"></textarea>
                    <button type="submit" class="admin-btn admin-btn-primary">{{ __('coin.admin.approve_plan_change') }}</button>
                </form>
                <form method="POST" action="{{ route('admin.plan-changes.reject', $request) }}">
                    @csrf
                    <textarea name="admin_note" rows="2" placeholder="{{ __('coin.admin.reject_note_placeholder') }}" style="width:100%;margin-bottom:10px;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.2);color:#e8edf5;"></textarea>
                    <button type="submit" class="admin-btn" style="border-color:rgba(255,143,143,0.45);color:#ff8f8f;">{{ __('coin.admin.reject_plan_change') }}</button>
                </form>
            </div>
            @endif
        </div>

        @include('admin.partials.user-context', ['user' => $request->user])
    </div>
@endsection
