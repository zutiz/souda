<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('tenant_id', 36)->nullable()->index();
            $table->string('order_id', 26)->index();
            $table->string('product_id', 26)->nullable()->index();
            $table->string('variant_id', 26)->nullable()->index();
            $table->string('warehouse_id', 26)->nullable();
            $table->string('name', 255);
            $table->string('sku', 100)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->bigInteger('unit_price')->default(0);
            $table->bigInteger('total_price')->default(0);
            $table->bigInteger('tax_amount')->default(0);
            $table->bigInteger('discount_amount')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->unique(['order_id', 'variant_id', 'product_id'], 'order_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
