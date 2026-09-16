<?php

namespace App\Mail;

use App\Models\Admin;
use App\Models\AdminInvitation;
use App\Services\AdminInvitationService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AdminInvitation $invitation,
        public Admin $inviter,
        public string $plainToken,
    ) {}

    public function envelope(): Envelope
    {
        $subjectKey = $this->invitation->isPasswordReset()
            ? 'coin.admin.admins.reset_mail_subject'
            : 'coin.admin.admins.invite_mail_subject';

        return new Envelope(
            subject: __($subjectKey),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-invitation',
            with: [
                'inviterName' => $this->inviter->name,
                'inviteUrl' => app(AdminInvitationService::class)->inviteUrl($this->plainToken),
                'expiresAt' => $this->invitation->expires_at,
                'email' => $this->invitation->email,
                'isPasswordReset' => $this->invitation->isPasswordReset(),
            ],
        );
    }
}
