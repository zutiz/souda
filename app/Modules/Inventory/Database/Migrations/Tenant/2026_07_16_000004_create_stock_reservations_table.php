<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->ulid('product_id');
            $table->ulid('variant_id')->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->integer('quantity');
            $table->string('status', 20)->default('active');
            $table->string('reference', 100);
            $table->string('reference_type', 30);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->string('created_by', 36)->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at'], 'idx_sr_status_expires');
            $table->index(['product_id', 'warehouse_id', 'status'], 'idx_sr_product_warehouse_status');
            $table->index(['reference', 'reference_type'], 'idx_sr_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_reservations');
    }
};
