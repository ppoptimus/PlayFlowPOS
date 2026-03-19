<?php

namespace App\Http\Controllers;

use App\Services\BranchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    private BranchService $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');

        $pageData = $this->branchService->getPageData($request->user(), $search);

        return view('branches.index', $pageData);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $this->branchService->createBranch($request->user(), $payload);

        return redirect()
            ->route('branches.index')
            ->with('success', 'เพิ่มสาขาเรียบร้อยแล้ว');
    }

    public function update(Request $request, int $branchId): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable'],
        ]);

        $this->branchService->updateBranch($request->user(), $branchId, $payload);

        return redirect()
            ->route('branches.index')
            ->with('success', 'อัปเดตสาขาเรียบร้อยแล้ว');
    }

    public function destroy(Request $request, int $branchId): RedirectResponse
    {
        $this->branchService->deleteBranch($request->user(), $branchId);

        return redirect()
            ->route('branches.index')
            ->with('success', 'ลบสาขาเรียบร้อยแล้ว');
    }
}
