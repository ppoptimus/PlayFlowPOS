<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBookingServicesTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bookings') || !Schema::hasTable('services')) {
            return;
        }

        if (!Schema::hasTable('booking_services')) {
            Schema::create('booking_services', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('booking_id');
                $table->unsignedBigInteger('service_id');
                $table->unsignedTinyInteger('sort_order')->default(1);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index(['booking_id', 'sort_order'], 'booking_services_booking_sort_idx');
                $table->index('service_id', 'booking_services_service_idx');
            });
        }

        $legacyRows = DB::table('bookings')
            ->whereNotNull('service_id')
            ->get(['id', 'service_id']);

        foreach ($legacyRows as $row) {
            $exists = DB::table('booking_services')
                ->where('booking_id', (int) $row->id)
                ->where('service_id', (int) $row->service_id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('booking_services')->insert([
                'booking_id' => (int) $row->id,
                'service_id' => (int) $row->service_id,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_services')) {
            Schema::drop('booking_services');
        }
    }
}
