<?php

namespace App\Support;

final class SeoVisibility
{
    /**
     * Public marketing pages may be indexed only in production on an allowlisted host.
     */
    public static function shouldIndexPublicPages(): bool
    {
        if (! app()->environment('production')) {
            return false;
        }

        $hosts = config('coin.seo.indexable_hosts', []);

        if (! is_array($hosts) || $hosts === []) {
            return false;
        }

        $host = strtolower((string) request()->getHost());

        foreach ($hosts as $allowed) {
            if ($host === strtolower((string) $allowed)) {
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
