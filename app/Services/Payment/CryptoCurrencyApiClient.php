<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;

class CryptoCurrencyApiClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {}

    /** @return mixed */
    public function call(string $network, string $method, array $params = []): mixed
    {
        $endpoint = rtrim($this->baseUrl, '/').'/api/'.$network.'/'.$method;

        $response = Http::timeout(30)
            ->acceptJson()
            ->withHeaders([
                'CCAPI-KEY' => $this->apiKey,
            ])
            ->get($endpoint, array_merge($params, [
                'key' => $this->apiKey,
            ]));

        if (! $response->successful()) {
            throw new PaymentGatewayException(
                'ccapi_http_error',
                $response->body(),
            );
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new PaymentGatewayException('ccapi_invalid_response');
        }

        if (isset($payload['error'])) {
            throw new PaymentGatewayException(
                (string) $payload['error'],
                $payload['value'] ?? null,
            );
        }

        return $payload['result'] ?? null;
    }
}
