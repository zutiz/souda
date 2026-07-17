<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('product_id', 26);
            $table->string('variant_id', 26)->nullable();
            $table->string('serial_number', 200);
            $table->string('status', 20)->default('available');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->string('order_reference', 100)->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamp('warranty_expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'serial_number'], 'uq_serial_product');
            $table->index(['status', 'warehouse_id'], 'idx_serial_status_warehouse');
            $table->index(['warehouse_id', 'product_id', 'status'], 'idx_serial_wh_product_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_numbers');
    }
};
