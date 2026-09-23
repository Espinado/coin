<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use App\Services\Payment\CryptoCurrencyApiClient;
use App\Services\Payment\PaymentGatewayException;
use Illuminate\Console\Command;

class CcapiSyncCommand extends Command
{
    protected $signature = 'coin:ccapi-sync
                            {--deposit= : Refresh CCAPI address params for a pending deposit ID}
                            {--manual-confirm= : Trigger CCAPI manualConfirm for a received deposit (address:amount:txid)}
                            {--check-webhook : POST a dry invalid payload to verify webhook reachability}';

    protected $description = 'Sync CCAPI deposit address params (statusURL, forward) and optional manualConfirm';

    public function handle(CryptoCurrencyApiClient $client): int
    {
        if ($this->option('manual-confirm') && app()->environment('production')) {
            $this->components->error('manual-confirm is disabled in production.');

            return self::FAILURE;
        }

        $apiKey = (string) config('coin.payments.ccapi.api_key');
        $ipnUrl = (string) config('coin.payments.ccapi.ipn_url');

        if ($apiKey === '') {
            $this->components->error('CCAPI_API_KEY is not configured.');

            return self::FAILURE;
        }

        if ($ipnUrl === '') {
            $this->components->error('CCAPI_IPN_URL is not configured.');

            return self::FAILURE;
        }

        $this->components->info('CCAPI IPN URL: '.$ipnUrl);
        $this->line('Cabinet checklist (manual): Settings → statusURL = '.$ipnUrl);
        $this->line('Cabinet checklist (manual): Selective IPN → enable type "in"');
        $this->line('Whitelist CCAPI IP if needed: 168.119.158.209');
        $this->newLine();

        if ($this->option('check-webhook')) {
            return $this->checkWebhook($ipnUrl) ? self::SUCCESS : self::FAILURE;
        }

        if ($manual = $this->option('manual-confirm')) {
            return $this->runManualConfirm($client, (string) $manual);
        }

        $depositId = $this->option('deposit');

        if ($depositId === null) {
            $this->components->warn('Pass --deposit=ID to refresh a pending deposit address in CCAPI.');

            return self::SUCCESS;
        }

        return $this->refreshDepositAddress($client, (int) $depositId);
    }

    private function checkWebhook(string $ipnUrl): bool
    {
        $response = \Illuminate\Support\Facades\Http::timeout(10)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($ipnUrl, []);

        $this->line('Webhook probe: HTTP '.$response->status());

        if ($response->status() === 403) {
            $this->components->info('Webhook reachable (403 invalid signature is expected for empty payload).');

            return true;
        }

        $this->components->error('Unexpected webhook response. Expected HTTP 403 for unsigned payload.');

        return false;
    }

    private function refreshDepositAddress(CryptoCurrencyApiClient $client, int $depositId): int
    {
        $deposit = Deposit::query()->find($depositId);

        if ($deposit === null) {
            $this->components->error("Deposit #{$depositId} not found.");

            return self::FAILURE;
        }

        if ($deposit->gateway_uniq_id === null || $deposit->gateway_uniq_id === '') {
            $deposit->update(['gateway_uniq_id' => Deposit::gatewayUniqId($deposit->id)]);
            $deposit->refresh();
        }

        $network = (string) ($deposit->gateway_network ?: 'trx');
        $uniqId = (string) $deposit->gateway_uniq_id;

        $params = [
            'label' => $uniqId,
            'uniqID' => $uniqId,
            'period' => (int) config('coin.payments.ccapi.deposit_period_minutes', 60),
            'statusURL' => (string) config('coin.payments.ccapi.ipn_url'),
        ];

        if (strtoupper((string) $deposit->currency) !== 'BTC') {
            $params['token'] = strtoupper((string) $deposit->currency);
        }

        $forwardTo = trim((string) config('coin.payments.ccapi.forward_to', ''));

        if ($forwardTo !== '') {
            $params['to'] = $forwardTo;
            $forwardFrom = trim((string) config('coin.payments.ccapi.forward_from', ''));
            $params['from'] = $forwardFrom !== '' ? $forwardFrom : $forwardTo;
        }

        try {
            $result = $client->call($network, '.give', $params);
        } catch (PaymentGatewayException $exception) {
            $this->components->error('CCAPI .give failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        if (! is_array($result) || empty($result['address'])) {
            $this->components->error('CCAPI .give returned an unexpected response.');
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::FAILURE;
        }

        $this->components->info("Refreshed CCAPI address for {$uniqId}");
        $this->line('address: '.(string) $result['address']);
        $this->line('statusURL: '.$params['statusURL']);

        if (isset($params['to'])) {
            $this->line('forward to: '.$params['to']);
            $this->line('forward from: '.$params['from']);
        }

        return self::SUCCESS;
    }

    private function runManualConfirm(CryptoCurrencyApiClient $client, string $spec): int
    {
        $parts = array_map('trim', explode(':', $spec));

        if (count($parts) < 3) {
            $this->components->error('Use --manual-confirm=address:amount:txid[:token]');

            return self::FAILURE;
        }

        [$address, $amount, $txid] = $parts;
        $token = $parts[3] ?? 'USDT';

        try {
            $result = $client->call('trx', '.manualConfirm', [
                'address' => $address,
                'amount' => $amount,
                'txid' => $txid,
                'token' => $token,
            ]);
        } catch (PaymentGatewayException $exception) {
            $this->components->error('CCAPI manualConfirm failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info('manualConfirm result: '.json_encode($result));

        return self::SUCCESS;
    }
}
