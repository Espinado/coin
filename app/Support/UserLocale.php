<?php

namespace App\Support;

final class UserLocale
{
    public const LOCALE = 'en';

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function run(callable $callback): mixed
    {
        $previous = app()->getLocale();

        app()->setLocale(self::LOCALE);

        try {
            return $callback();
        } finally {
            app()->setLocale($previous);
        }
    }
}
