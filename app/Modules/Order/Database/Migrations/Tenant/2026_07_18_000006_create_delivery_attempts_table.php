<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_attempts', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('tenant_id', 36)->nullable()->index();
            $table->string('shipment_id', 26)->index();
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->string('status', 30)->default('pending');
            $table->text('notes')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamps();

            $table->foreign('shipment_id')->references('id')->on('shipments')->cascadeOnDelete();
            $table->unique(['shipment_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_attempts');
    }
};
