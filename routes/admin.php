<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\DashboardController;
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

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('admin.logout');
    });
});
