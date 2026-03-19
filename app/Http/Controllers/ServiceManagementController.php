<?php

namespace App\Http\Controllers;

use App\Services\ServiceManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceManagementController extends Controller
{
    private ServiceManagementService $serviceManagement;

    public function __construct(ServiceManagementService $serviceManagement)
    {
        $this->serviceManagement = $serviceManagement;
    }

    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $categoryId = $request->has('category_id') && $request->query('category_id') !== ''
            ? (int) $request->query('category_id')
            : null;

        $pageData = $this->serviceManagement->getPageData($search, $categoryId);

        return view('services.index', $pageData);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $this->serviceManagement->createService($request->all());

        return redirect()
            ->route('services.index')
            ->with('success', 'เพิ่มบริการเรียบร้อยแล้ว');
    }

    public function update(Request $request, int $serviceId): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable'],
        ]);

        $this->serviceManagement->updateService($serviceId, $request->all());

        return redirect()
            ->route('services.index')
            ->with('success', 'อัปเดตบริการเรียบร้อยแล้ว');
    }

    public function destroy(int $serviceId): RedirectResponse
    {
        $this->serviceManagement->deleteService($serviceId);

        return redirect()
            ->route('services.index')
            ->with('success', 'ลบบริการเรียบร้อยแล้ว');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->serviceManagement->createCategory($request->all());

        return redirect()
            ->route('services.index')
            ->with('success', 'เพิ่มหมวดหมู่บริการเรียบร้อยแล้ว');
    }

    public function updateCategory(Request $request, int $categoryId): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->serviceManagement->updateCategory($categoryId, $request->all());

        return redirect()
            ->route('services.index')
            ->with('success', 'อัปเดตหมวดหมู่เรียบร้อยแล้ว');
    }

    public function deleteCategory(int $categoryId): RedirectResponse
    {
        $this->serviceManagement->deleteCategory($categoryId);

        return redirect()
            ->route('services.index')
            ->with('success', 'ลบหมวดหมู่บริการเรียบร้อยแล้ว');
    }
}
