@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.support')]))

@section('content')
    <div class="admin-card">
        <div style="margin-bottom:16px;">
            <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.support') }}</h1>
            <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.support_sub') }}</p>
        </div>
        @include('admin.partials.list-toolbar', [
            'action' => route('admin.support.index'),
            'search' => $search,
            'status' => $status,
            'statuses' => $statuses,
            'showStatus' => true,
            'sort' => $sort,
            'dir' => $dir,
            'perPage' => $perPage,
            'searchPlaceholder' => __('coin.admin.search_placeholder_support'),
        ])
    </div>

    <div class="admin-card admin-card--table" style="margin-top:16px;"><div class="admin-table-scroll">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                    @include('admin.partials.sortable-th', ['column' => 'reference', 'label' => strtoupper(__('coin.admin.reference')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'user', 'label' => strtoupper(__('coin.user')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'subject', 'label' => strtoupper(__('coin.support.subject')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'category', 'label' => strtoupper(__('coin.support.category')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'status', 'label' => strtoupper(__('coin.status')), 'sort' => $sort, 'dir' => $dir])
                    @include('admin.partials.sortable-th', ['column' => 'updated_at', 'label' => strtoupper(__('coin.admin.updated')), 'sort' => $sort, 'dir' => $dir])
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    @php($unread = $ticket->unreadMessagesForAdmin())
                    <tr data-support-ticket-id="{{ $ticket->id }}" style="border-bottom:1px solid rgba(255,255,255,0.06);{{ $unread > 0 ? 'background:rgba(255,180,84,0.05);' : '' }}">
                        <td style="padding:14px 18px;font-family:'JetBrains Mono',monospace;">
                            <a href="{{ route('admin.support.show', $ticket) }}">{{ $ticket->reference }}</a>
                        </td>
                        <td style="padding:14px 18px;" data-support-user-cell>
                            <div data-support-user-wrap style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                                <div>
                                    <span data-support-user-name style="font-weight:{{ $unread > 0 ? '600' : '400' }};">{{ $ticket->contactLabel() }}</span>
                                    <br><span style="color:rgba(232,237,245,0.62);font-size:12px;">{{ $ticket->contactEmail() }}</span>
                                    @if($ticket->isGuest())
                                        <br><span style="color:rgba(255,180,84,0.85);font-size:11px;">{{ __('coin.admin.guest_chat') }}</span>
                                    @endif
                                </div>
                                @if($unread > 0)
                                    <span class="admin-support-badge" data-support-row-badge style="flex-shrink:0;font-family:'JetBrains Mono',monospace;font-size:11px;font-weight:700;min-width:22px;text-align:center;padding:4px 9px;border-radius:999px;background:linear-gradient(140deg,#ffb454,#e8872e);color:#1a1208;box-shadow:0 0 14px rgba(255,180,84,0.45);">{{ $unread }}</span>
                                @endif
                            </div>
                        </td>
                        <td style="padding:14px 18px;">{{ $ticket->subject }}</td>
                        <td style="padding:14px 18px;">{{ $ticket->categoryLabel() }}</td>
                        <td style="padding:14px 18px;font-family:'JetBrains Mono',monospace;">{{ $ticket->statusLabel() }}</td>
                        <td data-support-updated-cell style="padding:14px 18px;color:rgba(232,237,245,0.72);">{{ $ticket->updated_at?->format('M j, H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:24px 18px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.no_tickets') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @include('admin.partials.list-pagination', ['paginator' => $tickets])
@endsection
