<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\User;
use App\Services\DepositService;
use App\Services\Payment\CryptoCurrencyApiGateway;
use App\Services\Payment\PaymentGatewayException;
use App\Support\PaymentStatusReason;
use Database\Seeders\PlatformSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CryptoCurrencyApiGatewayGiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'coin.payments.driver' => 'ccapi',
            'coin.payments.ccapi.api_key' => 'test-ccapi-key',
            'coin.payments.ccapi.base_url' => 'https://new.cryptocurrencyapi.net',
            'coin.payments.ccapi.ipn_url' => 'https://coin.test/webhooks/ccapi',
            'coin.payments.ccapi.forward_to' => 'TLcvabZXL8sfwux16zqwgdiMzhBgKGNDCy',
            'coin.payments.ccapi.forward_from' => '',
            'coin.deposits.auto_confirm_mock' => false,
        ]);

        $this->seed(PlatformSettingsSeeder::class);

        Http::fake([
            'https://new.cryptocurrencyapi.net/api/trx/.give*' => Http::response([
                'result' => [
                    'address' => 'TDepositAddress123456789012345',
                    'publicKey' => '03abc',
                ],
            ]),
        ]);
    }

    public function test_give_passes_forward_to_and_from_for_usdt_deposits(): void
    {
        $user = User::factory()->create();

        $deposit = app(DepositService::class)->createPending($user, 10, 'USDT', 'ccapi');

        app(CryptoCurrencyApiGateway::class)->createDepositIntent($deposit);

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/api/trx/.give')) {
                return false;
            }

            $query = [];
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return ! array_key_exists('key', $query)
                && ($query['to'] ?? null) === 'TLcvabZXL8sfwux16zqwgdiMzhBgKGNDCy'
                && ($query['from'] ?? null) === 'TLcvabZXL8sfwux16zqwgdiMzhBgKGNDCy'
                && ($query['token'] ?? null) === 'USDT'
                && ($query['statusURL'] ?? null) === 'https://coin.test/webhooks/ccapi'
                && $request->header('CCAPI-KEY')[0] === 'test-ccapi-key';
        });
    }

    public function test_btc_deposit_uses_btc_give_endpoint(): void
    {
        config([
            'coin.exchange_rates.coinmarketcap.enabled' => true,
            'coin.exchange_rates.coinmarketcap.api_key' => 'test-cmc-key',
        ]);

        Http::fake([
            'pro-api.coinmarketcap.com/v3/cryptocurrency/quotes/latest*' => Http::response([
                'data' => [[
                    'symbol' => 'BTC',
                    'quote' => [['symbol' => 'USDT', 'price' => 80000]],
                ]],
            ]),
            'https://new.cryptocurrencyapi.net/api/btc/.give*' => Http::response([
                'result' => [
                    'address' => 'bc1qqhza20mal9tdar863pzrlpjgfx6kdhyfssccpf',
                    'publicKey' => '03abc',
                ],
            ]),
        ]);

        $user = User::factory()->create();

        $deposit = app(DepositService::class)->createPending($user, 0.000125, 'BTC', 'ccapi');

        $this->assertSame('BTC', $deposit->currency);

        $intent = app(CryptoCurrencyApiGateway::class)->createDepositIntent($deposit);

        $this->assertSame('btc', $intent->gatewayNetwork);
        $this->assertSame('bc1qqhza20mal9tdar863pzrlpjgfx6kdhyfssccpf', $intent->paymentAddress);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/btc/.give');
        });
    }

    public function test_gateway_failure_rejects_orphan_deposit_with_user_message(): void
    {
        Http::fake([
            'https://new.cryptocurrencyapi.net/api/trx/.give*' => Http::response([
                'error' => 'from_wrong',
            ], 200),
        ]);

        $user = User::factory()->create();

        try {
            app(DepositService::class)->initiateWithGateway(
                $user,
                10,
                'USDT',
                app(CryptoCurrencyApiGateway::class),
            );
            $this->fail('Expected gateway failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame(__('coin.ccapi_errors.from_wrong'), $exception->getMessage());
            $this->assertInstanceOf(PaymentGatewayException::class, $exception->getPrevious());
        }

        $deposit = Deposit::query()->where('user_id', $user->id)->latest('id')->firstOrFail();

        $this->assertSame(Deposit::STATUS_REJECTED, $deposit->status);
        $this->assertSame(PaymentStatusReason::DEPOSIT_GATEWAY_FAILED, $deposit->status_reason);
        $this->assertNull($deposit->payment_address);
    }

    public function test_give_omits_forward_params_when_forward_address_not_configured(): void
    {
        config(['coin.payments.ccapi.forward_to' => '']);

        $user = User::factory()->create();
        $deposit = app(DepositService::class)->createPending($user, 10, 'USDT', 'ccapi');

        app(CryptoCurrencyApiGateway::class)->createDepositIntent($deposit);

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/api/trx/.give')) {
                return false;
            }

            $query = [];
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return ! array_key_exists('to', $query) && ! array_key_exists('from', $query);
        });
    }
}
