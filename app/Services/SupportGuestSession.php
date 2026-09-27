<?php

namespace App\Services;

use App\Models\SupportTicket;

class SupportGuestSession
{
    private const SESSION_KEY = 'guest_support';

    public static function put(SupportTicket $ticket): void
    {
        session([
            self::SESSION_KEY => [
                'ticket_id' => $ticket->id,
                'token' => $ticket->guest_token,
            ],
        ]);
    }

    public static function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public static function current(): ?SupportTicket
    {
        $data = session(self::SESSION_KEY);

        if (! is_array($data) || empty($data['ticket_id']) || empty($data['token'])) {
            return null;
        }

        return SupportTicket::query()
            ->whereKey($data['ticket_id'])
            ->where('guest_token', $data['token'])
            ->whereNull('user_id')
            ->with('messages')
            ->first();
    }

    public static function canAccessTicket(int $ticketId): bool
    {
        // Always verify token against DB (same bar as current()); never trust session ticket_id alone.
        if (self::tokenMatchesTicket($ticketId, self::token())) {
            return true;
        }

        return self::tokenMatchesTicket($ticketId, request()->header('X-Support-Guest-Token'));
    }

    public static function tokenMatchesTicket(int $ticketId, ?string $token): bool
    {
        if (! filled($token)) {
            return false;
        }

        return SupportTicket::query()
            ->whereKey($ticketId)
            ->whereNull('user_id')
            ->where('guest_token', $token)
            ->exists();
    }

    public static function token(): ?string
    {
        $data = session(self::SESSION_KEY);

        if (! is_array($data)) {
            return null;
        }

        $token = $data['token'] ?? null;

        return is_string($token) && $token !== '' ? $token : null;
    }
}
