<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('value', 255);
            $table->string('swatch_color', 7)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('attribute_id', 'idx_attr_values_attribute');
            $table->unique(['attribute_id', 'value'], 'uq_attr_values_attribute_value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};
