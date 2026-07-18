<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('tenant_id', 36)->nullable()->index();
            $table->string('shipment_id', 26)->index();
            $table->string('order_item_id', 26)->nullable()->index();
            $table->string('product_id', 26)->nullable();
            $table->string('variant_id', 26)->nullable();
            $table->string('name', 255);
            $table->string('sku', 100)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->bigInteger('unit_price')->default(0);
            $table->timestamps();

            $table->foreign('shipment_id')->references('id')->on('shipments')->cascadeOnDelete();
            $table->foreign('order_item_id')->references('id')->on('order_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};
