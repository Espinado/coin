<?php

use App\Http\Controllers\GuestBroadcastAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralInviteController;
use App\Http\Controllers\ReverbDebugLogController;
use App\Livewire\Dashboard;
use App\Livewire\ProfitHistory;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::domain(config('coin.user_domain'))
    ->middleware('user.domain')
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');

        Route::get('/legal/{legalPage:slug}', [LegalPageController::class, 'show'])
            ->where('legalPage', 'terms|privacy|risks|faq')
            ->name('legal.show');

        Route::get('/r/{code}', ReferralInviteController::class)->name('referral.invite');

        Route::post('/webhooks/ccapi', [PaymentWebhookController::class, 'handleCcapi'])
            ->name('webhooks.ccapi');

        Route::post('/guest/broadcasting/auth', [GuestBroadcastAuthController::class, 'store'])
            ->middleware('web')
            ->name('guest.broadcasting.auth');

        Route::get('/dashboard/profit-history', ProfitHistory::class)->middleware(['auth', 'verified', 'user.not-blocked'])->name('dashboard.profit-history');
        Route::get('/dashboard', Dashboard::class)->middleware(['auth', 'verified', 'user.not-blocked'])->name('dashboard');

        Broadcast::routes(['middleware' => ['web', 'broadcast.auth:web']]);

        Route::middleware(['auth', 'verified', 'user.not-blocked'])->group(function () {
            Route::post('/reverb-debug', [ReverbDebugLogController::class, 'store'])->name('reverb-debug.store');
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });

        require __DIR__.'/auth.php';
    });
