<?php

namespace App\Http\Controllers;

use App\Services\MassageRoomService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MassageRoomController extends Controller
{
    private MassageRoomService $massageRoomService;

    public function __construct(MassageRoomService $massageRoomService)
    {
        $this->massageRoomService = $massageRoomService;
    }

    public function index(Request $request): View
    {
        $requestedBranchId = $request->has('branch_id') ? (int) $request->query('branch_id') : null;

        return view('massage-rooms.index', $this->massageRoomService->getPageData(
            $request->user(),
            $requestedBranchId
        ));
    }

    public function edit(Request $request, int $roomId): View
    {
        $requestedBranchId = $request->has('branch_id') ? (int) $request->query('branch_id') : null;
        $pageData = $this->massageRoomService->getPageData($request->user(), $requestedBranchId);
        $room = collect($pageData['rooms'] ?? [])->firstWhere('id', $roomId);

        abort_if(!($pageData['moduleReady'] ?? false), 404);
        abort_if(!is_array($room), 404);

        return view('massage-rooms.edit', array_merge($pageData, [
            'room' => $room,
        ]));
    }

    public function storeRoom(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'branch_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
        ]);

        $requestedBranchId = isset($payload['branch_id']) ? (int) $payload['branch_id'] : null;

        $this->massageRoomService->createRoom($request->user(), $requestedBranchId, $payload);

        return $this->redirectToIndex($requestedBranchId)
            ->with('success', 'เพิ่มห้องนวดเรียบร้อยแล้ว');
    }

    public function updateRoom(Request $request, int $roomId): RedirectResponse
    {
        $payload = $request->validate([
            'branch_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
        ]);

        $requestedBranchId = isset($payload['branch_id']) ? (int) $payload['branch_id'] : null;

        $this->massageRoomService->updateRoom($request->user(), $requestedBranchId, $roomId, $payload);

        return $this->redirectToRoomEdit($requestedBranchId, $roomId)
            ->with('success', 'อัปเดตห้องนวดเรียบร้อยแล้ว');
    }

    public function destroyRoom(Request $request, int $roomId): RedirectResponse
    {
        $payload = $request->validate([
            'branch_id' => 'nullable|integer',
        ]);

        $requestedBranchId = isset($payload['branch_id']) ? (int) $payload['branch_id'] : null;

        $this->massageRoomService->deleteRoom($request->user(), $requestedBranchId, $roomId);

        return $this->redirectToIndex($requestedBranchId)
            ->with('success', 'ลบห้องนวดเรียบร้อยแล้ว');
    }

    public function storeBed(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'branch_id' => 'nullable|integer',
            'room_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'status' => 'nullable|string|max:100',
        ]);

        $requestedBranchId = isset($payload['branch_id']) ? (int) $payload['branch_id'] : null;

        $this->massageRoomService->createBed($request->user(), $requestedBranchId, $payload);

        return $this->redirectToRoomEdit($requestedBranchId, (int) $payload['room_id'])
            ->with('success', 'เพิ่มเตียงเรียบร้อยแล้ว');
    }

    public function updateBed(Request $request, int $bedId): RedirectResponse
    {
        $payload = $request->validate([
            'branch_id' => 'nullable|integer',
            'room_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'status' => 'nullable|string|max:100',
        ]);

        $requestedBranchId = isset($payload['branch_id']) ? (int) $payload['branch_id'] : null;

        $this->massageRoomService->updateBed($request->user(), $requestedBranchId, $bedId, $payload);

        return $this->redirectToRoomEdit($requestedBranchId, (int) $payload['room_id'])
            ->with('success', 'อัปเดตเตียงเรียบร้อยแล้ว');
    }

    public function destroyBed(Request $request, int $bedId): RedirectResponse
    {
        $payload = $request->validate([
            'branch_id' => 'nullable|integer',
            'room_id' => 'required|integer',
        ]);

        $requestedBranchId = isset($payload['branch_id']) ? (int) $payload['branch_id'] : null;

        $this->massageRoomService->deleteBed($request->user(), $requestedBranchId, $bedId);

        return $this->redirectToRoomEdit($requestedBranchId, (int) $payload['room_id'])
            ->with('success', 'ลบเตียงเรียบร้อยแล้ว');
    }

    private function redirectToIndex(?int $requestedBranchId): RedirectResponse
    {
        return redirect()->route('massage-rooms', array_filter([
            'branch_id' => $requestedBranchId,
        ], static function ($value): bool {
            return $value !== null && $value !== '';
        }));
    }

    private function redirectToRoomEdit(?int $requestedBranchId, int $roomId): RedirectResponse
    {
        return redirect()->route('massage-rooms.rooms.edit', array_filter([
            'roomId' => $roomId,
            'branch_id' => $requestedBranchId,
        ], static function ($value): bool {
            return $value !== null && $value !== '';
        }));
    }
}
