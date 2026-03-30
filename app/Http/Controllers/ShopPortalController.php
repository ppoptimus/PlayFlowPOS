<?php

namespace App\Http\Controllers;

use App\Services\ShopContextService;
use App\Services\ShopService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShopPortalController extends Controller
{
    private ShopService $shopService;
    private ShopContextService $shopContext;

    public function __construct(ShopService $shopService, ShopContextService $shopContext)
    {
        $this->shopService = $shopService;
        $this->shopContext = $shopContext;
    }

    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');

        return view('system.shops.index', $this->shopService->getPageData($request->user(), $search));
    }

    public function enter(Request $request, int $shopId): RedirectResponse
    {
        $this->shopContext->setActiveShop($request->user(), $shopId);

        return redirect()
            ->route('branches.index')
            ->with('success', 'เลือกร้านสำหรับจัดการเรียบร้อยแล้ว');
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'owner_username' => ['required', 'string', 'max:100'],
            'owner_password' => ['required', 'string', 'min:4'],
            'expires_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable'],
        ]);

        $this->shopService->createShop($request->user(), $payload);

        return redirect()
            ->route('system.shops.index')
            ->with('success', 'เพิ่มร้านพร้อมบัญชีเจ้าของร้านเรียบร้อยแล้ว');
    }

    public function update(Request $request, int $shopId): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'expires_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable'],
        ]);

        $this->shopService->updateShop($request->user(), $shopId, $payload);

        return redirect()
            ->route('system.shops.index')
            ->with('success', 'อัปเดตร้านเรียบร้อยแล้ว');
    }

    public function toggle(Request $request, int $shopId): RedirectResponse
    {
        $message = $this->shopService->toggleShopActive($request->user(), $shopId);

        return redirect()
            ->route('system.shops.index')
            ->with('success', $message);
    }

    public function destroy(Request $request, int $shopId): RedirectResponse
    {
        $this->shopService->deleteShop($request->user(), $shopId);

        return redirect()
            ->route('system.shops.index')
            ->with('success', 'ลบร้านและข้อมูลที่เกี่ยวข้องเรียบร้อยแล้ว');
    }
}
