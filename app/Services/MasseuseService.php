<?php

namespace App\Services;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MasseuseService
{
    private const STATUS_LABELS = [
        'available' => 'พร้อมรับงาน',
        'busy' => 'ติดคิว',
        'on_break' => 'พัก',
        'off_duty' => 'ไม่ประจำกะ',
    ];

    private BookingService $bookingService;

    private array $tableExistsCache = [];

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function getPageData(User $user, ?int $requestedBranchId, string $date): array
    {
        $pageData = $this->bookingService->getStaffPageData($user, $requestedBranchId, $date);
        $activeBranchId = (int) ($pageData['activeBranchId'] ?? 0);
        $staffWithMonthlySummary = $this->appendMonthlySummary(
            isset($pageData['staff']) && is_array($pageData['staff']) ? $pageData['staff'] : [],
            $activeBranchId,
            $date
        );

        return array_merge($pageData, [
            'staff' => $staffWithMonthlySummary,
            'moduleReady' => $this->tableExists('masseuses'),
            'canManage' => $this->canManage($user),
            'statusOptions' => $this->getStatusOptions(),
            'staffRecords' => $this->tableExists('masseuses')
                ? $this->getStaffRecords($activeBranchId, $staffWithMonthlySummary)
                : [],
        ]);
    }

    public function getEmptyFormRecord(): array
    {
        return [
            'id' => null,
            'display_id' => 'NEW',
            'nickname' => '',
            'full_name' => '',
            'name' => '',
            'profile_image' => '',
            'avatar' => '',
            'skills_description' => '',
            'status_value' => 'available',
            'status_label' => $this->formatStatusLabel('available'),
            'income' => 0.0,
            'commission' => 0.0,
            'queue_count' => 0,
            'queue_load' => 0,
            'is_working_today' => false,
            'performance_status' => $this->formatStatusLabel('available'),
            'latest_queue' => null,
        ];
    }

    public function createMasseuse(User $user, ?int $requestedBranchId, array $payload, ?UploadedFile $profileImage): void
    {
        $this->assertModuleReady();

        $branchId = $this->bookingService->resolveBranchIdForUser($user, $requestedBranchId);

        $row = [
            'branch_id' => $branchId,
            'nickname' => $this->normalizeRequiredString($payload['nickname'] ?? null, 'nickname', 'กรุณาระบุชื่อเล่นหมอนวด'),
            'full_name' => $this->normalizeNullableString($payload['full_name'] ?? null),
            'skills_description' => $this->normalizeNullableString($payload['skills_description'] ?? null),
            'status' => $this->normalizeStatus($payload['status'] ?? null),
        ];

        if ($profileImage !== null) {
            $row['profile_image'] = $this->storeProfileImage($profileImage);
        }

        DB::table('masseuses')->insert($row);
    }

    public function updateStaffAttendance(
        User $user,
        ?int $requestedBranchId,
        string $date,
        int $staffId,
        bool $isWorking
    ): array {
        return $this->bookingService->updateStaffAttendance(
            $user,
            $requestedBranchId,
            $date,
            $staffId,
            $isWorking
        );
    }

    public function updateMasseuse(
        User $user,
        ?int $requestedBranchId,
        int $staffId,
        array $payload,
        ?UploadedFile $profileImage
    ): void {
        $this->assertModuleReady();

        $branchId = $this->bookingService->resolveBranchIdForUser($user, $requestedBranchId);
        $existing = $this->findStaffRow($branchId, $staffId);

        if ($existing === null) {
            throw ValidationException::withMessages([
                'staff' => ['ไม่พบข้อมูลหมอนวดที่ต้องการแก้ไข'],
            ]);
        }

        $updates = [
            'nickname' => $this->normalizeRequiredString($payload['nickname'] ?? null, 'nickname', 'กรุณาระบุชื่อเล่นหมอนวด'),
            'full_name' => $this->normalizeNullableString($payload['full_name'] ?? null),
            'skills_description' => $this->normalizeNullableString($payload['skills_description'] ?? null),
            'status' => $this->normalizeStatus($payload['status'] ?? null),
        ];

        $removeProfileImage = !empty($payload['remove_profile_image']);
        if ($removeProfileImage) {
            $updates['profile_image'] = null;
        }

        if ($profileImage !== null) {
            $updates['profile_image'] = $this->storeProfileImage($profileImage);
        }

        DB::table('masseuses')
            ->where('id', $staffId)
            ->where('branch_id', $branchId)
            ->update($updates);

        if ($removeProfileImage && $existing->profile_image !== null) {
            $this->deleteManagedProfileImage((string) $existing->profile_image);
        }

        if ($profileImage !== null && $existing->profile_image !== null) {
            $this->deleteManagedProfileImage((string) $existing->profile_image);
        }
    }

    public function deleteMasseuse(User $user, ?int $requestedBranchId, int $staffId): void
    {
        $this->assertModuleReady();

        $branchId = $this->bookingService->resolveBranchIdForUser($user, $requestedBranchId);
        $existing = $this->findStaffRow($branchId, $staffId);

        if ($existing === null) {
            throw ValidationException::withMessages([
                'staff' => ['ไม่พบข้อมูลหมอนวดที่ต้องการลบ'],
            ]);
        }

        if ($this->tableExists('bookings')) {
            $hasBookings = DB::table('bookings')
                ->where('masseuse_id', $staffId)
                ->exists();

            if ($hasBookings) {
                throw ValidationException::withMessages([
                    'staff' => ['ไม่สามารถลบหมอนวดที่มีประวัติคิวงานได้'],
                ]);
            }
        }

        if ($this->tableExists('order_items')) {
            $hasSales = DB::table('order_items')
                ->where('masseuse_id', $staffId)
                ->exists();

            if ($hasSales) {
                throw ValidationException::withMessages([
                    'staff' => ['ไม่สามารถลบหมอนวดที่มีประวัติรายการขายได้'],
                ]);
            }
        }

        if ($this->tableExists('staff_attendance')) {
            DB::table('staff_attendance')
                ->where('masseuse_id', $staffId)
                ->delete();
        }

        DB::table('masseuses')
            ->where('id', $staffId)
            ->where('branch_id', $branchId)
            ->delete();

        if ($existing->profile_image !== null) {
            $this->deleteManagedProfileImage((string) $existing->profile_image);
        }
    }

    public function getStatusOptions(): array
    {
        $options = [];

        foreach (self::STATUS_LABELS as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }

    private function getStaffRecords(int $branchId, array $staffStats): array
    {
        $statsById = [];
        foreach ($staffStats as $staff) {
            $statsById[(string) ($staff['id'] ?? '')] = $staff;
        }

        return DB::table('masseuses')
            ->where('branch_id', $branchId)
            ->orderBy('id')
            ->get([
                'id',
                'branch_id',
                'nickname',
                'full_name',
                'profile_image',
                'skills_description',
                'status',
                'base_salary',
            ])
            ->map(function ($row) use ($statsById): array {
                $staffId = (string) $row->id;
                $stats = $statsById[$staffId] ?? [];
                $queue = isset($stats['queue']) && is_array($stats['queue']) ? $stats['queue'] : [];

                return [
                    'id' => (int) $row->id,
                    'display_id' => 'MS' . str_pad($staffId, 3, '0', STR_PAD_LEFT),
                    'nickname' => (string) ($row->nickname ?? ''),
                    'full_name' => $row->full_name !== null ? (string) $row->full_name : '',
                    'name' => (string) ($stats['name'] ?? $row->nickname ?? ''),
                    'profile_image' => $row->profile_image !== null ? (string) $row->profile_image : '',
                    'avatar' => $this->resolveAvatar(
                        $row->profile_image !== null ? (string) $row->profile_image : '',
                        $staffId
                    ),
                    'skills_description' => $row->skills_description !== null ? (string) $row->skills_description : '',
                    'status_value' => $row->status !== null ? (string) $row->status : 'off_duty',
                    'status_label' => $this->formatStatusLabel($row->status !== null ? (string) $row->status : 'off_duty'),
                    'base_salary' => $row->base_salary !== null ? (float) $row->base_salary : 0.0,
                    'income' => (float) ($stats['income'] ?? 0),
                    'commission' => (float) ($stats['commission'] ?? 0),
                    'queue_count' => count($queue),
                    'queue_load' => (int) ($stats['queueLoad'] ?? 0),
                    'is_working_today' => (bool) ($stats['isWorkingToday'] ?? true),
                    'performance_status' => (string) ($stats['status'] ?? $this->formatStatusLabel($row->status !== null ? (string) $row->status : 'off_duty')),
                    'latest_queue' => $queue[0] ?? null,
                ];
            })
            ->all();
    }

    private function appendMonthlySummary(array $staffStats, int $branchId, string $date): array
    {
        $monthlySummaryByStaff = $this->getMonthlySummaryByStaff($branchId, $date);

        return array_map(static function (array $staff) use ($monthlySummaryByStaff): array {
            $staffId = (string) ($staff['id'] ?? '');
            $queue = isset($staff['queue']) && is_array($staff['queue']) ? $staff['queue'] : [];
            $monthlySummary = $monthlySummaryByStaff[$staffId] ?? [
                'income' => 0.0,
                'commission' => 0.0,
                'queue_count' => 0,
            ];

            $staff['daily_queue_count'] = count($queue);
            $staff['monthly_income'] = (float) ($monthlySummary['income'] ?? 0.0);
            $staff['monthly_commission'] = (float) ($monthlySummary['commission'] ?? 0.0);
            $staff['monthly_queue_count'] = (int) ($monthlySummary['queue_count'] ?? 0);

            return $staff;
        }, $staffStats);
    }

    private function getMonthlySummaryByStaff(int $branchId, string $date): array
    {
        if (!$this->tableExists('bookings') || !$this->tableExists('services')) {
            return [];
        }

        $selectedDate = Carbon::parse($date);
        $monthStart = $selectedDate->copy()->startOfMonth()->startOfDay();
        $nextMonthStart = $selectedDate->copy()->addMonthNoOverflow()->startOfMonth()->startOfDay();

        $bookingRows = DB::table('bookings as b')
            ->leftJoin('services as s', 's.id', '=', 'b.service_id')
            ->where('b.branch_id', $branchId)
            ->whereNotNull('b.masseuse_id')
            ->where('b.status', '!=', 'cancelled')
            ->where('b.start_time', '>=', $monthStart->format('Y-m-d H:i:s'))
            ->where('b.start_time', '<', $nextMonthStart->format('Y-m-d H:i:s'))
            ->orderBy('b.start_time')
            ->get([
                'b.id',
                'b.masseuse_id',
                'b.service_id',
                's.name as service_name',
                's.price as service_price',
            ]);

        if ($bookingRows->isEmpty()) {
            return [];
        }

        $serviceMap = $this->loadBookingServicesMap($bookingRows);
        $commissionConfigs = $this->getCommissionConfigsByService();
        $summaryByStaff = [];

        foreach ($bookingRows as $row) {
            $staffId = (string) $row->masseuse_id;
            $bookingId = (int) $row->id;
            $services = $serviceMap[$bookingId] ?? [];

            if (!isset($summaryByStaff[$staffId])) {
                $summaryByStaff[$staffId] = [
                    'income' => 0.0,
                    'commission' => 0.0,
                    'queue_count' => 0,
                ];
            }

            $summaryByStaff[$staffId]['queue_count']++;
            $summaryByStaff[$staffId]['income'] += array_reduce($services, static function (float $sum, array $service): float {
                return $sum + (float) ($service['price'] ?? 0.0);
            }, 0.0);
            $summaryByStaff[$staffId]['commission'] += $this->estimateServicesCommission($services, $commissionConfigs);
        }

        return $summaryByStaff;
    }

    private function loadBookingServicesMap($bookingRows): array
    {
        $bookingIds = [];
        $fallbackRows = [];

        foreach ($bookingRows as $row) {
            $bookingId = (int) ($row->id ?? 0);
            if ($bookingId <= 0) {
                continue;
            }

            $bookingIds[] = $bookingId;
            $fallbackRows[$bookingId] = $row;
        }

        if (empty($bookingIds)) {
            return [];
        }

        $servicesByBooking = [];

        if ($this->tableExists('booking_services')) {
            $rows = DB::table('booking_services as bs')
                ->join('services as s', 's.id', '=', 'bs.service_id')
                ->whereIn('bs.booking_id', $bookingIds)
                ->orderBy('bs.booking_id')
                ->orderBy('bs.sort_order')
                ->orderBy('bs.id')
                ->get([
                    'bs.booking_id',
                    'bs.service_id',
                    's.name as service_name',
                    's.price as service_price',
                ]);

            foreach ($rows as $row) {
                $bookingId = (int) $row->booking_id;
                if (!isset($servicesByBooking[$bookingId])) {
                    $servicesByBooking[$bookingId] = [];
                }

                $servicesByBooking[$bookingId][] = [
                    'id' => (int) $row->service_id,
                    'name' => (string) ($row->service_name ?? ''),
                    'price' => (float) ($row->service_price ?? 0.0),
                ];
            }
        }

        foreach ($bookingIds as $bookingId) {
            if (isset($servicesByBooking[$bookingId]) && !empty($servicesByBooking[$bookingId])) {
                continue;
            }

            $fallbackRow = $fallbackRows[$bookingId] ?? null;
            if ($fallbackRow === null || $fallbackRow->service_id === null) {
                $servicesByBooking[$bookingId] = [];
                continue;
            }

            $servicesByBooking[$bookingId] = [[
                'id' => (int) $fallbackRow->service_id,
                'name' => (string) ($fallbackRow->service_name ?? ''),
                'price' => (float) ($fallbackRow->service_price ?? 0.0),
            ]];
        }

        return $servicesByBooking;
    }

    private function getCommissionConfigsByService(): array
    {
        if (!$this->tableExists('commission_configs')) {
            return [];
        }

        return DB::table('commission_configs')
            ->get(['service_id', 'type', 'value', 'deduct_cost'])
            ->keyBy('service_id')
            ->map(static function ($row): array {
                return [
                    'type' => (string) $row->type,
                    'value' => (float) $row->value,
                    'deduct_cost' => (float) $row->deduct_cost,
                ];
            })
            ->all();
    }

    private function estimateServicesCommission(array $services, array $commissionConfigs): float
    {
        $total = 0.0;

        foreach ($services as $service) {
            $serviceId = isset($service['id']) ? (int) $service['id'] : 0;
            if ($serviceId <= 0 || !isset($commissionConfigs[$serviceId])) {
                continue;
            }

            $config = $commissionConfigs[$serviceId];
            $servicePrice = isset($service['price']) ? (float) $service['price'] : 0.0;

            if (($config['type'] ?? '') === 'fixed') {
                $total += (float) ($config['value'] ?? 0.0);
                continue;
            }

            $baseAmount = max(0.0, $servicePrice - (float) ($config['deduct_cost'] ?? 0.0));
            $total += $baseAmount * ((float) ($config['value'] ?? 0.0) / 100);
        }

        return $total;
    }

    private function findStaffRow(int $branchId, int $staffId): ?object
    {
        return DB::table('masseuses')
            ->where('branch_id', $branchId)
            ->where('id', $staffId)
            ->first([
                'id',
                'branch_id',
                'profile_image',
            ]);
    }

    private function resolveAvatar(string $profileImage, string $staffId): string
    {
        if ($profileImage === '') {
            return 'https://i.pravatar.cc/160?u=' . rawurlencode('masseuse-' . $staffId);
        }

        if (preg_match('#^https?://#i', $profileImage) === 1) {
            return $profileImage;
        }

        return '/' . ltrim($profileImage, '/');
    }

    private function storeProfileImage(UploadedFile $profileImage): string
    {
        $directory = public_path('uploads/masseuses');
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $extension = strtolower($profileImage->getClientOriginalExtension());
        $safeExtension = $extension !== '' ? $extension : 'jpg';
        $filename = 'masseuse-' . now()->format('YmdHis') . '-' . Str::random(8) . '.' . $safeExtension;

        $profileImage->move($directory, $filename);

        return 'uploads/masseuses/' . $filename;
    }

    private function deleteManagedProfileImage(string $profileImage): void
    {
        if (preg_match('#^https?://#i', $profileImage) === 1) {
            return;
        }

        $normalizedPath = ltrim($profileImage, '/');
        if (strpos($normalizedPath, 'uploads/masseuses/') !== 0) {
            return;
        }

        $absolutePath = public_path($normalizedPath);
        if (File::exists($absolutePath)) {
            File::delete($absolutePath);
        }
    }

    private function formatStatusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ucfirst(str_replace('_', ' ', $status));
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

    private function normalizeNullableString($value): ?string
    {
        $normalized = trim((string) $value);
        return $normalized !== '' ? $normalized : null;
    }

    private function normalizeStatus($value): string
    {
        $status = trim((string) $value);
        if (!array_key_exists($status, self::STATUS_LABELS)) {
            throw ValidationException::withMessages([
                'status' => ['สถานะหมอนวดไม่ถูกต้อง'],
            ]);
        }

        return $status;
    }

    private function assertModuleReady(): void
    {
        if ($this->tableExists('masseuses')) {
            return;
        }

        throw ValidationException::withMessages([
            'masseuses' => ['ยังไม่พบตาราง masseuses ในฐานข้อมูล'],
        ]);
    }

    private function canManage(User $user): bool
    {
        return in_array((string) ($user->role ?? ''), ['admin', 'super_admin'], true);
    }

    private function tableExists(string $table): bool
    {
        if (!array_key_exists($table, $this->tableExistsCache)) {
            $this->tableExistsCache[$table] = Schema::hasTable($table);
        }

        return (bool) $this->tableExistsCache[$table];
    }
}
