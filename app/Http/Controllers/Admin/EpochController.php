<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminListQuery;
use App\Http\Controllers\Controller;
use App\Models\Epoch;
use App\Services\EpochService;
use App\Services\PlatformSettingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EpochController extends Controller
{
    use AdminListQuery;

    public function index(Request $request, PlatformSettingsService $settings): View
    {
        $query = Epoch::query()->with('triggeredByAdmin');

        $this->adminApplySort($request, $query, [
            'number' => 'number',
            'contracts_settled' => 'contracts_settled',
            'total_rewards' => 'total_rewards',
            'reward_rate' => 'reward_rate',
            'completed_at' => 'completed_at',
        ], 'number', 'desc');

        return view('admin.epochs.index', [
            'epochs' => $this->adminPaginate($query, $request, 15),
            'currentEpoch' => app(EpochService::class)->currentEpochNumber(),
            'nextEpoch' => app(EpochService::class)->nextEpochNumber(),
            'settings' => [
                'reward_rate' => $settings->rewardRate(),
                'epochs_per_day' => $settings->epochsPerDay(),
                'token_symbol' => $settings->tokenSymbol(),
            ],
            ...$this->adminListState($request, 15),
        ]);
    }

    public function show(Epoch $epoch): View
    {
        $epoch->load(['rewards.user', 'rewards.contract.plan', 'triggeredByAdmin']);

        return view('admin.epochs.show', [
            'epoch' => $epoch,
        ]);
    }
}
