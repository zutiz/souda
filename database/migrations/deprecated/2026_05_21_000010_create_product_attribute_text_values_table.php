<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attribute_text_values', function (Blueprint $table) {
            $table->foreignId('product_attribute_value_id')
                ->constrained('product_attribute_values')
                ->cascadeOnDelete()
                ->primary();
            $table->text('text_value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_text_values');
    }
};
