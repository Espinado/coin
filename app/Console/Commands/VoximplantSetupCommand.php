<?php

namespace App\Console\Commands;

use App\Services\Voximplant\VoximplantApiClient;
use App\Services\Voximplant\VoximplantException;
use App\Services\Voximplant\VoximplantSetupService;
use Illuminate\Console\Command;

class VoximplantSetupCommand extends Command
{
    protected $signature = 'voximplant:setup {--show-env : Print .env lines after provisioning}';

    protected $description = 'Provision CloudFlops Voximplant application, scenario, rule, and SDK user';

    public function handle(VoximplantSetupService $setup, VoximplantApiClient $client): int
    {
        if (! $client->isConfigured()) {
            $this->error('Set VOXIMPLANT_ACCOUNT_ID and VOXIMPLANT_API_KEY in .env first.');

            return self::FAILURE;
        }

        try {
            $account = $client->call('GetAccountInfo');
            $balance = $account['result']['balance'] ?? '?';
            $this->line('Account: '.($account['result']['account_name'] ?? 'unknown')." · balance: {$balance}");

            $result = $setup->provision();
        } catch (VoximplantException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Voximplant provisioning complete.');
        $this->table(['Key', 'Value'], collect($result)->map(fn ($value, $key) => [$key, (string) $value])->values());

        if ($this->option('show-env')) {
            $this->newLine();
            $this->line('Suggested .env values:');
            $this->line('VOXIMPLANT_ENABLED=true');
            $this->line('VOXIMPLANT_ACCOUNT_NAME='.config('voximplant.account_name'));
            $this->line('VOXIMPLANT_APPLICATION_NAME='.$result['application_name']);
            $this->line('VOXIMPLANT_APPLICATION_ID='.$result['application_id']);
            $this->line('VOXIMPLANT_RULE_ID='.$result['rule_id']);
            $this->line('VOXIMPLANT_SDK_USER='.$result['sdk_user']);
            $this->line('VOXIMPLANT_SDK_PASSWORD='.$result['sdk_password']);
            $this->line('VOXIMPLANT_CALLER_ID=');
        }

        return self::SUCCESS;
    }
}
