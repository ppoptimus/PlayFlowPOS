<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PackageService
{
    private array $tableExistsCache = [];
    private array $columnExistsCache = [];

    public function getPageData(string $search = ''): array
    {
        $normalizedSearch = trim($search);

        if (!$this->tableExists('packages') || !$this->tableExists('customer_packages')) {
            return [
                'moduleReady' => false,
                'search' => $normalizedSearch,
                'packages' => [],
                'balances' => [],
                'redemptions' => [],
            ];
        }

        $packageQuery = DB::table('packages')->orderBy('id');
        if ($normalizedSearch !== '' && $this->hasColumn('packages', 'name')) {
            $packageQuery->where('name', 'like', '%' . $normalizedSearch . '%');
        }

        $packages = $packageQuery
            ->get(['id', 'name', 'price', 'total_qty', 'valid_days'])
            ->map(static function ($row): array {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) ($row->name ?? ''),
                    'price' => (float) ($row->price ?? 0),
                    'total_qty' => (int) ($row->total_qty ?? 0),
                    'valid_days' => $row->valid_days !== null ? (int) $row->valid_days : null,
                ];
            })
            ->all();

        $balances = DB::table('customer_packages as cp')
            ->leftJoin('customers as c', 'c.id', '=', 'cp.customer_id')
            ->leftJoin('packages as p', 'p.id', '=', 'cp.package_id')
            ->where('cp.remaining_qty', '>', 0)
            ->orderByDesc('cp.id')
            ->limit(80)
            ->get([
                'cp.id',
                'cp.remaining_qty',
                'cp.expired_at',
                'cp.bought_at',
                'c.name as customer_name',
                'p.name as package_name',
            ])
            ->map(static function ($row): array {
                return [
                    'id' => (int) $row->id,
                    'customer_name' => (string) ($row->customer_name ?? 'Walk-in'),
                    'package_name' => (string) ($row->package_name ?? '-'),
                    'remaining_qty' => (int) ($row->remaining_qty ?? 0),
                    'expired_at' => $row->expired_at !== null ? (string) $row->expired_at : null,
                    'bought_at' => $row->bought_at !== null ? (string) $row->bought_at : null,
                ];
            })
            ->all();

        $redemptions = [];
        if ($this->tableExists('orders') && $this->tableExists('order_items')) {
            $redemptionQuery = DB::table('order_items as oi')
                ->join('orders as o', 'o.id', '=', 'oi.order_id')
                ->leftJoin('customers as c', 'c.id', '=', 'o.customer_id')
                ->leftJoin('packages as p', 'p.id', '=', 'oi.item_id')
                ->where('oi.item_type', 'package')
                ->where('oi.unit_price', 0)
                ->orderByDesc('o.created_at')
                ->limit(80);

            if ($this->tableExists('users')) {
                $redemptionQuery->leftJoin('users as u', 'u.id', '=', 'oi.masseuse_id');
            }

            $redemptions = $redemptionQuery
                ->get([
                    'o.order_no',
                    'o.created_at',
                    'oi.qty',
                    'c.name as customer_name',
                    'p.name as package_name',
                    DB::raw("COALESCE(u.name, '-') as redeemed_by"),
                ])
                ->map(static function ($row): array {
                    return [
                        'order_no' => (string) ($row->order_no ?? '-'),
                        'created_at' => (string) ($row->created_at ?? ''),
                        'qty' => (int) ($row->qty ?? 0),
                        'customer_name' => (string) ($row->customer_name ?? 'Walk-in'),
                        'package_name' => (string) ($row->package_name ?? '-'),
                        'redeemed_by' => (string) ($row->redeemed_by ?? '-'),
                    ];
                })
                ->all();
        }

        return [
            'moduleReady' => true,
            'search' => $normalizedSearch,
            'packages' => $packages,
            'balances' => $balances,
            'redemptions' => $redemptions,
        ];
    }

    public function createPackage(array $payload): void
    {
        $this->assertModuleReady();

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อแพ็กเกจ'],
            ]);
        }

        $exists = DB::table('packages')
            ->where('name', $name)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อแพ็กเกจนี้มีอยู่แล้ว'],
            ]);
        }

        $row = [
            'name' => $name,
            'price' => $this->normalizeMoney($payload['price'] ?? 0),
            'total_qty' => $this->normalizeQty($payload['total_qty'] ?? 1),
            'valid_days' => $this->normalizeValidDays($payload['valid_days'] ?? null),
        ];

        if ($this->hasColumn('packages', 'created_at')) {
            $row['created_at'] = now();
        }
        if ($this->hasColumn('packages', 'updated_at')) {
            $row['updated_at'] = now();
        }

        DB::table('packages')->insert($row);
    }

    public function updatePackage(int $packageId, array $payload): void
    {
        $this->assertModuleReady();

        $existing = DB::table('packages')
            ->where('id', $packageId)
            ->first(['id']);

        if ($existing === null) {
            throw ValidationException::withMessages([
                'package' => ['ไม่พบแพ็กเกจที่ต้องการแก้ไข'],
            ]);
        }

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อแพ็กเกจ'],
            ]);
        }

        $nameExists = DB::table('packages')
            ->where('name', $name)
            ->where('id', '!=', $packageId)
            ->exists();

        if ($nameExists) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อแพ็กเกจนี้มีอยู่แล้ว'],
            ]);
        }

        $updates = [
            'name' => $name,
            'price' => $this->normalizeMoney($payload['price'] ?? 0),
            'total_qty' => $this->normalizeQty($payload['total_qty'] ?? 1),
            'valid_days' => $this->normalizeValidDays($payload['valid_days'] ?? null),
        ];

        if ($this->hasColumn('packages', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('packages')
            ->where('id', $packageId)
            ->update($updates);
    }

    public function getPackagesForPos(): array
    {
        if (!$this->tableExists('packages')) {
            return [];
        }

        return DB::table('packages')
            ->orderBy('id')
            ->get(['id', 'name', 'price'])
            ->map(static function ($row): array {
                return [
                    'id' => 'package:' . (string) $row->id,
                    'source_id' => (int) $row->id,
                    'type' => 'package',
                    'name' => (string) ($row->name ?? ''),
                    'price' => (float) ($row->price ?? 0),
                    'duration' => null,
                ];
            })
            ->all();
    }

    public function getCustomerPackageBalancesMap(): array
    {
        if (!$this->tableExists('customer_packages') || !$this->tableExists('packages')) {
            return [];
        }

        $rows = DB::table('customer_packages as cp')
            ->join('packages as p', 'p.id', '=', 'cp.package_id')
            ->where('cp.remaining_qty', '>', 0)
            ->orderBy('cp.customer_id')
            ->orderByDesc('cp.id')
            ->get([
                'cp.customer_id',
                'cp.remaining_qty',
                'cp.expired_at',
                'p.name as package_name',
            ]);

        $map = [];
        foreach ($rows as $row) {
            $customerId = (int) ($row->customer_id ?? 0);
            if ($customerId <= 0) {
                continue;
            }

            if (!array_key_exists((string) $customerId, $map)) {
                $map[(string) $customerId] = [];
            }

            $map[(string) $customerId][] = [
                'package_name' => (string) ($row->package_name ?? ''),
                'remaining_qty' => (int) ($row->remaining_qty ?? 0),
                'expired_at' => $row->expired_at !== null ? (string) $row->expired_at : null,
            ];
        }

        return $map;
    }

    private function assertModuleReady(): void
    {
        if ($this->tableExists('packages') && $this->tableExists('customer_packages')) {
            return;
        }

        throw ValidationException::withMessages([
            'packages' => ['ยังไม่พบตาราง packages/customer_packages ในฐานข้อมูล'],
        ]);
    }

    private function normalizeMoney($value): float
    {
        $parsed = is_numeric($value) ? (float) $value : 0.0;
        if ($parsed < 0) {
            return 0.0;
        }

        return round($parsed, 2);
    }

    private function normalizeQty($value): int
    {
        $parsed = is_numeric($value) ? (int) $value : 1;
        return max(1, $parsed);
    }

    private function normalizeValidDays($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $parsed = is_numeric($value) ? (int) $value : 0;
        if ($parsed <= 0) {
            return null;
        }

        return $parsed;
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
