<?php

namespace App\Http\Controllers;

use App\Services\Seo\IndexNowService;
use App\Support\LlmsDocument;
use App\Support\PublicSeoUrls;
use App\Support\SeoVisibility;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /** @var list<string> */
    private const AI_USER_AGENTS = [
        'GPTBot',
        'ChatGPT-User',
        'OAI-SearchBot',
        'ClaudeBot',
        'anthropic-ai',
        'PerplexityBot',
        'Google-Extended',
        'Bytespider',
        'CCBot',
        'cohere-ai',
    ];

    public function indexNowKey(string $key, IndexNowService $indexNow): Response
    {
        if (! $indexNow->isConfigured() || ! hash_equals($indexNow->key(), $key)) {
            abort(404);
        }

        return response($indexNow->key()."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
        ];

        if (! SeoVisibility::shouldIndexPublicPages()) {
            $lines[] = 'Disallow: /';
            $lines[] = '';
            $lines[] = '# AI: '.url('/llms.txt');
            $lines[] = '# AI full: '.url('/llms-full.txt');
            $lines[] = '';

            return response(implode("\n", $lines)."\n", 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        $disallow = [
            '/dashboard',
            '/login',
            '/register',
            '/password',
            '/forgot-password',
            '/reset-password',
            '/verify-email',
            '/confirm-password',
            '/email',
            '/session-expired',
            '/profile',
            '/webhooks',
            '/guest',
            '/reverb-debug',
            '/r/',
        ];

        foreach ($disallow as $path) {
            $lines[] = 'Disallow: '.$path;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.url('/sitemap.xml');
        $lines[] = '# AI: '.url('/llms.txt');
        $lines[] = '# AI full: '.url('/llms-full.txt');
        $lines[] = '';

        foreach (self::AI_USER_AGENTS as $agent) {
            $lines[] = 'User-agent: '.$agent;
            $lines[] = 'Allow: /';
            $lines[] = 'Allow: /llms.txt';
            $lines[] = 'Allow: /llms-full.txt';
            $lines[] = 'Allow: /invest';
            $lines[] = 'Allow: /legal/';
            $lines[] = 'Disallow: /dashboard';
            $lines[] = 'Disallow: /login';
            $lines[] = 'Disallow: /register';
            $lines[] = 'Disallow: /webhooks';
            $lines[] = '';
        }

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(): Response
    {
        if (! SeoVisibility::shouldIndexPublicPages()) {
            $xml = view('seo.sitemap', ['urls' => []])->render();

            return response($xml, 200, [
                'Content-Type' => 'application/xml; charset=UTF-8',
                'X-Robots-Tag' => 'noindex, nofollow',
            ]);
        }

        $xml = view('seo.sitemap', ['urls' => PublicSeoUrls::sitemapEntries()])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function llms(): Response
    {
        return response(LlmsDocument::short()."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function llmsFull(): Response
    {
        return response(LlmsDocument::full()."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
