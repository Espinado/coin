<?php

use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\Admin\Auth\AcceptInvitationController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\RequestPasswordResetController;
use App\Http\Controllers\Admin\Auth\TwoFactorLoginController;
use App\Http\Controllers\ReverbDebugLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepositController;
use App\Http\Controllers\Admin\EpochController;
use App\Http\Controllers\Admin\LegalPageController;
use App\Http\Controllers\Admin\ProfitAccrualController;
use App\Http\Controllers\Admin\PlanChangeRequestController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WithdrawalController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin.domain', 'reject.web.on.admin'])->group(function () {
    Route::redirect('/', '/login');

    Route::any('register', fn () => abort(404));
    Route::any('reset-password/{token?}', fn () => abort(404));

    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('admin.login');

        Route::post('login', [AuthenticatedSessionController::class, 'store'])
            ->name('admin.login.store');

        Route::get('login/two-factor', [TwoFactorLoginController::class, 'create'])
            ->name('admin.login.two-factor');

        Route::post('login/two-factor', [TwoFactorLoginController::class, 'store'])
            ->name('admin.login.two-factor.store');

        Route::post('login/two-factor/resend', [TwoFactorLoginController::class, 'resend'])
            ->middleware('throttle:3,1')
            ->name('admin.login.two-factor.resend');

        Route::get('forgot-password', [RequestPasswordResetController::class, 'create'])
            ->name('admin.password.request');

        Route::post('forgot-password', [RequestPasswordResetController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('admin.password.request.store');

        Route::get('invite/{token}', [AcceptInvitationController::class, 'create'])
            ->name('admin.invite.show');

        Route::post('invite/{token}', [AcceptInvitationController::class, 'store'])
            ->name('admin.invite.store');
    });

    Route::middleware('auth:admin')->group(function () {
        Broadcast::routes(['middleware' => ['web', 'broadcast.auth:admin']]);

        Route::post('reverb-debug', [ReverbDebugLogController::class, 'store'])->name('admin.reverb-debug.store');

        Route::get('dashboard', DashboardController::class)
            ->name('admin.dashboard');

        Route::get('users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('admin.users.show');
        Route::patch('users/{user}', [UserController::class, 'update'])
            ->middleware('admin.ability:manage_users')
            ->name('admin.users.update');

        Route::get('deposits', [DepositController::class, 'index'])->name('admin.deposits.index');
        Route::get('deposits/{deposit}', [DepositController::class, 'show'])->name('admin.deposits.show');
        Route::post('deposits/{deposit}/confirm', [DepositController::class, 'confirm'])
            ->middleware('admin.ability:manage_deposits')
            ->name('admin.deposits.confirm');
        Route::post('deposits/{deposit}/reject', [DepositController::class, 'reject'])
            ->middleware('admin.ability:manage_deposits')
            ->name('admin.deposits.reject');

        Route::get('withdrawals', [WithdrawalController::class, 'index'])->name('admin.withdrawals.index');
        Route::get('withdrawals/{withdrawal}', [WithdrawalController::class, 'show'])->name('admin.withdrawals.show');
        Route::patch('withdrawals/{withdrawal}/status', [WithdrawalController::class, 'updateStatus'])
            ->middleware('admin.ability:manage_withdrawals')
            ->name('admin.withdrawals.status');

        Route::get('plan-changes', [PlanChangeRequestController::class, 'index'])->name('admin.plan-changes.index');
        Route::get('plan-changes/{planChange}', [PlanChangeRequestController::class, 'show'])->name('admin.plan-changes.show');
        Route::post('plan-changes/{planChange}/approve', [PlanChangeRequestController::class, 'approve'])
            ->middleware('admin.ability:manage_plan_changes')
            ->name('admin.plan-changes.approve');
        Route::post('plan-changes/{planChange}/reject', [PlanChangeRequestController::class, 'reject'])
            ->middleware('admin.ability:manage_plan_changes')
            ->name('admin.plan-changes.reject');

        Route::get('plans', [PlanController::class, 'index'])->name('admin.plans.index');
        Route::get('plans/create', [PlanController::class, 'create'])->name('admin.plans.create');
        Route::post('plans', [PlanController::class, 'store'])
            ->middleware('admin.ability:manage_plans')
            ->name('admin.plans.store');
        Route::get('plans/{plan}/edit', [PlanController::class, 'edit'])->name('admin.plans.edit');
        Route::patch('plans/{plan}', [PlanController::class, 'update'])
            ->middleware('admin.ability:manage_plans')
            ->name('admin.plans.update');
        Route::delete('plans/{plan}', [PlanController::class, 'destroy'])
            ->middleware('admin.ability:manage_plans')
            ->name('admin.plans.destroy');

        Route::get('profit-accrual', [ProfitAccrualController::class, 'index'])->name('admin.profit-accrual.index');

        Route::redirect('epochs', '/profit-accrual')->name('admin.epochs.index');
        Route::get('epochs/{epoch}', [EpochController::class, 'show'])->name('admin.epochs.show');

        Route::get('settings', [SettingsController::class, 'edit'])->name('admin.settings.edit');
        Route::patch('settings', [SettingsController::class, 'update'])
            ->middleware('admin.ability:manage_settings')
            ->name('admin.settings.update');
        Route::patch('settings/legal', [SettingsController::class, 'updateLegal'])
            ->middleware('admin.ability:manage_settings')
            ->name('admin.settings.legal.update');

        Route::get('admins', [AdminStaffController::class, 'index'])->name('admin.admins.index');
        Route::get('admins/invite', [AdminStaffController::class, 'create'])->name('admin.admins.invite');
        Route::post('admins/invite', [AdminStaffController::class, 'store'])
            ->middleware('admin.ability:manage_admins')
            ->name('admin.admins.invite.store');
        Route::delete('admins/{admin}', [AdminStaffController::class, 'destroy'])
            ->middleware('admin.ability:manage_admins')
            ->name('admin.admins.destroy');
        Route::post('admins/{admin}/reset-password', [AdminStaffController::class, 'resetPassword'])
            ->middleware('admin.ability:manage_admins')
            ->name('admin.admins.reset-password');
        Route::delete('admins/invitations/{invitation}', [AdminStaffController::class, 'destroyInvitation'])
            ->middleware('admin.ability:manage_admins')
            ->name('admin.admins.invitations.destroy');
        Route::post('admins/invitations/{invitation}/resend', [AdminStaffController::class, 'resendInvitation'])
            ->middleware('admin.ability:manage_admins')
            ->name('admin.admins.invitations.resend');

        Route::get('legal', [LegalPageController::class, 'index'])->name('admin.legal.index');
        Route::get('legal/{legalPage}/edit', [LegalPageController::class, 'edit'])->name('admin.legal.edit');
        Route::patch('legal/{legalPage}', [LegalPageController::class, 'update'])
            ->middleware('admin.ability:manage_legal')
            ->name('admin.legal.update');

        Route::get('broadcasts', [BroadcastController::class, 'index'])->name('admin.broadcasts.index');
        Route::get('broadcasts/create', [BroadcastController::class, 'create'])->name('admin.broadcasts.create');
        Route::post('broadcasts', [BroadcastController::class, 'store'])
            ->middleware('admin.ability:manage_broadcasts')
            ->name('admin.broadcasts.store');
        Route::get('broadcasts/{broadcast}', [BroadcastController::class, 'show'])->name('admin.broadcasts.show');

        Route::get('support', [SupportTicketController::class, 'index'])
            ->name('admin.support.index');
        Route::get('support/unread-count', [SupportTicketController::class, 'unreadCount'])
            ->name('admin.support.unread-count');
        Route::get('support/{ticket}', [SupportTicketController::class, 'show'])
            ->name('admin.support.show');
        Route::post('support/{ticket}/reply', [SupportTicketController::class, 'reply'])
            ->middleware('admin.ability:manage_support')
            ->name('admin.support.reply');
        Route::patch('support/{ticket}/status', [SupportTicketController::class, 'updateStatus'])
            ->middleware('admin.ability:manage_support')
            ->name('admin.support.status');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('admin.logout');
    });
});
