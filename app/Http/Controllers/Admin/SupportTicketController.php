<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Admin\Concerns\RedirectsWithAdminFlash;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    use AdminListQuery;
    use RedirectsWithAdminFlash;

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'total' => SupportTicket::totalUnreadForAdmin(),
        ]);
    }

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = $this->adminSearchTerm($request);

        $query = SupportTicket::query()
            ->with(['user', 'assignedAdmin', 'messages'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('reference', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('guest_email', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('account_slug', 'like', "%{$search}%"));
                });
            });

        $this->adminApplySort($request, $query, [
            'reference' => 'reference',
            'subject' => 'subject',
            'category' => 'category',
            'status' => 'status',
            'updated_at' => 'updated_at',
        ], 'updated_at', 'desc', [
            'user' => function ($ticketQuery, $direction) {
                $ticketQuery
                    ->leftJoin('users', 'users.id', '=', 'support_tickets.user_id')
                    ->orderByRaw('COALESCE(users.email, support_tickets.guest_email) '.$direction)
                    ->select('support_tickets.*');
            },
        ]);

        return view('admin.support.index', [
            'tickets' => $this->adminPaginate($query, $request),
            'statuses' => SupportTicket::statuses(),
            ...$this->adminListState($request),
        ]);
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load([
            'messages',
            'assignedAdmin',
        ]);

        if (! $ticket->isGuest()) {
            $ticket->load([
                'user.wallet',
                'user.contracts.plan',
            ]);
        }

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

        return $this->adminSuccess('coin.admin.flash.reply_sent', 'admin.support.index');
    }

    public function updateStatus(Request $request, SupportTicket $ticket, SupportTicketService $support): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(SupportTicket::statuses()))],
        ]);

        $support->updateStatus($ticket, $validated['status'], $request->user('admin'));

        return $this->adminSuccess('coin.admin.flash.ticket_status_updated', 'admin.support.index');
    }
}
