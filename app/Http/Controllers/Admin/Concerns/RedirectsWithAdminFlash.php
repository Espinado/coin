<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\RedirectResponse;

trait RedirectsWithAdminFlash
{
    protected function adminSuccess(string $messageKey, string $route, mixed $parameters = [], array $replace = []): RedirectResponse
    {
        return redirect()
            ->route($route, $parameters)
            ->with('status', __($messageKey, $replace))
            ->with('status_type', 'success');
    }

    protected function adminError(string $messageKey, string $route, mixed $parameters = [], array $replace = []): RedirectResponse
    {
        return redirect()
            ->route($route, $parameters)
            ->with('status', __($messageKey, $replace))
            ->with('status_type', 'error');
    }
}
