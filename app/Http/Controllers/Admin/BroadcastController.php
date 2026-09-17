<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Admin\Concerns\RedirectsWithAdminFlash;
use App\Http\Controllers\Controller;
use App\Models\PlatformBroadcast;
use App\Services\PlatformBroadcastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BroadcastController extends Controller
{
    use AdminListQuery;
    use RedirectsWithAdminFlash;

    public function index(Request $request): View
    {
        $search = $this->adminSearchTerm($request);

        $query = PlatformBroadcast::query()
            ->with('admin')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            });

        $this->adminApplySort($request, $query, [
            'title' => 'title',
            'recipients' => 'recipients_count',
            'sent' => 'created_at',
        ], 'created_at', 'desc');

        return view('admin.broadcasts.index', [
            'broadcasts' => $this->adminPaginate($query, $request),
            ...$this->adminListState($request),
        ]);
    }

    public function create(): View
    {
        return view('admin.broadcasts.form');
    }

    public function store(Request $request, PlatformBroadcastService $broadcasts): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:10000'],
        ], [], [
            'title' => __('coin.admin.broadcasts.title_field'),
            'body' => __('coin.admin.broadcasts.body_field'),
        ]);

        $broadcast = $broadcasts->send(
            $request->user('admin'),
            $validated['title'],
            $validated['body'],
        );

        return $this->adminSuccess(
            'coin.admin.flash.broadcast_sent',
            'admin.broadcasts.show',
            ['broadcast' => $broadcast->id],
            ['count' => $broadcast->recipients_count],
        );
    }

    public function show(PlatformBroadcast $broadcast): View
    {
        $broadcast->load('admin');

        return view('admin.broadcasts.show', [
            'broadcast' => $broadcast,
        ]);
    }
}
