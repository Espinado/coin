<?php

namespace App\Support;

use App\Services\PlatformSettingsService;

final class PlatformBrand
{
    public static function name(): string
    {
        return (string) config('coin.brand.name', config('app.name', 'CudaFlops'));
    }

    public static function adminName(): string
    {
        return (string) config('coin.brand.admin_name', self::name().' Admin');
    }

    /**
     * Legal entity name: admin Legal information → company_name, else config fallback.
     */
    public static function legalName(): string
    {
        try {
            $fromSettings = trim(app(PlatformSettingsService::class)->get('company_name'));
        } catch (\Throwable) {
            $fromSettings = '';
        }

        if ($fromSettings !== '') {
            return $fromSettings;
        }

        return (string) config('coin.brand.legal_name', self::name().' LLC');
    }

    /**
     * Public contact email: admin Legal information → company_email, else config fallback.
     */
    public static function contactEmail(): string
    {
        try {
            $fromSettings = trim(app(PlatformSettingsService::class)->get('company_email'));
        } catch (\Throwable) {
            $fromSettings = '';
        }

        if ($fromSettings !== '') {
            return $fromSettings;
        }

        return trim((string) config('coin.contact_email', ''));
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

    public static function ogImageUrl(): string
    {
        $path = ltrim((string) config('coin.seo.og_image', 'cloudflops/og-default.png'), '/');
        $fullPath = public_path($path);

        if (! is_file($fullPath)) {
            return self::logoUrl('mark');
        }

        $url = asset($path);
        $version = (int) filemtime($fullPath);

        return $url.'?v='.$version;
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
