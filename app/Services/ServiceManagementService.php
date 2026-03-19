<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ServiceManagementService
{
    private BranchContextService $branchContext;

    public function __construct(BranchContextService $branchContext)
    {
        $this->branchContext = $branchContext;
    }

    public function getPageData(User $user, string $search = '', ?int $categoryId = null, ?int $requestedBranchId = null): array
    {
        $branchId = $this->branchContext->resolveAuthorizedBranchId($user, $requestedBranchId);
        $normalizedSearch = trim($search);

        $query = DB::table('services')
            ->orderBy('id');

        if (Schema::hasColumn('services', 'branch_id')) {
            $query->where('branch_id', $branchId);
        }

        if ($normalizedSearch !== '') {
            $query->where('name', 'like', '%' . $normalizedSearch . '%');
        }

        if ($categoryId !== null && $categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        $services = $query
            ->get(['id', 'name', 'category_id', 'duration_minutes', 'price', 'is_active'])
            ->map(static function ($row): array {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) ($row->name ?? ''),
                    'category_id' => $row->category_id !== null ? (int) $row->category_id : null,
                    'duration_minutes' => (int) ($row->duration_minutes ?? 0),
                    'price' => (float) ($row->price ?? 0),
                    'is_active' => (bool) ($row->is_active ?? true),
                ];
            })
            ->all();

        return [
            'search' => $normalizedSearch,
            'categoryFilter' => $categoryId,
            'services' => $services,
            'categories' => $this->getCategories($branchId),
            'activeBranchId' => $branchId,
        ];
    }

    public function createService(User $user, array $payload): void
    {
        $branchId = $this->branchContext->resolveAuthorizedBranchId($user);
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อบริการ'],
            ]);
        }

        $exists = DB::table('services')
            ->where('name', $name);
        if (Schema::hasColumn('services', 'branch_id')) {
            $exists->where('branch_id', $branchId);
        }

        if ($exists->exists()) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อบริการนี้มีอยู่แล้วในสาขานี้'],
            ]);
        }

        $row = [
            'name' => $name,
            'category_id' => $this->normalizeNullableId($payload['category_id'] ?? null),
            'duration_minutes' => max(1, (int) ($payload['duration_minutes'] ?? 60)),
            'price' => $this->normalizeMoney($payload['price'] ?? 0),
            'is_active' => true,
        ];

        if (Schema::hasColumn('services', 'branch_id')) {
            $row['branch_id'] = $branchId;
        }

        DB::table('services')->insert($row);
    }

    public function updateService(User $user, int $serviceId, array $payload): void
    {
        $branchId = $this->branchContext->resolveAuthorizedBranchId($user);
        $existing = $this->findScopedService($serviceId, $branchId);

        if ($existing === null) {
            throw ValidationException::withMessages([
                'service' => ['ไม่พบบริการที่ต้องการแก้ไข'],
            ]);
        }

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อบริการ'],
            ]);
        }

        $nameExists = DB::table('services')
            ->where('name', $name)
            ->where('id', '!=', $serviceId);
        if (Schema::hasColumn('services', 'branch_id')) {
            $nameExists->where('branch_id', $branchId);
        }

        if ($nameExists->exists()) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อบริการนี้มีอยู่แล้วในสาขานี้'],
            ]);
        }

        DB::table('services')
            ->where('id', $serviceId)
            ->update([
                'name' => $name,
                'category_id' => $this->normalizeNullableId($payload['category_id'] ?? null),
                'duration_minutes' => max(1, (int) ($payload['duration_minutes'] ?? 60)),
                'price' => $this->normalizeMoney($payload['price'] ?? 0),
                'is_active' => !empty($payload['is_active']),
            ]);
    }

    public function deleteService(User $user, int $serviceId): void
    {
        $branchId = $this->branchContext->resolveAuthorizedBranchId($user);
        $existing = $this->findScopedService($serviceId, $branchId);

        if ($existing === null) {
            throw ValidationException::withMessages([
                'service' => ['ไม่พบบริการที่ต้องการลบ'],
            ]);
        }

        $usedInOrders = Schema::hasTable('order_items')
            && DB::table('order_items')
                ->where('item_type', 'service')
                ->where('item_id', $serviceId)
                ->exists();

        if ($usedInOrders) {
            DB::table('services')
                ->where('id', $serviceId)
                ->update(['is_active' => false]);
        } else {
            DB::table('services')
                ->where('id', $serviceId)
                ->delete();
        }
    }

    public function getCategories(int $branchId): array
    {
        if (!Schema::hasTable('service_categories')) {
            return [];
        }

        $query = DB::table('service_categories')
            ->orderBy('id');

        if (Schema::hasColumn('service_categories', 'branch_id')) {
            $query->where('branch_id', $branchId);
        }

        return $query
            ->get(['id', 'name'])
            ->map(static function ($row): array {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) ($row->name ?? ''),
                ];
            })
            ->all();
    }

    public function createCategory(User $user, array $payload): void
    {
        $branchId = $this->branchContext->resolveAuthorizedBranchId($user);
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อหมวดหมู่'],
            ]);
        }

        $exists = DB::table('service_categories')
            ->where('name', $name);
        if (Schema::hasColumn('service_categories', 'branch_id')) {
            $exists->where('branch_id', $branchId);
        }

        if ($exists->exists()) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อหมวดหมู่นี้มีอยู่แล้วในสาขานี้'],
            ]);
        }

        $row = ['name' => $name];
        if (Schema::hasColumn('service_categories', 'branch_id')) {
            $row['branch_id'] = $branchId;
        }

        DB::table('service_categories')->insert($row);
    }

    public function updateCategory(User $user, int $categoryId, array $payload): void
    {
        $branchId = $this->branchContext->resolveAuthorizedBranchId($user);
        $existing = $this->findScopedCategory($categoryId, $branchId);

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

        $nameExists = DB::table('service_categories')
            ->where('name', $name)
            ->where('id', '!=', $categoryId);
        if (Schema::hasColumn('service_categories', 'branch_id')) {
            $nameExists->where('branch_id', $branchId);
        }

        if ($nameExists->exists()) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อหมวดหมู่นี้มีอยู่แล้วในสาขานี้'],
            ]);
        }

        DB::table('service_categories')
            ->where('id', $categoryId)
            ->update(['name' => $name]);
    }

    public function deleteCategory(User $user, int $categoryId): void
    {
        $branchId = $this->branchContext->resolveAuthorizedBranchId($user);
        $existing = $this->findScopedCategory($categoryId, $branchId);

        if ($existing === null) {
            throw ValidationException::withMessages([
                'category' => ['ไม่พบหมวดหมู่ที่ต้องการลบ'],
            ]);
        }

        $servicesQuery = DB::table('services')
            ->where('category_id', $categoryId);
        if (Schema::hasColumn('services', 'branch_id')) {
            $servicesQuery->where('branch_id', $branchId);
        }

        $servicesQuery->update(['category_id' => null]);

        DB::table('service_categories')
            ->where('id', $categoryId)
            ->delete();
    }

    private function findScopedService(int $serviceId, int $branchId): ?object
    {
        $query = DB::table('services')
            ->where('id', $serviceId);

        if (Schema::hasColumn('services', 'branch_id')) {
            $query->where('branch_id', $branchId);
        }

        return $query->first(['id']);
    }

    private function findScopedCategory(int $categoryId, int $branchId): ?object
    {
        $query = DB::table('service_categories')
            ->where('id', $categoryId);

        if (Schema::hasColumn('service_categories', 'branch_id')) {
            $query->where('branch_id', $branchId);
        }

        return $query->first(['id']);
    }

    private function normalizeMoney($value): float
    {
        $parsed = is_numeric($value) ? (float) $value : 0.0;
        return max(0.0, round($parsed, 2));
    }

    private function normalizeNullableId($value): ?int
    {
        if ($value === null || $value === '' || $value === '0') {
            return null;
        }

        $parsed = is_numeric($value) ? (int) $value : 0;
        return $parsed > 0 ? $parsed : null;
    }
}
