<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ProductService
{
    private BranchContextService $branchContext;
    private array $tableExistsCache = [];
    private array $columnExistsCache = [];

    public function __construct(BranchContextService $branchContext)
    {
        $this->branchContext = $branchContext;
    }

    public function getPageData(User $user, string $search = '', string $typeFilter = '', ?int $categoryId = null, ?int $requestedBranchId = null): array
    {
        if (!$this->tableExists('products')) {
            return [
                'moduleReady' => false,
                'search' => trim($search),
                'typeFilter' => $typeFilter,
                'categoryFilter' => $categoryId,
                'products' => [],
                'categories' => [],
                'lowStockProducts' => [],
                'activeBranchId' => $this->branchContext->resolveAuthorizedBranchId($user, $requestedBranchId),
            ];
        }

        $branchId = $this->branchContext->resolveAuthorizedBranchId($user, $requestedBranchId);
        $normalizedSearch = trim($search);

        $query = DB::table('products')
            ->orderBy('id');

        if ($this->hasColumn('products', 'branch_id')) {
            $query->where('branch_id', $branchId);
        }

        if ($normalizedSearch !== '' && $this->hasColumn('products', 'name')) {
            $query->where(function ($q) use ($normalizedSearch): void {
                $q->where('name', 'like', '%' . $normalizedSearch . '%');
                if ($this->hasColumn('products', 'sku')) {
                    $q->orWhere('sku', 'like', '%' . $normalizedSearch . '%');
                }
                if ($this->hasColumn('products', 'barcode')) {
                    $q->orWhere('barcode', 'like', '%' . $normalizedSearch . '%');
                }
            });
        }

        if ($typeFilter !== '' && in_array($typeFilter, ['retail', 'internal'], true)) {
            $query->where('type', $typeFilter);
        }

        if ($categoryId !== null && $categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        $products = $query
            ->get([
                'id', 'name', 'sku', 'barcode', 'type',
                'category_id', 'cost_price', 'sell_price',
                'stock_qty', 'min_stock', 'is_active',
            ])
            ->map(static function ($row): array {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) ($row->name ?? ''),
                    'sku' => (string) ($row->sku ?? ''),
                    'barcode' => (string) ($row->barcode ?? ''),
                    'type' => (string) ($row->type ?? 'retail'),
                    'category_id' => $row->category_id !== null ? (int) $row->category_id : null,
                    'cost_price' => (float) ($row->cost_price ?? 0),
                    'sell_price' => (float) ($row->sell_price ?? 0),
                    'stock_qty' => (int) ($row->stock_qty ?? 0),
                    'min_stock' => (int) ($row->min_stock ?? 0),
                    'is_active' => (bool) ($row->is_active ?? true),
                ];
            })
            ->all();

        $categories = $this->getCategories();

        $lowStockProducts = [];
        if ($this->hasColumn('products', 'min_stock')) {
            $lowStockQuery = DB::table('products')
                ->whereColumn('stock_qty', '<', DB::raw('min_stock'))
                ->where('min_stock', '>', 0)
                ->where('is_active', 1)
                ->orderBy('stock_qty')
                ->limit(50);

            if ($this->hasColumn('products', 'branch_id')) {
                $lowStockQuery->where('branch_id', $branchId);
            }

            $lowStockProducts = $lowStockQuery
                ->get(['id', 'name', 'stock_qty', 'min_stock', 'type'])
                ->map(static function ($row): array {
                    return [
                        'id' => (int) $row->id,
                        'name' => (string) ($row->name ?? ''),
                        'stock_qty' => (int) ($row->stock_qty ?? 0),
                        'min_stock' => (int) ($row->min_stock ?? 0),
                        'type' => (string) ($row->type ?? 'retail'),
                    ];
                })
                ->all();
        }

        return [
            'moduleReady' => true,
            'search' => $normalizedSearch,
            'typeFilter' => $typeFilter,
            'categoryFilter' => $categoryId,
            'products' => $products,
            'categories' => $categories,
            'lowStockProducts' => $lowStockProducts,
            'activeBranchId' => $branchId,
        ];
    }

    public function createProduct(User $user, array $payload): void
    {
        $this->assertModuleReady();
        $branchId = $this->branchContext->resolveAuthorizedBranchId($user);

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อสินค้า'],
            ]);
        }

        $existsQuery = DB::table('products')
            ->where('name', $name);
        if ($this->hasColumn('products', 'branch_id')) {
            $existsQuery->where('branch_id', $branchId);
        }

        if ($existsQuery->exists()) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อสินค้านี้มีอยู่แล้วในสาขานี้'],
            ]);
        }

        $sku = $this->normalizeNullableString($payload['sku'] ?? null);
        if ($sku !== null) {
            $skuExists = DB::table('products')
                ->where('sku', $sku);
            if ($this->hasColumn('products', 'branch_id')) {
                $skuExists->where('branch_id', $branchId);
            }

            if ($skuExists->exists()) {
                throw ValidationException::withMessages([
                    'sku' => ['รหัส SKU นี้มีอยู่แล้วในสาขานี้'],
                ]);
            }
        }

        $barcode = $this->normalizeNullableString($payload['barcode'] ?? null);
        if ($barcode !== null) {
            $barcodeExists = DB::table('products')
                ->where('barcode', $barcode);
            if ($this->hasColumn('products', 'branch_id')) {
                $barcodeExists->where('branch_id', $branchId);
            }

            if ($barcodeExists->exists()) {
                throw ValidationException::withMessages([
                    'barcode' => ['บาร์โค้ดนี้มีอยู่แล้วในสาขานี้'],
                ]);
            }
        }

        $row = [
            'name' => $name,
            'sku' => $sku,
            'barcode' => $barcode,
            'type' => $this->normalizeProductType($payload['type'] ?? 'retail'),
            'category_id' => $this->normalizeNullableId($payload['category_id'] ?? null),
            'cost_price' => $this->normalizeMoney($payload['cost_price'] ?? 0),
            'sell_price' => $this->normalizeMoney($payload['sell_price'] ?? 0),
            'stock_qty' => $this->normalizeStockQty($payload['stock_qty'] ?? 0),
            'min_stock' => $this->normalizeStockQty($payload['min_stock'] ?? 0),
            'is_active' => true,
        ];

        if ($this->hasColumn('products', 'branch_id')) {
            $row['branch_id'] = $branchId;
        }
        if ($this->hasColumn('products', 'created_at')) {
            $row['created_at'] = now();
        }
        if ($this->hasColumn('products', 'updated_at')) {
            $row['updated_at'] = now();
        }

        DB::table('products')->insert($row);
    }

    public function updateProduct(User $user, int $productId, array $payload): void
    {
        $this->assertModuleReady();
        $branchId = $this->branchContext->resolveAuthorizedBranchId($user);

        $existing = $this->findScopedProduct($productId, $branchId);
        if ($existing === null) {
            throw ValidationException::withMessages([
                'product' => ['ไม่พบสินค้าที่ต้องการแก้ไขในสาขานี้'],
            ]);
        }

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อสินค้า'],
            ]);
        }

        $nameExists = DB::table('products')
            ->where('name', $name)
            ->where('id', '!=', $productId);
        if ($this->hasColumn('products', 'branch_id')) {
            $nameExists->where('branch_id', $branchId);
        }

        if ($nameExists->exists()) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อสินค้านี้มีอยู่แล้วในสาขานี้'],
            ]);
        }

        $sku = $this->normalizeNullableString($payload['sku'] ?? null);
        if ($sku !== null) {
            $skuExists = DB::table('products')
                ->where('sku', $sku)
                ->where('id', '!=', $productId);
            if ($this->hasColumn('products', 'branch_id')) {
                $skuExists->where('branch_id', $branchId);
            }

            if ($skuExists->exists()) {
                throw ValidationException::withMessages([
                    'sku' => ['รหัส SKU นี้มีอยู่แล้วในสาขานี้'],
                ]);
            }
        }

        $barcode = $this->normalizeNullableString($payload['barcode'] ?? null);
        if ($barcode !== null) {
            $barcodeExists = DB::table('products')
                ->where('barcode', $barcode)
                ->where('id', '!=', $productId);
            if ($this->hasColumn('products', 'branch_id')) {
                $barcodeExists->where('branch_id', $branchId);
            }

            if ($barcodeExists->exists()) {
                throw ValidationException::withMessages([
                    'barcode' => ['บาร์โค้ดนี้มีอยู่แล้วในสาขานี้'],
                ]);
            }
        }

        $updates = [
            'name' => $name,
            'sku' => $sku,
            'barcode' => $barcode,
            'type' => $this->normalizeProductType($payload['type'] ?? 'retail'),
            'category_id' => $this->normalizeNullableId($payload['category_id'] ?? null),
            'cost_price' => $this->normalizeMoney($payload['cost_price'] ?? 0),
            'sell_price' => $this->normalizeMoney($payload['sell_price'] ?? 0),
            'stock_qty' => $this->normalizeStockQty($payload['stock_qty'] ?? 0),
            'min_stock' => $this->normalizeStockQty($payload['min_stock'] ?? 0),
            'is_active' => !empty($payload['is_active']),
        ];

        if ($this->hasColumn('products', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('products')
            ->where('id', $productId)
            ->update($updates);
    }

    public function deleteProduct(User $user, int $productId): void
    {
        $this->assertModuleReady();
        $branchId = $this->branchContext->resolveAuthorizedBranchId($user);

        $existing = $this->findScopedProduct($productId, $branchId);
        if ($existing === null) {
            throw ValidationException::withMessages([
                'product' => ['ไม่พบสินค้าที่ต้องการลบในสาขานี้'],
            ]);
        }

        $usedInOrders = $this->tableExists('order_items')
            && DB::table('order_items')
                ->where('item_type', 'product')
                ->where('item_id', $productId)
                ->exists();

        if ($usedInOrders) {
            $updates = ['is_active' => false];
            if ($this->hasColumn('products', 'updated_at')) {
                $updates['updated_at'] = now();
            }

            DB::table('products')
                ->where('id', $productId)
                ->update($updates);
        } else {
            DB::table('products')
                ->where('id', $productId)
                ->delete();
        }
    }

    public function adjustStock(User $user, int $productId, int $adjustQty): void
    {
        $this->assertModuleReady();
        $branchId = $this->branchContext->resolveAuthorizedBranchId($user);

        $existing = $this->findScopedProduct($productId, $branchId, ['id', 'stock_qty']);
        if ($existing === null) {
            throw ValidationException::withMessages([
                'product' => ['ไม่พบสินค้าที่ต้องการปรับสต็อกในสาขานี้'],
            ]);
        }

        if ($adjustQty === 0) {
            throw ValidationException::withMessages([
                'adjust_qty' => ['กรุณาระบุจำนวนที่ต้องการปรับ (ไม่ใช่ 0)'],
            ]);
        }

        $newQty = (int) ($existing->stock_qty ?? 0) + $adjustQty;
        if ($newQty < 0) {
            $newQty = 0;
        }

        $updates = ['stock_qty' => $newQty];
        if ($this->hasColumn('products', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('products')
            ->where('id', $productId)
            ->update($updates);
    }

    public function getCategories(): array
    {
        if (!$this->tableExists('product_categories')) {
            return [];
        }

        return DB::table('product_categories')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(static function ($row): array {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) ($row->name ?? ''),
                ];
            })
            ->all();
    }

    public function createCategory(array $payload): void
    {
        $this->assertCategoryTableReady();

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อหมวดหมู่'],
            ]);
        }

        $exists = DB::table('product_categories')
            ->where('name', $name)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อหมวดหมู่นี้มีอยู่แล้ว'],
            ]);
        }

        $row = ['name' => $name];
        if ($this->hasColumn('product_categories', 'created_at')) {
            $row['created_at'] = now();
        }
        if ($this->hasColumn('product_categories', 'updated_at')) {
            $row['updated_at'] = now();
        }

        DB::table('product_categories')->insert($row);
    }

    public function updateCategory(int $categoryId, array $payload): void
    {
        $this->assertCategoryTableReady();

        $existing = DB::table('product_categories')
            ->where('id', $categoryId)
            ->first(['id']);

        if ($existing === null) {
            throw ValidationException::withMessages([
                'category' => ['ไม่พบหมวดหมู่ที่ต้องการแก้ไข'],
            ]);
        }

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อหมวดหมู่'],
            ]);
        }

        $nameExists = DB::table('product_categories')
            ->where('name', $name)
            ->where('id', '!=', $categoryId)
            ->exists();

        if ($nameExists) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อหมวดหมู่นี้มีอยู่แล้ว'],
            ]);
        }

        $updates = ['name' => $name];
        if ($this->hasColumn('product_categories', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('product_categories')
            ->where('id', $categoryId)
            ->update($updates);
    }

    public function deleteCategory(int $categoryId): void
    {
        $this->assertCategoryTableReady();

        $existing = DB::table('product_categories')
            ->where('id', $categoryId)
            ->first(['id']);

        if ($existing === null) {
            throw ValidationException::withMessages([
                'category' => ['ไม่พบหมวดหมู่ที่ต้องการลบ'],
            ]);
        }

        if ($this->tableExists('products')) {
            DB::table('products')
                ->where('category_id', $categoryId)
                ->update(['category_id' => null]);
        }

        DB::table('product_categories')
            ->where('id', $categoryId)
            ->delete();
    }

    private function findScopedProduct(int $productId, int $branchId, array $columns = ['id']): ?object
    {
        $query = DB::table('products')
            ->where('id', $productId);

        if ($this->hasColumn('products', 'branch_id')) {
            $query->where('branch_id', $branchId);
        }

        return $query->first($columns);
    }

    private function assertModuleReady(): void
    {
        if ($this->tableExists('products')) {
            return;
        }

        throw ValidationException::withMessages([
            'products' => ['ยังไม่พบตาราง products ในฐานข้อมูล'],
        ]);
    }

    private function assertCategoryTableReady(): void
    {
        if ($this->tableExists('product_categories')) {
            return;
        }

        throw ValidationException::withMessages([
            'product_categories' => ['ยังไม่พบตาราง product_categories ในฐานข้อมูล'],
        ]);
    }

    private function normalizeMoney($value): float
    {
        $parsed = is_numeric($value) ? (float) $value : 0.0;
        if ($parsed < 0) {
            return 0.0;
        }

        return round($parsed, 2);
    }

    private function normalizeStockQty($value): int
    {
        $parsed = is_numeric($value) ? (int) $value : 0;
        return max(0, $parsed);
    }

    private function normalizeProductType(string $type): string
    {
        return in_array($type, ['retail', 'internal'], true) ? $type : 'retail';
    }

    private function normalizeNullableString($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $trimmed = trim((string) $value);
        return $trimmed !== '' ? $trimmed : null;
    }

    private function normalizeNullableId($value): ?int
    {
        if ($value === null || $value === '' || $value === '0') {
            return null;
        }

        $parsed = is_numeric($value) ? (int) $value : 0;
        return $parsed > 0 ? $parsed : null;
    }

    private function tableExists(string $table): bool
    {
        if (!array_key_exists($table, $this->tableExistsCache)) {
            $this->tableExistsCache[$table] = Schema::hasTable($table);
        }

        return (bool) $this->tableExistsCache[$table];
    }

    private function hasColumn(string $table, string $column): bool
    {
        $cacheKey = $table . '.' . $column;

        if (!array_key_exists($cacheKey, $this->columnExistsCache)) {
            $this->columnExistsCache[$cacheKey] = $this->tableExists($table) && Schema::hasColumn($table, $column);
        }

        return (bool) $this->columnExistsCache[$cacheKey];
    }
}
