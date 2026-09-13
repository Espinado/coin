<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EpochController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WithdrawalController;
use Illuminate\Support\Facades\Broadcast;
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
        Broadcast::routes(['middleware' => ['web', 'broadcast.auth:admin']]);

        Route::get('dashboard', DashboardController::class)
            ->name('admin.dashboard');

        Route::get('users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('admin.users.show');
        Route::patch('users/{user}', [UserController::class, 'update'])->name('admin.users.update');

        Route::get('withdrawals', [WithdrawalController::class, 'index'])->name('admin.withdrawals.index');
        Route::get('withdrawals/{withdrawal}', [WithdrawalController::class, 'show'])->name('admin.withdrawals.show');
        Route::patch('withdrawals/{withdrawal}/status', [WithdrawalController::class, 'updateStatus'])->name('admin.withdrawals.status');

        Route::get('plans', [PlanController::class, 'index'])->name('admin.plans.index');
        Route::get('plans/create', [PlanController::class, 'create'])->name('admin.plans.create');
        Route::post('plans', [PlanController::class, 'store'])->name('admin.plans.store');
        Route::get('plans/{plan}/edit', [PlanController::class, 'edit'])->name('admin.plans.edit');
        Route::patch('plans/{plan}', [PlanController::class, 'update'])->name('admin.plans.update');
        Route::delete('plans/{plan}', [PlanController::class, 'destroy'])->name('admin.plans.destroy');

        Route::get('epochs', [EpochController::class, 'index'])->name('admin.epochs.index');
        Route::get('epochs/{epoch}', [EpochController::class, 'show'])->name('admin.epochs.show');
        Route::post('epochs/run', [EpochController::class, 'run'])->name('admin.epochs.run');

        Route::get('settings', [SettingsController::class, 'edit'])->name('admin.settings.edit');
        Route::patch('settings', [SettingsController::class, 'update'])->name('admin.settings.update');

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
