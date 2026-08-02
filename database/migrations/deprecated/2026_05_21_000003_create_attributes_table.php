<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('frontend_type', 20);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_variant')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('validation_rules')->nullable();
            $table->timestamps();

            $table->index('slug', 'idx_attributes_slug');
            $table->index('is_variant', 'idx_attributes_variant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
