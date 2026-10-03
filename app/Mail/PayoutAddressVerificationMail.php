<?php

namespace App\Mail;

use App\Mail\Concerns\UsesEnglishLocale;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayoutAddressVerificationMail extends Mailable
{
    use Queueable, SerializesModels, UsesEnglishLocale;

    public function __construct(
        public User $user,
        public string $code,
        public string $action,
        public string $currency,
        public string $address = '',
        public ?string $previousAddress = null,
    ) {
        $this->forceEnglishLocale();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('coin.profile.payout_address_verify_mail_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payout-address-verification',
            with: [
                'userName' => $this->user->name,
                'code' => $this->code,
                'action' => $this->action,
                'currency' => $this->currency,
                'address' => $this->address,
                'previousAddress' => $this->previousAddress,
            ],
        );
    }
}
