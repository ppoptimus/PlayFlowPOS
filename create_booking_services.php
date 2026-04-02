<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    Schema::dropIfExists('booking_services');

    Schema::create('booking_services', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->bigInteger('booking_id')->unsigned();
        $table->integer('service_id');
        $table->integer('sort_order')->default(1);
        $table->timestamps();

        $table->unique(['booking_id', 'service_id'], 'booking_service_idx');
        $table->index(['booking_id', 'sort_order'], 'booking_services_booking_sort_idx');
    });
    echo "Table booking_services recreated successfully.\n";

    // Migrate existing data from bookings.service_id
    $bookings = DB::table('bookings')->whereNotNull('service_id')->get();
    foreach ($bookings as $row) {
        DB::table('booking_services')->insertOrIgnore([
            'booking_id' => $row->id,
            'service_id' => $row->service_id,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    echo "Migrated existing bookings.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
