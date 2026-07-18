<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('tenant_id', 36)->nullable()->index();
            $table->string('store_id', 26)->index();
            $table->string('order_number', 50)->unique();
            $table->string('customer_id', 26)->nullable()->index();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->string('order_type', 30)->default('in_store');
            $table->string('fulfillment_status', 30)->default('unfulfilled');
            $table->string('payment_status', 30)->default('pending');
            $table->string('currency', 3)->default('BDT');
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('shipping_total')->default(0);
            $table->bigInteger('tax_total')->default(0);
            $table->bigInteger('discount_total')->default(0);
            $table->bigInteger('grand_total')->default(0);
            $table->bigInteger('paid_total')->default(0);
            $table->bigInteger('refund_total')->default(0);
            $table->bigInteger('due_total')->default(0);
            $table->string('coupon_code', 100)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('shipping_name', 255)->nullable();
            $table->string('shipping_phone', 30)->nullable();
            $table->string('shipping_address_line_1', 255)->nullable();
            $table->string('shipping_address_line_2', 255)->nullable();
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_state', 100)->nullable();
            $table->string('shipping_postal_code', 20)->nullable();
            $table->string('shipping_country', 100)->nullable();
            $table->string('billing_name', 255)->nullable();
            $table->string('billing_phone', 30)->nullable();
            $table->string('billing_address_line_1', 255)->nullable();
            $table->string('billing_address_line_2', 255)->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->string('billing_state', 100)->nullable();
            $table->string('billing_postal_code', 20)->nullable();
            $table->string('billing_country', 100)->nullable();
            $table->string('source', 30)->default('pos')->index();
            $table->string('created_by', 36)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'store_id', 'status']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'placed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
