<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SupportTicketController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin.domain', 'reject.web.on.admin'])->group(function () {
    Route::redirect('/', '/login');

    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('admin.login');

        Route::post('login', [AuthenticatedSessionController::class, 'store'])
            ->name('admin.login.store');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::get('dashboard', DashboardController::class)
            ->name('admin.dashboard');

        Route::get('support', [SupportTicketController::class, 'index'])
            ->name('admin.support.index');
        Route::get('support/{ticket}', [SupportTicketController::class, 'show'])
            ->name('admin.support.show');
        Route::post('support/{ticket}/reply', [SupportTicketController::class, 'reply'])
            ->name('admin.support.reply');
        Route::patch('support/{ticket}/status', [SupportTicketController::class, 'updateStatus'])
            ->name('admin.support.status');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('admin.logout');
    });
});
