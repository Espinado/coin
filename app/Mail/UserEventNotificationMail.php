<?php

namespace App\Mail;

use App\Mail\Concerns\UsesEnglishLocale;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserEventNotificationMail extends Mailable
{
    use Queueable, SerializesModels, UsesEnglishLocale;

    /** @param  list<string>  $lines */
    public function __construct(
        public User $user,
        public string $subjectLine,
        public string $intro,
        public array $lines = [],
        public ?string $footer = null,
    ) {
        $this->forceEnglishLocale();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-notification',
        );
    }
}
