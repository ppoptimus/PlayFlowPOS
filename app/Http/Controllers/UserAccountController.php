<?php

namespace App\Http\Controllers;

use App\Services\UserAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserAccountController extends Controller
{
    private UserAccountService $userService;

    public function __construct(UserAccountService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $branchId = $request->has('branch_id') && $request->query('branch_id') !== ''
            ? (int) $request->query('branch_id')
            : null;

        return view('users.index', $this->userService->getPageData($request->user(), $search, $branchId));
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'staff_id' => ['nullable'],
            'source_type' => ['nullable', 'string'],
            'source_id' => ['nullable'],
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:4'],
            'role' => ['required', 'string'],
            'branch_id' => ['nullable', 'integer'],
        ]);

        $this->userService->createUser($request->user(), $payload);

        return redirect()
            ->route('users.index')
            ->with('success', 'เพิ่มผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function update(Request $request, int $userId): RedirectResponse
    {
        $payload = $request->validate([
            'role' => ['required', 'string'],
            'branch_id' => ['nullable', 'integer'],
            'is_active' => ['nullable'],
        ]);

        $this->userService->updateUser($request->user(), $userId, $payload);

        return redirect()
            ->route('users.index')
            ->with('success', 'อัปเดตผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function resetPassword(Request $request, int $userId): RedirectResponse
    {
        $payload = $request->validate([
            'new_password' => ['required', 'string', 'min:4'],
        ]);

        $this->userService->resetPassword($request->user(), $userId, $payload['new_password']);

        return redirect()
            ->route('users.index')
            ->with('success', 'รีเซ็ตรหัสผ่านเรียบร้อยแล้ว');
    }

    public function destroy(Request $request, int $userId): RedirectResponse
    {
        $currentUserId = (int) ($request->user()->id ?? 0);

        $this->userService->deleteUser($request->user(), $userId, $currentUserId);

        return redirect()
            ->route('users.index')
            ->with('success', 'ลบผู้ใช้งานเรียบร้อยแล้ว');
    }
}
