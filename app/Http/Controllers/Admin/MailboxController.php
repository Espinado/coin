<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RedirectsWithAdminFlash;
use App\Http\Controllers\Controller;
use App\Services\Mail\CpanelMailboxService;
use App\Services\Mail\MailboxException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rules\Password;

class MailboxController extends Controller
{
    use RedirectsWithAdminFlash;

    public function index(CpanelMailboxService $mailboxes): View
    {
        $list = [];
        $listError = null;

        try {
            if ($mailboxes->isEnabled()) {
                $list = $mailboxes->mergedList();
            } else {
                $listError = __('coin.admin.mailboxes.disabled');
            }
        } catch (MailboxException $exception) {
            $listError = $exception->getMessage();
        }

        return view('admin.settings.mailboxes.index', [
            'server' => $mailboxes->serverSettings(),
            'mailboxes' => $list,
            'listError' => $listError,
            'enabled' => $mailboxes->isEnabled(),
        ]);
    }

    public function show(string $localPart, CpanelMailboxService $mailboxes): View|RedirectResponse
    {
        try {
            $mailbox = $mailboxes->find($localPart);
        } catch (MailboxException $exception) {
            return $this->adminError('coin.admin.flash.mailbox_error', 'admin.settings.mailboxes.index', [], [
                'message' => $exception->getMessage(),
            ]);
        }

        if ($mailbox === null) {
            try {
                $email = $mailboxes->emailFor($mailboxes->normalizeLocalPart($localPart));
            } catch (MailboxException) {
                $email = $localPart;
            }

            return $this->adminError('coin.admin.flash.mailbox_not_found', 'admin.settings.mailboxes.index', [], [
                'email' => $email,
            ]);
        }

        return view('admin.settings.mailboxes.show', [
            'server' => $mailboxes->serverSettings(),
            'mailbox' => $mailbox,
        ]);
    }

    public function store(Request $request, CpanelMailboxService $mailboxes): RedirectResponse
    {
        $validated = $request->validate([
            'local_part' => ['required', 'string', 'max:64'],
            'password' => ['required', 'string', Password::defaults()],
            'quota_mb' => ['nullable', 'integer', 'min:0', 'max:102400'],
        ], [], [
            'local_part' => __('coin.admin.mailboxes.local_part'),
            'password' => __('coin.admin.mailboxes.password'),
            'quota_mb' => __('coin.admin.mailboxes.quota_mb'),
        ]);

        try {
            $box = $mailboxes->create(
                $validated['local_part'],
                $validated['password'],
                isset($validated['quota_mb']) ? (int) $validated['quota_mb'] : null,
            );
        } catch (MailboxException $exception) {
            return back()
                ->withInput()
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        return $this->adminSuccess('coin.admin.flash.mailbox_created', 'admin.settings.mailboxes.show', [
            'localPart' => $box->local_part,
        ], ['email' => $box->email]);
    }

    public function updatePassword(Request $request, string $localPart, CpanelMailboxService $mailboxes): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', Password::defaults()],
        ], [], [
            'password' => __('coin.admin.mailboxes.password'),
        ]);

        try {
            $box = $mailboxes->changePassword($localPart, $validated['password']);
        } catch (MailboxException $exception) {
            return back()
                ->withInput()
                ->with('status', $exception->getMessage())
                ->with('status_type', 'error');
        }

        return $this->adminSuccess('coin.admin.flash.mailbox_password_updated', 'admin.settings.mailboxes.show', [
            'localPart' => $box->local_part,
        ], ['email' => $box->email]);
    }
}
