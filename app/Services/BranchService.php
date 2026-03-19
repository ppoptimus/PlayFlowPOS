<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BranchService
{
    private BranchContextService $branchContext;
    private array $tableExistsCache = [];
    private array $columnExistsCache = [];

    public function __construct(BranchContextService $branchContext)
    {
        $this->branchContext = $branchContext;
    }

    // ─── Page Data ───────────────────────────────────────────────

    public function getPageData(User $user, string $search = ''): array
    {
        if (!$this->tableExists('branches')) {
            return [
                'moduleReady' => false,
                'search' => trim($search),
                'branches' => [],
                'canManageAllBranches' => $this->branchContext->canManageAllBranches($user),
            ];
        }

        $normalizedSearch = trim($search);

        $query = DB::table('branches')->orderBy('id');
        if (!$this->branchContext->canManageAllBranches($user)) {
            $query->where('id', $this->branchContext->resolveAuthorizedBranchId($user));
        }

        if ($normalizedSearch !== '') {
            $query->where(function ($q) use ($normalizedSearch): void {
                $q->where('name', 'like', '%' . $normalizedSearch . '%');
                if ($this->hasColumn('branches', 'address')) {
                    $q->orWhere('address', 'like', '%' . $normalizedSearch . '%');
                }
                if ($this->hasColumn('branches', 'phone')) {
                    $q->orWhere('phone', 'like', '%' . $normalizedSearch . '%');
                }
            });
        }

        $columns = ['id', 'name'];
        foreach (['address', 'phone', 'is_active', 'created_at'] as $col) {
            if ($this->hasColumn('branches', $col)) {
                $columns[] = $col;
            }
        }

        $branches = $query
            ->get($columns)
            ->map(static function ($row): array {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) ($row->name ?? ''),
                    'address' => (string) ($row->address ?? ''),
                    'phone' => (string) ($row->phone ?? ''),
                    'is_active' => (bool) ($row->is_active ?? true),
                    'created_at' => $row->created_at ?? null,
                ];
            })
            ->all();

        // สถิติจำนวนพนักงาน + users ต่อสาขา
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
            'canManageAllBranches' => $this->branchContext->canManageAllBranches($user),
        ];
    }

    // ─── Branch CRUD ─────────────────────────────────────────────

    public function createBranch(User $user, array $payload): void
    {
        $this->assertModuleReady();
        $this->assertCanManageAllBranches($user);

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อสาขา'],
            ]);
        }

        $exists = DB::table('branches')
            ->where('name', $name)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อสาขานี้มีอยู่แล้ว'],
            ]);
        }

        $row = [
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
            ->first(['id']);

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
            ->where('name', $name)
            ->where('id', '!=', $branchId)
            ->exists();

        if ($nameExists) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อสาขานี้มีอยู่แล้ว'],
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
            // Soft-deactivate แทนลบ
            $updates = ['is_active' => false];
            if ($this->hasColumn('branches', 'updated_at')) {
                $updates['updated_at'] = now();
            }
            DB::table('branches')
                ->where('id', $branchId)
                ->update($updates);
        } else {
            DB::table('branches')
                ->where('id', $branchId)
                ->delete();
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function getAllBranches(): array
    {
        if (!$this->tableExists('branches')) {
            return [];
        }

        return DB::table('branches')
            ->orderBy('name')
            ->get(['id', 'name', 'is_active'])
            ->map(static function ($row): array {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) ($row->name ?? ''),
                    'is_active' => (bool) ($row->is_active ?? true),
                ];
            })
            ->all();
    }

    private function assertCanManageAllBranches(User $user): void
    {
        if ($this->branchContext->canManageAllBranches($user)) {
            return;
        }

        throw ValidationException::withMessages([
            'branch' => ['เฉพาะ Super Admin เท่านั้นที่สามารถจัดการหลายสาขาได้'],
        ]);
    }

    private function assertCanManageBranch(User $user, int $branchId): void
    {
        if ($this->branchContext->canManageAllBranches($user)) {
            return;
        }

        $userBranchId = $this->branchContext->resolveAuthorizedBranchId($user);
        if ($userBranchId === $branchId) {
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
