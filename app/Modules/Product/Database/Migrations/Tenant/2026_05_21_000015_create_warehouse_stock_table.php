<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignUlid('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignUlid('variant_id')->nullable()->constrained('variants')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);
            $table->unsignedInteger('available_quantity')->virtualAs('quantity - reserved_quantity');
            $table->unsignedInteger('reorder_level')->default(5);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_id', 'variant_id'], 'uq_warehouse_stock_location');
            $table->index('warehouse_id', 'idx_ws_warehouse');
            $table->index('product_id', 'idx_ws_product');
            $table->index('variant_id', 'idx_ws_variant');
            $table->index(['warehouse_id', 'available_quantity'], 'idx_ws_available');
            $table->index(['product_id', 'variant_id'], 'idx_ws_product_variant');
            $table->index(['reorder_level', 'quantity'], 'idx_ws_low_stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_stock');
    }
};
