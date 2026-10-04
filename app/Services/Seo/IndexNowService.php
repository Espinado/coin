<?php

namespace App\Services\Seo;

use App\Support\PublicSeoUrls;
use App\Support\SeoVisibility;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class IndexNowService
{
    public function key(): string
    {
        return trim((string) config('coin.seo.indexnow_key', ''));
    }

    public function isConfigured(): bool
    {
        $key = $this->key();

        return $key !== '' && (bool) preg_match('/^[A-Za-z0-9-]{8,128}$/', $key);
    }

    public function keyFileUrl(): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('IndexNow key is not configured.');
        }

        return rtrim((string) config('app.url'), '/').'/'.$this->key().'.txt';
    }

    /**
     * @param  list<string>|null  $urls
     * @return array{submitted: int, endpoint: string, status: int}
     */
    public function submit(?array $urls = null): array
    {
        if (! SeoVisibility::shouldIndexPublicPages()) {
            throw new RuntimeException('IndexNow is only allowed on indexable production hosts.');
        }

        if (! $this->isConfigured()) {
            throw new RuntimeException('Set COIN_SEO_INDEXNOW_KEY (8–128 chars: letters, digits, hyphen).');
        }

        $urls = array_values(array_unique(array_filter(
            $urls ?? PublicSeoUrls::absoluteUrls(),
            static fn ($url) => is_string($url) && filter_var($url, FILTER_VALIDATE_URL),
        )));

        if ($urls === []) {
            throw new RuntimeException('No public URLs to submit.');
        }

        $host = parse_url((string) config('app.url'), PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            throw new RuntimeException('APP_URL host is missing.');
        }

        $endpoint = (string) config('coin.seo.indexnow_endpoint', 'https://api.indexnow.org/indexnow');
        $payload = [
            'host' => $host,
            'key' => $this->key(),
            'keyLocation' => $this->keyFileUrl(),
            'urlList' => $urls,
        ];

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(20)
                ->post($endpoint, $payload)
                ->throw();
        } catch (RequestException $exception) {
            Log::warning('indexnow.submit_failed', [
                'status' => $exception->response?->status(),
                'body' => $exception->response?->body(),
                'urls' => count($urls),
            ]);

            throw $exception;
        }

        Log::info('indexnow.submitted', [
            'status' => $response->status(),
            'urls' => count($urls),
            'endpoint' => $endpoint,
        ]);

        return [
            'submitted' => count($urls),
            'endpoint' => $endpoint,
            'status' => $response->status(),
        ];
    }
}
