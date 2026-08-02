<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('reference', 100)->unique();
            $table->unsignedBigInteger('from_warehouse_id');
            $table->unsignedBigInteger('to_warehouse_id');
            $table->string('status', 20)->default('draft');
            $table->text('description')->nullable();
            $table->string('created_by', 36)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->foreign('from_warehouse_id')
                ->references('id')
                ->on('inventory_warehouses');
            $table->foreign('to_warehouse_id')
                ->references('id')
                ->on('inventory_warehouses');
            $table->index(['from_warehouse_id', 'status']);
            $table->index(['reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transfers');
    }
};
