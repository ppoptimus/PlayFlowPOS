<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PrepareCustomersCrmProfileColumns extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('name', 150);
                $table->string('phone', 32)->unique();
                $table->string('line_id', 120)->nullable();
                $table->string('preferred_pressure_level', 20)->nullable();
                $table->text('health_notes')->nullable();
                $table->text('contraindications')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('customers', function (Blueprint $table): void {
            if (!Schema::hasColumn('customers', 'name')) {
                $table->string('name', 150)->nullable();
            }
            if (!Schema::hasColumn('customers', 'phone')) {
                $table->string('phone', 32)->nullable();
            }
            if (!Schema::hasColumn('customers', 'line_id')) {
                $table->string('line_id', 120)->nullable();
            }
            if (!Schema::hasColumn('customers', 'preferred_pressure_level')) {
                $table->string('preferred_pressure_level', 20)->nullable();
            }
            if (!Schema::hasColumn('customers', 'health_notes')) {
                $table->text('health_notes')->nullable();
            }
            if (!Schema::hasColumn('customers', 'contraindications')) {
                $table->text('contraindications')->nullable();
            }
            if (!Schema::hasColumn('customers', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('customers', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table): void {
            if (Schema::hasColumn('customers', 'preferred_pressure_level')) {
                $table->dropColumn('preferred_pressure_level');
            }
            if (Schema::hasColumn('customers', 'health_notes')) {
                $table->dropColumn('health_notes');
            }
            if (Schema::hasColumn('customers', 'contraindications')) {
                $table->dropColumn('contraindications');
            }
        });
    }
}
