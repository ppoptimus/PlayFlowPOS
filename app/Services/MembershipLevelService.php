<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class MembershipLevelService
{
    private array $tableExistsCache = [];
    private array $columnExistsCache = [];

    public function getPageData(string $search = ''): array
    {
        $normalizedSearch = trim($search);

        if (!$this->tableExists('membership_tiers')) {
            return [
                'moduleReady' => false,
                'search' => $normalizedSearch,
                'tiers' => [],
            ];
        }

        $query = DB::table('membership_tiers')
            ->orderBy('min_spend')
            ->orderBy('id');

        if ($normalizedSearch !== '' && $this->hasColumn('membership_tiers', 'name')) {
            $query->where('name', 'like', '%' . $normalizedSearch . '%');
        }

        $tiers = $query
            ->get(['id', 'name', 'discount_percent', 'min_spend'])
            ->map(static function ($row): array {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) $row->name,
                    'discount_percent' => (float) ($row->discount_percent ?? 0),
                    'min_spend' => (float) ($row->min_spend ?? 0),
                ];
            })
            ->all();

        return [
            'moduleReady' => true,
            'search' => $normalizedSearch,
            'tiers' => $tiers,
        ];
    }

    public function createTier(array $payload): void
    {
        $this->assertModuleReady();

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อระดับสมาชิก'],
            ]);
        }

        $exists = DB::table('membership_tiers')
            ->where('name', $name)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อระดับสมาชิกนี้มีอยู่แล้ว'],
            ]);
        }

        $row = [
            'name' => $name,
            'discount_percent' => $this->normalizePercent($payload['discount_percent'] ?? 0),
            'min_spend' => $this->normalizeMoney($payload['min_spend'] ?? 0),
        ];

        if ($this->hasColumn('membership_tiers', 'created_at')) {
            $row['created_at'] = now();
        }
        if ($this->hasColumn('membership_tiers', 'updated_at')) {
            $row['updated_at'] = now();
        }

        DB::table('membership_tiers')->insert($row);
    }

    public function updateTier(int $tierId, array $payload): void
    {
        $this->assertModuleReady();

        $tier = DB::table('membership_tiers')->where('id', $tierId)->first(['id', 'name']);
        if ($tier === null) {
            throw ValidationException::withMessages([
                'tier' => ['ไม่พบระดับสมาชิกที่ต้องการแก้ไข'],
            ]);
        }

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['กรุณาระบุชื่อระดับสมาชิก'],
            ]);
        }

        $nameExists = DB::table('membership_tiers')
            ->where('name', $name)
            ->where('id', '!=', $tierId)
            ->exists();

        if ($nameExists) {
            throw ValidationException::withMessages([
                'name' => ['ชื่อระดับสมาชิกนี้มีอยู่แล้ว'],
            ]);
        }

        $updates = [
            'name' => $name,
            'discount_percent' => $this->normalizePercent($payload['discount_percent'] ?? 0),
            'min_spend' => $this->normalizeMoney($payload['min_spend'] ?? 0),
        ];

        if ($this->hasColumn('membership_tiers', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        DB::table('membership_tiers')
            ->where('id', $tierId)
            ->update($updates);
    }

    private function assertModuleReady(): void
    {
        if ($this->tableExists('membership_tiers')) {
            return;
        }

        throw ValidationException::withMessages([
            'membership_tiers' => ['ยังไม่พบตาราง membership_tiers ในฐานข้อมูล'],
        ]);
    }

    private function normalizePercent($value): float
    {
        $parsed = is_numeric($value) ? (float) $value : 0.0;
        if ($parsed < 0) {
            return 0.0;
        }
        if ($parsed > 100) {
            return 100.0;
        }
        return round($parsed, 2);
    }

    private function normalizeMoney($value): float
    {
        $parsed = is_numeric($value) ? (float) $value : 0.0;
        if ($parsed < 0) {
            return 0.0;
        }
        return round($parsed, 2);
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
