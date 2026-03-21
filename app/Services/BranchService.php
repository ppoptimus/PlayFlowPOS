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
        if (!$this->tableExists('branches')) {
            return [
                'moduleReady' => false,
                'search' => trim($search),
                'branches' => [],
                'activeShop' => $this->shopContext->getActiveShop($user),
                'shopSelected' => $this->shopContext->getActiveShopId($user) !== null,
                'canManageAllBranches' => $this->branchContext->canManageAllBranches($user),
            ];
        }

        $normalizedSearch = trim($search);
        $canManageAllBranches = $this->branchContext->canManageAllBranches($user);
        $activeShop = $this->shopContext->getActiveShop($user);
        $accessibleBranchIds = array_column($this->branchContext->getAccessibleBranches($user, false), 'id');

        if ($canManageAllBranches && empty($accessibleBranchIds)) {
            return [
                'moduleReady' => true,
                'search' => $normalizedSearch,
                'branches' => [],
                'activeShop' => $activeShop,
                'shopSelected' => false,
                'canManageAllBranches' => true,
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
            'canManageAllBranches' => $canManageAllBranches,
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
