<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessageAttachment extends Model
{
    protected $fillable = [
        'support_ticket_message_id',
        'disk',
        'path',
        'original_name',
        'size',
        'kyc_document_id',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(SupportTicketMessage::class, 'support_ticket_message_id');
    }

    public function kycDocument(): BelongsTo
    {
        return $this->belongsTo(KycDocument::class);
    }

    public function isSavedToKyc(): bool
    {
        return $this->kyc_document_id !== null;
    }
}
