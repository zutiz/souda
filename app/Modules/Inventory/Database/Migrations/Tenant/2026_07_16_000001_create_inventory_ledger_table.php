<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->ulid('product_id');
            $table->ulid('variant_id')->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('bin_id')->nullable();
            $table->integer('quantity');
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('movement_type', 30);
            $table->string('reference', 100);
            $table->string('reference_type', 30);
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->json('serial_numbers')->nullable();
            $table->bigInteger('unit_cost')->nullable();
            $table->bigInteger('total_cost')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->string('created_by', 36)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_id', 'warehouse_id', 'created_at'], 'idx_il_product_warehouse_created');
            $table->index(['reference', 'reference_type'], 'idx_il_reference');
            $table->index(['batch_id'], 'idx_il_batch');
            $table->index(['created_at'], 'idx_il_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_ledger');
    }
};
