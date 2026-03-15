<?php

namespace App\Http\Controllers;

use App\Services\MockDataService;
use Illuminate\Contracts\View\View;

class PlayFlowController extends Controller
{
    private MockDataService $mockDataService;

    public function __construct(MockDataService $mockDataService)
    {
        $this->mockDataService = $mockDataService;
    }

    public function dashboard(): View
    {
        return view('dashboard', [
            'stats' => $this->mockDataService->getDashboardStats(),
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

    public function staff(): View
    {
        return view('staff.index', [
            'staff' => $this->mockDataService->getStaff(),
        ]);
    }
}
