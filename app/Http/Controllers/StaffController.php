<?php

namespace App\Http\Controllers;

use App\Services\MockDataService;
use Illuminate\Contracts\View\View;

class StaffController extends Controller
{
    private MockDataService $mockDataService;

    public function __construct(MockDataService $mockDataService)
    {
        $this->mockDataService = $mockDataService;
    }

    public function index(): View
    {
        return view('staff.index', [
            'staff' => $this->mockDataService->getStaff(),
        ]);
    }
}
