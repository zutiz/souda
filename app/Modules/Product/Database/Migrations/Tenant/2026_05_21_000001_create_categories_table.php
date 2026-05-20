<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->string('image_path', 500)->nullable();
            $table->string('materialized_path', 500)->nullable();
            $table->tinyInteger('depth')->unsigned()->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->index('parent_id', 'idx_categories_parent_id');
            $table->index('slug', 'idx_categories_slug');
            $table->index('is_active', 'idx_categories_active');
            $table->index('materialized_path', 'idx_categories_materialized_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
