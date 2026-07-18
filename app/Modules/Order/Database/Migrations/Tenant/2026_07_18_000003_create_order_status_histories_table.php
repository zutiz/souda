<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('tenant_id', 36)->nullable()->index();
            $table->string('order_id', 26)->index();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->string('changed_by', 36)->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
