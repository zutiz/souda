<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('billing_subscriptions');
            $table->string('tenant_id');
            $table->string('gateway', 50);
            $table->string('transaction_id')->nullable()->index();
            $table->integer('amount')->default(0);
            $table->string('currency', 3)->default('BDT');
            $table->string('status', 50)->index();
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_payments');
    }
};
