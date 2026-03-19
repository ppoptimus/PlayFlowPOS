<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $typeFilter = (string) $request->query('type', '');
        $categoryId = $request->has('category_id') && $request->query('category_id') !== ''
            ? (int) $request->query('category_id')
            : null;
        $branchId = $request->has('branch_id') && $request->query('branch_id') !== ''
            ? (int) $request->query('branch_id')
            : null;

        $pageData = $this->productService->getPageData($request->user(), $search, $typeFilter, $categoryId, $branchId);

        return view('products.index', $pageData);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:retail,internal'],
            'category_id' => ['nullable', 'integer'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'stock_qty' => ['nullable', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $this->productService->createProduct($request->user(), $payload);

        return redirect()
            ->route('products')
            ->with('success', 'เพิ่มสินค้าเรียบร้อยแล้ว');
    }

    public function update(Request $request, int $productId): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:retail,internal'],
            'category_id' => ['nullable', 'integer'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'stock_qty' => ['nullable', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable'],
        ]);

        $this->productService->updateProduct($request->user(), $productId, $payload);

        return redirect()
            ->route('products')
            ->with('success', 'อัปเดตสินค้าเรียบร้อยแล้ว');
    }

    public function destroy(Request $request, int $productId): RedirectResponse
    {
        $this->productService->deleteProduct($request->user(), $productId);

        return redirect()
            ->route('products')
            ->with('success', 'ลบสินค้าเรียบร้อยแล้ว');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->productService->createCategory($payload);

        return redirect()
            ->route('products')
            ->with('success', 'เพิ่มหมวดหมู่เรียบร้อยแล้ว');
    }

    public function updateCategory(Request $request, int $categoryId): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->productService->updateCategory($categoryId, $payload);

        return redirect()
            ->route('products')
            ->with('success', 'อัปเดตหมวดหมู่เรียบร้อยแล้ว');
    }

    public function deleteCategory(Request $request, int $categoryId): RedirectResponse
    {
        $this->productService->deleteCategory($categoryId);

        return redirect()
            ->route('products')
            ->with('success', 'ลบหมวดหมู่เรียบร้อยแล้ว');
    }

    public function adjustStock(Request $request, int $productId): RedirectResponse
    {
        $payload = $request->validate([
            'adjust_qty' => ['required', 'integer'],
        ]);

        $this->productService->adjustStock($request->user(), $productId, (int) $payload['adjust_qty']);

        return redirect()
            ->route('products')
            ->with('success', 'ปรับสต็อกเรียบร้อยแล้ว');
    }
}
