<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MasseuseController extends Controller
{
    private BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request): View
    {
        $requestedBranchId = $request->has('branch_id') ? (int) $request->query('branch_id') : null;
        $selectedDate = (string) $request->query('date', now()->toDateString());

        return view('masseuse.index', $this->bookingService->getStaffPageData(
            $request->user(),
            $requestedBranchId,
            $selectedDate
        ));
    }

    public function updateAttendance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|integer',
            'date' => 'required|date_format:Y-m-d',
            'staff_id' => 'required|integer',
            'is_working' => 'required|boolean',
        ]);

        $requestedBranchId = isset($validated['branch_id']) ? (int) $validated['branch_id'] : null;

        $this->bookingService->updateStaffAttendance(
            $request->user(),
            $requestedBranchId,
            (string) $validated['date'],
            (int) $validated['staff_id'],
            (bool) $validated['is_working']
        );

        return redirect()->route('masseuse', array_filter([
            'branch_id' => $requestedBranchId,
            'date' => (string) $validated['date'],
        ], static function ($value): bool {
            return $value !== null && $value !== '';
        }));
    }
}
