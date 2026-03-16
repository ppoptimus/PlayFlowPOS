<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\MockDataService;
use Illuminate\Contracts\View\View;

class PlayFlowController extends Controller
{
    private DashboardService $dashboardService;
    private MockDataService $mockDataService;

    public function __construct(
        DashboardService $dashboardService,
        MockDataService $mockDataService
    )
    {
        $this->dashboardService = $dashboardService;
        $this->mockDataService = $mockDataService;
    }

    public function dashboard(): View
    {
        $branchId = request()->has('branch_id') ? (int) request()->query('branch_id') : null;
        $range = (string) request()->query('range', 'today');
        $stats = $this->dashboardService->getDashboardStats($branchId, $range);

        return view('dashboard', [
            'stats' => $stats,
        ]);
    }

    public function pos(): View
    {
        $items = $this->mockDataService->getPosItems();

        return view('pos.index', [
            'items' => $items,
            'serviceItems' => array_values(array_filter($items, static function ($item) {
                return $item['type'] === 'service';
            })),
            'staff' => $this->mockDataService->getStaff(),
            'customers' => $this->mockDataService->getCustomers(),
        ]);
    }

    public function booking(): View
    {
        $items = $this->mockDataService->getPosItems();

        return view('booking.index', [
            'staff' => $this->mockDataService->getStaff(),
            'items' => $items,
            'serviceItems' => array_values(array_filter($items, static function ($item) {
                return $item['type'] === 'service';
            })),
            'rooms' => $this->mockDataService->getRooms(),
            'customers' => $this->mockDataService->getCustomers(),
            'statuses' => $this->mockDataService->getQueueStatuses(),
        ]);
    }

    public function comingSoon(): View
    {
        $moduleName = (string) request()->segment(1);

        return view('coming-soon', [
            'module' => ucfirst($moduleName),
        ]);
    }

    public function staff(): View
    {
        return view('staff.index', [
            'staff' => $this->mockDataService->getStaff(),
        ]);
    }
}
