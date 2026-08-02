<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_layers', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->ulid('product_id');
            $table->ulid('variant_id')->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->bigInteger('unit_cost');
            $table->integer('quantity_remaining');
            $table->integer('quantity_original');
            $table->string('costing_method', 20);
            $table->unsignedBigInteger('ledger_entry_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_id', 'warehouse_id', 'costing_method'], 'idx_cl_product_warehouse_method');
            $table->index(['created_at'], 'idx_cl_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_layers');
    }
};
