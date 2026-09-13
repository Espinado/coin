<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'total' => SupportTicket::totalUnreadForAdmin(),
        ]);
    }

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $tickets = SupportTicket::query()
            ->with(['user', 'assignedAdmin', 'messages'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.support.index', [
            'tickets' => $tickets,
            'status' => $status,
            'statuses' => SupportTicket::statuses(),
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

        $ticket->markReadByAdmin();

        return view('admin.support.show', [
            'ticket' => $ticket,
            'statuses' => SupportTicket::statuses(),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket, SupportTicketService $support): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:5000'],
            'status' => ['nullable', 'in:'.implode(',', array_keys(SupportTicket::statuses()))],
        ]);

        $message = $support->addAdminMessage(
            $ticket,
            $request->user('admin'),
            $validated['body'],
            $validated['status'] ?? SupportTicket::STATUS_PENDING,
        );

        $ticket = $ticket->fresh();
        $ticket->markReadByAdmin();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'ticket_id' => $message->support_ticket_id,
                    'author_type' => $message->author_type,
                    'author_label' => $message->authorLabelForBroadcast(),
                    'body' => $message->body,
                    'created_at' => $message->created_at?->format('M j, Y H:i'),
                    'is_from_admin' => $message->isFromAdmin(),
                ],
                'ticket' => [
                    'id' => $ticket->id,
                    'status' => $ticket->status,
                    'status_label' => $ticket->statusLabel(),
                    'reference' => $ticket->reference,
                    'updated_at' => $ticket->updated_at?->format('M j, H:i'),
                ],
            ]);
        }

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
