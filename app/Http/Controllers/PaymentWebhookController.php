<?php

namespace App\Http\Controllers;

use App\Models\PaymentWebhookLog;
use App\Services\Payment\PaymentGatewayException;
use App\Services\Payment\CryptoCurrencyApiGateway;
use App\Services\Payment\PaymentIpnRetryableException;
use App\Services\Payment\PaymentIpnService;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use PDOException;

class PaymentWebhookController extends Controller
{
    public function handleCcapi(
        Request $request,
        CryptoCurrencyApiGateway $gateway,
        PaymentIpnService $ipnService,
    ): Response {
        try {
            $event = $gateway->verifyIpn($request);
        } catch (PaymentGatewayException $exception) {
            Log::warning('ccapi.webhook.invalid_signature', [
                'client_ip' => $request->ip(),
                'message' => $exception->getMessage(),
            ]);

            try {
                PaymentWebhookLog::query()->create([
                    'gateway' => (string) config('coin.payments.driver', 'mock'),
                    'event_type' => 'invalid',
                    'payload' => $request->json()->all(),
                    'signature_valid' => false,
                    'idempotency_key' => 'invalid:'.sha1($request->getContent()),
                    'processing_result' => PaymentWebhookLog::formatProcessingResult(
                        PaymentWebhookLog::RESULT_FAILED,
                        $exception->getMessage(),
                    ),
                    'processed_at' => now(),
                ]);
            } catch (UniqueConstraintViolationException) {
                // Duplicate replay of the same invalid payload.
            }

            return response('invalid signature', 403);
        }

        try {
            $ipnService->handle($event);
        } catch (PaymentIpnRetryableException|QueryException|PDOException $exception) {
            Log::error('ccapi.webhook.retryable_failure', [
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);

            return response('Service Unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return response('OK', 200);
    }
}
