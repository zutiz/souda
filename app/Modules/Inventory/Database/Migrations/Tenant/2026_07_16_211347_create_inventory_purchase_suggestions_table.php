<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_purchase_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('product_id', 36);
            $table->string('variant_id', 36)->nullable();
            $table->integer('warehouse_id');
            $table->integer('current_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('available_quantity')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->integer('lead_time_days')->default(7);
            $table->integer('safety_stock')->default(0);
            $table->decimal('sales_velocity', 10, 2)->default(0);
            $table->integer('suggested_quantity');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->string('order_reference')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['product_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_purchase_suggestions');
    }
};
