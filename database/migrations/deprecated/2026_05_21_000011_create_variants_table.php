<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku', 100)->unique();
            $table->string('barcode', 100)->nullable();
            $table->string('barcode_type', 10)->nullable();
            $table->string('name', 500);
            $table->unsignedInteger('price');
            $table->unsignedInteger('compare_at_price')->nullable();
            $table->unsignedInteger('cost_price')->nullable();
            $table->boolean('track_inventory')->default(true);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id', 'idx_variants_product');
            $table->index('sku', 'idx_variants_sku');
            $table->index('barcode', 'idx_variants_barcode');
            $table->index(['product_id', 'sku'], 'idx_variants_product_sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};
