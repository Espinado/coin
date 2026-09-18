<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Admin\Concerns\RedirectsWithAdminFlash;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Models\Wallet;
use App\Services\PlatformBroadcastService;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    use AdminListQuery;
    use RedirectsWithAdminFlash;

    public function index(Request $request): View
    {
        $search = $this->adminSearchTerm($request);

        $query = User::query()
            ->withCount(['contracts', 'supportTickets', 'withdrawals'])
            ->with('wallet')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('account_slug', 'like', "%{$search}%");
                });
            });

        $this->adminApplySort($request, $query, [
            'account' => 'account_slug',
            'email' => 'email',
            'kyc' => 'kyc_status',
            'contracts' => 'contracts_count',
            'created_at' => 'created_at',
            'last_login' => 'last_login_at',
        ], 'created_at', 'desc', [
            'balance' => fn ($userQuery, $direction) => $userQuery->orderBy(
                Wallet::query()
                    ->select('balance')
                    ->whereColumn('wallets.user_id', 'users.id')
                    ->limit(1),
                $direction,
            ),
        ]);

        return view('admin.users.index', [
            'users' => $this->adminPaginate($query, $request),
            'kycStatuses' => User::kycStatuses(),
            ...$this->adminListState($request),
        ]);
    }

    public function show(User $user): View
    {
        $user->load([
            'wallet',
            'contracts.plan',
            'walletTransactions',
            'referralProfile',
            'referrer',
            'directReferrals',
            'referralCommissionsEarned.referral',
            'referralCommissionsEarned.contract.plan',
            'deposits' => fn ($query) => $query->latest()->limit(10),
            'supportTickets',
            'withdrawals' => fn ($query) => $query->latest()->limit(10),
        ]);

        return view('admin.users.show', [
            'user' => $user,
            'kycStatuses' => User::kycStatuses(),
            'referralVolume' => (float) $user->referralCommissionsEarned->sum('purchase_amount'),
            'referralEarnings' => (float) $user->referralCommissionsEarned->sum('commission_amount'),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'kyc_status' => ['required', 'in:'.implode(',', array_keys(User::kycStatuses()))],
            'is_blocked' => ['required', 'boolean'],
            'admin_lead_note' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:32'],
            'telegram' => ['nullable', 'string', 'max:64'],
            'country_code' => ['nullable', 'string', 'size:2'],
        ]);

        $user->update($validated);

        return $this->adminSuccess('coin.admin.flash.user_updated', 'admin.users.index');
    }

    public function sendNotification(
        Request $request,
        User $user,
        PlatformBroadcastService $broadcasts,
        UserNotificationService $notifications,
    ): RedirectResponse {
        $validated = $request->validate([
            'notification_title' => ['required', 'string', 'max:160'],
            'notification_body' => ['required', 'string', 'max:10000'],
        ], [], [
            'notification_title' => __('coin.admin.user_notification.title_field'),
            'notification_body' => __('coin.admin.user_notification.body_field'),
        ]);

        /** @var Admin $admin */
        $admin = $request->user('admin');

        $broadcasts->sendToUser(
            $admin,
            $user,
            $validated['notification_title'],
            $validated['notification_body'],
        );

        $notifications->notifyInAppMessageReceived($user, $validated['notification_title']);

        return $this->adminSuccess('coin.admin.flash.user_notification_sent', 'admin.users.show', $user);
    }
}
