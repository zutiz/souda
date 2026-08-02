<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->unsignedBigInteger('transfer_id');
            $table->string('product_id', 26);
            $table->string('variant_id', 26)->nullable();
            $table->integer('quantity');
            $table->integer('quantity_received')->default(0);
            $table->timestamps();

            $table->foreign('transfer_id')
                ->references('id')
                ->on('inventory_transfers')
                ->cascadeOnDelete();
            $table->index('transfer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transfer_items');
    }
};
