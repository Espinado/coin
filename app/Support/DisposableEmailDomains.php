<?php

namespace App\Support;

use Illuminate\Support\Str;

final class DisposableEmailDomains
{
    /**
     * Domains blocked at registration (temp/spam inboxes + seen bot domains).
     *
     * @var list<string>
     */
    private const DOMAINS = [
        // Seen on CudaFlops prod
        'mx-mailsrv.com',
        'abowned.com',
        'duidir.com',
        // Common disposable / throwaway
        'mailinator.com',
        'guerrillamail.com',
        'guerrillamail.net',
        'sharklasers.com',
        'grr.la',
        'guerrillamailblock.com',
        'pokemail.net',
        'spam4.me',
        'yopmail.com',
        'yopmail.fr',
        'trashmail.com',
        'trashmail.me',
        'trashmail.net',
        'tempmail.com',
        'temp-mail.org',
        'temp-mail.io',
        '10minutemail.com',
        '10minutemail.net',
        'minuteinbox.com',
        'fakeinbox.com',
        'discard.email',
        'discardmail.com',
        'mailnesia.com',
        'maildrop.cc',
        'getnada.com',
        'nada.ltd',
        'emailondeck.com',
        'moakt.com',
        'throwawaymail.com',
        'tmpmail.org',
        'tmpmail.net',
        'tmpbox.net',
        'dispostable.com',
        'mailcatch.com',
        'mytemp.email',
        'tempail.com',
        'tempr.email',
        'emailfake.com',
        'fake-mail.net',
        'armyspy.com',
        'cuvox.de',
        'dayrep.com',
        'einrot.com',
        'fleckens.hu',
        'gustr.com',
        'jourrapide.com',
        'rhyta.com',
        'superrito.com',
        'teleworm.us',
    ];

    public static function isBlocked(?string $email): bool
    {
        $email = Str::lower(trim((string) $email));
        $at = strrpos($email, '@');

        if ($at === false) {
            return false;
        }

        $domain = substr($email, $at + 1);

        if ($domain === '' || ! str_contains($domain, '.')) {
            return false;
        }

        foreach (self::DOMAINS as $blocked) {
            if ($domain === $blocked || str_ends_with($domain, '.'.$blocked)) {
                return true;
            }
        }

        return false;
    }
}
