<?php

namespace App\Http\Controllers;

use App\Services\MockDataService;
use Illuminate\Contracts\View\View;

class PosController extends Controller
{
    private MockDataService $mockDataService;

    public function __construct(MockDataService $mockDataService)
    {
        $this->mockDataService = $mockDataService;
    }

    public function index(): View
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
}
