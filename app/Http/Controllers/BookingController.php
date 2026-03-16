<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    private BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request): View
    {
        $requestedBranchId = $request->has('branch_id') ? (int) $request->query('branch_id') : null;
        $date = (string) $request->query('date', now()->toDateString());
        $pageData = $this->bookingService->getPageData($request->user(), $requestedBranchId, $date);

        return view('booking.index', $pageData);
    }

    public function data(Request $request): JsonResponse
    {
        $requestedBranchId = $request->has('branch_id') ? (int) $request->query('branch_id') : null;
        $date = (string) $request->query('date', now()->toDateString());

        return response()->json($this->bookingService->getBookingsDataForDate($request->user(), $requestedBranchId, $date));
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $this->validateBookingPayload($request);
        $booking = $this->bookingService->saveBooking(null, $payload, $request->user());

        return response()->json([
            'message' => 'บันทึกคิวสำเร็จ',
            'booking' => $booking,
        ]);
    }

    public function update(Request $request, int $bookingId): JsonResponse
    {
        $payload = $this->validateBookingPayload($request);
        $booking = $this->bookingService->saveBooking($bookingId, $payload, $request->user());

        return response()->json([
            'message' => 'แก้ไขคิวสำเร็จ',
            'booking' => $booking,
        ]);
    }

    public function destroy(Request $request, int $bookingId): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|integer',
        ]);

        $this->bookingService->deleteBooking(
            $bookingId,
            $request->user(),
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null
        );

        return response()->json([
            'message' => 'ลบคิวสำเร็จ',
            'booking_id' => $bookingId,
        ]);
    }

    private function validateBookingPayload(Request $request): array
    {
        return $request->validate([
            'branch_id' => 'nullable|integer',
            'queue_date' => 'required|date_format:Y-m-d',
            'customer_id' => 'required|integer|exists:customers,id',
            'service_id' => 'required|integer|exists:services,id',
            'masseuse_id' => 'nullable|integer|exists:masseuses,id',
            'bed_id' => 'nullable|integer|exists:beds,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:waiting,in_service,cancelled',
            'cancel_reason' => 'nullable|string|max:1000',
        ]);
    }
}
