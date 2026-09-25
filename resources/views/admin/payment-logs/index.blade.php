@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.payment_logs')]))

@section('content')
    <div class="admin-card">
        <div style="margin-bottom:16px;">
            <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.payment_logs') }}</h1>
            <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.payment_logs_sub') }}</p>
        </div>

        <form method="GET" action="{{ route('admin.payment-logs.index') }}" class="admin-list-toolbar">
            <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('coin.admin.search_placeholder_payment_logs') }}">

            <select name="entity_type">
                <option value="">{{ __('coin.admin.all_entity_types') }}</option>
                @foreach($entityTypes as $value => $label)
                    <option value="{{ $value }}" @selected($entityType === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="source">
                <option value="">{{ __('coin.admin.all_sources') }}</option>
                @foreach($sources as $value => $label)
                    <option value="{{ $value }}" @selected($source === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="result">
                <option value="">{{ __('coin.admin.all_results') }}</option>
                @foreach($results as $value => $label)
                    <option value="{{ $value }}" @selected($result === $value)>{{ $label }}</option>
                @endforeach
            </select>

            @if($sort !== '')
                <input type="hidden" name="sort" value="{{ $sort }}">
            @endif
            @if($dir !== '')
                <input type="hidden" name="dir" value="{{ $dir }}">
            @endif

            <label>
                <span>{{ __('coin.pagination.per_page') }}</span>
                <select name="per_page">
                    @foreach([10, 20, 50, 100] as $option)
                        <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </label>

            <button type="submit" class="admin-btn">{{ __('coin.admin.apply') }}</button>
        </form>
    </div>

    <div class="admin-card admin-card--table payment-logs-table" style="margin-top:16px;">
        <div class="admin-table-scroll">
            <table class="payment-logs-table__grid">
                <thead>
                    <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                        @include('admin.partials.sortable-th', ['column' => 'created_at', 'label' => strtoupper(__('coin.admin.time')), 'sort' => $sort, 'dir' => $dir])
                        @include('admin.partials.sortable-th', ['column' => 'entity_type', 'label' => strtoupper(__('coin.admin.entity')), 'sort' => $sort, 'dir' => $dir])
                        @include('admin.partials.sortable-th', ['column' => 'reference', 'label' => strtoupper(__('coin.admin.application_reference')), 'sort' => $sort, 'dir' => $dir])
                        <th style="padding:12px 18px;">{{ strtoupper(__('coin.user')) }}</th>
                        <th style="padding:12px 18px;">{{ strtoupper(__('coin.payment_log.entity_status_label')) }}</th>
                        <th style="padding:12px 18px;">{{ strtoupper(__('coin.admin.events')) }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $group)
                        <tr class="payment-log-group-row" style="border-bottom:1px solid rgba(255,255,255,0.06);">
                            <td style="padding:14px 18px;white-space:nowrap;">{{ $group->lastEventAt() }}</td>
                            <td style="padding:14px 18px;">{{ $group->entityLabel() }}</td>
                            <td style="padding:14px 18px;font-family:'JetBrains Mono',monospace;font-size:12px;">
                                @if($group->entityAdminUrl())
                                    <a href="{{ $group->entityAdminUrl() }}">{{ $group->displayReference() }}</a>
                                @else
                                    {{ $group->displayReference() }}
                                @endif
                            </td>
                            <td style="padding:14px 18px;">{{ $group->user()?->email ?? '—' }}</td>
                            <td style="padding:14px 18px;white-space:nowrap;">{{ $group->currentStatusLabel() }}</td>
                            <td style="padding:14px 18px;">
                                <details class="payment-log-group-details">
                                    <summary class="payment-log-group-details__summary">
                                        {{ __('coin.admin.payment_log_events', ['count' => $group->eventsCount()]) }}
                                    </summary>
                                    <div class="payment-log-group-details__panel">
                                        <div class="payment-log-group-details__sources">
                                            {{ implode(' · ', $group->sourceLabels()) }}
                                        </div>
                                        @foreach($group->events as $event)
                                            <div class="payment-log-group-details__event">
                                                <div class="payment-log-group-details__event-head">
                                                    <a href="{{ route('admin.payment-logs.show', $event) }}" class="payment-log-group-details__event-id">#{{ $event->id }}</a>
                                                    <span>{{ $event->formattedCreatedAt() }}</span>
                                                    <span class="payment-log-group-details__event-source">{{ $event->sourceLabel() }}</span>
                                                    <span>{{ $event->resultLabel() }}</span>
                                                </div>
                                                <div class="payment-log-group-details__event-title">{{ $event->title }}</div>
                                                <div class="payment-log-group-details__event-message">{{ $event->displayMessage() }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="padding:18px;color:rgba(232,237,245,0.65);">{{ __('coin.admin.no_payment_logs') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.partials.list-pagination', ['paginator' => $groups])
@endsection
