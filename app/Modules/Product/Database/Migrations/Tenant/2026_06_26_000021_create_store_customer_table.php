<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_customer', function (Blueprint $table) {
            $table->string('store_id', 26);
            $table->string('customer_id', 26);
            $table->string('loyalty_number')->nullable();
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->unsignedInteger('total_visits')->default(0);
            $table->unsignedBigInteger('total_spent')->default(0);
            $table->timestamp('last_visit_at')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->primary(['store_id', 'customer_id']);
            $table->index('customer_id');
            $table->index('loyalty_number');

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_customer');
    }
};
