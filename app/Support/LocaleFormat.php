<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final class LocaleFormat
{
    public static function date(CarbonInterface|string|null $value): string
    {
        $date = self::parse($value);

        if (! $date) {
            return '—';
        }

        return $date->locale(app()->getLocale())->isoFormat('D MMM YYYY');
    }

    public static function dateTime(CarbonInterface|string|null $value): string
    {
        $date = self::parse($value);

        if (! $date) {
            return '—';
        }

        return $date->locale(app()->getLocale())->isoFormat('D MMM YYYY · HH:mm');
    }

    public static function shortDateTime(CarbonInterface|string|null $value): string
    {
        $date = self::parse($value);

        if (! $date) {
            return '—';
        }

        return $date->locale(app()->getLocale())->isoFormat('D MMM · HH:mm');
    }

    private static function parse(CarbonInterface|string|null $value): ?Carbon
    {
        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        if (is_string($value) && filled($value)) {
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
