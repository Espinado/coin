<?php

namespace App\Http\Controllers;

use App\Models\PaymentWebhookLog;
use App\Services\Payment\PaymentGatewayException;
use App\Services\Payment\CryptoCurrencyApiGateway;
use App\Services\Payment\PaymentIpnRetryableException;
use App\Services\Payment\PaymentIpnService;
use Illuminate\Database\QueryException;
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
