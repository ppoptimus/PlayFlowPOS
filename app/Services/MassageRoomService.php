<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class MassageRoomService
{
    private const DEFAULT_STATUS_LABELS = [
        'available' => 'พร้อมใช้งาน',
        'unavailable' => 'ไม่พร้อมใช้งาน',
        'maintenance' => 'ซ่อมบำรุง',
        'inactive' => 'ปิดใช้งาน',
    ];

    private BookingService $bookingService;

    private array $tableExistsCache = [];

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function getPageData(User $user, ?int $requestedBranchId): array
    {
        $branchId = $this->bookingService->resolveBranchIdForUser($user, $requestedBranchId);
        $moduleReady = $this->tableExists('rooms') && $this->tableExists('beds');
        $rooms = $moduleReady ? $this->getRoomRecords($branchId) : [];
        $totalBeds = array_sum(array_map(static function (array $room): int {
            return (int) ($room['bed_count'] ?? 0);
        }, $rooms));
        $availableBeds = array_sum(array_map(static function (array $room): int {
            return (int) ($room['available_bed_count'] ?? 0);
        }, $rooms));
        $bookingLinkedBeds = array_sum(array_map(static function (array $room): int {
            return (int) ($room['booking_linked_bed_count'] ?? 0);
        }, $rooms));

        return [
            'moduleReady' => $moduleReady,
            'activeBranchId' => $branchId,
            'canManage' => $this->canManage($user),
            'rooms' => $rooms,
            'statusOptions' => $moduleReady ? $this->getStatusOptions($branchId) : $this->getDefaultStatusOptions(),
            'summary' => [
                'rooms' => count($rooms),
                'beds' => $totalBeds,
                'availableBeds' => $availableBeds,
                'bookingLinkedBeds' => $bookingLinkedBeds,
            ],
        ];
    }

    public function createRoom(User $user, ?int $requestedBranchId, array $payload): void
    {
        $this->assertModuleReady();

        $branchId = $this->bookingService->resolveBranchIdForUser($user, $requestedBranchId);
        $name = $this->normalizeRequiredString($payload['name'] ?? null, 'name', 'กรุณาระบุชื่อห้องนวด');

        DB::transaction(function () use ($branchId, $name): void {
            $roomId = (int) DB::table('rooms')->insertGetId([
                'branch_id' => $branchId,
                'name' => $name,
            ]);

            DB::table('beds')->insert([
                'room_id' => $roomId,
                'name' => 'เตียง 1',
                'status' => 'available',
            ]);
        });
    }

    public function updateRoom(User $user, ?int $requestedBranchId, int $roomId, array $payload): void
    {
        $this->assertModuleReady();

        $branchId = $this->bookingService->resolveBranchIdForUser($user, $requestedBranchId);
        $room = $this->findRoomRow($branchId, $roomId);

        if ($room === null) {
            throw ValidationException::withMessages([
                'room' => ['ไม่พบห้องนวดที่ต้องการแก้ไขในสาขานี้'],
            ]);
        }

        DB::table('rooms')
            ->where('id', $roomId)
            ->where('branch_id', $branchId)
            ->update([
                'name' => $this->normalizeRequiredString($payload['name'] ?? null, 'name', 'กรุณาระบุชื่อห้องนวด'),
            ]);
    }

    public function deleteRoom(User $user, ?int $requestedBranchId, int $roomId): void
    {
        $this->assertModuleReady();

        $branchId = $this->bookingService->resolveBranchIdForUser($user, $requestedBranchId);
        $room = $this->findRoomRow($branchId, $roomId);

        if ($room === null) {
            throw ValidationException::withMessages([
                'room' => ['ไม่พบห้องนวดที่ต้องการลบในสาขานี้'],
            ]);
        }

        $hasBeds = DB::table('beds')
            ->where('room_id', $roomId)
            ->exists();

        if ($hasBeds) {
            throw ValidationException::withMessages([
                'room' => ['กรุณาลบเตียงทั้งหมดในห้องนี้ก่อนจึงจะลบห้องได้'],
            ]);
        }

        DB::table('rooms')
            ->where('id', $roomId)
            ->where('branch_id', $branchId)
            ->delete();
    }

    public function createBed(User $user, ?int $requestedBranchId, array $payload): void
    {
        $this->assertModuleReady();

        $branchId = $this->bookingService->resolveBranchIdForUser($user, $requestedBranchId);
        $roomId = (int) ($payload['room_id'] ?? 0);
        $room = $this->findRoomRow($branchId, $roomId);

        if ($room === null) {
            throw ValidationException::withMessages([
                'room_id' => ['ไม่พบห้องนวดที่เลือกในสาขานี้'],
            ]);
        }

        DB::table('beds')->insert([
            'room_id' => $roomId,
            'name' => $this->normalizeRequiredString($payload['name'] ?? null, 'name', 'กรุณาระบุชื่อเตียง'),
            'status' => $this->normalizeBedStatus($payload['status'] ?? null),
        ]);
    }

    public function updateBed(User $user, ?int $requestedBranchId, int $bedId, array $payload): void
    {
        $this->assertModuleReady();

        $branchId = $this->bookingService->resolveBranchIdForUser($user, $requestedBranchId);
        $bed = $this->findBedRow($branchId, $bedId);

        if ($bed === null) {
            throw ValidationException::withMessages([
                'bed' => ['ไม่พบเตียงที่ต้องการแก้ไขในสาขานี้'],
            ]);
        }

        $roomId = (int) ($payload['room_id'] ?? 0);
        $room = $this->findRoomRow($branchId, $roomId);

        if ($room === null) {
            throw ValidationException::withMessages([
                'room_id' => ['ไม่พบห้องนวดที่เลือกในสาขานี้'],
            ]);
        }

        DB::table('beds')
            ->where('id', $bedId)
            ->update([
                'room_id' => $roomId,
                'name' => $this->normalizeRequiredString($payload['name'] ?? null, 'name', 'กรุณาระบุชื่อเตียง'),
                'status' => $this->normalizeBedStatus($payload['status'] ?? null),
            ]);
    }

    public function deleteBed(User $user, ?int $requestedBranchId, int $bedId): void
    {
        $this->assertModuleReady();

        $branchId = $this->bookingService->resolveBranchIdForUser($user, $requestedBranchId);
        $bed = $this->findBedRow($branchId, $bedId);

        if ($bed === null) {
            throw ValidationException::withMessages([
                'bed' => ['ไม่พบเตียงที่ต้องการลบในสาขานี้'],
            ]);
        }

        if ($this->tableExists('bookings')) {
            $hasBookings = DB::table('bookings')
                ->where('bed_id', $bedId)
                ->exists();

            if ($hasBookings) {
                throw ValidationException::withMessages([
                    'bed' => ['ไม่สามารถลบเตียงที่มีประวัติคิวได้'],
                ]);
            }
        }

        DB::table('beds')
            ->where('id', $bedId)
            ->delete();
    }

    private function getRoomRecords(int $branchId): array
    {
        $roomRows = DB::table('rooms')
            ->where('branch_id', $branchId)
            ->orderBy('id')
            ->get(['id', 'name']);

        if ($roomRows->isEmpty()) {
            return [];
        }

        $roomIds = $roomRows->pluck('id')->map(static function ($id): int {
            return (int) $id;
        })->all();

        $bedRows = DB::table('beds')
            ->whereIn('room_id', $roomIds)
            ->orderBy('room_id')
            ->orderBy('id')
            ->get(['id', 'room_id', 'name', 'status']);

        $bookingCounts = [];
        if ($this->tableExists('bookings') && !$bedRows->isEmpty()) {
            $bedIds = $bedRows->pluck('id')->map(static function ($id): int {
                return (int) $id;
            })->all();

            $bookingCounts = DB::table('bookings')
                ->selectRaw('bed_id, COUNT(*) as booking_count')
                ->whereNotNull('bed_id')
                ->whereIn('bed_id', $bedIds)
                ->groupBy('bed_id')
                ->pluck('booking_count', 'bed_id')
                ->map(static function ($count): int {
                    return (int) $count;
                })
                ->all();
        }

        $bedsByRoom = [];
        foreach ($bedRows as $bedRow) {
            $roomId = (int) $bedRow->room_id;
            if (!isset($bedsByRoom[$roomId])) {
                $bedsByRoom[$roomId] = [];
            }

            $status = $bedRow->status !== null && trim((string) $bedRow->status) !== ''
                ? trim((string) $bedRow->status)
                : 'available';
            $bookingCount = (int) ($bookingCounts[(int) $bedRow->id] ?? 0);

            $bedsByRoom[$roomId][] = [
                'id' => (int) $bedRow->id,
                'room_id' => $roomId,
                'name' => (string) $bedRow->name,
                'status' => $status,
                'status_label' => $this->formatStatusLabel($status),
                'booking_count' => $bookingCount,
                'can_delete' => $bookingCount === 0,
            ];
        }

        return $roomRows->map(function ($roomRow) use ($bedsByRoom): array {
            $roomId = (int) $roomRow->id;
            $beds = $bedsByRoom[$roomId] ?? [];

            return [
                'id' => $roomId,
                'name' => (string) $roomRow->name,
                'beds' => $beds,
                'bed_count' => count($beds),
                'available_bed_count' => count(array_filter($beds, function (array $bed): bool {
                    return (string) ($bed['status'] ?? '') === 'available';
                })),
                'booking_linked_bed_count' => count(array_filter($beds, function (array $bed): bool {
                    return (int) ($bed['booking_count'] ?? 0) > 0;
                })),
            ];
        })->all();
    }

    private function getStatusOptions(int $branchId): array
    {
        $options = $this->getDefaultStatusOptions();

        $rows = DB::table('beds as b')
            ->join('rooms as r', 'r.id', '=', 'b.room_id')
            ->where('r.branch_id', $branchId)
            ->whereNotNull('b.status')
            ->distinct()
            ->orderBy('b.status')
            ->pluck('b.status');

        foreach ($rows as $status) {
            $value = trim((string) $status);
            if ($value === '') {
                continue;
            }

            if (!isset($options[$value])) {
                $options[$value] = [
                    'value' => $value,
                    'label' => $this->formatStatusLabel($value),
                ];
            }
        }

        return array_values($options);
    }

    private function getDefaultStatusOptions(): array
    {
        $options = [];

        foreach (self::DEFAULT_STATUS_LABELS as $value => $label) {
            $options[$value] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }

    private function formatStatusLabel(string $status): string
    {
        if (isset(self::DEFAULT_STATUS_LABELS[$status])) {
            return self::DEFAULT_STATUS_LABELS[$status];
        }

        return ucfirst(str_replace(['-', '_'], ' ', $status));
    }

    private function normalizeRequiredString($value, string $field, string $message): string
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            throw ValidationException::withMessages([
                $field => [$message],
            ]);
        }

        return $normalized;
    }

    private function normalizeBedStatus($value): string
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return 'available';
        }

        return $normalized;
    }

    private function findRoomRow(int $branchId, int $roomId): ?object
    {
        return DB::table('rooms')
            ->where('id', $roomId)
            ->where('branch_id', $branchId)
            ->first(['id', 'branch_id', 'name']);
    }

    private function findBedRow(int $branchId, int $bedId): ?object
    {
        return DB::table('beds as b')
            ->join('rooms as r', 'r.id', '=', 'b.room_id')
            ->where('b.id', $bedId)
            ->where('r.branch_id', $branchId)
            ->first(['b.id', 'b.room_id', 'b.name', 'b.status']);
    }

    private function assertModuleReady(): void
    {
        if ($this->tableExists('rooms') && $this->tableExists('beds')) {
            return;
        }

        throw ValidationException::withMessages([
            'rooms' => ['ยังไม่พบตาราง rooms และ beds ในฐานข้อมูล'],
        ]);
    }

    private function canManage(User $user): bool
    {
        return in_array((string) ($user->role ?? ''), ['super_admin', 'branch_manager'], true);
    }

    private function tableExists(string $table): bool
    {
        if (!array_key_exists($table, $this->tableExistsCache)) {
            $this->tableExistsCache[$table] = Schema::hasTable($table);
        }

        return (bool) $this->tableExistsCache[$table];
    }
}
