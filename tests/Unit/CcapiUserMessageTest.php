<?php

namespace Tests\Unit;

use App\Services\Payment\PaymentGatewayException;
use App\Support\CcapiUserMessage;
use Tests\TestCase;

class CcapiUserMessageTest extends TestCase
{
    public function test_from_wrong_is_translated_for_users(): void
    {
        $message = CcapiUserMessage::fromGatewayException(
            new PaymentGatewayException('from_wrong'),
        );

        $this->assertSame(__('coin.ccapi_errors.from_wrong'), $message);
        $this->assertStringNotContainsString('from_wrong', $message);
    }

    public function test_unknown_codes_fall_back_to_generic_message(): void
    {
        $message = CcapiUserMessage::fromGatewayException(
            new PaymentGatewayException('some_internal_code'),
        );

        $this->assertSame(__('coin.ccapi_errors.generic'), $message);
    }
}
