<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ServiceManagementService
{
    // ─── Page Data ───────────────────────────────────────────────

    public function getPageData(string $search = '', ?int $categoryId = null): array
    {
        $normalizedSearch = trim($search);

        $query = DB::table('services')->orderBy('id');

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

        $categories = $this->getCategories();

        return [
            'search' => $normalizedSearch,
            'categoryFilter' => $categoryId,
            'services' => $services,
            'categories' => $categories,
        ];
    }

    // ─── Service CRUD ────────────────────────────────────────────

    public function createService(array $payload): void
    {
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อบริการ'],
            ]);
        }

        $exists = DB::table('services')
            ->where('name', $name)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อบริการนี้มีอยู่แล้ว'],
            ]);
        }

        DB::table('services')->insert([
            'name' => $name,
            'category_id' => $this->normalizeNullableId($payload['category_id'] ?? null),
            'duration_minutes' => max(1, (int) ($payload['duration_minutes'] ?? 60)),
            'price' => $this->normalizeMoney($payload['price'] ?? 0),
            'is_active' => true,
        ]);
    }

    public function updateService(int $serviceId, array $payload): void
    {
        $existing = DB::table('services')
            ->where('id', $serviceId)
            ->first(['id']);

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
            ->where('id', '!=', $serviceId)
            ->exists();

        if ($nameExists) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อบริการนี้มีอยู่แล้ว'],
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

    public function deleteService(int $serviceId): void
    {
        $existing = DB::table('services')
            ->where('id', $serviceId)
            ->first(['id']);

        if ($existing === null) {
            throw ValidationException::withMessages([
                'service' => ['ไม่พบบริการที่ต้องการลบ'],
            ]);
        }

        // ถ้ามีออเดอร์อ้างอิง → soft-delete (is_active=0) แทนลบจริง
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

    // ─── Category CRUD ───────────────────────────────────────────

    public function getCategories(): array
    {
        if (!Schema::hasTable('service_categories')) {
            return [];
        }

        return DB::table('service_categories')
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
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อหมวดหมู่'],
            ]);
        }

        $exists = DB::table('service_categories')
            ->where('name', $name)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อหมวดหมู่นี้มีอยู่แล้ว'],
            ]);
        }

        DB::table('service_categories')->insert(['name' => $name]);
    }

    public function updateCategory(int $categoryId, array $payload): void
    {
        $existing = DB::table('service_categories')
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

        $nameExists = DB::table('service_categories')
            ->where('name', $name)
            ->where('id', '!=', $categoryId)
            ->exists();

        if ($nameExists) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อหมวดหมู่นี้มีอยู่แล้ว'],
            ]);
        }

        DB::table('service_categories')
            ->where('id', $categoryId)
            ->update(['name' => $name]);
    }

    public function deleteCategory(int $categoryId): void
    {
        $existing = DB::table('service_categories')
            ->where('id', $categoryId)
            ->first(['id']);

        if ($existing === null) {
            throw ValidationException::withMessages([
                'category' => ['ไม่พบหมวดหมู่ที่ต้องการลบ'],
            ]);
        }

        // ปลด category ออกจาก services ก่อนลบ
        DB::table('services')
            ->where('category_id', $categoryId)
            ->update(['category_id' => null]);

        DB::table('service_categories')
            ->where('id', $categoryId)
            ->delete();
    }

    // ─── Internal Helpers ────────────────────────────────────────

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
