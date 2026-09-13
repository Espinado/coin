<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminOverviewService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AdminOverviewService $overview): View
    {
        return view('admin.dashboard', [
            'admin' => auth('admin')->user(),
            'metrics' => $overview->metrics(),
        ]);
    }
}
