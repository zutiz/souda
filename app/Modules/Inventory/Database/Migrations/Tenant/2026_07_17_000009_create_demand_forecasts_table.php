<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demand_forecasts', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->date('forecast_date');
            $table->integer('forecast_quantity');
            $table->integer('confidence_lower')->nullable();
            $table->integer('confidence_upper')->nullable();
            $table->string('model_used');
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('actual_quantity')->nullable();
            $table->decimal('accuracy_score', 5, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id', 'forecast_date']);
            $table->index('model_used');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demand_forecasts');
    }
};
