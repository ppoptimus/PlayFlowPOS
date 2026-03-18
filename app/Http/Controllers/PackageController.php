<?php

namespace App\Http\Controllers;

use App\Services\PackageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    private PackageService $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $pageData = $this->packageService->getPageData($search);

        return view('packages.index', $pageData);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'total_qty' => ['required', 'integer', 'min:1'],
            'valid_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $this->packageService->createPackage($payload);

        return redirect()
            ->route('packages')
            ->with('success', 'เพิ่มแพ็กเกจเรียบร้อยแล้ว');
    }

    public function update(Request $request, int $packageId): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'total_qty' => ['required', 'integer', 'min:1'],
            'valid_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $this->packageService->updatePackage($packageId, $payload);

        return redirect()
            ->route('packages')
            ->with('success', 'อัปเดตแพ็กเกจเรียบร้อยแล้ว');
    }
}
