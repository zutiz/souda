<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_media', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUlid('variant_id')->nullable()->constrained('variants')->cascadeOnDelete();
            $table->string('file_path', 500);
            $table->string('file_type', 20)->default('image');
            $table->string('mime_type', 100);
            $table->unsignedInteger('file_size');
            $table->string('alt_text', 255)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id', 'idx_media_product');
            $table->index('variant_id', 'idx_media_variant');
            $table->index(['product_id', 'is_primary'], 'idx_media_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_media');
    }
};
