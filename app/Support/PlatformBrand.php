<?php

namespace App\Support;

final class PlatformBrand
{
    public static function name(): string
    {
        return (string) config('coin.brand.name', config('app.name', 'CloudFlops'));
    }

    public static function adminName(): string
    {
        return (string) config('coin.brand.admin_name', self::name().' Admin');
    }

    public static function legalName(): string
    {
        return (string) config('coin.brand.legal_name', self::name().' SIA');
    }

    public static function logo(string $variant = 'horizontal'): string
    {
        $path = config("coin.brand.logos.{$variant}", config('coin.brand.logos.horizontal'));

        return asset(ltrim((string) $path, '/'));
    }

    public static function logoUrl(string $variant = 'horizontal'): string
    {
        $path = config("coin.brand.logos.{$variant}", config('coin.brand.logos.horizontal'));
        $base = rtrim((string) config('app.url'), '/');

        return $base.'/'.ltrim((string) $path, '/');
    }

    public static function pageTitle(string $section): string
    {
        return self::name().' — '.$section;
    }

    public static function adminPageTitle(string $section): string
    {
        return self::adminName().' — '.$section;
    }
}
