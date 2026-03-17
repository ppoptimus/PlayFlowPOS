<?php

namespace App\Services;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PosService
{
    private BookingService $bookingService;
    private array $tableExistsCache = [];
    private array $columnExistsCache = [];

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function getPageData(User $user, array $query = []): array
    {
        $requestedBranchId = isset($query['branch_id']) ? (int) $query['branch_id'] : null;
        $branchId = $this->resolveAuthorizedBranchId($user, $requestedBranchId);

        return [
            'activeBranchId' => $branchId,
            'items' => $this->getPosItems(),
            'serviceItems' => $this->getServiceItems(),
            'staff' => $this->getStaff($branchId),
            'customers' => $this->getCustomers(),
            'bookingContext' => $this->resolveBookingContext($user, $query, $branchId),
        ];
    }

    public function checkout(User $user, array $payload): array
    {
        $requestedBranchId = isset($payload['branch_id']) ? (int) $payload['branch_id'] : null;
        $branchId = $this->resolveAuthorizedBranchId($user, $requestedBranchId);
        $items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
        $customerId = isset($payload['customer_id']) && $payload['customer_id'] !== null ? (int) $payload['customer_id'] : null;
        $staffId = isset($payload['staff_id']) && $payload['staff_id'] !== null ? (int) $payload['staff_id'] : null;
        $discountAmount = isset($payload['discount_amount']) ? (float) $payload['discount_amount'] : 0.0;
        $paymentMethod = $this->normalizePaymentMethod((string) ($payload['payment_method'] ?? 'cash'));
        $bookingContext = isset($payload['booking_context']) && is_array($payload['booking_context'])
            ? $payload['booking_context']
            : null;

        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => ['กรุณาเลือกรายการอย่างน้อย 1 รายการก่อนชำระเงิน'],
            ]);
        }

        $normalizedItems = $this->normalizeCartItems($items, $staffId);
        $subtotal = array_reduce($normalizedItems, static function (float $carry, array $item): float {
            return $carry + (float) $item['line_total'];
        }, 0.0);
        $discount = max(0.0, $discountAmount);
        $grandTotal = max(0.0, $subtotal - $discount);

        return DB::transaction(function () use (
            $user,
            $branchId,
            $normalizedItems,
            $customerId,
            $discount,
            $grandTotal,
            $paymentMethod,
            $bookingContext
        ): array {
            $orderNo = $this->generateOrderNo($branchId);
            $orderId = (int) DB::table('orders')->insertGetId([
                'branch_id' => $branchId,
                'order_no' => $orderNo,
                'customer_id' => $customerId,
                'total_amount' => $grandTotal + $discount,
                'discount_amount' => $discount,
                'grand_total' => $grandTotal,
                'payment_method' => $paymentMethod,
                'status' => 'paid',
                'created_at' => now(),
            ]);

            foreach ($normalizedItems as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'masseuse_id' => $item['masseuse_id'],
                ]);
            }

            $booking = null;
            if ($bookingContext !== null) {
                if (!empty($bookingContext['is_paid'])) {
                    throw ValidationException::withMessages([
                        'booking_context' => ['คิวนี้ชำระเงินแล้ว ไม่สามารถชำระซ้ำได้'],
                    ]);
                }

                $requiredBookingFields = ['queue_date', 'start_time', 'end_time', 'service_id'];
                foreach ($requiredBookingFields as $field) {
                    if (!isset($bookingContext[$field]) || $bookingContext[$field] === null || $bookingContext[$field] === '') {
                        throw ValidationException::withMessages([
                            'booking_context' => ['ข้อมูลจองไม่ครบถ้วนสำหรับการชำระเงิน'],
                        ]);
                    }
                }

                $booking = $this->bookingService->saveBooking(
                    isset($bookingContext['booking_id']) ? (int) $bookingContext['booking_id'] : null,
                    [
                        'branch_id' => $branchId,
                        'queue_date' => (string) $bookingContext['queue_date'],
                        'customer_id' => isset($bookingContext['customer_id']) ? (int) $bookingContext['customer_id'] : $customerId,
                        'service_id' => (int) $bookingContext['service_id'],
                        'masseuse_id' => isset($bookingContext['staff_id']) ? (int) $bookingContext['staff_id'] : $this->firstServiceMasseuseId($normalizedItems),
                        'bed_id' => isset($bookingContext['bed_id']) && $bookingContext['bed_id'] !== null ? (int) $bookingContext['bed_id'] : null,
                        'start_time' => (string) $bookingContext['start_time'],
                        'end_time' => (string) $bookingContext['end_time'],
                        'status' => 'completed',
                        'cancel_reason' => null,
                    ],
                    $user
                );
            }

            return [
                'message' => 'ชำระเงินสำเร็จ',
                'order_id' => $orderId,
                'order_no' => $orderNo,
                'booking' => $booking,
                'membership' => $this->syncCustomerTierAfterPayment($customerId),
            ];
        });
    }

    private function getPosItems(): array
    {
        return array_merge($this->getServiceItems(), $this->getProductItems());
    }

    private function getServiceItems(): array
    {
        return DB::table('services')
            ->where('is_active', 1)
            ->orderBy('id')
            ->get(['id', 'name', 'duration_minutes', 'price'])
            ->map(static function ($row): array {
                return [
                    'id' => 'service:' . (string) $row->id,
                    'source_id' => (int) $row->id,
                    'type' => 'service',
                    'name' => (string) $row->name,
                    'price' => (float) $row->price,
                    'duration' => (int) $row->duration_minutes,
                ];
            })
            ->all();
    }

    private function getProductItems(): array
    {
        return DB::table('products')
            ->where('type', 'retail')
            ->orderBy('id')
            ->get(['id', 'name', 'sell_price'])
            ->map(static function ($row): array {
                return [
                    'id' => 'product:' . (string) $row->id,
                    'source_id' => (int) $row->id,
                    'type' => 'product',
                    'name' => (string) $row->name,
                    'price' => (float) ($row->sell_price ?? 0),
                    'duration' => null,
                ];
            })
            ->all();
    }

    private function getStaff(int $branchId): array
    {
        return DB::table('masseuses as m')
            ->where('m.branch_id', $branchId)
            ->selectRaw("m.id, COALESCE(NULLIF(m.full_name, ''), NULLIF(m.nickname, ''), CONCAT('Masseuse #', m.id)) as name")
            ->orderBy('m.id')
            ->get()
            ->map(static function ($row): array {
                return [
                    'id' => (string) $row->id,
                    'name' => (string) $row->name,
                ];
            })
            ->all();
    }

    private function getCustomers(): array
    {
        return DB::table('customers')
            ->orderBy('name')
            ->get(['id', 'name', 'phone'])
            ->map(static function ($row): array {
                return [
                    'id' => (string) $row->id,
                    'name' => (string) $row->name,
                    'phone' => (string) $row->phone,
                ];
            })
            ->all();
    }

    private function normalizeCartItems(array $items, ?int $staffId): array
    {
        $normalized = [];

        foreach ($items as $index => $item) {
            $type = isset($item['type']) ? (string) $item['type'] : '';
            $itemId = isset($item['source_id']) ? (int) $item['source_id'] : 0;
            $qty = isset($item['qty']) ? (int) $item['qty'] : 0;

            if (!in_array($type, ['service', 'product', 'package'], true)) {
                throw ValidationException::withMessages([
                    'items' => ["ประเภทสินค้า/บริการไม่ถูกต้องที่รายการลำดับ " . ($index + 1)],
                ]);
            }

            if ($itemId <= 0 || $qty <= 0) {
                throw ValidationException::withMessages([
                    'items' => ["ข้อมูลรายการไม่ครบถ้วนที่ลำดับ " . ($index + 1)],
                ]);
            }

            if ($type === 'service') {
                $service = DB::table('services')
                    ->where('id', $itemId)
                    ->where('is_active', 1)
                    ->first(['id', 'price']);

                if ($service === null) {
                    throw ValidationException::withMessages([
                        'items' => ['ไม่พบบริการที่เลือก หรือบริการถูกปิดใช้งาน'],
                    ]);
                }

                $unitPrice = (float) $service->price;
                $normalized[] = [
                    'item_type' => 'service',
                    'item_id' => (int) $service->id,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $unitPrice * $qty,
                    'masseuse_id' => $staffId,
                ];
                continue;
            }

            if ($type === 'product') {
                $product = DB::table('products')
                    ->where('id', $itemId)
                    ->first(['id', 'sell_price']);

                if ($product === null) {
                    throw ValidationException::withMessages([
                        'items' => ['ไม่พบสินค้าที่เลือก'],
                    ]);
                }

                $unitPrice = (float) ($product->sell_price ?? 0);
                $normalized[] = [
                    'item_type' => 'product',
                    'item_id' => (int) $product->id,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $unitPrice * $qty,
                    'masseuse_id' => null,
                ];
                continue;
            }

            throw ValidationException::withMessages([
                'items' => ['ระบบยังไม่รองรับการชำระแพ็กเกจในขั้นตอนนี้'],
            ]);
        }

        return $normalized;
    }

    private function resolveBookingContext(User $user, array $query, int $branchId): ?array
    {
        $fromBooking = isset($query['from_booking']) && ((string) $query['from_booking'] === '1' || $query['from_booking'] === 1);
        if (!$fromBooking) {
            return null;
        }

        if (isset($query['booking_id']) && (int) $query['booking_id'] > 0) {
            $context = $this->bookingService->getBookingContextForCheckout(
                $user,
                (int) $query['booking_id'],
                $branchId
            );
            $context['fromBooking'] = true;
            return $context;
        }

        $queueDate = (string) ($query['queue_date'] ?? Carbon::today()->toDateString());
        $serviceId = isset($query['service_id']) ? (int) $query['service_id'] : null;

        if ($serviceId === null || $serviceId <= 0) {
            return null;
        }

        return [
            'fromBooking' => true,
            'bookingId' => null,
            'queueDate' => $queueDate,
            'startTime' => (string) ($query['start_time'] ?? '10:00'),
            'endTime' => (string) ($query['end_time'] ?? '11:00'),
            'customerId' => isset($query['customer_id']) && (int) $query['customer_id'] > 0 ? (int) $query['customer_id'] : null,
            'staffId' => isset($query['staff_id']) && (int) $query['staff_id'] > 0 ? (int) $query['staff_id'] : null,
            'serviceId' => $serviceId,
            'bedId' => isset($query['bed_id']) && (int) $query['bed_id'] > 0 ? (int) $query['bed_id'] : null,
            'isPaid' => false,
        ];
    }

    private function normalizePaymentMethod(string $method): string
    {
        if ($method === 'card') {
            return 'credit_card';
        }

        if (in_array($method, ['cash', 'transfer', 'credit_card', 'package_redeem'], true)) {
            return $method;
        }

        return 'cash';
    }

    private function generateOrderNo(int $branchId): string
    {
        return 'PF' . $branchId . Carbon::now()->format('ymdHis') . random_int(10, 99);
    }

    private function firstServiceMasseuseId(array $normalizedItems): ?int
    {
        foreach ($normalizedItems as $item) {
            if ($item['item_type'] === 'service' && $item['masseuse_id'] !== null) {
                return (int) $item['masseuse_id'];
            }
        }

        return null;
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

    private function syncCustomerTierAfterPayment(?int $customerId): ?array
    {
        if ($customerId === null || $customerId <= 0) {
            return null;
        }

        if (
            !$this->tableExists('orders') ||
            !$this->tableExists('customers') ||
            !$this->tableExists('membership_tiers') ||
            !$this->hasColumn('customers', 'tier_id')
        ) {
            return null;
        }

        $tiers = DB::table('membership_tiers')
            ->orderBy('min_spend')
            ->orderBy('id')
            ->get(['id', 'name', 'min_spend']);

        if ($tiers->isEmpty()) {
            return null;
        }

        $totalSpent = (float) DB::table('orders')
            ->where('customer_id', $customerId)
            ->where('status', 'paid')
            ->sum('grand_total');

        $recommendedTier = null;
        foreach ($tiers as $tier) {
            if ($totalSpent >= (float) ($tier->min_spend ?? 0)) {
                $recommendedTier = $tier;
            }
        }

        if ($recommendedTier === null) {
            return null;
        }

        $customer = DB::table('customers')
            ->where('id', $customerId)
            ->first(['id', 'tier_id']);

        if ($customer === null) {
            return null;
        }

        $currentTierMinSpend = -1.0;
        if ($customer->tier_id !== null) {
            foreach ($tiers as $tier) {
                if ((int) $tier->id === (int) $customer->tier_id) {
                    $currentTierMinSpend = (float) ($tier->min_spend ?? 0);
                    break;
                }
            }
        }

        $recommendedMinSpend = (float) ($recommendedTier->min_spend ?? 0);
        if ($recommendedMinSpend > $currentTierMinSpend) {
            $updates = ['tier_id' => (int) $recommendedTier->id];
            if ($this->hasColumn('customers', 'updated_at')) {
                $updates['updated_at'] = now();
            }

            DB::table('customers')
                ->where('id', $customerId)
                ->update($updates);
        }

        return [
            'tier_id' => (int) $recommendedTier->id,
            'tier_name' => (string) ($recommendedTier->name ?? ''),
            'total_spent' => $totalSpent,
        ];
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
