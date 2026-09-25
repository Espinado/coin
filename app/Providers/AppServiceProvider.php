<?php

namespace App\Providers;

use App\Listeners\LogAuthLockout;
use App\Models\PaymentWebhookLog;
use App\Observers\PaymentWebhookLogObserver;
use App\Services\Payment\CcapiIpnVerifier;
use App\Services\PlatformSettingsService;
use App\Services\Payment\CryptoCurrencyApiClient;
use App\Services\Payment\CryptoCurrencyApiGateway;
use App\Services\Payment\MockPaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;
use App\Support\ProductionPaymentConfigGuard;
use App\View\Composers\AdminNavComposer;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CcapiIpnVerifier::class);

        $this->app->singleton(CryptoCurrencyApiClient::class, function () {
            return new CryptoCurrencyApiClient(
                (string) config('coin.payments.ccapi.base_url'),
                (string) config('coin.payments.ccapi.api_key'),
            );
        });

        $this->app->singleton(PaymentGatewayInterface::class, function ($app) {
            $settings = $app->make(PlatformSettingsService::class);

            if (! $settings->paymentGateEnabled()) {
                return $app->make(MockPaymentGateway::class);
            }

            $driver = (string) config('coin.payments.driver', 'mock');

            return match ($driver) {
                'ccapi' => $app->make(CryptoCurrencyApiGateway::class),
                default => $app->make(MockPaymentGateway::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        ProductionPaymentConfigGuard::assertValid();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer(
            ['admin.partials.sidebar', 'admin.partials.finance-tabs'],
            AdminNavComposer::class,
        );

        PaymentWebhookLog::observe(PaymentWebhookLogObserver::class);

        Event::listen(Lockout::class, LogAuthLockout::class);

        RateLimiter::for('ccapi-webhook', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip() ?? 'unknown');
        });
    }
}
