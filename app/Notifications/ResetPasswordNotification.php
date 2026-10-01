<?php

namespace App\Notifications;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): PasswordResetMail
    {
        /** @var User $notifiable */
        $expireMinutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new PasswordResetMail($notifiable, $url, $expireMinutes))
            ->to($notifiable->getEmailForPasswordReset());
    }

    /**
     * Keep parent signature unused — branded mailable replaces MailMessage.
     *
     * @param  mixed  $notifiable
     */
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)->subject('unused');
    }
}
