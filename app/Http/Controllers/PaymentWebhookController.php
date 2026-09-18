<?php

namespace App\Http\Controllers;

use App\Models\PaymentWebhookLog;
use App\Services\Payment\PaymentGatewayException;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentIpnService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentWebhookController extends Controller
{
    public function handleCcapi(
        Request $request,
        PaymentGatewayInterface $gateway,
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

        $ipnService->handle($event);

        return response('OK', 200);
    }
}
