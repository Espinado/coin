<?php

namespace App\Mail;

use App\Mail\Concerns\UsesEnglishLocale;
use App\Models\ReferralProfile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReferralInvitationMail extends Mailable
{
    use Queueable, SerializesModels, UsesEnglishLocale;

    public function __construct(
        public User $referrer,
        public ReferralProfile $profile,
    ) {
        $this->forceEnglishLocale();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('coin.referrals.invite_mail_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.referral-invitation',
            with: [
                'referrerName' => $this->referrer->name,
                'shareUrl' => $this->profile->shareUrl(),
                'commissionLabel' => $this->profile->commissionLabel(),
            ],
        );
    }
}
