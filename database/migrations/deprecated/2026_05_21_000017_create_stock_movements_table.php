<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignUlid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignUlid('variant_id')->nullable()->constrained('variants')->nullOnDelete();
            $table->string('movement_type', 20);
            $table->integer('quantity');
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->json('snapshot_before')->nullable();
            $table->json('snapshot_after')->nullable();
            $table->string('audit_log_id', 26)->nullable();
            $table->timestamps();

            $table->index('warehouse_id', 'idx_sm_warehouse');
            $table->index('product_id', 'idx_sm_product');
            $table->index('variant_id', 'idx_sm_variant');
            $table->index('movement_type', 'idx_sm_type');
            $table->index(['reference_type', 'reference_id'], 'idx_sm_reference');
            $table->index('created_at', 'idx_sm_created');
            $table->index(['product_id', 'variant_id', 'created_at'], 'idx_sm_product_created');
            $table->index(['reference_type', 'reference_id', 'created_at'], 'idx_sm_reference_lookup');
            $table->index(['warehouse_id', 'created_at'], 'idx_sm_warehouse_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
