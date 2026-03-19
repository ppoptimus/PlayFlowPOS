<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsModuleTables extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('name', 255);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('name', 255);
                $table->string('sku', 100)->nullable();
                $table->string('barcode', 100)->nullable();
                $table->enum('type', ['retail', 'internal'])->default('retail');
                $table->unsignedBigInteger('category_id')->nullable();
                $table->decimal('cost_price', 10, 2)->default(0);
                $table->decimal('sell_price', 10, 2)->default(0);
                $table->integer('stock_qty')->default(0);
                $table->integer('min_stock')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index('type', 'products_type_idx');
                $table->index('category_id', 'products_category_idx');
                $table->index('is_active', 'products_active_idx');
            });
        } else {
            Schema::table('products', function (Blueprint $table): void {
                if (!Schema::hasColumn('products', 'barcode')) {
                    $table->string('barcode', 100)->nullable()->after('sku');
                }
                if (!Schema::hasColumn('products', 'category_id')) {
                    $table->unsignedBigInteger('category_id')->nullable()->after('type');
                }
                if (!Schema::hasColumn('products', 'min_stock')) {
                    $table->integer('min_stock')->default(0)->after('stock_qty');
                }
                if (!Schema::hasColumn('products', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('min_stock');
                }
                if (!Schema::hasColumn('products', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('products', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table): void {
                $columns = ['barcode', 'category_id', 'min_stock', 'is_active', 'created_at', 'updated_at'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('products', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('product_categories')) {
            Schema::drop('product_categories');
        }
    }
}

