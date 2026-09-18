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
        return self::logoUrl($variant);
    }

    public static function logoUrl(string $variant = 'horizontal'): string
    {
        $path = ltrim((string) config("coin.brand.logos.{$variant}", config('coin.brand.logos.horizontal')), '/');
        $url = asset($path);
        $version = self::logoVersion($path);

        return $version ? $url.'?v='.$version : $url;
    }

    private static function logoVersion(string $path): ?int
    {
        $fullPath = public_path($path);

        return is_file($fullPath) ? (int) filemtime($fullPath) : null;
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
