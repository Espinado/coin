<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WithdrawalVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('coin.payment_modal.payout_verify_mail_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.withdrawal-verification',
            with: [
                'userName' => $this->user->name,
                'code' => $this->code,
            ],
        );
    }
}
