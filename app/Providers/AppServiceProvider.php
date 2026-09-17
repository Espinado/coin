<?php

namespace App\Providers;

use App\Listeners\LogAuthLockout;
use App\View\Composers\AdminNavComposer;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Event;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('admin.partials.sidebar', AdminNavComposer::class);

        Event::listen(Lockout::class, LogAuthLockout::class);
    }
}
