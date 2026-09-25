<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$client = app(App\Services\Voximplant\VoximplantApiClient::class);

$history = $client->call('GetCallHistory', [
    'count' => 8,
    'with_calls' => true,
    'desc_order' => true,
]);

echo json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
