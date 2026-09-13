@extends('layouts.admin')

@section('title', 'Coin Admin — Support')

@section('content')
    @include('admin.partials.nav')

    <div class="admin-card">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <h1 style="margin:0;font-size:24px;font-weight:600;">Support tickets</h1>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">User requests from the dashboard support section.</p>
            </div>
            <form method="GET" action="{{ route('admin.support.index') }}" style="display:flex;gap:8px;">
                <select name="status" style="padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
                    <option value="">All statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn">Filter</button>
            </form>
        </div>
    </div>

    <div class="admin-card" style="margin-top:16px;padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);color:rgba(232,237,245,0.62);font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.1em;">
                    <th style="padding:14px 18px;">REFERENCE</th>
                    <th style="padding:14px 18px;">USER</th>
                    <th style="padding:14px 18px;">SUBJECT</th>
                    <th style="padding:14px 18px;">CATEGORY</th>
                    <th style="padding:14px 18px;">STATUS</th>
                    <th style="padding:14px 18px;">UPDATED</th>
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
                                    <span data-support-user-name style="font-weight:{{ $unread > 0 ? '600' : '400' }};">{{ $ticket->user->accountLabel() }}</span>
                                    <br><span style="color:rgba(232,237,245,0.62);font-size:12px;">{{ $ticket->user->email }}</span>
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
                        <td colspan="6" style="padding:24px 18px;color:rgba(232,237,245,0.72);">No tickets yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tickets->hasPages())
        <div style="margin-top:16px;">{{ $tickets->links() }}</div>
    @endif
@endsection
