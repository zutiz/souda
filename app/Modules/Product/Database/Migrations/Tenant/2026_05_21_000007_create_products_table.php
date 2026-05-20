<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('tax_category_id')->nullable()->constrained('tax_categories')->nullOnDelete();
            $table->string('name', 500);
            $table->string('slug', 500)->unique();
            $table->string('sku', 100)->nullable()->unique();
            $table->string('barcode', 100)->nullable();
            $table->string('barcode_type', 10)->nullable();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('type', 20)->default('simple');
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('base_price');
            $table->unsignedInteger('compare_at_price')->nullable();
            $table->unsignedInteger('cost_price')->nullable();
            $table->boolean('tax_inclusive')->default(false);
            $table->boolean('track_inventory')->default(true);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->unsignedInteger('total_quantity')->default(0);
            $table->unsignedInteger('total_reserved')->default(0);
            $table->unsignedInteger('total_available')->virtualAs('total_quantity - total_reserved');
            $table->unsignedInteger('warehouse_count')->default(0);
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('category_id', 'idx_products_category');
            $table->index('brand_id', 'idx_products_brand');
            $table->index('sku', 'idx_products_sku');
            $table->index('barcode', 'idx_products_barcode');
            $table->index('status', 'idx_products_status');
            $table->index('type', 'idx_products_type');
            $table->index('slug', 'idx_products_slug');
            $table->index(['status', 'published_at'], 'idx_products_active_status');
            $table->index(['status', 'category_id', 'created_at'], 'idx_products_active_category_created');
            $table->index(['status', 'brand_id', 'created_at'], 'idx_products_active_brand_created');
            $table->index(['status', 'slug'], 'idx_products_active_slug');
            $table->index('total_available', 'idx_products_total_available');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
