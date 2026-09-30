<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RedirectsWithAdminFlash;
use App\Http\Controllers\Controller;
use App\Models\PlanOffer;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\PrivateOfferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use RuntimeException;

class PrivateOfferController extends Controller
{
    use RedirectsWithAdminFlash;

    public function create(User $user, PrivateOfferService $offers): View
    {
        $history = $offers->offersHistoryForUser($user);

        return view('admin.private-offers.create', [
            'user' => $user,
            'pendingOffers' => $history->where('status', PlanOffer::STATUS_PENDING)->values(),
            'offerArchive' => $history->where('status', '!=', PlanOffer::STATUS_PENDING)->values(),
            'defaultExpiresAt' => now()->addHours(PrivateOfferService::DEFAULT_TTL_HOURS)->format('Y-m-d\TH:i'),
            'preview' => $offers->quotePreview(10000, 180, 18),
        ]);
    }

    public function store(Request $request, User $user, PrivateOfferService $offers): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'amount' => ['required', 'numeric', 'min:1'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'annual_profit_percent' => ['required', 'numeric', 'min:0.01', 'max:1000'],
            'expires_at' => ['required', 'date', 'after:now'],
            'ticket_id' => ['nullable', 'integer', 'exists:support_tickets,id'],
        ]);

        $returnTicket = $this->resolveReturnTicket($user, $validated['ticket_id'] ?? null);

        try {
            $offer = $offers->create(
                $request->user('admin'),
                $user,
                (float) $validated['amount'],
                (int) $validated['duration_days'],
                (float) $validated['annual_profit_percent'],
                Carbon::parse($validated['expires_at']),
                null,
                (string) $validated['name'],
            );
        } catch (RuntimeException $exception) {
            if ($returnTicket) {
                return redirect()
                    ->route('admin.support.show', $returnTicket)
                    ->withInput()
                    ->with('status', $exception->getMessage())
                    ->with('status_type', 'error');
            }

            return redirect()
                ->route('admin.users.private-offers.create', $user)
                ->withInput()
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        if ($returnTicket) {
            return $this->adminSuccess('coin.admin.private_offer_created_flash', 'admin.support.show', $returnTicket, [
                'amount' => $offer->formattedAmount(),
            ])->withFragment('ticket-offer-composer');
        }

        return $this->adminSuccess('coin.admin.private_offer_created_flash', 'admin.users.show', $user, [
            'amount' => $offer->formattedAmount(),
        ]);
    }

    public function revoke(Request $request, User $user, PlanOffer $planOffer, PrivateOfferService $offers): RedirectResponse
    {
        abort_unless((int) $planOffer->user_id === (int) $user->id, 404);

        $validated = $request->validate([
            'ticket_id' => ['nullable', 'integer', 'exists:support_tickets,id'],
        ]);

        $returnTicket = $this->resolveReturnTicket($user, $validated['ticket_id'] ?? null);

        try {
            $offers->revoke($planOffer, $request->user('admin'));
        } catch (RuntimeException $exception) {
            if ($returnTicket) {
                return redirect()
                    ->route('admin.support.show', $returnTicket)
                    ->with('status', $exception->getMessage())
                    ->with('status_type', 'error');
            }

            return redirect()
                ->route('admin.users.show', $user)
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        if ($returnTicket) {
            return $this->adminSuccess('coin.admin.private_offer_revoked_flash', 'admin.support.show', $returnTicket)
                ->withFragment('ticket-offer-composer');
        }

        return $this->adminSuccess('coin.admin.private_offer_revoked_flash', 'admin.users.show', $user);
    }

    private function resolveReturnTicket(User $user, mixed $ticketId): ?SupportTicket
    {
        if ($ticketId === null || $ticketId === '') {
            return null;
        }

        $ticket = SupportTicket::query()->find((int) $ticketId);

        if (! $ticket instanceof SupportTicket) {
            return null;
        }

        if ((int) $ticket->user_id !== (int) $user->id) {
            return null;
        }

        return $ticket;
    }
}
