@php
    $activeTab = $activeTab ?? 'investments';
    if (! in_array($activeTab, ['investments', 'deposits', 'withdrawals', 'transactions'], true)) {
        $activeTab = 'investments';
    }

    $tabs = [
        'investments' => [
            'label' => __('coin.admin.investments'),
            'count' => $user->contracts->count(),
        ],
        'deposits' => [
            'label' => __('coin.admin.top_ups'),
            'count' => $user->deposits->count(),
        ],
        'withdrawals' => [
            'label' => __('coin.admin.payouts'),
            'count' => $user->withdrawals->count(),
        ],
        'transactions' => [
            'label' => __('coin.admin.recent_transactions'),
            'count' => min($user->walletTransactions->count(), 8),
        ],
    ];
@endphp

<div class="admin-user-activity admin-card" style="margin-top:16px;" data-active-tab="{{ $activeTab }}">
    <div class="admin-user-activity__tabs" role="tablist" aria-label="{{ __('coin.admin.user_activity') }}">
        @foreach($tabs as $key => $tab)
            <button
                type="button"
                role="tab"
                id="user-activity-tab-{{ $key }}"
                class="admin-user-activity__tab{{ $activeTab === $key ? ' is-active' : '' }}"
                aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}"
                aria-controls="user-activity-panel-{{ $key }}"
                data-tab="{{ $key }}"
            >
                <span>{{ $tab['label'] }}</span>
                <span class="admin-user-activity__count">{{ $tab['count'] }}</span>
            </button>
        @endforeach
    </div>

    <div class="admin-user-activity__panel{{ $activeTab === 'investments' ? ' is-active' : '' }}" role="tabpanel" id="user-activity-panel-investments" aria-labelledby="user-activity-tab-investments" @if($activeTab !== 'investments') hidden @endif>
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

    <div class="admin-user-activity__panel{{ $activeTab === 'deposits' ? ' is-active' : '' }}" role="tabpanel" id="user-activity-panel-deposits" aria-labelledby="user-activity-tab-deposits" @if($activeTab !== 'deposits') hidden @endif>
        @forelse($user->deposits as $deposit)
            <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.06);font-size:13px;">
                <span><a href="{{ route('admin.deposits.show', $deposit) }}">#{{ $deposit->id }}</a> · {{ ucfirst($deposit->status) }}</span>
                <span style="font-family:'JetBrains Mono',monospace;">{{ $deposit->formattedAmount() }}</span>
            </div>
        @empty
            <p style="margin:0;color:rgba(232,237,245,0.62);">{{ __('coin.admin.no_top_ups') }}</p>
        @endforelse
    </div>

    <div class="admin-user-activity__panel{{ $activeTab === 'withdrawals' ? ' is-active' : '' }}" role="tabpanel" id="user-activity-panel-withdrawals" aria-labelledby="user-activity-tab-withdrawals" @if($activeTab !== 'withdrawals') hidden @endif>
        @forelse($user->withdrawals as $withdrawal)
            <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.06);font-size:13px;">
                <span><a href="{{ route('admin.withdrawals.show', $withdrawal) }}">{{ $withdrawal->reference }}</a> · {{ $withdrawal->statusLabel() }}</span>
                <span style="font-family:'JetBrains Mono',monospace;">{{ $withdrawal->formattedAmount() }}</span>
            </div>
        @empty
            <p style="margin:0;color:rgba(232,237,245,0.62);">{{ __('coin.admin.no_payouts') }}</p>
        @endforelse
    </div>

    <div class="admin-user-activity__panel{{ $activeTab === 'transactions' ? ' is-active' : '' }}" role="tabpanel" id="user-activity-panel-transactions" aria-labelledby="user-activity-tab-transactions" @if($activeTab !== 'transactions') hidden @endif>
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

@once
    <style>
            .admin-user-activity__tabs {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 16px;
                padding-bottom: 14px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            }
            .admin-user-activity__tab {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 14px;
                border-radius: 999px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                background: rgba(255, 255, 255, 0.03);
                color: rgba(232, 237, 245, 0.72);
                font-size: 13px;
                line-height: 1.2;
                transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease;
            }
            .admin-user-activity__tab:hover {
                border-color: rgba(255, 180, 84, 0.35);
                color: #e8edf5;
            }
            .admin-user-activity__tab.is-active {
                border-color: rgba(255, 180, 84, 0.55);
                background: rgba(255, 180, 84, 0.12);
                color: #ffe6c2;
                font-weight: 600;
            }
            .admin-user-activity__count {
                min-width: 1.4em;
                padding: 2px 7px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.08);
                font-family: 'JetBrains Mono', monospace;
                font-size: 11px;
                text-align: center;
            }
            .admin-user-activity__tab.is-active .admin-user-activity__count {
                background: rgba(255, 180, 84, 0.22);
                color: #ffe6c2;
            }
            .admin-user-activity__panel {
                display: none;
            }
            .admin-user-activity__panel.is-active {
                display: block;
            }
    </style>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.admin-user-activity').forEach(function (root) {
                    var tabs = root.querySelectorAll('.admin-user-activity__tab');
                    var panels = root.querySelectorAll('.admin-user-activity__panel');

                    function activate(tabKey, pushState) {
                        tabs.forEach(function (tab) {
                            var active = tab.dataset.tab === tabKey;
                            tab.classList.toggle('is-active', active);
                            tab.setAttribute('aria-selected', active ? 'true' : 'false');
                        });

                        panels.forEach(function (panel) {
                            var active = panel.id === 'user-activity-panel-' + tabKey;
                            panel.classList.toggle('is-active', active);
                            panel.hidden = !active;
                        });

                        if (pushState) {
                            var url = new URL(window.location.href);
                            url.searchParams.set('activity', tabKey);
                            window.history.replaceState({}, '', url);
                        }
                    }

                    tabs.forEach(function (tab) {
                        tab.addEventListener('click', function () {
                            activate(tab.dataset.tab, true);
                        });
                    });
                });
            });
        </script>
    @endpush
@endonce
