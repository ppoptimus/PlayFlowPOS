<?php

namespace App\Services;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShopService
{
    private ShopContextService $shopContext;
    private array $tableExistsCache = [];
    private array $columnExistsCache = [];

    public function __construct(ShopContextService $shopContext)
    {
        $this->shopContext = $shopContext;
    }

    public function getPageData(User $user, string $search = ''): array
    {
        $this->assertSuperAdmin($user);

        $moduleReady = $this->shopContext->isReady() && $this->hasColumn('shops', 'owner_user_id');
        $normalizedSearch = trim($search);

        if (!$moduleReady) {
            return [
                'moduleReady' => false,
                'search' => $normalizedSearch,
                'shops' => [],
                'activeShopId' => null,
                'sqlScriptPath' => base_path('database/sql/2026_03_21_shop_portal_phase1.sql'),
            ];
        }

        $query = DB::table('shops as sh')
            ->leftJoin('users as owner', 'owner.id', '=', 'sh.owner_user_id')
            ->orderByDesc('sh.is_active')
            ->orderBy('sh.name');

        if ($normalizedSearch !== '') {
            $query->where(function ($builder) use ($normalizedSearch): void {
                $builder->where('sh.name', 'like', '%' . $normalizedSearch . '%');

                if ($this->hasColumn('shops', 'code')) {
                    $builder->orWhere('sh.code', 'like', '%' . $normalizedSearch . '%');
                }
                if ($this->hasColumn('shops', 'contact_name')) {
                    $builder->orWhere('sh.contact_name', 'like', '%' . $normalizedSearch . '%');
                }
                if ($this->hasColumn('shops', 'contact_phone')) {
                    $builder->orWhere('sh.contact_phone', 'like', '%' . $normalizedSearch . '%');
                }
                if ($this->hasColumn('shops', 'owner_user_id')) {
                    $builder->orWhere('owner.username', 'like', '%' . $normalizedSearch . '%');
                }
            });
        }

        $columns = ['sh.id', 'sh.name'];
        foreach (['code', 'contact_name', 'contact_phone', 'notes', 'is_active', 'expires_on', 'created_at', 'owner_user_id', 'limit_branch'] as $column) {
            if ($this->hasColumn('shops', $column)) {
                $columns[] = 'sh.' . $column;
            }
        }
        $columns[] = 'owner.username as owner_username';

        $shops = $query->get($columns)->map(function ($row): array {
            $expired = $this->shopContext->isShopExpired($row);
            $expiresOn = $row->expires_on ? Carbon::parse($row->expires_on) : null;

            return [
                'id' => (int) $row->id,
                'name' => (string) ($row->name ?? ''),
                'code' => (string) ($row->code ?? ''),
                'contact_name' => (string) ($row->contact_name ?? ''),
                'contact_phone' => (string) ($row->contact_phone ?? ''),
                'notes' => (string) ($row->notes ?? ''),
                'is_active' => (bool) ($row->is_active ?? true),
                'expires_on' => $expiresOn ? $expiresOn->format('Y-m-d') : '',
                'expires_label' => $expiresOn ? $expiresOn->format('d/m/Y') : 'Life Time',
                'is_lifetime' => $expiresOn === null,
                'is_expired' => $expired,
                'status_label' => !$this->shopContext->isShopActive($row)
                    ? 'ปิดร้าน'
                    : ($expired ? 'หมดอายุ' : 'ใช้งานอยู่'),
                'created_at' => $row->created_at ?? null,
                'owner_user_id' => isset($row->owner_user_id) && $row->owner_user_id !== null ? (int) $row->owner_user_id : null,
                'owner_username' => (string) ($row->owner_username ?? ''),
                'limit_branch' => $this->normalizeLimitBranch($row->limit_branch ?? null),
            ];
        })->all();

        $branchCounts = [];
        $userCounts = [];
        $ownerCounts = [];

        if ($this->tableExists('branches') && $this->hasColumn('branches', 'shop_id')) {
            $branchCounts = DB::table('branches')
                ->select('shop_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('shop_id')
                ->pluck('cnt', 'shop_id')
                ->all();
        }

        if (
            $this->tableExists('users')
            && $this->tableExists('branches')
            && $this->hasColumn('branches', 'shop_id')
            && $this->hasColumn('users', 'branch_id')
        ) {
            $userCounts = DB::table('users as u')
                ->join('branches as b', 'b.id', '=', 'u.branch_id')
                ->select('b.shop_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('b.shop_id')
                ->pluck('cnt', 'b.shop_id')
                ->all();
        }

        if ($this->hasColumn('shops', 'owner_user_id')) {
            $ownerCounts = DB::table('shops')
                ->whereNotNull('owner_user_id')
                ->select('id', DB::raw('1 as cnt'))
                ->pluck('cnt', 'id')
                ->all();
        }

        foreach ($shops as &$shop) {
            $shopId = $shop['id'];
            $shop['branch_count'] = (int) ($branchCounts[$shopId] ?? 0);
            $shop['user_count'] = (int) ($userCounts[$shopId] ?? 0) + (int) ($ownerCounts[$shopId] ?? 0);
            $shop['branch_usage_label'] = $shop['branch_count'] . '/' . $shop['limit_branch'];
            $shop['branch_limit_reached'] = $shop['branch_count'] >= $shop['limit_branch'];
        }
        unset($shop);

        return [
            'moduleReady' => true,
            'search' => $normalizedSearch,
            'shops' => $shops,
            'activeShopId' => $this->shopContext->getActiveShopId($user),
            'sqlScriptPath' => base_path('database/sql/2026_03_21_shop_portal_phase1.sql'),
        ];
    }

    public function createShop(User $user, array $payload): void
    {
        $this->assertSuperAdmin($user);
        $this->assertModuleReady();
        $this->assertOwnerBindingReady();

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อร้าน'],
            ]);
        }

        $ownerUsername = trim((string) ($payload['owner_username'] ?? ''));
        if ($ownerUsername === '') {
            throw ValidationException::withMessages([
                'owner_username' => ['กรุณาระบุ Username เจ้าของร้าน'],
            ]);
        }

        $ownerPassword = (string) ($payload['owner_password'] ?? '');
        if (strlen($ownerPassword) < 4) {
            throw ValidationException::withMessages([
                'owner_password' => ['รหัสผ่านเจ้าของร้านต้องมีอย่างน้อย 4 ตัวอักษร'],
            ]);
        }

        if (DB::table('users')->where('username', $ownerUsername)->exists()) {
            throw ValidationException::withMessages([
                'owner_username' => ['Username นี้ถูกใช้งานแล้ว'],
            ]);
        }

        $code = $this->resolveUniqueCode(trim((string) ($payload['code'] ?? '')), $name);
        $expiresOn = $this->normalizeExpiresOn($payload['expires_on'] ?? null);
        $limitBranch = $this->normalizeLimitBranch($payload['limit_branch'] ?? null);

        DB::transaction(function () use ($name, $code, $expiresOn, $limitBranch, $payload, $ownerUsername, $ownerPassword): void {
            $shopRow = [
                'name' => $name,
                'code' => $code,
                'contact_name' => trim((string) ($payload['contact_name'] ?? '')),
                'contact_phone' => trim((string) ($payload['contact_phone'] ?? '')),
                'notes' => trim((string) ($payload['notes'] ?? '')),
                'is_active' => !array_key_exists('is_active', $payload) || !empty($payload['is_active']),
                'expires_on' => $expiresOn,
                'owner_user_id' => null,
            ];

            if ($this->hasColumn('shops', 'limit_branch')) {
                $shopRow['limit_branch'] = $limitBranch;
            }

            if ($this->hasColumn('shops', 'created_at')) {
                $shopRow['created_at'] = now();
            }
            if ($this->hasColumn('shops', 'updated_at')) {
                $shopRow['updated_at'] = now();
            }

            $shopId = (int) DB::table('shops')->insertGetId($shopRow);

            $ownerRow = [
                'username' => $ownerUsername,
                'password' => Hash::make($ownerPassword),
                'role' => 'shop_owner',
                'branch_id' => null,
                'staff_id' => null,
            ];

            if ($this->hasColumn('users', 'is_active')) {
                $ownerRow['is_active'] = true;
            }
            if ($this->hasColumn('users', 'created_at')) {
                $ownerRow['created_at'] = now();
            }
            if ($this->hasColumn('users', 'updated_at')) {
                $ownerRow['updated_at'] = now();
            }

            $ownerUserId = (int) DB::table('users')->insertGetId($ownerRow);

            $updates = ['owner_user_id' => $ownerUserId];
            if ($this->hasColumn('shops', 'updated_at')) {
                $updates['updated_at'] = now();
            }

            DB::table('shops')
                ->where('id', $shopId)
                ->update($updates);
        });
    }

    public function updateShop(User $user, int $shopId, array $payload): void
    {
        $this->assertSuperAdmin($user);
        $this->assertModuleReady();

        $existing = $this->findShopOrFail($shopId);
        $name = trim((string) ($payload['name'] ?? ''));

        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อร้าน'],
            ]);
        }

        $code = $this->resolveUniqueCode(trim((string) ($payload['code'] ?? '')), $name, $shopId);
        $expiresOn = $this->normalizeExpiresOn($payload['expires_on'] ?? null);
        $limitBranch = $this->normalizeLimitBranch($payload['limit_branch'] ?? null);

        $updates = [
            'name' => $name,
            'code' => $code,
            'contact_name' => trim((string) ($payload['contact_name'] ?? '')),
            'contact_phone' => trim((string) ($payload['contact_phone'] ?? '')),
            'notes' => trim((string) ($payload['notes'] ?? '')),
            'is_active' => !empty($payload['is_active']),
            'expires_on' => $expiresOn,
        ];

        if ($this->hasColumn('shops', 'limit_branch')) {
            $updates['limit_branch'] = $limitBranch;
        }

        if ($this->hasColumn('shops', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('shops')
            ->where('id', (int) $existing->id)
            ->update($updates);
    }

    public function toggleShopActive(User $user, int $shopId): string
    {
        $this->assertSuperAdmin($user);
        $this->assertModuleReady();

        $existing = $this->findShopOrFail($shopId);
        $newState = !((bool) ($existing->is_active ?? true));
        $updates = ['is_active' => $newState];

        if ($this->hasColumn('shops', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('shops')
            ->where('id', (int) $existing->id)
            ->update($updates);

        return $newState
            ? 'เปิดใช้งานร้านเรียบร้อยแล้ว'
            : 'ปิดการใช้งานร้านเรียบร้อยแล้ว';
    }

    public function deleteShop(User $user, int $shopId): void
    {
        $this->assertSuperAdmin($user);
        $this->assertModuleReady();

        $existing = $this->findShopOrFail($shopId);
        $ownerUserId = $this->hasColumn('shops', 'owner_user_id')
            ? $this->normalizeNullableId($existing->owner_user_id ?? null)
            : null;
        $branchIds = $this->getShopBranchIds((int) $existing->id);

        DB::transaction(function () use ($existing, $ownerUserId, $branchIds): void {
            $this->deleteShopBranchData($branchIds);

            if (!empty($branchIds) && $this->tableExists('branches')) {
                DB::table('branches')
                    ->whereIn('id', $branchIds)
                    ->delete();
            }

            DB::table('shops')
                ->where('id', (int) $existing->id)
                ->delete();

            if ($ownerUserId !== null && $this->tableExists('users')) {
                DB::table('users')
                    ->where('id', $ownerUserId)
                    ->delete();
            }
        });

        if ($this->shopContext->getActiveShopId($user) === (int) $existing->id) {
            $this->shopContext->clearActiveShop();
        }
    }

    public function getShopOptions(bool $activeOnly = false): array
    {
        if (!$this->shopContext->isReady()) {
            return [];
        }

        $query = DB::table('shops')->orderBy('name');
        if ($activeOnly && $this->hasColumn('shops', 'is_active')) {
            $query->where('is_active', 1);
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

    public function getDefaultShopId(): ?int
    {
        if (!$this->shopContext->isReady()) {
            return null;
        }

        $shopId = DB::table('shops')
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->value('id');

        return $shopId !== null ? (int) $shopId : null;
    }

    public function getActiveShopOwnerUserId(User $user): ?int
    {
        $shopId = $this->shopContext->getActiveShopId($user);
        if ($shopId === null || !$this->hasColumn('shops', 'owner_user_id')) {
            return null;
        }

        $ownerUserId = DB::table('shops')
            ->where('id', $shopId)
            ->value('owner_user_id');

        return $ownerUserId !== null ? (int) $ownerUserId : null;
    }

    public function assignOwnerToActiveShop(User $user, int $ownerUserId): void
    {
        $this->assertSuperAdmin($user);
        $this->assertOwnerBindingReady();

        $shopId = $this->shopContext->getActiveShopId($user);
        if ($shopId === null) {
            throw ValidationException::withMessages([
                'shop_owner' => ['กรุณาเลือกร้านที่ต้องการจัดการก่อน'],
            ]);
        }

        $existingOwnerUserId = $this->getActiveShopOwnerUserId($user);
        if ($existingOwnerUserId !== null && $existingOwnerUserId !== $ownerUserId) {
            throw ValidationException::withMessages([
                'shop_owner' => ['ร้านนี้มีเจ้าของร้านอยู่แล้ว กรุณาเปลี่ยนบัญชีเดิมก่อน'],
            ]);
        }

        $updates = ['owner_user_id' => $ownerUserId];
        if ($this->hasColumn('shops', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('shops')
            ->where('id', $shopId)
            ->update($updates);
    }

    public function clearShopOwnerIfMatches(int $userId): void
    {
        if (!$this->hasColumn('shops', 'owner_user_id')) {
            return;
        }

        $updates = ['owner_user_id' => null];
        if ($this->hasColumn('shops', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('shops')
            ->where('owner_user_id', $userId)
            ->update($updates);
    }

    public function assertShopExists(int $shopId): void
    {
        $this->findShopOrFail($shopId);
    }

    private function findShopOrFail(int $shopId): object
    {
        $shop = DB::table('shops')
            ->where('id', $shopId)
            ->first(['id', 'name', 'is_active', 'owner_user_id']);

        if ($shop !== null) {
            return $shop;
        }

        throw ValidationException::withMessages([
            'shop_id' => ['ไม่พบร้านที่เลือก'],
        ]);
    }

    private function normalizeExpiresOn($value): ?string
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        return Carbon::parse($normalized)->format('Y-m-d');
    }

    private function normalizeLimitBranch($value): int
    {
        $limit = is_numeric($value) ? (int) $value : 1;

        return $limit > 0 ? $limit : 1;
    }

    private function resolveUniqueCode(string $requestedCode, string $name, ?int $ignoreShopId = null): string
    {
        $base = Str::slug($requestedCode !== '' ? $requestedCode : $name);
        if ($base === '') {
            $base = 'shop';
        }

        $candidate = $base;
        $suffix = 1;

        while ($this->codeExists($candidate, $ignoreShopId)) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function codeExists(string $code, ?int $ignoreShopId = null): bool
    {
        $query = DB::table('shops')->where('code', $code);
        if ($ignoreShopId !== null) {
            $query->where('id', '!=', $ignoreShopId);
        }

        return $query->exists();
    }

    private function deleteShopBranchData(array $branchIds): void
    {
        if (empty($branchIds)) {
            return;
        }

        $customerIds = $this->pluckIdsByBranch('customers', $branchIds);
        $packageIds = $this->pluckIdsByBranch('packages', $branchIds);
        $roomIds = $this->pluckIdsByBranch('rooms', $branchIds);
        $orderIds = $this->pluckIdsByBranch('orders', $branchIds);
        $orderItemIds = $this->pluckIdsByBranch('order_items', $branchIds);
        $bookingIds = $this->pluckIdsByBranch('bookings', $branchIds);
        $masseuseIds = $this->pluckIdsByBranch('masseuses', $branchIds);

        $this->deleteByIds('commissions', 'order_item_id', $orderItemIds);
        $this->deleteByIds('commissions', 'masseuse_id', $masseuseIds);
        $this->deleteByBranchIds('commissions', $branchIds);

        $this->deleteByIds('booking_services', 'booking_id', $bookingIds);
        $this->deleteByBranchIds('booking_services', $branchIds);

        $this->deleteByBranchIds('order_items', $branchIds);
        $this->deleteByIds('order_items', 'order_id', $orderIds);

        $this->deleteByBranchIds('customer_packages', $branchIds);
        $this->deleteByIds('customer_packages', 'customer_id', $customerIds);
        $this->deleteByIds('customer_packages', 'package_id', $packageIds);

        $this->deleteByBranchIds('bookings', $branchIds);
        $this->deleteByBranchIds('staff_attendance', $branchIds);
        $this->deleteByIds('staff_attendance', 'masseuse_id', $masseuseIds);
        $this->deleteByBranchIds('staff_shifts', $branchIds);
        $this->deleteByIds('staff_shifts', 'masseuse_id', $masseuseIds);

        $this->deleteByBranchIds('expenses', $branchIds);
        $this->deleteByBranchIds('commission_configs', $branchIds);
        $this->deleteByBranchIds('promotions', $branchIds);
        $this->deleteByBranchIds('products', $branchIds);
        $this->deleteByBranchIds('services', $branchIds);
        $this->deleteByBranchIds('service_categories', $branchIds);
        $this->deleteByBranchIds('packages', $branchIds);
        $this->deleteByBranchIds('membership_tiers', $branchIds);
        $this->deleteByBranchIds('orders', $branchIds);
        $this->deleteByBranchIds('customers', $branchIds);
        $this->deleteByBranchIds('beds', $branchIds);
        $this->deleteByIds('beds', 'room_id', $roomIds);
        $this->deleteByBranchIds('rooms', $branchIds);
        $this->deleteByBranchIds('masseuses', $branchIds);
        $this->deleteByBranchIds('users', $branchIds);
        $this->deleteByBranchIds('staff', $branchIds);
    }

    private function getShopBranchIds(int $shopId): array
    {
        if (
            !$this->tableExists('branches')
            || !$this->hasColumn('branches', 'shop_id')
        ) {
            return [];
        }

        return DB::table('branches')
            ->where('shop_id', $shopId)
            ->pluck('id')
            ->map(static function ($id): int {
                return (int) $id;
            })
            ->all();
    }

    private function pluckIdsByBranch(string $table, array $branchIds): array
    {
        if (
            empty($branchIds)
            || !$this->tableExists($table)
            || !$this->hasColumn($table, 'branch_id')
        ) {
            return [];
        }

        return DB::table($table)
            ->whereIn('branch_id', $branchIds)
            ->pluck('id')
            ->map(static function ($id): int {
                return (int) $id;
            })
            ->all();
    }

    private function deleteByBranchIds(string $table, array $branchIds): void
    {
        if (
            empty($branchIds)
            || !$this->tableExists($table)
            || !$this->hasColumn($table, 'branch_id')
        ) {
            return;
        }

        DB::table($table)
            ->whereIn('branch_id', $branchIds)
            ->delete();
    }

    private function deleteByIds(string $table, string $column, array $ids): void
    {
        if (
            empty($ids)
            || !$this->tableExists($table)
            || !$this->hasColumn($table, $column)
        ) {
            return;
        }

        DB::table($table)
            ->whereIn($column, $ids)
            ->delete();
    }

    private function normalizeNullableId($value): ?int
    {
        if ($value === null || $value === '' || $value === '0') {
            return null;
        }

        $parsed = is_numeric($value) ? (int) $value : 0;

        return $parsed > 0 ? $parsed : null;
    }

    private function assertSuperAdmin(User $user): void
    {
        if ((string) ($user->role ?? '') === 'super_admin') {
            return;
        }

        throw ValidationException::withMessages([
            'shop' => ['เฉพาะ Super Admin เท่านั้นที่จัดการพอร์ทัลร้านได้'],
        ]);
    }

    private function assertModuleReady(): void
    {
        if ($this->shopContext->isReady()) {
            return;
        }

        throw ValidationException::withMessages([
            'shop' => ['ระบบร้านยังไม่พร้อมใช้งาน กรุณารัน SQL setup ก่อน'],
        ]);
    }

    private function assertOwnerBindingReady(): void
    {
        if ($this->hasColumn('shops', 'owner_user_id')) {
            return;
        }

        throw ValidationException::withMessages([
            'shop_owner' => ['ฐานข้อมูลยังไม่รองรับการผูกเจ้าของร้าน กรุณารัน SQL setup เพิ่มเติมก่อน'],
        ]);
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
