<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('tenant_id', 36)->nullable()->index();
            $table->string('order_id', 26)->index();
            $table->string('shipment_number', 50)->unique();
            $table->string('courier', 50)->nullable();
            $table->string('courier_service', 100)->nullable();
            $table->string('tracking_number', 255)->nullable()->index();
            $table->string('tracking_url', 500)->nullable();
            $table->string('label_url', 500)->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->string('recipient_name', 255)->nullable();
            $table->string('recipient_phone', 30)->nullable();
            $table->text('recipient_address')->nullable();
            $table->string('recipient_city', 100)->nullable();
            $table->string('recipient_postal_code', 20)->nullable();
            $table->bigInteger('shipping_cost')->default(0);
            $table->bigInteger('cod_amount')->default(0);
            $table->bigInteger('declared_value')->default(0);
            $table->unsignedInteger('total_weight_grams')->nullable();
            $table->unsignedInteger('total_items')->default(0);
            $table->text('notes')->nullable();
            $table->json('courier_response')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('estimated_delivery')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->index(['tenant_id', 'order_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'courier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
