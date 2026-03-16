<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    private DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(): View
    {
        $branchId = request()->has('branch_id') ? (int) request()->query('branch_id') : null;
        $range = (string) request()->query('range', 'today');
        $stats = $this->dashboardService->getDashboardStats($branchId, $range);

        return view('dashboard', [
            'stats' => $stats,
        ]);
    }
}
