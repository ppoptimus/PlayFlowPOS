<?php

namespace App\Http\Controllers;

use App\Services\MockDataService;
use Illuminate\Contracts\View\View;

class BookingController extends Controller
{
    private MockDataService $mockDataService;

    public function __construct(MockDataService $mockDataService)
    {
        $this->mockDataService = $mockDataService;
    }

    public function index(): View
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
}
