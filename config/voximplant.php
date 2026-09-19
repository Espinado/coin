<?php

return [

    'enabled' => (bool) env('VOXIMPLANT_ENABLED', false),

    'account_id' => env('VOXIMPLANT_ACCOUNT_ID'),

    'api_key' => env('VOXIMPLANT_API_KEY'),

    'account_name' => env('VOXIMPLANT_ACCOUNT_NAME', 'arguss'),

    'application_name' => env('VOXIMPLANT_APPLICATION_NAME', 'cloudflops'),

    'application_id' => env('VOXIMPLANT_APPLICATION_ID'),

    'rule_id' => env('VOXIMPLANT_RULE_ID'),

    'scenario_name' => env('VOXIMPLANT_SCENARIO_NAME', 'outbound_bridge'),

    'sdk_user' => env('VOXIMPLANT_SDK_USER', 'admin-operator'),

    'sdk_password' => env('VOXIMPLANT_SDK_PASSWORD'),

    'caller_id' => env('VOXIMPLANT_CALLER_ID'),

    'api_url' => env('VOXIMPLANT_API_URL', 'https://api.voximplant.com/platform_api'),

];
