<?php

namespace App\Services;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    private const STATUSES = [
        'waiting' => 'รอรับบริการ',
        'in_service' => 'กำลังนวด',
        'completed' => 'เสร็จสิ้น',
        'cancelled' => 'ยกเลิก',
    ];

    private StaffAttendanceService $staffAttendanceService;

    public function __construct(StaffAttendanceService $staffAttendanceService)
    {
        $this->staffAttendanceService = $staffAttendanceService;
    }

    public function getPageData(User $user, ?int $requestedBranchId, string $date): array
    {
        $branchId = $this->resolveAuthorizedBranchId($user, $requestedBranchId);
        $selectedDate = $this->normalizeDate($date);

        return [
            'activeBranchId' => $branchId,
            'selectedDate' => $selectedDate,
            'staff' => $this->getStaff($branchId, $selectedDate, true),
            'serviceItems' => $this->getServiceItems(),
            'customers' => $this->getCustomers(),
            'beds' => $this->getBeds($branchId),
            'statuses' => [
                'waiting' => self::STATUSES['waiting'],
                'in_service' => self::STATUSES['in_service'],
                'cancelled' => self::STATUSES['cancelled'],
            ],
            'bookings' => $this->getBookingsByDate($branchId, $selectedDate),
        ];
    }

    public function getStaffRoster(User $user, ?int $requestedBranchId, string $date, bool $onlyWorking = false): array
    {
        $branchId = $this->resolveAuthorizedBranchId($user, $requestedBranchId);
        $selectedDate = $this->normalizeDate($date);

        return $this->getStaff($branchId, $selectedDate, $onlyWorking);
    }

    public function getStaffPageData(User $user, ?int $requestedBranchId, string $date): array
    {
        $branchId = $this->resolveAuthorizedBranchId($user, $requestedBranchId);
        $selectedDate = $this->normalizeDate($date);

        return [
            'activeBranchId' => $branchId,
            'selectedDate' => $selectedDate,
            'staff' => $this->buildStaffPageStaff($branchId, $selectedDate),
        ];
    }

    public function resolveBranchIdForUser(User $user, ?int $requestedBranchId): int
    {
        return $this->resolveAuthorizedBranchId($user, $requestedBranchId);
    }

    public function updateStaffAttendance(User $user, ?int $requestedBranchId, string $date, int $staffId, bool $isWorking): array
    {
        $branchId = $this->resolveAuthorizedBranchId($user, $requestedBranchId);
        $selectedDate = $this->normalizeDate($date);
        $staff = $this->findStaffById($branchId, $staffId);

        if ($staff === null) {
            throw ValidationException::withMessages([
                'staff_id' => ['à¹„à¸¡à¹ˆà¸žà¸šà¸žà¸™à¸±à¸à¸‡à¸²à¸™à¹ƒà¸™à¸ªà¸²à¸‚à¸²à¸™à¸µà¹‰'],
            ]);
        }

        $this->staffAttendanceService->setAttendance($branchId, $selectedDate, (string) $staffId, $isWorking);

        return [
            'staff_id' => $staffId,
            'date' => $selectedDate,
            'isWorkingToday' => $isWorking,
        ];
    }

    public function getBookingsDataForDate(User $user, ?int $requestedBranchId, string $date): array
    {
        $branchId = $this->resolveAuthorizedBranchId($user, $requestedBranchId);
        $selectedDate = $this->normalizeDate($date);

        return [
            'branch_id' => $branchId,
            'date' => $selectedDate,
            'bookings' => $this->getBookingsByDate($branchId, $selectedDate),
        ];
    }

    public function saveBooking(?int $bookingId, array $payload, User $user): array
    {
        $requestedBranchId = isset($payload['branch_id']) ? (int) $payload['branch_id'] : null;
        $branchId = $this->resolveAuthorizedBranchId($user, $requestedBranchId);
        $queueDate = $this->normalizeDate((string) $payload['queue_date']);
        $startAt = Carbon::createFromFormat('Y-m-d H:i', $queueDate . ' ' . $payload['start_time']);
        $endAt = Carbon::createFromFormat('Y-m-d H:i', $queueDate . ' ' . $payload['end_time']);
        $customerId = (int) $payload['customer_id'];
        $serviceId = (int) $payload['service_id'];
        $masseuseId = isset($payload['masseuse_id']) ? (int) $payload['masseuse_id'] : null;
        $bedId = isset($payload['bed_id']) ? (int) $payload['bed_id'] : null;
        $status = (string) $payload['status'];
        $cancelReason = isset($payload['cancel_reason']) ? (string) $payload['cancel_reason'] : null;

        if ($bookingId !== null) {
            $existing = DB::table('bookings')
                ->where('id', $bookingId)
                ->where('branch_id', $branchId)
                ->first();

            if ($existing === null) {
                throw ValidationException::withMessages([
                    'booking' => ['ไม่พบคิวที่ต้องการแก้ไขในสาขานี้'],
                ]);
            }
        }

        $this->ensureServiceExists($serviceId);
        $this->ensureCustomerExists($customerId);
        $this->ensureMasseuseInBranch($masseuseId, $branchId);
        $this->ensureBedInBranch($bedId, $branchId);
        $this->ensureNoTimeConflict($branchId, $startAt, $endAt, $masseuseId, $bedId, $bookingId);

        $data = [
            'branch_id' => $branchId,
            'customer_id' => $customerId,
            'service_id' => $serviceId,
            'masseuse_id' => $masseuseId,
            'bed_id' => $bedId,
            'start_time' => $startAt->format('Y-m-d H:i:s'),
            'end_time' => $endAt->format('Y-m-d H:i:s'),
            'status' => $status,
            'cancel_reason' => $status === 'cancelled' ? $cancelReason : null,
        ];

        if ($bookingId === null) {
            $bookingId = (int) DB::table('bookings')->insertGetId($data);
        } else {
            DB::table('bookings')
                ->where('id', $bookingId)
                ->update($data);
        }

        return $this->findBookingById($bookingId, $branchId);
    }

    public function cancelBooking(int $bookingId, User $user, ?int $requestedBranchId = null, ?string $cancelReason = null): array
    {
        $branchId = $this->resolveAuthorizedBranchId($user, $requestedBranchId);
        $booking = DB::table('bookings')
            ->where('id', $bookingId)
            ->where('branch_id', $branchId)
            ->first();

        if ($booking === null) {
            throw ValidationException::withMessages([
                'booking' => ['ไม่พบคิวที่ต้องการยกเลิกในสาขานี้'],
            ]);
        }

        DB::table('bookings')
            ->where('id', $bookingId)
            ->update([
                'status' => 'cancelled',
                'cancel_reason' => $cancelReason !== null && $cancelReason !== '' ? $cancelReason : 'ยกเลิกจากหน้าจองคิว',
            ]);

        return $this->findBookingById($bookingId, $branchId);
    }

    public function deleteBooking(int $bookingId, User $user, ?int $requestedBranchId = null): void
    {
        $branchId = $this->resolveAuthorizedBranchId($user, $requestedBranchId);
        $booking = DB::table('bookings')
            ->where('id', $bookingId)
            ->where('branch_id', $branchId)
            ->first();

        if ($booking === null) {
            throw ValidationException::withMessages([
                'booking' => ['ไม่พบคิวที่ต้องการลบในสาขานี้'],
            ]);
        }

        if ((string) $booking->status === 'completed') {
            throw ValidationException::withMessages([
                'booking' => ['คิวที่ชำระเงินแล้วไม่สามารถยกเลิกหรือลบได้'],
            ]);
        }

        DB::table('bookings')
            ->where('id', $bookingId)
            ->delete();
    }

    public function getBookingContextForCheckout(User $user, int $bookingId, ?int $requestedBranchId = null): array
    {
        $branchId = $this->resolveAuthorizedBranchId($user, $requestedBranchId);
        $booking = $this->buildBookingQuery($branchId)
            ->where('b.id', $bookingId)
            ->first();

        if ($booking === null) {
            throw ValidationException::withMessages([
                'booking' => ['ไม่พบคิวที่เลือกสำหรับการชำระเงิน'],
            ]);
        }

        return $this->mapBookingToCheckoutContext($this->mapBookingRow($booking));
    }

    private function getStaff(int $branchId, string $date, bool $onlyWorking = false): array
    {
        return DB::table('masseuses as m')
            ->where('m.branch_id', $branchId)
            ->selectRaw(
                "m.id, " .
                "COALESCE(NULLIF(m.full_name, ''), NULLIF(m.nickname, ''), CONCAT('Masseuse #', m.id)) as name, " .
                "m.status, m.profile_image"
            )
            ->orderBy('m.id')
            ->get()
            ->map(function ($row) use ($branchId, $date): array {
                $staffId = (string) $row->id;
                $isWorkingToday = $this->staffAttendanceService->isWorking($branchId, $date, $staffId);

                return [
                    'id' => $staffId,
                    'name' => (string) $row->name,
                    'profileImage' => $row->profile_image !== null ? (string) $row->profile_image : '',
                    'isWorkingToday' => $isWorkingToday,
                    'attendanceLabel' => $isWorkingToday ? 'มาทำงานวันนี้' : 'ไม่มาทำงานวันนี้',
                ];
            })
            ->filter(static function (array $staff) use ($onlyWorking): bool {
                if (!$onlyWorking) {
                    return true;
                }

                return (bool) ($staff['isWorkingToday'] ?? false);
            })
            ->values()
            ->all();
    }

    private function buildStaffPageStaff(int $branchId, string $date): array
    {
        $staffRoster = $this->getStaff($branchId, $date, false);
        $queueByStaff = $this->getQueueByStaff($branchId, $date);
        $commissionConfigs = $this->getCommissionConfigsByService();

        return array_map(function (array $staff) use ($queueByStaff, $commissionConfigs, $date): array {
            $staffId = (string) ($staff['id'] ?? '');
            $queue = $queueByStaff[$staffId]['items'] ?? [];
            $bookedValue = (float) ($queueByStaff[$staffId]['bookedValue'] ?? 0);
            $bookedMinutes = (int) ($queueByStaff[$staffId]['bookedMinutes'] ?? 0);

            return [
                'id' => $staffId,
                'display_id' => 'MS' . str_pad($staffId, 3, '0', STR_PAD_LEFT),
                'name' => (string) ($staff['name'] ?? ''),
                'status' => $this->resolveStaffPageStatus($staff, $queue, $date),
                'isWorkingToday' => (bool) ($staff['isWorkingToday'] ?? true),
                'income' => $bookedValue,
                'commission' => $this->estimateQueueCommission($queue, $commissionConfigs),
                'avatar' => $this->resolveStaffAvatar((string) ($staff['profileImage'] ?? ''), $staffId),
                'shift' => '-',
                'break' => '-',
                'queueLoad' => (int) min(100, round(($bookedMinutes / 600) * 100)),
                'queue' => $queue,
            ];
        }, $staffRoster);
    }

    private function getQueueByStaff(int $branchId, string $date): array
    {
        $rows = $this->buildBookingQuery($branchId)
            ->whereDate('b.start_time', $date)
            ->whereNotNull('b.masseuse_id')
            ->where('b.status', '!=', 'cancelled')
            ->orderBy('b.start_time')
            ->get();

        $queueByStaff = [];

        foreach ($rows as $row) {
            $staffId = (string) $row->masseuse_id;
            if (!isset($queueByStaff[$staffId])) {
                $queueByStaff[$staffId] = [
                    'items' => [],
                    'bookedValue' => 0.0,
                    'bookedMinutes' => 0,
                ];
            }

            $startAt = Carbon::parse((string) $row->start_time);
            $endAt = Carbon::parse((string) $row->end_time);
            $servicePrice = $row->service_price !== null ? (float) $row->service_price : 0.0;

            $queueByStaff[$staffId]['items'][] = [
                'booking_id' => (string) $row->id,
                'customer' => $row->customer_name !== null ? (string) $row->customer_name : '',
                'service' => $row->service_name !== null ? (string) $row->service_name : '',
                'service_id' => $row->service_id !== null ? (int) $row->service_id : null,
                'service_price' => $servicePrice,
                'start' => $startAt->format('H:i'),
                'end' => $endAt->format('H:i'),
                'status' => (string) $row->status,
            ];
            $queueByStaff[$staffId]['bookedValue'] += $servicePrice;
            $queueByStaff[$staffId]['bookedMinutes'] += max(0, $startAt->diffInMinutes($endAt, false));
        }

        return $queueByStaff;
    }

    private function getCommissionConfigsByService(): array
    {
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

    private function estimateQueueCommission(array $queue, array $commissionConfigs): float
    {
        $total = 0.0;

        foreach ($queue as $item) {
            $serviceId = isset($item['service_id']) ? (int) $item['service_id'] : 0;
            if ($serviceId <= 0 || !isset($commissionConfigs[$serviceId])) {
                continue;
            }

            $config = $commissionConfigs[$serviceId];
            $servicePrice = isset($item['service_price']) ? (float) $item['service_price'] : 0.0;

            if (($config['type'] ?? '') === 'fixed') {
                $total += (float) ($config['value'] ?? 0);
                continue;
            }

            $baseAmount = max(0.0, $servicePrice - (float) ($config['deduct_cost'] ?? 0));
            $total += $baseAmount * ((float) ($config['value'] ?? 0) / 100);
        }

        return $total;
    }

    private function resolveStaffPageStatus(array $staff, array $queue, string $date): string
    {
        $isWorkingToday = (bool) ($staff['isWorkingToday'] ?? true);
        if (!$isWorkingToday) {
            return 'ไม่มาทำงานวันนี้';
        }

        foreach ($queue as $item) {
            if (($item['status'] ?? '') === 'in_service') {
                return self::STATUSES['in_service'];
            }
        }

        if (!empty($queue)) {
            return $date === Carbon::today()->toDateString() ? 'มีคิววันนี้' : 'มีคิวในวันที่เลือก';
        }

        return 'มาทำงานวันนี้';
    }

    private function resolveStaffAvatar(string $profileImage, string $staffId): string
    {
        if ($profileImage === '') {
            return 'https://i.pravatar.cc/150?u=' . rawurlencode($staffId);
        }

        if (preg_match('#^https?://#i', $profileImage) === 1) {
            return $profileImage;
        }

        return '/' . ltrim($profileImage, '/');
    }

    private function findStaffById(int $branchId, int $staffId): ?object
    {
        return DB::table('masseuses')
            ->where('branch_id', $branchId)
            ->where('id', $staffId)
            ->first(['id']);
    }

    private function getServiceItems(): array
    {
        return DB::table('services')
            ->where('is_active', 1)
            ->orderBy('id')
            ->get(['id', 'name', 'duration_minutes', 'price'])
            ->map(static function ($row): array {
                return [
                    'id' => (string) $row->id,
                    'name' => (string) $row->name,
                    'duration' => (int) $row->duration_minutes,
                    'price' => (float) $row->price,
                ];
            })
            ->all();
    }

    private function getCustomers(): array
    {
        return DB::table('customers')
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'line_id'])
            ->map(static function ($row): array {
                return [
                    'id' => (string) $row->id,
                    'name' => (string) $row->name,
                    'phone' => (string) $row->phone,
                    'line_id' => $row->line_id !== null ? (string) $row->line_id : '',
                ];
            })
            ->all();
    }

    private function getBeds(int $branchId): array
    {
        return DB::table('beds as b')
            ->join('rooms as r', 'r.id', '=', 'b.room_id')
            ->where('r.branch_id', $branchId)
            ->orderBy('r.id')
            ->orderBy('b.id')
            ->get(['b.id', 'b.name as bed_name', 'r.name as room_name'])
            ->map(static function ($row): array {
                return [
                    'id' => (string) $row->id,
                    'name' => (string) $row->room_name . ' / ' . (string) $row->bed_name,
                ];
            })
            ->all();
    }

    private function getBookingsByDate(int $branchId, string $date): array
    {
        return $this->buildBookingQuery($branchId)
            ->whereDate('b.start_time', $date)
            ->orderBy('b.start_time')
            ->get()
            ->map(function ($row): array {
                return $this->mapBookingRow($row);
            })
            ->all();
    }

    private function findBookingById(int $bookingId, int $branchId): array
    {
        $booking = $this->buildBookingQuery($branchId)
            ->where('b.id', $bookingId)
            ->first();

        if ($booking === null) {
            throw ValidationException::withMessages([
                'booking' => ['ไม่พบข้อมูลคิวที่บันทึก'],
            ]);
        }

        return $this->mapBookingRow($booking);
    }

    private function buildBookingQuery(int $branchId)
    {
        return DB::table('bookings as b')
            ->leftJoin('customers as c', 'c.id', '=', 'b.customer_id')
            ->leftJoin('services as s', 's.id', '=', 'b.service_id')
            ->leftJoin('masseuses as m', 'm.id', '=', 'b.masseuse_id')
            ->leftJoin('beds as bed', 'bed.id', '=', 'b.bed_id')
            ->leftJoin('rooms as room', 'room.id', '=', 'bed.room_id')
            ->where('b.branch_id', $branchId)
            ->selectRaw(
                "b.id, b.customer_id, b.service_id, b.masseuse_id, b.bed_id, " .
                "b.start_time, b.end_time, b.status, b.cancel_reason, " .
                "c.name as customer_name, s.name as service_name, s.price as service_price, " .
                "COALESCE(NULLIF(m.full_name, ''), NULLIF(m.nickname, ''), '') as staff_name, " .
                "bed.name as bed_name, room.name as room_name"
            );
    }

    private function mapBookingRow(object $row): array
    {
        $start = Carbon::parse((string) $row->start_time)->format('H:i');
        $queueDate = Carbon::parse((string) $row->start_time)->toDateString();
        $end = Carbon::parse((string) $row->end_time)->format('H:i');
        $serviceId = $row->service_id !== null ? (string) $row->service_id : '';

        return [
            'id' => (string) $row->id,
            'queueDate' => $queueDate,
            'customerId' => $row->customer_id !== null ? (string) $row->customer_id : '',
            'customerName' => $row->customer_name !== null ? (string) $row->customer_name : '',
            'serviceId' => $serviceId,
            'serviceIds' => $serviceId !== '' ? [$serviceId] : [],
            'staffId' => $row->masseuse_id !== null ? (string) $row->masseuse_id : '',
            'staffName' => $row->staff_name !== null ? (string) $row->staff_name : '',
            'bedId' => $row->bed_id !== null ? (string) $row->bed_id : '',
            'bedName' => $this->formatBedName($row->room_name, $row->bed_name),
            'start' => $start,
            'end' => $end,
            'status' => (string) $row->status,
            'cancelReason' => $row->cancel_reason !== null ? (string) $row->cancel_reason : null,
            'paid' => (string) $row->status === 'completed',
        ];
    }

    private function mapBookingToCheckoutContext(array $booking): array
    {
        return [
            'bookingId' => isset($booking['id']) ? (int) $booking['id'] : null,
            'queueDate' => (string) ($booking['queueDate'] ?? Carbon::today()->toDateString()),
            'startTime' => (string) ($booking['start'] ?? '10:00'),
            'endTime' => (string) ($booking['end'] ?? '11:00'),
            'customerId' => isset($booking['customerId']) && $booking['customerId'] !== '' ? (int) $booking['customerId'] : null,
            'staffId' => isset($booking['staffId']) && $booking['staffId'] !== '' ? (int) $booking['staffId'] : null,
            'serviceId' => isset($booking['serviceId']) && $booking['serviceId'] !== '' ? (int) $booking['serviceId'] : null,
            'bedId' => isset($booking['bedId']) && $booking['bedId'] !== '' ? (int) $booking['bedId'] : null,
            'isPaid' => (bool) ($booking['paid'] ?? false),
        ];
    }

    private function formatBedName($roomName, $bedName): string
    {
        if ($bedName === null || $bedName === '') {
            return '';
        }

        if ($roomName === null || $roomName === '') {
            return (string) $bedName;
        }

        return (string) $roomName . ' / ' . (string) $bedName;
    }

    private function ensureNoTimeConflict(
        int $branchId,
        Carbon $startAt,
        Carbon $endAt,
        ?int $masseuseId,
        ?int $bedId,
        ?int $excludeBookingId = null
    ): void {
        $baseQuery = DB::table('bookings as b')
            ->where('b.branch_id', $branchId)
            ->where('b.status', '!=', 'cancelled')
            ->where('b.start_time', '<', $endAt->format('Y-m-d H:i:s'))
            ->where('b.end_time', '>', $startAt->format('Y-m-d H:i:s'));

        if ($excludeBookingId !== null) {
            $baseQuery->where('b.id', '!=', $excludeBookingId);
        }

        $messages = [];

        if ($masseuseId !== null) {
            $staffConflict = (clone $baseQuery)
                ->where('b.masseuse_id', $masseuseId)
                ->exists();

            if ($staffConflict) {
                $messages[] = 'หมอนวดคนนี้มีคิวซ้อนในช่วงเวลาเดียวกัน';
            }
        }

        if ($bedId !== null) {
            $bedConflict = (clone $baseQuery)
                ->where('b.bed_id', $bedId)
                ->exists();

            if ($bedConflict) {
                $messages[] = 'ห้อง/เตียงนี้มีคิวซ้อนในช่วงเวลาเดียวกัน';
            }
        }

        if (!empty($messages)) {
            throw ValidationException::withMessages([
                'start_time' => [implode(' และ ', $messages)],
            ]);
        }
    }

    private function ensureCustomerExists(int $customerId): void
    {
        $exists = DB::table('customers')->where('id', $customerId)->exists();
        if (!$exists) {
            throw ValidationException::withMessages([
                'customer_id' => ['ไม่พบข้อมูลลูกค้า'],
            ]);
        }
    }

    private function ensureServiceExists(int $serviceId): void
    {
        $exists = DB::table('services')
            ->where('id', $serviceId)
            ->where('is_active', 1)
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'service_id' => ['ไม่พบข้อมูลบริการ หรือบริการถูกปิดใช้งาน'],
            ]);
        }
    }

    private function ensureMasseuseInBranch(?int $masseuseId, int $branchId): void
    {
        if ($masseuseId === null) {
            return;
        }

        $exists = DB::table('masseuses')
            ->where('id', $masseuseId)
            ->where('branch_id', $branchId)
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'masseuse_id' => ['หมอนวดไม่อยู่ในสาขาที่เลือก'],
            ]);
        }
    }

    private function ensureBedInBranch(?int $bedId, int $branchId): void
    {
        if ($bedId === null) {
            return;
        }

        $exists = DB::table('beds as b')
            ->join('rooms as r', 'r.id', '=', 'b.room_id')
            ->where('b.id', $bedId)
            ->where('r.branch_id', $branchId)
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'bed_id' => ['ห้อง/เตียงไม่อยู่ในสาขาที่เลือก'],
            ]);
        }
    }

    private function resolveAuthorizedBranchId(User $user, ?int $requestedBranchId): int
    {
        $role = (string) ($user->role ?? '');
        $userBranchId = isset($user->branch_id) ? (int) $user->branch_id : 0;

        if ($role === 'super_admin') {
            if ($requestedBranchId !== null && $requestedBranchId > 0 && $this->branchExists($requestedBranchId)) {
                return $requestedBranchId;
            }

            return $this->getDefaultBranchId();
        }

        if ($userBranchId > 0 && $this->branchExists($userBranchId)) {
            return $userBranchId;
        }

        if ($requestedBranchId !== null && $requestedBranchId > 0 && $this->branchExists($requestedBranchId)) {
            return $requestedBranchId;
        }

        return $this->getDefaultBranchId();
    }

    private function branchExists(int $branchId): bool
    {
        return DB::table('branches')
            ->where('id', $branchId)
            ->exists();
    }

    private function getDefaultBranchId(): int
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

    private function normalizeDate(string $date): string
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $date)->toDateString();
        } catch (\Throwable $e) {
            return Carbon::today()->toDateString();
        }
    }
}
