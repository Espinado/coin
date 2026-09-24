<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use App\Models\PaymentWebhookLog;
use App\Models\Withdrawal;
use App\Services\Payment\CcapiIpnPayloadBuilder;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class SimulateCcapiIpnCommand extends Command
{
    protected $signature = 'coin:simulate-ccapi-ipn
                            {--deposit= : Pending deposit ID to confirm via simulated IPN}
                            {--withdrawal= : Processing withdrawal reference to mark paid}
                            {--amount= : Override IPN amount}
                            {--txid= : Override transaction hash}
                            {--confirmation= : Confirmation level (default: 1 for deposits, 7 for withdrawals)}
                            {--via=internal : Delivery: internal sub-request or http external POST}
                            {--url= : Webhook URL for --via=http (defaults to CCAPI_IPN_URL)}
                            {--dry-run : Print payload only, do not dispatch}
                            {--allow-live-target : Allow simulating IPN for ccapi-method deposits (local/testing only)}
                            {--force : Ignored; kept for backward compatibility}';

    protected $description = 'Simulate a signed CryptoCurrencyAPI IPN for local webhook testing';

    public function handle(CcapiIpnPayloadBuilder $builder): int
    {
        if (! $this->allowedEnvironment()) {
            $this->components->error(
                app()->environment('production')
                    ? 'This command is disabled in production.'
                    : 'This command is restricted to local/testing environments.',
            );

            return self::FAILURE;
        }

        $depositId = $this->option('deposit');
        $withdrawalRef = $this->option('withdrawal');

        if (($depositId && $withdrawalRef) || (! $depositId && ! $withdrawalRef)) {
            $this->components->error('Specify exactly one target: --deposit=ID or --withdrawal=REFERENCE.');
            $this->showPendingTargets();

            return self::FAILURE;
        }

        if ($depositId) {
            return $this->simulateDeposit($builder, (int) $depositId);
        }

        return $this->simulateWithdrawal($builder, (string) $withdrawalRef);
    }

    private function simulateDeposit(CcapiIpnPayloadBuilder $builder, int $depositId): int
    {
        $deposit = Deposit::query()->find($depositId);

        if ($deposit === null) {
            $this->components->error("Deposit #{$depositId} not found.");

            return self::FAILURE;
        }

        if ($deposit->status !== Deposit::STATUS_PENDING) {
            $this->components->warn("Deposit #{$depositId} status is \"{$deposit->status}\", expected pending.");
        }

        if ($deposit->method === 'ccapi' && ! $this->option('allow-live-target')) {
            $this->components->error(
                'Simulating IPN for ccapi-method deposits is blocked. Use a mock deposit or pass --allow-live-target.',
            );

            return self::FAILURE;
        }

        $confirmation = (int) ($this->option('confirmation') ?? config('coin.payments.ccapi.min_confirmations', 1));
        $amount = $this->option('amount') !== null ? (float) $this->option('amount') : null;
        $payload = $builder->forDeposit(
            $deposit,
            $this->option('txid'),
            $confirmation,
            $amount,
        );

        $this->printPayloadSummary('deposit', (string) $deposit->id, $payload);

        if ($this->option('dry-run')) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        return $this->dispatchPayload($payload, $deposit->id, null);
    }

    private function simulateWithdrawal(CcapiIpnPayloadBuilder $builder, string $reference): int
    {
        $withdrawal = Withdrawal::query()->where('reference', $reference)->first();

        if ($withdrawal === null) {
            $this->components->error("Withdrawal \"{$reference}\" not found.");

            return self::FAILURE;
        }

        if ($withdrawal->status !== Withdrawal::STATUS_PROCESSING) {
            $this->components->warn("Withdrawal \"{$reference}\" status is \"{$withdrawal->status}\", expected processing.");
        }

        $confirmation = (int) ($this->option('confirmation') ?? 7);
        $amount = $this->option('amount') !== null ? (float) $this->option('amount') : null;
        $payload = $builder->forWithdrawal(
            $withdrawal,
            $this->option('txid'),
            $confirmation,
            $amount,
        );

        $this->printPayloadSummary('withdrawal', $withdrawal->reference, $payload);

        if ($this->option('dry-run')) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        return $this->dispatchPayload($payload, null, $withdrawal->id);
    }

    /** @param array<string, mixed> $payload */
    private function dispatchPayload(array $payload, ?int $depositId, ?int $withdrawalId): int
    {
        $via = (string) $this->option('via');

        try {
            $response = match ($via) {
                'http' => $this->dispatchViaHttp($payload),
                'internal' => $this->dispatchViaInternalRequest($payload),
                default => null,
            };
        } catch (\Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($response === null) {
            $this->components->error('Invalid --via value. Use "internal" or "http".');

            return self::FAILURE;
        }

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $this->components->error('Webhook returned HTTP '.$response->getStatusCode().': '.$response->getContent());

            return self::FAILURE;
        }

        $log = PaymentWebhookLog::query()
            ->when($depositId, fn ($query) => $query->where('deposit_id', $depositId))
            ->when($withdrawalId, fn ($query) => $query->where('withdrawal_id', $withdrawalId))
            ->latest('id')
            ->first();

        $this->components->info('Simulated IPN accepted (HTTP 200).');

        if ($log !== null) {
            $this->line('Webhook log #'.$log->id.': '.$log->processing_result);
        }

        if ($depositId) {
            $deposit = Deposit::query()->find($depositId);
            $this->line('Deposit status: '.($deposit?->status ?? 'unknown'));
        }

        if ($withdrawalId) {
            $withdrawal = Withdrawal::query()->find($withdrawalId);
            $this->line('Withdrawal status: '.($withdrawal?->status ?? 'unknown'));
        }

        return self::SUCCESS;
    }

    /** @param array<string, mixed> $payload */
    private function dispatchViaInternalRequest(array $payload): Response
    {
        $host = (string) config('coin.user_domain', parse_url((string) config('app.url'), PHP_URL_HOST));

        $request = Request::create(
            '/webhooks/ccapi',
            'POST',
            server: ['HTTP_HOST' => $host],
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('Accept', 'application/json');

        return app()->handle($request);
    }

    /** @param array<string, mixed> $payload */
    private function dispatchViaHttp(array $payload): Response
    {
        $url = (string) ($this->option('url') ?: config('coin.payments.ccapi.ipn_url'));

        if ($url === '') {
            throw new \RuntimeException('Webhook URL is empty. Set CCAPI_IPN_URL or pass --url=');
        }

        $httpResponse = Http::timeout(15)
            ->acceptJson()
            ->asJson()
            ->post($url, $payload);

        return new Response($httpResponse->body(), $httpResponse->status(), ['Content-Type' => 'text/plain']);
    }

    /** @param array<string, mixed> $payload */
    private function printPayloadSummary(string $targetType, string $targetId, array $payload): void
    {
        $this->components->info(sprintf(
            'Simulating CCAPI IPN (%s) for %s %s',
            (string) ($payload['type'] ?? '?'),
            $targetType,
            $targetId,
        ));

        $this->line('txid: '.($payload['txid'] ?? '—'));
        $this->line('amount: '.($payload['amount'] ?? '—'));
        $this->line('confirmation: '.($payload['confirmation'] ?? '—'));
        $this->line('label: '.($payload['label'] ?? '—'));
    }

    private function showPendingTargets(): void
    {
        $deposits = Deposit::query()
            ->where('status', Deposit::STATUS_PENDING)
            ->latest('id')
            ->limit(5)
            ->get(['id', 'amount', 'currency', 'user_id']);

        if ($deposits->isNotEmpty()) {
            $this->newLine();
            $this->line('Recent pending deposits:');
            foreach ($deposits as $deposit) {
                $this->line("  #{$deposit->id} — {$deposit->amount} {$deposit->currency} (user {$deposit->user_id})");
            }
        }

        $withdrawals = Withdrawal::query()
            ->where('status', Withdrawal::STATUS_PROCESSING)
            ->latest('id')
            ->limit(5)
            ->get(['reference', 'amount', 'currency']);

        if ($withdrawals->isNotEmpty()) {
            $this->newLine();
            $this->line('Processing withdrawals:');
            foreach ($withdrawals as $withdrawal) {
                $this->line("  {$withdrawal->reference} — {$withdrawal->amount} {$withdrawal->currency}");
            }
        }
    }

    private function allowedEnvironment(): bool
    {
        if ($this->option('force')) {
            $this->warn('--force is ignored; command runs only in local/testing.');
        }

        return app()->environment(['local', 'testing']);
    }
}
