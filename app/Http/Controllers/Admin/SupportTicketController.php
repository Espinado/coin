<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $tickets = SupportTicket::query()
            ->with(['user', 'assignedAdmin'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.support.index', [
            'tickets' => $tickets,
            'status' => $status,
            'statuses' => SupportTicket::statuses(),
            'openCount' => SupportTicket::query()->where('status', SupportTicket::STATUS_OPEN)->count(),
        ]);
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load([
            'user.wallet',
            'user.contracts.plan',
            'messages',
            'assignedAdmin',
        ]);

        return view('admin.support.show', [
            'ticket' => $ticket,
            'statuses' => SupportTicket::statuses(),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket, SupportTicketService $support): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:5000'],
            'status' => ['nullable', 'in:'.implode(',', array_keys(SupportTicket::statuses()))],
        ]);

        $support->addAdminMessage(
            $ticket,
            $request->user('admin'),
            $validated['body'],
            $validated['status'] ?? SupportTicket::STATUS_PENDING,
        );

        return redirect()
            ->route('admin.support.show', $ticket)
            ->with('status', 'Reply sent.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket, SupportTicketService $support): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(SupportTicket::statuses()))],
        ]);

        $support->updateStatus($ticket, $validated['status'], $request->user('admin'));

        return redirect()
            ->route('admin.support.show', $ticket)
            ->with('status', 'Ticket status updated.');
    }
}
