<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BranchService
{
    private BranchContextService $branchContext;
    private ShopContextService $shopContext;
    private array $tableExistsCache = [];
    private array $columnExistsCache = [];

    public function __construct(BranchContextService $branchContext, ShopContextService $shopContext)
    {
        $this->branchContext = $branchContext;
        $this->shopContext = $shopContext;
    }

    public function getPageData(User $user, string $search = ''): array
    {
        $normalizedSearch = trim($search);
        $canManageAllBranches = $this->branchContext->canManageAllBranches($user);
        $activeShop = $this->shopContext->getActiveShop($user);
        $activeShopId = isset($activeShop->id) ? (int) $activeShop->id : null;
        $branchLimit = $this->resolveBranchLimit($activeShop, $activeShopId);
        $branchTotalCount = $this->countBranchesForShop($activeShopId);
        $branchQuotaLabel = $this->formatBranchQuota($branchTotalCount, $branchLimit);
        $branchLimitReached = $activeShopId !== null && $branchTotalCount >= $branchLimit;

        if (!$this->tableExists('branches')) {
            return [
                'moduleReady' => false,
                'search' => $normalizedSearch,
                'branches' => [],
                'activeShop' => $activeShop,
                'shopSelected' => $activeShopId !== null,
                'requiresBranchSetup' => false,
                'canManageAllBranches' => $canManageAllBranches,
                'branchLimit' => $branchLimit,
                'branchTotalCount' => $branchTotalCount,
                'branchQuotaLabel' => $branchQuotaLabel,
                'branchLimitReached' => $branchLimitReached,
            ];
        }

        $accessibleBranchIds = array_column($this->branchContext->getAccessibleBranches($user, false), 'id');
        $requiresBranchSetup = $canManageAllBranches && $activeShop !== null && empty($accessibleBranchIds);

        if ($canManageAllBranches && empty($accessibleBranchIds)) {
            return [
                'moduleReady' => true,
                'search' => $normalizedSearch,
                'branches' => [],
                'activeShop' => $activeShop,
                'shopSelected' => $activeShop !== null,
                'requiresBranchSetup' => $requiresBranchSetup,
                'canManageAllBranches' => true,
                'branchLimit' => $branchLimit,
                'branchTotalCount' => $branchTotalCount,
                'branchQuotaLabel' => $branchQuotaLabel,
                'branchLimitReached' => $branchLimitReached,
            ];
        }

        $query = DB::table('branches as b')
            ->leftJoin('shops as s', 's.id', '=', 'b.shop_id')
            ->orderBy('b.id');

        if ($canManageAllBranches) {
            $query->whereIn('b.id', $accessibleBranchIds);
        } else {
            $query->where('b.id', $this->branchContext->resolveAuthorizedBranchId($user));
        }

        if ($normalizedSearch !== '') {
            $query->where(function ($builder) use ($normalizedSearch): void {
                $builder->where('b.name', 'like', '%' . $normalizedSearch . '%');
                if ($this->hasColumn('branches', 'address')) {
                    $builder->orWhere('b.address', 'like', '%' . $normalizedSearch . '%');
                }
                if ($this->hasColumn('branches', 'phone')) {
                    $builder->orWhere('b.phone', 'like', '%' . $normalizedSearch . '%');
                }
                if ($this->shopContext->hasColumn('branches', 'shop_id')) {
                    $builder->orWhere('s.name', 'like', '%' . $normalizedSearch . '%');
                }
            });
        }

        $branches = $query->get([
            'b.id',
            'b.name',
            'b.address',
            'b.phone',
            'b.open_time',
            'b.close_time',
            'b.is_active',
            'b.created_at',
            'b.shop_id',
            's.name as shop_name',
        ])->map(static function ($row): array {
            return [
                'id' => (int) $row->id,
                'name' => (string) ($row->name ?? ''),
                'address' => (string) ($row->address ?? ''),
                'phone' => (string) ($row->phone ?? ''),
                'open_time' => self::normalizeDisplayTime($row->open_time ?? null, '10:00'),
                'close_time' => self::normalizeDisplayTime($row->close_time ?? null, '20:00'),
                'is_active' => (bool) ($row->is_active ?? true),
                'created_at' => $row->created_at ?? null,
                'shop_id' => isset($row->shop_id) ? (int) $row->shop_id : null,
                'shop_name' => (string) ($row->shop_name ?? ''),
            ];
        })->all();

        $staffCounts = [];
        $userCounts = [];

        if ($this->tableExists('staff') && $this->hasColumn('staff', 'branch_id')) {
            $staffCounts = DB::table('staff')
                ->select('branch_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('branch_id')
                ->pluck('cnt', 'branch_id')
                ->all();
        }

        if ($this->tableExists('users') && $this->hasColumn('users', 'branch_id')) {
            $userCounts = DB::table('users')
                ->select('branch_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('branch_id')
                ->pluck('cnt', 'branch_id')
                ->all();
        }

        foreach ($branches as &$branch) {
            $branch['staff_count'] = (int) ($staffCounts[$branch['id']] ?? 0);
            $branch['user_count'] = (int) ($userCounts[$branch['id']] ?? 0);
        }
        unset($branch);

        return [
            'moduleReady' => true,
            'search' => $normalizedSearch,
            'branches' => $branches,
            'activeShop' => $activeShop,
            'shopSelected' => !$canManageAllBranches || $activeShop !== null,
            'requiresBranchSetup' => false,
            'canManageAllBranches' => $canManageAllBranches,
            'branchLimit' => $branchLimit,
            'branchTotalCount' => $branchTotalCount,
            'branchQuotaLabel' => $branchQuotaLabel,
            'branchLimitReached' => $branchLimitReached,
        ];
    }

    public function createBranch(User $user, array $payload): void
    {
        $this->assertModuleReady();
        $this->assertCanManageAllBranches($user);

        $activeShopId = $this->shopContext->getActiveShopId($user);
        if ($activeShopId === null) {
            throw ValidationException::withMessages([
                'shop' => ['กรุณาเลือกร้านจากพอร์ทัลก่อนเพิ่มสาขา'],
            ]);
        }

        $this->assertBranchLimitAvailable($activeShopId);

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อสาขา'],
            ]);
        }

        $exists = DB::table('branches')
            ->where('shop_id', $activeShopId)
            ->where('name', $name)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อสาขานี้มีอยู่แล้วในร้านนี้'],
            ]);
        }

        $row = [
            'shop_id' => $activeShopId,
            'name' => $name,
            'address' => trim((string) ($payload['address'] ?? '')),
            'phone' => trim((string) ($payload['phone'] ?? '')),
            'is_active' => true,
        ];

        $hours = $this->normalizeBranchHours($payload);
        if ($this->hasColumn('branches', 'open_time')) {
            $row['open_time'] = $hours['open_time'];
        }
        if ($this->hasColumn('branches', 'close_time')) {
            $row['close_time'] = $hours['close_time'];
        }

        if ($this->hasColumn('branches', 'created_at')) {
            $row['created_at'] = now();
        }
        if ($this->hasColumn('branches', 'updated_at')) {
            $row['updated_at'] = now();
        }

        DB::table('branches')->insert($row);
    }

    public function updateBranch(User $user, int $branchId, array $payload): void
    {
        $this->assertModuleReady();
        $this->assertCanManageBranch($user, $branchId);

        $existing = DB::table('branches')
            ->where('id', $branchId)
            ->first(['id', 'shop_id']);

        if ($existing === null) {
            throw ValidationException::withMessages([
                'branch' => ['ไม่พบสาขาที่ต้องการแก้ไข'],
            ]);
        }

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อสาขา'],
            ]);
        }

        $nameExists = DB::table('branches')
            ->where('shop_id', (int) $existing->shop_id)
            ->where('name', $name)
            ->where('id', '!=', $branchId)
            ->exists();

        if ($nameExists) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อสาขานี้มีอยู่แล้วในร้านนี้'],
            ]);
        }

        $updates = [
            'name' => $name,
            'address' => trim((string) ($payload['address'] ?? '')),
            'phone' => trim((string) ($payload['phone'] ?? '')),
            'is_active' => !empty($payload['is_active']),
        ];

        $hours = $this->normalizeBranchHours($payload);
        if ($this->hasColumn('branches', 'open_time')) {
            $updates['open_time'] = $hours['open_time'];
        }
        if ($this->hasColumn('branches', 'close_time')) {
            $updates['close_time'] = $hours['close_time'];
        }

        if ($this->hasColumn('branches', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('branches')
            ->where('id', $branchId)
            ->update($updates);
    }

    public function deleteBranch(User $user, int $branchId): void
    {
        $this->assertModuleReady();
        $this->assertCanManageAllBranches($user);
        $this->assertCanManageBranch($user, $branchId);

        $existing = DB::table('branches')
            ->where('id', $branchId)
            ->first(['id']);

        if ($existing === null) {
            throw ValidationException::withMessages([
                'branch' => ['ไม่พบสาขาที่ต้องการลบ'],
            ]);
        }

        $branchBoundTables = [
            'users',
            'staff',
            'orders',
            'bookings',
            'products',
            'services',
            'packages',
            'rooms',
            'masseuses',
            'customer_packages',
            'commissions',
            'order_items',
        ];

        $hasRelatedData = false;
        foreach ($branchBoundTables as $table) {
            if (!$this->tableExists($table) || !$this->hasColumn($table, 'branch_id')) {
                continue;
            }

            if (DB::table($table)->where('branch_id', $branchId)->exists()) {
                $hasRelatedData = true;
                break;
            }
        }

        if ($hasRelatedData) {
            $updates = ['is_active' => false];
            if ($this->hasColumn('branches', 'updated_at')) {
                $updates['updated_at'] = now();
            }

            DB::table('branches')
                ->where('id', $branchId)
                ->update($updates);

            return;
        }

        DB::table('branches')
            ->where('id', $branchId)
            ->delete();
    }

    private function assertCanManageAllBranches(User $user): void
    {
        if ($this->branchContext->canManageAllBranches($user)) {
            return;
        }

        throw ValidationException::withMessages([
            'branch' => ['เฉพาะ Super Admin เท่านั้นที่จัดการหลายสาขาได้'],
        ]);
    }

    private function assertCanManageBranch(User $user, int $branchId): void
    {
        $accessibleIds = array_column($this->branchContext->getAccessibleBranches($user, false), 'id');
        if (in_array($branchId, $accessibleIds, true)) {
            return;
        }

        throw ValidationException::withMessages([
            'branch' => ['คุณไม่มีสิทธิ์แก้ไขสาขานี้'],
        ]);
    }

    private function assertModuleReady(): void
    {
        if ($this->tableExists('branches')) {
            return;
        }

        throw ValidationException::withMessages([
            'branches' => ['ยังไม่พบตาราง branches ในฐานข้อมูล'],
        ]);
    }

    private function assertBranchLimitAvailable(int $shopId): void
    {
        $branchLimit = $this->resolveBranchLimit(null, $shopId);
        $branchTotalCount = $this->countBranchesForShop($shopId);

        if ($branchTotalCount < $branchLimit) {
            return;
        }

        throw ValidationException::withMessages([
            'branch_limit' => ['à¸ˆà¸³à¸™à¸§à¸™à¸ªà¸²à¸‚à¸²à¸„à¸£à¸šà¸•à¸²à¸¡à¹‚à¸„à¸§à¸•à¹‰à¸²à¸‚à¸­à¸‡à¸£à¹‰à¸²à¸™à¹à¸¥à¹‰à¸§ (' . $branchTotalCount . '/' . $branchLimit . ')'],
        ]);
    }

    private function normalizeBranchHours(array $payload): array
    {
        $openTime = $this->normalizeStorageTime($payload['open_time'] ?? null, '10:00:00');
        $closeTime = $this->normalizeStorageTime($payload['close_time'] ?? null, '20:00:00');

        if (strtotime($closeTime) <= strtotime($openTime)) {
            throw ValidationException::withMessages([
                'close_time' => ['เวลาเปิดและปิดร้านไม่ถูกต้อง เวลาปิดต้องมากกว่าเวลาเปิด'],
            ]);
        }

        return [
            'open_time' => $openTime,
            'close_time' => $closeTime,
        ];
    }

    private function normalizeStorageTime($value, string $fallback): string
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return $fallback;
        }

        return strlen($normalized) === 5 ? $normalized . ':00' : $normalized;
    }

    private static function normalizeDisplayTime($value, string $fallback): string
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return $fallback;
        }

        return substr($normalized, 0, 5);
    }

    private function resolveBranchLimit(?object $activeShop, ?int $shopId): int
    {
        if ($activeShop !== null && isset($activeShop->limit_branch)) {
            return $this->normalizeLimitBranch($activeShop->limit_branch);
        }

        if (
            $shopId !== null
            && $shopId > 0
            && $this->tableExists('shops')
            && $this->hasColumn('shops', 'limit_branch')
        ) {
            $limit = DB::table('shops')
                ->where('id', $shopId)
                ->value('limit_branch');

            return $this->normalizeLimitBranch($limit);
        }

        return 1;
    }

    private function countBranchesForShop(?int $shopId): int
    {
        if (
            $shopId === null
            || $shopId <= 0
            || !$this->tableExists('branches')
            || !$this->hasColumn('branches', 'shop_id')
        ) {
            return 0;
        }

        return (int) DB::table('branches')
            ->where('shop_id', $shopId)
            ->count();
    }

    private function formatBranchQuota(int $branchTotalCount, int $branchLimit): string
    {
        return $branchTotalCount . '/' . $branchLimit;
    }

    private function normalizeLimitBranch($value): int
    {
        $limit = is_numeric($value) ? (int) $value : 1;

        return $limit > 0 ? $limit : 1;
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
