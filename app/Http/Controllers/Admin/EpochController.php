<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Epoch;
use App\Services\EpochService;
use App\Services\PlatformSettingsService;
use Illuminate\View\View;

class EpochController extends Controller
{
    public function index(PlatformSettingsService $settings): View
    {
        return view('admin.epochs.index', [
            'epochs' => Epoch::query()->with('triggeredByAdmin')->latest('number')->paginate(15),
            'currentEpoch' => app(EpochService::class)->currentEpochNumber(),
            'nextEpoch' => app(EpochService::class)->nextEpochNumber(),
            'settings' => [
                'reward_rate' => $settings->rewardRate(),
                'epochs_per_day' => $settings->epochsPerDay(),
                'token_symbol' => $settings->tokenSymbol(),
            ],
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
