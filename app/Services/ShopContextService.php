<?php

namespace App\Services;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ShopContextService
{
    private const ACTIVE_SHOP_SESSION_KEY = 'active_shop_id';

    private array $tableExistsCache = [];
    private array $columnExistsCache = [];

    public function isReady(): bool
    {
        return $this->tableExists('shops')
            && $this->tableExists('branches')
            && $this->hasColumn('branches', 'shop_id');
    }

    public function getUserShopId(?User $user): ?int
    {
        if ($user === null || !$this->isReady()) {
            return null;
        }

        $role = (string) ($user->role ?? '');
        if ($role === 'shop_owner' && $this->hasColumn('shops', 'owner_user_id')) {
            $shopId = DB::table('shops')
                ->where('owner_user_id', (int) $user->id)
                ->value('id');

            return $shopId !== null ? (int) $shopId : null;
        }

        $branchId = isset($user->branch_id) ? (int) $user->branch_id : 0;
        if ($branchId <= 0) {
            return null;
        }

        $shopId = DB::table('branches')
            ->where('id', $branchId)
            ->value('shop_id');

        return $shopId !== null ? (int) $shopId : null;
    }

    public function getShopById(int $shopId): ?object
    {
        if (!$this->tableExists('shops') || $shopId <= 0) {
            return null;
        }

        $columns = ['id', 'name'];
        foreach (['code', 'contact_name', 'contact_phone', 'notes', 'is_active', 'expires_on', 'owner_user_id'] as $column) {
            if ($this->hasColumn('shops', $column)) {
                $columns[] = $column;
            }
        }

        return DB::table('shops')
            ->where('id', $shopId)
            ->first($columns);
    }

    public function getUserShop(?User $user): ?object
    {
        $shopId = $this->getUserShopId($user);

        return $shopId !== null ? $this->getShopById($shopId) : null;
    }

    public function getActiveShopId(?User $user): ?int
    {
        if ($user === null || !$this->isReady()) {
            return null;
        }

        if ((string) ($user->role ?? '') !== 'super_admin') {
            return $this->getUserShopId($user);
        }

        $sessionShopId = (int) session(self::ACTIVE_SHOP_SESSION_KEY, 0);
        if ($sessionShopId > 0 && $this->getShopById($sessionShopId) !== null) {
            return $sessionShopId;
        }

        return null;
    }

    public function getActiveShop(?User $user): ?object
    {
        $shopId = $this->getActiveShopId($user);

        return $shopId !== null ? $this->getShopById($shopId) : null;
    }

    public function setActiveShop(User $user, int $shopId): void
    {
        if ((string) ($user->role ?? '') !== 'super_admin') {
            return;
        }

        $shop = $this->getShopById($shopId);
        if ($shop === null) {
            throw ValidationException::withMessages([
                'shop' => ['ไม่พบร้านที่เลือก'],
            ]);
        }

        session([self::ACTIVE_SHOP_SESSION_KEY => (int) $shop->id]);
    }

    public function clearActiveShop(): void
    {
        session()->forget(self::ACTIVE_SHOP_SESSION_KEY);
    }

    public function getLoginAccessState(?User $user): array
    {
        $role = (string) ($user->role ?? '');
        if ($user === null || $role === 'super_admin' || !$this->isReady()) {
            return [
                'allowed' => true,
                'message' => null,
                'shop' => null,
            ];
        }

        $shop = $this->getUserShop($user);
        if ($shop === null) {
            return [
                'allowed' => false,
                'message' => 'บัญชีนี้ยังไม่ได้ผูกร้าน จึงยังไม่สามารถเข้าใช้งานระบบได้',
                'shop' => null,
            ];
        }

        if (!$this->isShopActive($shop)) {
            return [
                'allowed' => false,
                'message' => 'ร้านไม่สามารถใช้งานได้ชั่วคราว กรุณาติดต่อเจ้าของระบบ',
                'shop' => $shop,
            ];
        }

        if ($this->isShopExpired($shop)) {
            return [
                'allowed' => false,
                'message' => 'ร้านนี้หมดอายุการใช้งานแล้ว กรุณาติดต่อผู้ดูแลระบบ',
                'shop' => $shop,
            ];
        }

        return [
            'allowed' => true,
            'message' => null,
            'shop' => $shop,
        ];
    }

    public function isShopActive(?object $shop): bool
    {
        if ($shop === null) {
            return false;
        }

        return (bool) ($shop->is_active ?? true);
    }

    public function isShopExpired(?object $shop): bool
    {
        $expiresOn = (string) ($shop->expires_on ?? '');
        if ($shop === null || $expiresOn === '') {
            return false;
        }

        return Carbon::today()->gt(Carbon::parse($expiresOn)->startOfDay());
    }

    public function tableExists(string $table): bool
    {
        if (!array_key_exists($table, $this->tableExistsCache)) {
            $this->tableExistsCache[$table] = Schema::hasTable($table);
        }

        return (bool) $this->tableExistsCache[$table];
    }

    public function hasColumn(string $table, string $column): bool
    {
        $cacheKey = $table . '.' . $column;

        if (!array_key_exists($cacheKey, $this->columnExistsCache)) {
            $this->columnExistsCache[$cacheKey] = $this->tableExists($table) && Schema::hasColumn($table, $column);
        }

        return (bool) $this->columnExistsCache[$cacheKey];
    }
}
