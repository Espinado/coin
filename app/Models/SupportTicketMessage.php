<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketMessage extends Model
{
    public const AUTHOR_USER = 'user';

    public const AUTHOR_ADMIN = 'admin';

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

        return $this->ticket?->user?->accountLabel() ?? 'User';
    }
}
