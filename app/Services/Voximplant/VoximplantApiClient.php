<?php

namespace App\Services\Voximplant;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class VoximplantApiClient
{
    public function isConfigured(): bool
    {
        return filled(config('voximplant.account_id'))
            && filled(config('voximplant.api_key'));
    }

    /**
     * @return array<string, mixed>
     */
    public function call(string $method, array $params = []): array
    {
        if (! $this->isConfigured()) {
            throw new VoximplantException(__('coin.voximplant.not_configured'));
        }

        $payload = array_merge([
            'account_id' => config('voximplant.account_id'),
            'api_key' => config('voximplant.api_key'),
        ], $params);

        try {
            $response = Http::asForm()
                ->timeout(20)
                ->post(rtrim((string) config('voximplant.api_url'), '/').'/'.trim($method, '/').'/', $payload)
                ->throw();
        } catch (RequestException $exception) {
            throw new VoximplantException(
                $exception->response?->json('error.msg')
                    ?? $exception->response?->body()
                    ?? $exception->getMessage(),
                previous: $exception,
            );
        }

        $body = $response->json();

        if (! is_array($body)) {
            throw new VoximplantException('Unexpected Voximplant API response.');
        }

        if (isset($body['error']['msg'])) {
            throw new VoximplantException((string) $body['error']['msg']);
        }

        return $body;
    }
}
