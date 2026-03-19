<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\DB;

class BranchContextService
{
    public function canAccessAdmin(?User $user): bool
    {
        return in_array((string) ($user->role ?? ''), ['super_admin', 'branch_manager'], true);
    }

    public function canManageAllBranches(?User $user): bool
    {
        return (string) ($user->role ?? '') === 'super_admin';
    }

    public function getUserBranchId(?User $user): ?int
    {
        $branchId = isset($user->branch_id) ? (int) $user->branch_id : 0;

        return $branchId > 0 ? $branchId : null;
    }

    public function resolveAuthorizedBranchId(User $user, ?int $requestedBranchId = null): int
    {
        if ($this->canManageAllBranches($user)) {
            if ($requestedBranchId !== null && $requestedBranchId > 0 && $this->branchExists($requestedBranchId)) {
                return $requestedBranchId;
            }

            return $this->getDefaultBranchId();
        }

        $userBranchId = $this->getUserBranchId($user);
        if ($userBranchId !== null && $this->branchExists($userBranchId)) {
            return $userBranchId;
        }

        if ($requestedBranchId !== null && $requestedBranchId > 0 && $this->branchExists($requestedBranchId)) {
            return $requestedBranchId;
        }

        return $this->getDefaultBranchId();
    }

    public function getAccessibleBranches(?User $user, bool $activeOnly = false): array
    {
        $query = DB::table('branches')
            ->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', 1);
        }

        if (!$this->canManageAllBranches($user)) {
            $userBranchId = $this->getUserBranchId($user);
            if ($userBranchId !== null) {
                $query->where('id', $userBranchId);
            }
        }

        return $query
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

    public function branchExists(int $branchId): bool
    {
        return DB::table('branches')
            ->where('id', $branchId)
            ->exists();
    }

    public function getDefaultBranchId(): int
    {
        $activeBranch = DB::table('branches')
            ->where('is_active', 1)
            ->orderBy('id')
            ->value('id');

        if ($activeBranch !== null) {
            return (int) $activeBranch;
        }

        $firstBranch = DB::table('branches')
            ->orderBy('id')
            ->value('id');

        if ($firstBranch !== null) {
            return (int) $firstBranch;
        }

        return 1;
    }
}
