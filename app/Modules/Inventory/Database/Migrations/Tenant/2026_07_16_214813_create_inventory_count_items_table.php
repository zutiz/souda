<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_count_items', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreignId('count_id')->constrained('inventory_counts')->cascadeOnDelete();
            $table->string('product_id', 36);
            $table->string('variant_id', 36)->nullable();
            $table->integer('bin_id')->unsigned()->nullable();
            $table->integer('expected_quantity')->default(0);
            $table->integer('physical_quantity')->nullable();
            $table->integer('discrepancy')->nullable();
            $table->integer('unit_cost')->default(0);
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['count_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_count_items');
    }
};
