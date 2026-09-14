<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketMessage extends Model
{
    public const AUTHOR_USER = 'user';

    public const AUTHOR_GUEST = 'guest';

    public const AUTHOR_ADMIN = 'admin';

    /** @return list<string> */
    public static function customerAuthorTypes(): array
    {
        return [self::AUTHOR_USER, self::AUTHOR_GUEST];
    }

    protected $fillable = [
        'support_ticket_id',
        'author_type',
        'author_id',
        'body',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function isFromAdmin(): bool
    {
        return $this->author_type === self::AUTHOR_ADMIN;
    }

    public function isFromCustomer(): bool
    {
        return in_array($this->author_type, self::customerAuthorTypes(), true);
    }

    public function authorLabel(): string
    {
        if ($this->author_type === self::AUTHOR_ADMIN) {
            return 'Support team';
        }

        return 'You';
    }

    public function authorLabelForBroadcast(): string
    {
        if ($this->isFromAdmin()) {
            return 'Support team';
        }

        $this->loadMissing('ticket.user');

        if ($this->ticket?->isGuest()) {
            return 'Guest · '.$this->ticket->guest_email;
        }

        return $this->ticket?->user?->accountLabel() ?? 'User';
    }
}
