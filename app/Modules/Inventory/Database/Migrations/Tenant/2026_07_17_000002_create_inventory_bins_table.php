<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_bins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->string('code', 50);
            $table->string('zone', 100)->nullable();
            $table->string('aisle', 100)->nullable();
            $table->string('rack', 100)->nullable();
            $table->string('shelf', 100)->nullable();
            $table->boolean('is_pickable')->default(true);
            $table->unsignedInteger('max_weight_kg')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['warehouse_id', 'code']);
            $table->foreign('warehouse_id')
                ->references('id')
                ->on('inventory_warehouses')
                ->cascadeOnDelete();
            $table->index(['warehouse_id', 'is_pickable']);
            $table->index('zone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_bins');
    }
};
