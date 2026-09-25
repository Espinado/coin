<?php

namespace App\Services\Voximplant;

class VoximplantSetupService
{
    public function __construct(
        private readonly VoximplantApiClient $client,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function provision(): array
    {
        $applicationName = (string) config('voximplant.application_name');
        $scenarioName = (string) config('voximplant.scenario_name');
        $scenarioScript = file_get_contents(base_path('voximplant/outbound-bridge.js'));

        if (! is_string($scenarioScript) || trim($scenarioScript) === '') {
            throw new VoximplantException('Voximplant scenario file is missing.');
        }

        $applications = $this->client->call('GetApplications', [
            'application_name' => $applicationName,
            'with_rules' => true,
            'with_scenarios' => true,
        ]);

        $application = collect($applications['result'] ?? [])->first();

        if ($application === null) {
            $created = $this->client->call('AddApplication', [
                'application_name' => $applicationName,
            ]);

            $applicationId = (int) ($created['application_id'] ?? 0);
        } else {
            $applicationId = (int) ($application['application_id'] ?? 0);
        }

        if ($applicationId <= 0) {
            throw new VoximplantException('Unable to resolve Voximplant application ID.');
        }

        $applications = $this->client->call('GetApplications', [
            'application_id' => $applicationId,
            'with_rules' => true,
            'with_scenarios' => true,
        ]);

        $application = collect($applications['result'] ?? [])->first() ?? [];

        $existingScenario = collect($application['scenarios'] ?? [])
            ->first(fn (array $scenario) => ($scenario['scenario_name'] ?? '') === $scenarioName);

        if ($existingScenario === null) {
            $scenarios = $this->client->call('GetScenarios', [
                'application_id' => $applicationId,
                'scenario_name' => $scenarioName,
            ]);

            $existingScenario = collect($scenarios['result'] ?? [])
                ->first(fn (array $scenario) => ($scenario['scenario_name'] ?? '') === $scenarioName);
        }

        if ($existingScenario !== null) {
            $this->client->call('SetScenarioInfo', [
                'scenario_id' => (int) $existingScenario['scenario_id'],
                'scenario_script' => $scenarioScript,
            ]);

            $scenarioId = (int) $existingScenario['scenario_id'];
        } else {
            $createdScenario = $this->client->call('AddScenario', [
                'application_id' => $applicationId,
                'scenario_name' => $scenarioName,
                'scenario_script' => $scenarioScript,
            ]);

            $scenarioId = (int) ($createdScenario['scenario_id'] ?? 0);
        }

        $existingRule = collect($application['rules'] ?? [])
            ->first(fn (array $rule) => ($rule['rule_name'] ?? '') === 'outbound');

        if ($existingRule !== null) {
            $ruleId = (int) ($existingRule['rule_id'] ?? 0);
        } else {
            $createdRule = $this->client->call('AddRule', [
                'application_id' => $applicationId,
                'rule_name' => 'outbound',
                'rule_pattern' => '.*',
                'scenario_id' => $scenarioId,
            ]);

            $ruleId = (int) ($createdRule['rule_id'] ?? 0);
        }

        if ($ruleId <= 0) {
            throw new VoximplantException('Unable to resolve Voximplant rule ID.');
        }

        $sdkUser = (string) config('voximplant.sdk_user');
        $sdkPassword = (string) config('voximplant.sdk_password');

        if ($sdkPassword === '') {
            $sdkPassword = 'CloudFlops1!';
        }

        $users = $this->client->call('GetUsers', [
            'application_id' => $applicationId,
            'user_name' => $sdkUser,
        ]);

        $existingUser = collect($users['result'] ?? [])->first();

        if ($existingUser === null) {
            $this->client->call('AddUser', [
                'application_id' => $applicationId,
                'user_name' => $sdkUser,
                'user_display_name' => 'CloudFlops Admin Operator',
                'user_password' => $sdkPassword,
                'user_active' => true,
                'parent_accounting' => true,
            ]);
        } else {
            $this->client->call('SetUserInfo', [
                'user_id' => (int) $existingUser['user_id'],
                'user_password' => $sdkPassword,
                'user_active' => true,
            ]);
        }

        return [
            'application_id' => $applicationId,
            'application_name' => $applicationName,
            'scenario_id' => $scenarioId,
            'rule_id' => $ruleId,
            'sdk_user' => $sdkUser,
            'sdk_password' => $sdkPassword,
        ];
    }
}
