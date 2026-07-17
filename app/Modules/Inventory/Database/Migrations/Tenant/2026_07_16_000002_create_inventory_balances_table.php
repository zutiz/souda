<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->id();
            $table->ulid('product_id');
            $table->ulid('variant_id')->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('available_quantity')->default(0);
            $table->bigInteger('average_unit_cost')->default(0);
            $table->bigInteger('total_stock_value')->default(0);
            $table->unsignedBigInteger('lock_version')->default(0);
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'variant_id', 'warehouse_id'], 'uq_ib_product_variant_warehouse');
            $table->index(['warehouse_id', 'quantity'], 'idx_ib_warehouse_quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_balances');
    }
};
