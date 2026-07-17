<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->nullable()->constrained('inventory_rules')->nullOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('severity')->default('info');
            $table->string('product_id')->nullable();
            $table->integer('warehouse_id')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('severity');
            $table->index(['product_id', 'warehouse_id']);
            $table->index('dismissed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_alerts');
    }
};
