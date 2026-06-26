<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignUlid('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignUlid('variant_id')->nullable()->constrained('variants')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('reference_type', 100);
            $table->unsignedBigInteger('reference_id');
            $table->timestamp('expires_at');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['reference_type', 'reference_id'], 'idx_sr_reference');
            $table->index(['status', 'expires_at'], 'idx_sr_expires');
            $table->index(['product_id', 'variant_id', 'status'], 'idx_sr_product');
            $table->unique(['reference_type', 'reference_id', 'warehouse_id', 'product_id', 'variant_id'], 'uq_sr_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
