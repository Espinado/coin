<?php

namespace App\Support;

use App\Services\Payment\PaymentGatewayException;
use Throwable;

final class CcapiUserMessage
{
    /** @var array<string, string> */
    private const CODE_MAP = [
        'from_wrong' => 'coin.ccapi_errors.from_wrong',
        'to_wrong' => 'coin.ccapi_errors.to_wrong',
        'amount_wrong' => 'coin.ccapi_errors.amount_wrong',
        'period_wrong' => 'coin.ccapi_errors.period_wrong',
        'token_wrong' => 'coin.ccapi_errors.token_wrong',
        'ccapi_http_error' => 'coin.ccapi_errors.http_error',
        'ccapi_invalid_response' => 'coin.ccapi_errors.invalid_response',
        'ccapi_give_invalid_response' => 'coin.ccapi_errors.give_invalid_response',
        'ccapi_unsupported_currency' => 'coin.ccapi_errors.unsupported_currency',
    ];

    public static function fromThrowable(Throwable $exception): string
    {
        if ($exception instanceof PaymentGatewayException) {
            return self::fromGatewayException($exception);
        }

        $previous = $exception->getPrevious();

        if ($previous instanceof PaymentGatewayException) {
            return self::fromGatewayException($previous);
        }

        $message = trim($exception->getMessage());

        if ($message !== '' && ! self::looksLikeRawGatewayCode($message)) {
            return $message;
        }

        return __('coin.ccapi_errors.generic');
    }

    public static function fromGatewayException(PaymentGatewayException $exception): string
    {
        $code = trim($exception->getMessage());
        $translationKey = self::CODE_MAP[$code] ?? null;

        if ($translationKey !== null && trans()->has($translationKey)) {
            return __($translationKey);
        }

        if ($code !== '' && ! self::looksLikeRawGatewayCode($code)) {
            return $code;
        }

        return __('coin.ccapi_errors.generic');
    }

    private static function looksLikeRawGatewayCode(string $message): bool
    {
        return preg_match('/^[a-z0-9_]+$/', $message) === 1;
    }
}
