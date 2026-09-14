<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());

        $users = User::query()
            ->withCount(['contracts', 'supportTickets', 'withdrawals'])
            ->with('wallet')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('account_slug', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'kycStatuses' => User::kycStatuses(),
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

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', 'User updated.');
    }
}
