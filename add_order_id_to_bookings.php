<?php
/**
 * เพิ่มคอลัมน์ order_id ในตาราง bookings
 * เพื่อเชื่อมโยง booking กับ order ที่ชำระเงินแล้ว
 *
 * วิธีใช้: php add_order_id_to_bookings.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Add order_id to bookings ===" . PHP_EOL;

// 1. เพิ่มคอลัมน์ order_id (ถ้ายังไม่มี)
if (!Schema::hasColumn('bookings', 'order_id')) {
    Schema::table('bookings', function ($table) {
        $table->unsignedBigInteger('order_id')->nullable()->after('cancel_reason');
        $table->index('order_id', 'bookings_order_id_idx');
    });
    echo "[OK] Added column bookings.order_id" . PHP_EOL;
} else {
    echo "[SKIP] Column bookings.order_id already exists" . PHP_EOL;
}

// 2. Backfill: เชื่อมโยง booking เดิมที่ชำระเงินแล้วกับ order
$completedBookings = DB::table('bookings')
    ->where('status', 'completed')
    ->where(function ($q) {
        $q->whereNull('order_id')->orWhere('order_id', 0);
    })
    ->orderBy('id')
    ->get(['id', 'branch_id', 'customer_id', 'start_time']);

$linked = 0;
foreach ($completedBookings as $booking) {
    $bookingDate = date('Y-m-d', strtotime($booking->start_time));

    // หา order ที่ตรงกัน: สาขาเดียวกัน, ลูกค้าเดียวกัน, วันเดียวกัน, status=paid
    $order = DB::table('orders')
        ->where('branch_id', $booking->branch_id)
        ->where('customer_id', $booking->customer_id)
        ->where('status', 'paid')
        ->whereDate('created_at', $bookingDate)
        ->whereNotIn('id', function ($q) {
            $q->select('order_id')
                ->from('bookings')
                ->whereNotNull('order_id')
                ->where('order_id', '>', 0);
        })
        ->orderBy('created_at')
        ->first(['id']);

    if ($order !== null) {
        DB::table('bookings')
            ->where('id', $booking->id)
            ->update(['order_id' => $order->id]);
        $linked++;
    }
}

echo "[OK] Backfill: linked {$linked} existing bookings to orders" . PHP_EOL;
echo "=== Done ===" . PHP_EOL;
