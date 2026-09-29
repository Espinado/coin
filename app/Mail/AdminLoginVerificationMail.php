<?php

namespace App\Mail;

use App\Mail\Concerns\UsesEnglishLocale;
use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminLoginVerificationMail extends Mailable
{
    use Queueable, SerializesModels, UsesEnglishLocale;

    public function __construct(
        public Admin $admin,
        public string $code,
    ) {
        $this->forceEnglishLocale();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('coin.admin.auth.two_factor_mail_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-login-verification',
            with: [
                'adminName' => $this->admin->name,
                'code' => $this->code,
            ],
        );
    }
}
