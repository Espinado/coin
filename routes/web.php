<?php

use App\Http\Controllers\GuestBroadcastAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralInviteController;
use App\Http\Controllers\ReverbDebugLogController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SeoHubController;
use App\Http\Controllers\SupportAttachmentController;
use App\Http\Controllers\UserAvatarController;
use App\Livewire\Dashboard;
use App\Livewire\ProfitHistory;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::domain(config('coin.user_domain'))
    ->middleware(['user.domain', 'platform.maintenance', 'record.site.visit'])
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');
        Route::redirect('/about', '/legal/about')->name('about');

        Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
        Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
        Route::get('/llms.txt', [SeoController::class, 'llms'])->name('seo.llms');
        Route::get('/llms-full.txt', [SeoController::class, 'llmsFull'])->name('seo.llms-full');
        Route::get('/{key}.txt', [SeoController::class, 'indexNowKey'])
            ->where('key', '[A-Za-z0-9-]{8,128}')
            ->name('seo.indexnow-key');
        Route::get('/invest', [SeoHubController::class, 'invest'])->name('seo.invest');

        Route::get('/legal/{legalPage:slug}', [LegalPageController::class, 'show'])
            ->where('legalPage', 'about|terms|privacy|risks|faq')
            ->name('legal.show');
        Route::redirect('/legal/invest', '/invest');

        Route::get('/r/{code}', ReferralInviteController::class)->name('referral.invite');

        Route::post('/webhooks/ccapi', [PaymentWebhookController::class, 'handleCcapi'])
            ->middleware(['ccapi.webhook.source', 'throttle:ccapi-webhook'])
            ->name('webhooks.ccapi');

        Route::post('/guest/broadcasting/auth', [GuestBroadcastAuthController::class, 'store'])
            ->middleware('web')
            ->name('guest.broadcasting.auth');

        // Allow guests too — connection monitor posts [ws_state] from the landing chat.
        Route::post('/reverb-debug', [ReverbDebugLogController::class, 'store'])
            ->middleware(['web', 'throttle:60,1'])
            ->name('reverb-debug.store');

        Route::get('/dashboard/profit-history', ProfitHistory::class)->middleware(['auth', 'verified', 'user.not-blocked', 'record.user.login'])->name('dashboard.profit-history');
        Route::get('/dashboard', Dashboard::class)->middleware(['auth', 'verified', 'user.not-blocked', 'record.user.login'])->name('dashboard');

        Broadcast::routes(['middleware' => ['web', 'broadcast.auth:web']]);

        Route::middleware(['auth', 'verified', 'user.not-blocked', 'record.user.login'])->group(function () {
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
            Route::get('/support/attachments/{attachment}', [SupportAttachmentController::class, 'show'])
                ->name('support.attachments.show');
            Route::get('/avatar', [UserAvatarController::class, 'show'])
                ->name('avatar.show');
        });

        require __DIR__.'/auth.php';
    });
