<?php

namespace App\Services;

use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class StaffManagementService
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

    public function getPageData(User $user, string $search = '', ?int $branchFilter = null): array
    {
        $resolvedBranchFilter = $this->resolveBranchFilter($user, $branchFilter);

        if (!$this->tableExists('staff')) {
            return [
                'moduleReady' => false,
                'search' => trim($search),
                'branchFilter' => $resolvedBranchFilter,
                'staffList' => [],
                'branches' => $this->branchContext->getAccessibleBranches($user, true),
                'canManageAllBranches' => $this->branchContext->canManageAllBranches($user),
            ];
        }

        $normalizedSearch = trim($search);

        $query = DB::table('staff as s')
            ->leftJoin('branches as b', 's.branch_id', '=', 'b.id')
            ->orderBy('s.id');

        if ($this->hasColumn('staff', 'position')) {
            $query->where(function ($q): void {
                $q->whereNull('s.position')
                    ->orWhere('s.position', '!=', 'หมอนวด');
            });
        }

        if ($normalizedSearch !== '') {
            $query->where(function ($q) use ($normalizedSearch): void {
                $q->where('s.name', 'like', '%' . $normalizedSearch . '%');
                if ($this->hasColumn('staff', 'nickname')) {
                    $q->orWhere('s.nickname', 'like', '%' . $normalizedSearch . '%');
                }
                if ($this->hasColumn('staff', 'phone')) {
                    $q->orWhere('s.phone', 'like', '%' . $normalizedSearch . '%');
                }
                if ($this->hasColumn('staff', 'position')) {
                    $q->orWhere('s.position', 'like', '%' . $normalizedSearch . '%');
                }
            });
        }

        if ($resolvedBranchFilter !== null && $resolvedBranchFilter > 0) {
            $query->where('s.branch_id', $resolvedBranchFilter);
        }

        $staffList = $query
            ->get([
                's.id',
                's.branch_id',
                's.name',
                's.nickname',
                's.phone',
                's.position',
                's.is_active',
                's.created_at',
                'b.name as branch_name',
            ])
            ->map(function ($row): array {
                $staffId = (int) $row->id;
                $linkedUserId = $this->staffDirectory->getLinkedUserIdForStaff($staffId);

                return [
                    'id' => $staffId,
                    'branch_id' => $row->branch_id !== null ? (int) $row->branch_id : null,
                    'branch_name' => (string) ($row->branch_name ?? '-'),
                    'name' => (string) ($row->name ?? ''),
                    'nickname' => (string) ($row->nickname ?? ''),
                    'phone' => (string) ($row->phone ?? ''),
                    'position' => (string) ($row->position ?? ''),
                    'is_active' => (bool) ($row->is_active ?? true),
                    'created_at' => $row->created_at ?? null,
                    'profile_image' => $this->staffDirectory->getStaffProfileImagePath($staffId),
                    'avatar' => $this->staffDirectory->getStaffAvatar($staffId, 'staff-' . $staffId),
                    'linked_user_id' => $linkedUserId,
                ];
            })
            ->all();

        return [
            'moduleReady' => true,
            'search' => $normalizedSearch,
            'branchFilter' => $resolvedBranchFilter,
            'staffList' => $staffList,
            'branches' => $this->branchContext->getAccessibleBranches($user, true),
            'canManageAllBranches' => $this->branchContext->canManageAllBranches($user),
        ];
    }

    public function createStaff(User $user, array $payload, ?UploadedFile $profileImage = null): void
    {
        $this->assertModuleReady();

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อพนักงาน'],
            ]);
        }

        $branchId = $this->resolveBranchFilter($user, $this->normalizeNullableId($payload['branch_id'] ?? null));

        DB::transaction(function () use ($branchId, $name, $payload, $profileImage): void {
            $row = [
                'branch_id' => $branchId,
                'name' => $name,
                'nickname' => trim((string) ($payload['nickname'] ?? '')),
                'phone' => trim((string) ($payload['phone'] ?? '')),
                'position' => trim((string) ($payload['position'] ?? '')),
                'is_active' => true,
            ];

            if ($this->hasColumn('staff', 'created_at')) {
                $row['created_at'] = now();
            }
            if ($this->hasColumn('staff', 'updated_at')) {
                $row['updated_at'] = now();
            }

            $staffId = (int) DB::table('staff')->insertGetId($row);

            if ($profileImage !== null) {
                $this->staffDirectory->saveStaffProfileImage($staffId, $profileImage);
            }
        });
    }

    public function updateStaff(User $user, int $staffId, array $payload, ?UploadedFile $profileImage = null): void
    {
        $this->assertModuleReady();

        $existing = $this->findScopedStaff($user, $staffId);
        if ($existing === null) {
            throw ValidationException::withMessages([
                'staff' => ['ไม่พบพนักงานที่ต้องการแก้ไข'],
            ]);
        }

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อพนักงาน'],
            ]);
        }

        $branchId = $this->resolveBranchFilter($user, $this->normalizeNullableId($payload['branch_id'] ?? null));

        DB::transaction(function () use ($staffId, $branchId, $name, $payload, $profileImage): void {
            $updates = [
                'branch_id' => $branchId,
                'name' => $name,
                'nickname' => trim((string) ($payload['nickname'] ?? '')),
                'phone' => trim((string) ($payload['phone'] ?? '')),
                'position' => trim((string) ($payload['position'] ?? '')),
                'is_active' => !empty($payload['is_active']),
            ];

            if ($this->hasColumn('staff', 'updated_at')) {
                $updates['updated_at'] = now();
            }

            DB::table('staff')
                ->where('id', $staffId)
                ->update($updates);

            if (!empty($payload['remove_profile_image'])) {
                $this->staffDirectory->removeStaffProfileImage($staffId);
            }

            if ($profileImage !== null) {
                $this->staffDirectory->saveStaffProfileImage($staffId, $profileImage);
            }
        });
    }

    public function deleteStaff(User $user, int $staffId): void
    {
        $this->assertModuleReady();

        $existing = $this->findScopedStaff($user, $staffId);
        if ($existing === null) {
            throw ValidationException::withMessages([
                'staff' => ['ไม่พบพนักงานที่ต้องการลบ'],
            ]);
        }

        $linkedUserId = $this->staffDirectory->getLinkedUserIdForStaff($staffId);
        if ($linkedUserId !== null) {
            throw ValidationException::withMessages([
                'staff' => ['พนักงานคนนี้มีบัญชีผู้ใช้งานผูกอยู่ กรุณาลบบัญชีผู้ใช้ก่อน'],
            ]);
        }

        DB::table('staff')
            ->where('id', $staffId)
            ->delete();

        $this->staffDirectory->removeStaffReferences($staffId);
    }

    public function getMyProfilePageData(User $user): array
    {
        return [
            'profile' => $this->staffDirectory->resolveUserProfile($user),
        ];
    }

    private function findScopedStaff(User $user, int $staffId): ?object
    {
        $query = DB::table('staff')
            ->where('id', $staffId);

        if (!$this->branchContext->canManageAllBranches($user)) {
            $query->where('branch_id', $this->branchContext->resolveAuthorizedBranchId($user));
        }

        return $query->first(['id', 'branch_id']);
    }

    private function resolveBranchFilter(User $user, ?int $branchFilter): ?int
    {
        if ($this->branchContext->canManageAllBranches($user)) {
            return $branchFilter;
        }

        return $this->branchContext->resolveAuthorizedBranchId($user);
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
        if ($this->tableExists('staff')) {
            return;
        }

        throw ValidationException::withMessages([
            'staff' => ['ยังไม่พบตาราง staff ในฐานข้อมูล'],
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
