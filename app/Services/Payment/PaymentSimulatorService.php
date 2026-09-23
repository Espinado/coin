<?php

namespace App\Services\Payment;

use App\Models\Deposit;
use App\Models\PaymentWebhookLog;
use App\Models\Withdrawal;
use App\Services\Payment\Dtos\VerifiedIpnEvent;
use RuntimeException;

class PaymentSimulatorService
{
    public function __construct(
        private readonly CcapiIpnPayloadBuilder $payloadBuilder,
        private readonly PaymentIpnService $ipnService,
    ) {}

    public function simulateDepositIpn(Deposit $deposit, ?string $txid = null): PaymentWebhookLog
    {
        $this->assertDepositSimulationAllowed($deposit);

        $event = VerifiedIpnEvent::fromPayload(
            $this->payloadBuilder->forDeposit($deposit, $txid),
        );

        return $this->assertProcessed($this->ipnService->handle($event));
    }

    public function simulateWithdrawalIpn(Withdrawal $withdrawal, ?string $txid = null): PaymentWebhookLog
    {
        $this->assertWithdrawalSimulationAllowed();

        $event = VerifiedIpnEvent::fromPayload(
            $this->payloadBuilder->forWithdrawal($withdrawal, $txid),
        );

        return $this->assertProcessed($this->ipnService->handle($event));
    }

    private function assertDepositSimulationAllowed(Deposit $deposit): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException(__('coin.wallet.payment_simulation_blocked'));
        }

        if ($deposit->method !== 'mock') {
            throw new RuntimeException(__('coin.wallet.payment_simulation_mock_only'));
        }
    }

    private function assertWithdrawalSimulationAllowed(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException(__('coin.wallet.payment_simulation_blocked'));
        }

        if ((string) config('coin.payments.driver', 'mock') !== 'mock') {
            throw new RuntimeException(__('coin.wallet.payment_simulation_mock_only'));
        }
    }

    private function assertProcessed(PaymentWebhookLog $log): PaymentWebhookLog
    {
        $result = (string) ($log->processing_result ?? '');

        if (! str_starts_with($result, PaymentWebhookLog::RESULT_PROCESSED)) {
            throw new RuntimeException($result !== '' ? $result : 'Payment IPN simulation failed.');
        }

        return $log;
    }
}
