<?php

namespace App\Support;

final class PlanLabels
{
    public static function tier(string $tierLabel): string
    {
        $key = 'coin.plan_tiers.'.$tierLabel;

        return __($key) !== $key ? __($key) : $tierLabel;
    }

    public static function infra(?string $infra): string
    {
        if ($infra === null || $infra === '') {
            return '—';
        }

        $slug = str_replace(' ', '_', strtolower($infra));
        $key = 'coin.plan_infra.'.$slug;

        return __($key) !== $key ? __($key) : $infra;
    }

    public static function name(string $slug, string $fallback): string
    {
        $key = 'coin.plan_names.'.$slug;

        return __($key) !== $key ? __($key) : $fallback;
    }
}
