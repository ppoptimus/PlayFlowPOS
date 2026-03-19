<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class UserAccountService
{
    private BranchContextService $branchContext;
    private StaffDirectoryService $staffDirectory;
    private array $tableExistsCache = [];
    private array $columnExistsCache = [];

    public function __construct(BranchContextService $branchContext, StaffDirectoryService $staffDirectory)
    {
        $this->branchContext = $branchContext;
        $this->staffDirectory = $staffDirectory;
    }

    public function getPageData(User $actor, string $search = '', ?int $branchFilter = null): array
    {
        $resolvedBranchFilter = $this->resolveBranchFilter($actor, $branchFilter);

        if (!$this->tableExists('users')) {
            return [
                'moduleReady' => false,
                'search' => trim($search),
                'branchFilter' => $resolvedBranchFilter,
                'users' => [],
                'branches' => $this->branchContext->getAccessibleBranches($actor, true),
                'roles' => $this->getAvailableRoles($actor),
                'staffOptions' => [],
                'supportsActiveToggle' => false,
                'canManageAllBranches' => $this->branchContext->canManageAllBranches($actor),
            ];
        }

        $normalizedSearch = trim($search);

        $query = DB::table('users as u')
            ->leftJoin('staff as s', 'u.staff_id', '=', 's.id')
            ->leftJoin('branches as b', 'u.branch_id', '=', 'b.id')
            ->orderBy('u.id');

        if ($normalizedSearch !== '') {
            $query->where(function ($q) use ($normalizedSearch): void {
                $q->where('u.username', 'like', '%' . $normalizedSearch . '%')
                    ->orWhere('s.name', 'like', '%' . $normalizedSearch . '%')
                    ->orWhere('s.nickname', 'like', '%' . $normalizedSearch . '%');
            });
        }

        if ($resolvedBranchFilter !== null && $this->hasColumn('users', 'branch_id')) {
            $query->where('u.branch_id', $resolvedBranchFilter);
        }

        if (!$this->branchContext->canManageAllBranches($actor)) {
            $query->where('u.role', '!=', 'super_admin');
        }

        $selectCols = [
            'u.id',
            'u.username',
            'u.role',
            'u.branch_id',
            'u.staff_id',
            'u.last_login',
            's.name as staff_name',
            's.nickname as staff_nickname',
            's.position as staff_position',
            'b.name as branch_name',
        ];

        if ($this->hasColumn('users', 'is_active')) {
            $selectCols[] = 'u.is_active';
        }

        $users = $query
            ->get($selectCols)
            ->map(function ($row): array {
                $staffId = isset($row->staff_id) && $row->staff_id !== null ? (int) $row->staff_id : null;

                return [
                    'id' => (int) $row->id,
                    'username' => (string) ($row->username ?? ''),
                    'role' => (string) ($row->role ?? ''),
                    'branch_id' => isset($row->branch_id) && $row->branch_id !== null ? (int) $row->branch_id : null,
                    'branch_name' => (string) ($row->branch_name ?? '-'),
                    'is_active' => (bool) ($row->is_active ?? true),
                    'last_login' => $row->last_login ?? null,
                    'staff_id' => $staffId,
                    'staff_name' => (string) ($row->staff_name ?? ''),
                    'staff_nickname' => (string) ($row->staff_nickname ?? ''),
                    'staff_position' => (string) ($row->staff_position ?? ''),
                    'staff_avatar' => $this->staffDirectory->getStaffAvatar($staffId, 'user-' . (string) $row->id),
                    'is_staff_linked' => $staffId !== null && $staffId > 0,
                ];
            })
            ->all();

        return [
            'moduleReady' => true,
            'search' => $normalizedSearch,
            'branchFilter' => $resolvedBranchFilter,
            'users' => $users,
            'branches' => $this->branchContext->getAccessibleBranches($actor, true),
            'roles' => $this->getAvailableRoles($actor),
            'staffOptions' => $this->getAvailableStaffOptions($actor),
            'supportsActiveToggle' => $this->hasColumn('users', 'is_active'),
            'canManageAllBranches' => $this->branchContext->canManageAllBranches($actor),
        ];
    }

    public function createUser(User $actor, array $payload): void
    {
        $this->assertModuleReady();

        $staffId = (int) ($payload['staff_id'] ?? 0);
        if ($staffId <= 0) {
            throw ValidationException::withMessages([
                'staff_id' => ['กรุณาเลือกพนักงาน'],
            ]);
        }

        $staff = $this->findSelectableStaff($actor, $staffId);
        if ($staff === null) {
            throw ValidationException::withMessages([
                'staff_id' => ['ไม่พบพนักงานที่เลือก หรือไม่มีสิทธิ์ใช้งานสาขานี้'],
            ]);
        }

        if ($this->hasColumn('users', 'staff_id')) {
            $exists = DB::table('users')
                ->where('staff_id', $staffId)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'staff_id' => ['พนักงานคนนี้มีบัญชีผู้ใช้งานแล้ว'],
                ]);
            }
        }

        $username = trim((string) ($payload['username'] ?? ''));
        if ($username === '') {
            throw ValidationException::withMessages([
                'username' => ['กรุณาระบุ Username'],
            ]);
        }

        $password = (string) ($payload['password'] ?? '');
        if (strlen($password) < 4) {
            throw ValidationException::withMessages([
                'password' => ['รหัสผ่านต้องมีอย่างน้อย 4 ตัวอักษร'],
            ]);
        }

        if (DB::table('users')->where('username', $username)->exists()) {
            throw ValidationException::withMessages([
                'username' => ['Username นี้มีอยู่แล้ว'],
            ]);
        }

        $role = $this->normalizeAssignableRole($actor, (string) ($payload['role'] ?? 'cashier'));
        $requestedBranchId = $this->normalizeNullableId($payload['branch_id'] ?? null);
        $defaultBranchId = isset($staff->branch_id) && (int) $staff->branch_id > 0 ? (int) $staff->branch_id : null;
        $branchId = $this->resolveAssignableBranchId($actor, $requestedBranchId ?? $defaultBranchId, $role);

        $row = [
            'username' => $username,
            'password' => Hash::make($password),
            'role' => $role,
            'branch_id' => $branchId,
        ];

        if ($this->hasColumn('users', 'staff_id')) {
            $row['staff_id'] = $staffId;
        }
        if ($this->hasColumn('users', 'is_active')) {
            $row['is_active'] = true;
        }
        if ($this->hasColumn('users', 'created_at')) {
            $row['created_at'] = now();
        }
        if ($this->hasColumn('users', 'updated_at')) {
            $row['updated_at'] = now();
        }

        DB::table('users')->insert($row);
    }

    public function updateUser(User $actor, int $userId, array $payload): void
    {
        $this->assertModuleReady();

        $existing = $this->findScopedUser($actor, $userId);
        if ($existing === null) {
            throw ValidationException::withMessages([
                'user' => ['ไม่พบผู้ใช้ที่ต้องการแก้ไข'],
            ]);
        }

        $role = $this->normalizeAssignableRole($actor, (string) ($payload['role'] ?? 'cashier'));
        $requestedBranchId = $this->normalizeNullableId($payload['branch_id'] ?? null);
        $defaultBranchId = isset($existing->staff_branch_id) && (int) $existing->staff_branch_id > 0 ? (int) $existing->staff_branch_id : null;
        $branchId = $this->resolveAssignableBranchId($actor, $requestedBranchId ?? $defaultBranchId, $role);

        $updates = [
            'role' => $role,
            'branch_id' => $branchId,
        ];

        if ($this->hasColumn('users', 'is_active')) {
            $updates['is_active'] = !empty($payload['is_active']);
        }
        if ($this->hasColumn('users', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('users')
            ->where('id', $userId)
            ->update($updates);
    }

    public function resetPassword(User $actor, int $userId, string $newPassword): void
    {
        $this->assertModuleReady();

        if (strlen($newPassword) < 4) {
            throw ValidationException::withMessages([
                'new_password' => ['รหัสผ่านต้องมีอย่างน้อย 4 ตัวอักษร'],
            ]);
        }

        $existing = $this->findScopedUser($actor, $userId);
        if ($existing === null) {
            throw ValidationException::withMessages([
                'user' => ['ไม่พบผู้ใช้ที่ต้องการรีเซ็ตรหัสผ่าน'],
            ]);
        }

        $updates = [
            'password' => Hash::make($newPassword),
        ];

        if ($this->hasColumn('users', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('users')
            ->where('id', $userId)
            ->update($updates);
    }

    public function deleteUser(User $actor, int $userId, ?int $currentUserId = null): void
    {
        $this->assertModuleReady();

        if ($currentUserId !== null && $userId === $currentUserId) {
            throw ValidationException::withMessages([
                'user' => ['ไม่สามารถลบบัญชีของตัวเองได้'],
            ]);
        }

        $existing = $this->findScopedUser($actor, $userId);
        if ($existing === null) {
            throw ValidationException::withMessages([
                'user' => ['ไม่พบผู้ใช้ที่ต้องการลบ'],
            ]);
        }

        if ($this->tableExists('masseuses') && $this->hasColumn('masseuses', 'user_id')) {
            $linkedMasseuse = DB::table('masseuses')
                ->where('user_id', $userId)
                ->exists();

            if ($linkedMasseuse) {
                throw ValidationException::withMessages([
                    'user' => ['บัญชีนี้ถูกผูกกับข้อมูลหมอนวดอยู่ ยังลบไม่ได้'],
                ]);
            }
        }

        DB::table('users')
            ->where('id', $userId)
            ->delete();
    }

    public function getAvailableRoles(?User $actor = null): array
    {
        if ($this->branchContext->canManageAllBranches($actor)) {
            return [
                ['value' => 'super_admin', 'label' => 'Super Admin'],
                ['value' => 'branch_manager', 'label' => 'ผู้จัดการสาขา'],
                ['value' => 'cashier', 'label' => 'แคชเชียร์'],
                ['value' => 'masseuse', 'label' => 'หมอนวด'],
            ];
        }

        return [
            ['value' => 'cashier', 'label' => 'แคชเชียร์'],
            ['value' => 'masseuse', 'label' => 'หมอนวด'],
        ];
    }

    private function getAvailableStaffOptions(User $actor): array
    {
        if (!$this->tableExists('staff')) {
            return [];
        }

        $query = DB::table('staff as s')
            ->leftJoin('branches as b', 's.branch_id', '=', 'b.id')
            ->leftJoin('users as u', 'u.staff_id', '=', 's.id')
            ->whereNull('u.id')
            ->orderBy('s.name');

        if (!$this->branchContext->canManageAllBranches($actor)) {
            $query->where('s.branch_id', $this->branchContext->resolveAuthorizedBranchId($actor));
        }

        if ($this->hasColumn('staff', 'is_active')) {
            $query->where('s.is_active', 1);
        }

        return $query
            ->get([
                's.id',
                's.name',
                's.nickname',
                's.position',
                's.branch_id',
                'b.name as branch_name',
            ])
            ->map(function ($row): array {
                $staffId = (int) $row->id;

                return [
                    'id' => $staffId,
                    'name' => (string) ($row->name ?? ''),
                    'nickname' => (string) ($row->nickname ?? ''),
                    'position' => (string) ($row->position ?? ''),
                    'branch_id' => $row->branch_id !== null ? (int) $row->branch_id : null,
                    'branch_name' => (string) ($row->branch_name ?? '-'),
                    'avatar' => $this->staffDirectory->getStaffAvatar($staffId, 'staff-option-' . $staffId),
                ];
            })
            ->all();
    }

    private function findSelectableStaff(User $actor, int $staffId, bool $activeOnly = true): ?object
    {
        if (!$this->tableExists('staff')) {
            return null;
        }

        $query = DB::table('staff')
            ->where('id', $staffId);

        if (!$this->branchContext->canManageAllBranches($actor)) {
            $query->where('branch_id', $this->branchContext->resolveAuthorizedBranchId($actor));
        }

        if ($activeOnly && $this->hasColumn('staff', 'is_active')) {
            $query->where('is_active', 1);
        }

        return $query->first([
            'id',
            'name',
            'nickname',
            'position',
            'branch_id',
            'is_active',
        ]);
    }

    private function findScopedUser(User $actor, int $userId): ?object
    {
        $query = DB::table('users as u')
            ->leftJoin('staff as s', 'u.staff_id', '=', 's.id')
            ->where('u.id', $userId);

        if (!$this->branchContext->canManageAllBranches($actor)) {
            $query->where('u.branch_id', $this->branchContext->resolveAuthorizedBranchId($actor))
                ->where('u.role', '!=', 'super_admin');
        }

        return $query->first([
            'u.id',
            'u.branch_id',
            'u.role',
            'u.staff_id',
            's.branch_id as staff_branch_id',
        ]);
    }

    private function normalizeAssignableRole(User $actor, string $role): string
    {
        $validRoles = array_column($this->getAvailableRoles($actor), 'value');
        if (!in_array($role, $validRoles, true)) {
            return $validRoles[0] ?? 'cashier';
        }

        return $role;
    }

    private function resolveAssignableBranchId(User $actor, ?int $branchId, string $role): ?int
    {
        if ($role === 'super_admin' && $this->branchContext->canManageAllBranches($actor)) {
            return $branchId;
        }

        if ($this->branchContext->canManageAllBranches($actor)) {
            if ($branchId !== null && $this->branchContext->branchExists($branchId)) {
                return $branchId;
            }

            return $this->branchContext->getDefaultBranchId();
        }

        return $this->branchContext->resolveAuthorizedBranchId($actor);
    }

    private function resolveBranchFilter(User $actor, ?int $branchFilter): ?int
    {
        if ($this->branchContext->canManageAllBranches($actor)) {
            return $branchFilter;
        }

        return $this->branchContext->resolveAuthorizedBranchId($actor);
    }

    private function normalizeNullableId($value): ?int
    {
        if ($value === null || $value === '' || $value === '0') {
            return null;
        }

        $parsed = is_numeric($value) ? (int) $value : 0;

        return $parsed > 0 ? $parsed : null;
    }

    private function assertModuleReady(): void
    {
        if ($this->tableExists('users')) {
            return;
        }

        throw ValidationException::withMessages([
            'users' => ['ยังไม่พบตาราง users ในฐานข้อมูล'],
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
