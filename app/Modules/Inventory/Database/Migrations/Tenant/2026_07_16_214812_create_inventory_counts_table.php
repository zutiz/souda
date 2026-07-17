<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_counts', function (Blueprint $table) {
            $table->id();
            $table->integer('warehouse_id');
            $table->string('reference')->unique();
            $table->string('type')->default('full');
            $table->string('status')->default('draft');
            $table->integer('counted_by')->unsigned()->nullable();
            $table->integer('verified_by')->unsigned()->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('counted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('adjusted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('warehouse_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_counts');
    }
};
