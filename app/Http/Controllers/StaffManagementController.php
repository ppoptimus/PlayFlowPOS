<?php

namespace App\Http\Controllers;

use App\Services\StaffManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StaffManagementController extends Controller
{
    private StaffManagementService $staffService;

    public function __construct(StaffManagementService $staffService)
    {
        $this->staffService = $staffService;
    }

    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $branchId = $request->has('branch_id') && $request->query('branch_id') !== ''
            ? (int) $request->query('branch_id')
            : null;

        $pageData = $this->staffService->getPageData($request->user(), $search, $branchId);

        return view('staff.index', $pageData);
    }

    public function profile(Request $request): View
    {
        return view('staff.profile', $this->staffService->getMyProfilePageData($request->user()));
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:100'],
            'branch_id' => ['nullable', 'integer'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $this->staffService->createStaff($request->user(), $payload, $request->file('profile_image'));

        return redirect()
            ->route('staff.index')
            ->with('success', 'เพิ่มพนักงานเรียบร้อยแล้ว');
    }

    public function update(Request $request, int $staffId): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:100'],
            'branch_id' => ['nullable', 'integer'],
            'is_active' => ['nullable'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'remove_profile_image' => ['nullable', 'boolean'],
        ]);

        $this->staffService->updateStaff($request->user(), $staffId, $payload, $request->file('profile_image'));

        return redirect()
            ->route('staff.index')
            ->with('success', 'อัปเดตพนักงานเรียบร้อยแล้ว');
    }

    public function destroy(Request $request, int $staffId): RedirectResponse
    {
        $this->staffService->deleteStaff($request->user(), $staffId);

        return redirect()
            ->route('staff.index')
            ->with('success', 'ลบพนักงานเรียบร้อยแล้ว');
    }
}
