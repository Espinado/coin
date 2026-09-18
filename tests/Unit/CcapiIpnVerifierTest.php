<?php

namespace Tests\Unit;

use App\Services\Payment\CcapiIpnVerifier;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CcapiIpnVerifierTest extends TestCase
{
    #[Test]
    public function it_builds_and_verifies_ccapi_ipn_signatures(): void
    {
        $verifier = new CcapiIpnVerifier;
        $apiKey = 'test-api-key-123';

        $payload = [
            'cryptocurrencyapi.net' => 3,
            'chain' => 'tron',
            'currency' => 'TRX',
            'type' => 'in',
            'date' => 1674901742,
            'from' => '',
            'to' => 'TH5Hz9FZUEaqLKHL4C2ZiHjNDXfTmfKT8S',
            'token' => 'USDT',
            'amount' => '1.230000',
            'fee' => '0.000000',
            'txid' => 'f5390aac45d53122661c011d887e5449931671a95ac3ba8feedfce031de11e33',
            'pos' => 0,
            'confirmation' => 100,
            'label' => 'deposit:42',
        ];

        $payload['sign'] = $verifier->sign($payload, $apiKey);

        $this->assertTrue($verifier->verify($payload, $apiKey));
        $this->assertFalse($verifier->verify($payload, 'wrong-key'));
    }
}
