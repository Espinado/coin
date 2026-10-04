<?php

namespace App\Support;

final class SeoVisibility
{
    /**
     * Public marketing pages may be indexed only in production on an allowlisted host.
     */
    public static function shouldIndexPublicPages(?string $host = null): bool
    {
        if (! app()->environment('production')) {
            return false;
        }

        $hosts = config('coin.seo.indexable_hosts', []);

        if (! is_array($hosts) || $hosts === []) {
            return false;
        }

        $resolved = strtolower(trim((string) ($host ?? request()->getHost())));

        if ($resolved === '') {
            $resolved = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        }

        if ($resolved === '') {
            return false;
        }

        foreach ($hosts as $allowed) {
            if ($resolved === strtolower((string) $allowed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  bool|null  $forceNoindex  true = always noindex; null = auto from environment/host
     */
    public static function robotsMeta(?bool $forceNoindex = null): string
    {
        if ($forceNoindex === true || ! self::shouldIndexPublicPages()) {
            return 'noindex, nofollow';
        }

        return 'index, follow';
    }

    public static function canonicalUrl(?string $url = null): string
    {
        return $url ?: url()->current();
    }
}
