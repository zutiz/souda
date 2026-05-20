<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_category_id')->constrained('tax_categories')->cascadeOnDelete();
            $table->string('name', 255);
            $table->decimal('rate', 5, 2);
            $table->string('country', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->boolean('is_compound')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(1);
            $table->timestamps();

            $table->index('tax_category_id', 'idx_tr_category');
            $table->index('is_active', 'idx_tr_active');
            $table->index(['country', 'state', 'postal_code'], 'idx_tr_location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
