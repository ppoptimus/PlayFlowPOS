<?php

namespace App\Services;

use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class BranchContextService
{
    private ShopContextService $shopContext;

    public function __construct(ShopContextService $shopContext)
    {
        $this->shopContext = $shopContext;
    }

    public function canAccessAdmin(?User $user): bool
    {
        return in_array((string) ($user->role ?? ''), ['super_admin', 'shop_owner', 'branch_manager'], true);
    }

    public function canManageAllBranches(?User $user): bool
    {
        return in_array((string) ($user->role ?? ''), ['super_admin', 'shop_owner'], true);
    }

    public function getUserBranchId(?User $user): ?int
    {
        $branchId = isset($user->branch_id) ? (int) $user->branch_id : 0;

        return $branchId > 0 ? $branchId : null;
    }

    public function resolveAuthorizedBranchId(User $user, ?int $requestedBranchId = null): int
    {
        if ($this->canManageAllBranches($user)) {
            $accessibleBranches = $this->getAccessibleBranches($user, false);

            if ($requestedBranchId !== null && $requestedBranchId > 0) {
                foreach ($accessibleBranches as $branch) {
                    if ((int) $branch['id'] === $requestedBranchId) {
                        return $requestedBranchId;
                    }
                }
            }

            if (!empty($accessibleBranches)) {
                return (int) $accessibleBranches[0]['id'];
            }

            throw new AuthorizationException('กรุณาเลือกร้านก่อนเข้าเมนูที่อ้างอิงสาขา');
        }

        $userBranchId = $this->getUserBranchId($user);
        if ($userBranchId !== null) {
            return $userBranchId;
        }

        throw new AuthorizationException('บัญชีนี้ยังไม่ได้ผูกกับสาขา จึงไม่สามารถเข้าถึงข้อมูลสาขาได้');
    }

    public function getAccessibleBranches(?User $user, bool $activeOnly = false): array
    {
        $query = DB::table('branches')
            ->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', 1);
        }

        if ($this->canManageAllBranches($user)) {
            $activeShopId = $this->shopContext->getActiveShopId($user);
            if ($activeShopId === null) {
                return [];
            }

            if ($this->shopContext->hasColumn('branches', 'shop_id')) {
                $query->where('shop_id', $activeShopId);
            }
        } else {
            $userBranchId = $this->getUserBranchId($user);
            if ($userBranchId !== null) {
                $query->where('id', $userBranchId);
            } else {
                return [];
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

    public function getDefaultBranchId(?User $user = null): int
    {
        $query = DB::table('branches');

        if ($user !== null && $this->canManageAllBranches($user) && $this->shopContext->hasColumn('branches', 'shop_id')) {
            $activeShopId = $this->shopContext->getActiveShopId($user);
            if ($activeShopId !== null) {
                $query->where('shop_id', $activeShopId);
            }
        }

        $activeBranch = (clone $query)
            ->where('is_active', 1)
            ->orderBy('id')
            ->value('id');

        if ($activeBranch !== null) {
            return (int) $activeBranch;
        }

        $firstBranch = (clone $query)
            ->orderBy('id')
            ->value('id');

        if ($firstBranch !== null) {
            return (int) $firstBranch;
        }

        return 1;
    }
}
