<?php

use App\Http\Controllers\GuestBroadcastAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralInviteController;
use App\Http\Controllers\ReverbDebugLogController;
use App\Http\Controllers\SeoController;
use App\Livewire\Dashboard;
use App\Livewire\ProfitHistory;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::domain(config('coin.user_domain'))
    ->middleware(['user.domain', 'platform.maintenance'])
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');
        Route::redirect('/about', '/legal/about')->name('about');

        Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
        Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
        Route::get('/llms.txt', [SeoController::class, 'llms'])->name('seo.llms');

        Route::get('/legal/{legalPage:slug}', [LegalPageController::class, 'show'])
            ->where('legalPage', 'about|terms|privacy|risks|faq')
            ->name('legal.show');

        Route::get('/r/{code}', ReferralInviteController::class)->name('referral.invite');

        Route::post('/webhooks/ccapi', [PaymentWebhookController::class, 'handleCcapi'])
            ->middleware(['ccapi.webhook.source', 'throttle:ccapi-webhook'])
            ->name('webhooks.ccapi');

        Route::post('/guest/broadcasting/auth', [GuestBroadcastAuthController::class, 'store'])
            ->middleware('web')
            ->name('guest.broadcasting.auth');

        Route::get('/dashboard/profit-history', ProfitHistory::class)->middleware(['auth', 'verified', 'user.not-blocked', 'record.user.login'])->name('dashboard.profit-history');
        Route::get('/dashboard', Dashboard::class)->middleware(['auth', 'verified', 'user.not-blocked', 'record.user.login'])->name('dashboard');

        Broadcast::routes(['middleware' => ['web', 'broadcast.auth:web']]);

        Route::middleware(['auth', 'verified', 'user.not-blocked', 'record.user.login'])->group(function () {
            Route::post('/reverb-debug', [ReverbDebugLogController::class, 'store'])->name('reverb-debug.store');
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });

        require __DIR__.'/auth.php';
    });
