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
        foreach (['code', 'contact_name', 'contact_phone', 'notes', 'is_active', 'expires_on', 'created_at', 'owner_user_id'] as $column) {
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
                    ? '???????'
                    : ($expired ? '???????' : '???????????'),
                'created_at' => $row->created_at ?? null,
                'owner_user_id' => isset($row->owner_user_id) && $row->owner_user_id !== null ? (int) $row->owner_user_id : null,
                'owner_username' => (string) ($row->owner_username ?? ''),
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
                'name' => ['?????????????????'],
            ]);
        }

        $ownerUsername = trim((string) ($payload['owner_username'] ?? ''));
        if ($ownerUsername === '') {
            throw ValidationException::withMessages([
                'owner_username' => ['????????? Username ??????????????'],
            ]);
        }

        $ownerPassword = (string) ($payload['owner_password'] ?? '');
        if (strlen($ownerPassword) < 4) {
            throw ValidationException::withMessages([
                'owner_password' => ['?????????????????????????????????? 4 ????????'],
            ]);
        }

        if (DB::table('users')->where('username', $ownerUsername)->exists()) {
            throw ValidationException::withMessages([
                'owner_username' => ['Username ????????????????'],
            ]);
        }

        $code = $this->resolveUniqueCode(trim((string) ($payload['code'] ?? '')), $name);
        $expiresOn = $this->normalizeExpiresOn($payload['expires_on'] ?? null);

        DB::transaction(function () use ($name, $code, $expiresOn, $payload, $ownerUsername, $ownerPassword): void {
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
                'name' => ['?????????????????'],
            ]);
        }

        $code = $this->resolveUniqueCode(trim((string) ($payload['code'] ?? '')), $name, $shopId);
        $expiresOn = $this->normalizeExpiresOn($payload['expires_on'] ?? null);

        $updates = [
            'name' => $name,
            'code' => $code,
            'contact_name' => trim((string) ($payload['contact_name'] ?? '')),
            'contact_phone' => trim((string) ($payload['contact_phone'] ?? '')),
            'notes' => trim((string) ($payload['notes'] ?? '')),
            'is_active' => !empty($payload['is_active']),
            'expires_on' => $expiresOn,
        ];

        if ($this->hasColumn('shops', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('shops')
            ->where('id', (int) $existing->id)
            ->update($updates);
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
                'shop_owner' => ['????????????????????????????????????????????'],
            ]);
        }

        $existingOwnerUserId = $this->getActiveShopOwnerUserId($user);
        if ($existingOwnerUserId !== null && $existingOwnerUserId !== $ownerUserId) {
            throw ValidationException::withMessages([
                'shop_owner' => ['???????????????????????????? ??????????????????????'],
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
            ->first(['id', 'name']);

        if ($shop !== null) {
            return $shop;
        }

        throw ValidationException::withMessages([
            'shop_id' => ['?????????????????'],
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

    private function assertSuperAdmin(User $user): void
    {
        if ((string) ($user->role ?? '') === 'super_admin') {
            return;
        }

        throw ValidationException::withMessages([
            'shop' => ['????? Super Admin ????????????????????????'],
        ]);
    }

    private function assertModuleReady(): void
    {
        if ($this->shopContext->isReady()) {
            return;
        }

        throw ValidationException::withMessages([
            'shop' => ['????????????????????????? ???????? SQL setup ????'],
        ]);
    }

    private function assertOwnerBindingReady(): void
    {
        if ($this->hasColumn('shops', 'owner_user_id')) {
            return;
        }

        throw ValidationException::withMessages([
            'shop_owner' => ['???????????????????????????????? ???????? SQL setup ?????????????'],
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
