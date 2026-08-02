<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('product_id', 26);
            $table->string('variant_id', 26)->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->string('batch_number', 100);
            $table->string('supplier_batch', 100)->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('best_before')->nullable();
            $table->integer('initial_quantity');
            $table->integer('remaining_quantity');
            $table->bigInteger('unit_cost')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_id', 'variant_id', 'batch_number'], 'uq_batch_wh_product_variant');
            $table->index(['expiry_date', 'status'], 'idx_batch_expiry_status');
            $table->index(['warehouse_id', 'product_id', 'status'], 'idx_batch_wh_product_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
