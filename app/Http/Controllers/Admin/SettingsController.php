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
            'legalDefinitions' => $settings->legalDefinitions(),
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
            } elseif ($definition['type'] === 'time') {
                $rules[$key] = ['required', 'date_format:H:i'];
            } elseif ($key === 'token_symbol') {
                $rules[$key] = ['required', 'string', 'max:12'];
            } elseif ($key === 'epochs_per_day') {
                $rules[$key] = ['required', 'integer', 'min:1', 'max:24'];
            } elseif (in_array($key, ['btc_per_usdt', 'usdt_per_btc'], true)) {
                $rules[$key] = ['nullable', 'numeric', 'gt:0'];
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

    public function updateLegal(Request $request, PlatformSettingsService $settings): RedirectResponse
    {
        $rules = [
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_legal_address' => ['nullable', 'string', 'max:2000'],
            'company_physical_address' => ['nullable', 'string', 'max:2000'],
            'company_registration_number' => ['nullable', 'string', 'max:100'],
            'company_license_number' => ['nullable', 'string', 'max:100'],
            'company_phone' => ['nullable', 'string', 'max:64'],
            'company_email' => ['nullable', 'email', 'max:255'],
        ];

        $settings->setLegalMany($request->validate($rules));

        return $this->adminSuccess('coin.admin.flash.legal_info_saved', 'admin.settings.edit');
    }
}
