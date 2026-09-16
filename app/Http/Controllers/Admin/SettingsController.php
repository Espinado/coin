<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RedirectsWithAdminFlash;
use App\Http\Controllers\Controller;
use App\Services\PlatformSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    use RedirectsWithAdminFlash;

    public function edit(PlatformSettingsService $settings): View
    {
        return view('admin.settings.edit', [
            'definitions' => $settings->adminDefinitions(),
            'values' => $settings->all(),
        ]);
    }

    public function update(Request $request, PlatformSettingsService $settings): RedirectResponse
    {
        $definitions = $settings->adminDefinitions();
        $rules = [];

        foreach ($definitions as $key => $definition) {
            if ($definition['type'] === 'boolean') {
                $rules[$key] = ['sometimes', 'boolean'];
            } elseif ($key === 'token_symbol') {
                $rules[$key] = ['required', 'string', 'max:12'];
            } elseif ($key === 'epochs_per_day') {
                $rules[$key] = ['required', 'integer', 'min:1', 'max:24'];
            } elseif ($key === 'btc_per_usdt') {
                $rules[$key] = ['required', 'numeric', 'gt:0'];
            } else {
                $rules[$key] = ['required', 'numeric', 'min:0'];
            }
        }

        $validated = $request->validate($rules);

        foreach ($definitions as $key => $definition) {
            if ($definition['type'] === 'boolean') {
                $validated[$key] = $request->boolean($key);
            }
        }

        $settings->setMany($validated);

        return $this->adminSuccess('coin.admin.flash.settings_saved', 'admin.settings.edit');
    }
}
