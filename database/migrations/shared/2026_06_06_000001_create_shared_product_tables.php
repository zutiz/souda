<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('shared')->create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('description')->nullable();
            $table->string('logo_path', 500)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('tenant_id', 'idx_brands_tenant');
            $table->unique(['tenant_id', 'slug'], 'uq_brands_tenant_slug');
            $table->index('is_active', 'idx_brands_active');
        });

        Schema::connection('shared')->create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreignId('parent_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('description')->nullable();
            $table->string('image_path', 500)->nullable();
            $table->string('materialized_path', 500)->nullable();
            $table->tinyInteger('depth')->unsigned()->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->index('tenant_id', 'idx_categories_tenant');
            $table->unique(['tenant_id', 'slug'], 'uq_categories_tenant_slug');
            $table->index('parent_id', 'idx_categories_parent_id');
            $table->index('is_active', 'idx_categories_active');
            $table->index('materialized_path', 'idx_categories_materialized_path');
        });

        Schema::connection('shared')->create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('frontend_type', 20);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_variant')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('validation_rules')->nullable();
            $table->timestamps();
            $table->index('tenant_id', 'idx_attributes_tenant');
            $table->unique(['tenant_id', 'slug'], 'uq_attributes_tenant_slug');
            $table->index('is_variant', 'idx_attributes_variant');
        });

        Schema::connection('shared')->create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('value', 255);
            $table->string('swatch_color', 7)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index('tenant_id', 'idx_attr_values_tenant');
            $table->index('attribute_id', 'idx_attr_values_attribute');
            $table->unique(['tenant_id', 'attribute_id', 'value'], 'uq_attr_values_tenant_attribute_value');
        });

        Schema::connection('shared')->create('tax_categories', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index('tenant_id', 'idx_tax_categories_tenant');
        });

        Schema::connection('shared')->create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
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
            $table->index('tenant_id', 'idx_tr_tenant');
            $table->index('tax_category_id', 'idx_tr_category');
            $table->index('is_active', 'idx_tr_active');
            $table->index(['country', 'state', 'postal_code'], 'idx_tr_location');
        });

        Schema::connection('shared')->create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('name', 255);
            $table->string('code', 50);
            $table->string('address_line_1', 255)->nullable();
            $table->string('address_line_2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->index('tenant_id', 'idx_warehouses_tenant');
            $table->unique(['tenant_id', 'code'], 'uq_warehouses_tenant_code');
            $table->index('is_active', 'idx_warehouses_active');
        });

        Schema::connection('shared')->create('products', function (Blueprint $table) {
            $table->string('tenant_id', 36);
            $table->ulid('id')->primary();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('tax_category_id')->nullable()->constrained('tax_categories')->nullOnDelete();
            $table->string('name', 500);
            $table->string('slug', 500);
            $table->string('sku', 100)->nullable();
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
            $table->index('tenant_id', 'idx_products_tenant');
            $table->unique(['tenant_id', 'slug'], 'uq_products_tenant_slug');
            $table->unique(['tenant_id', 'sku'], 'uq_products_tenant_sku');
            $table->index('category_id', 'idx_products_category');
            $table->index('brand_id', 'idx_products_brand');
            $table->index('status', 'idx_products_status');
            $table->index('type', 'idx_products_type');
            $table->index(['status', 'published_at'], 'idx_products_active_status');
            $table->index(['tenant_id', 'status', 'category_id', 'created_at'], 'idx_products_tenant_active_category_created');
            $table->index(['tenant_id', 'status', 'brand_id', 'created_at'], 'idx_products_tenant_active_brand_created');
            $table->index('total_available', 'idx_products_total_available');
        });

        Schema::connection('shared')->create('category_product', function (Blueprint $table) {
            $table->string('tenant_id', 36);
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained('products')->cascadeOnDelete();
            $table->primary(['tenant_id', 'category_id', 'product_id']);
            $table->index('tenant_id', 'idx_cp_tenant');
        });

        Schema::connection('shared')->create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreignUlid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->nullable()->constrained('attribute_values')->nullOnDelete();
            $table->timestamps();
            $table->index('tenant_id', 'idx_pav_tenant');
            $table->index('product_id', 'idx_pav_product');
            $table->index('attribute_id', 'idx_pav_attribute');
            $table->unique(['tenant_id', 'product_id', 'attribute_id'], 'uq_pav_tenant_product_attribute');
        });

        Schema::connection('shared')->create('product_attribute_text_values', function (Blueprint $table) {
            $table->string('tenant_id', 36);
            $table->foreignId('product_attribute_value_id')
                ->constrained('product_attribute_values')
                ->cascadeOnDelete()
                ->primary();
            $table->text('text_value');
            $table->index('tenant_id', 'idx_patv_tenant');
        });

        Schema::connection('shared')->create('variants', function (Blueprint $table) {
            $table->string('tenant_id', 36);
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku', 100);
            $table->string('barcode', 100)->nullable();
            $table->string('barcode_type', 10)->nullable();
            $table->string('name', 500);
            $table->unsignedInteger('price');
            $table->unsignedInteger('compare_at_price')->nullable();
            $table->unsignedInteger('cost_price')->nullable();
            $table->boolean('track_inventory')->default(true);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index('tenant_id', 'idx_variants_tenant');
            $table->unique(['tenant_id', 'sku'], 'uq_variants_tenant_sku');
            $table->index('product_id', 'idx_variants_product');
            $table->index(['tenant_id', 'product_id', 'sku'], 'idx_variants_tenant_product_sku');
        });

        Schema::connection('shared')->create('variant_attribute_values', function (Blueprint $table) {
            $table->string('tenant_id', 36);
            $table->foreignUlid('variant_id')->constrained('variants')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $table->primary(['tenant_id', 'variant_id', 'attribute_value_id']);
            $table->index('tenant_id', 'idx_vav_tenant');
            $table->index('attribute_value_id', 'idx_vav_attribute_value');
        });

        Schema::connection('shared')->create('product_media', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
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
            $table->index('tenant_id', 'idx_media_tenant');
            $table->index('product_id', 'idx_media_product');
            $table->index('variant_id', 'idx_media_variant');
            $table->index(['tenant_id', 'product_id', 'is_primary'], 'idx_media_tenant_primary');
        });

        Schema::connection('shared')->create('warehouse_stock', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignUlid('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignUlid('variant_id')->nullable()->constrained('variants')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);
            $table->unsignedInteger('available_quantity')->virtualAs('quantity - reserved_quantity');
            $table->unsignedInteger('reorder_level')->default(5);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamps();
            $table->index('tenant_id', 'idx_ws_tenant');
            $table->unique(['tenant_id', 'warehouse_id', 'product_id', 'variant_id'], 'uq_ws_tenant_location');
            $table->index('warehouse_id', 'idx_ws_warehouse');
            $table->index('product_id', 'idx_ws_product');
            $table->index('variant_id', 'idx_ws_variant');
            $table->index(['warehouse_id', 'available_quantity'], 'idx_ws_available');
            $table->index(['product_id', 'variant_id'], 'idx_ws_product_variant');
            $table->index(['reorder_level', 'quantity'], 'idx_ws_low_stock');
        });

        Schema::connection('shared')->create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignUlid('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignUlid('variant_id')->nullable()->constrained('variants')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('reference_type', 100);
            $table->unsignedBigInteger('reference_id');
            $table->timestamp('expires_at');
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->index('tenant_id', 'idx_sr_tenant');
            $table->index(['reference_type', 'reference_id'], 'idx_sr_reference');
            $table->index(['status', 'expires_at'], 'idx_sr_expires');
            $table->index(['product_id', 'variant_id', 'status'], 'idx_sr_product');
            $table->unique(['tenant_id', 'reference_type', 'reference_id', 'warehouse_id', 'product_id', 'variant_id'], 'uq_sr_tenant_reference');
        });

        Schema::connection('shared')->create('stock_movements', function (Blueprint $table) {
            $table->string('tenant_id', 36);
            $table->ulid('id')->primary();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignUlid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignUlid('variant_id')->nullable()->constrained('variants')->nullOnDelete();
            $table->string('movement_type', 20);
            $table->integer('quantity');
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->json('snapshot_before')->nullable();
            $table->json('snapshot_after')->nullable();
            $table->string('audit_log_id', 26)->nullable();
            $table->timestamps();
            $table->index('tenant_id', 'idx_sm_tenant');
            $table->index('warehouse_id', 'idx_sm_warehouse');
            $table->index('product_id', 'idx_sm_product');
            $table->index('variant_id', 'idx_sm_variant');
            $table->index('movement_type', 'idx_sm_type');
            $table->index(['reference_type', 'reference_id'], 'idx_sm_reference');
            $table->index('created_at', 'idx_sm_created');
            $table->index(['product_id', 'variant_id', 'created_at'], 'idx_sm_product_created');
            $table->index(['reference_type', 'reference_id', 'created_at'], 'idx_sm_reference_lookup');
            $table->index(['warehouse_id', 'created_at'], 'idx_sm_warehouse_created');
        });

        Schema::connection('shared')->create('audit_logs', function (Blueprint $table) {
            $table->string('tenant_id', 36);
            $table->ulid('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 255)->nullable();
            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id');
            $table->string('action', 30);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('changed_fields')->nullable();
            $table->string('reference_type', 100)->nullable();
            $table->string('reference_id', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at', 0);
            $table->index('tenant_id', 'idx_audit_tenant');
            $table->index(['entity_type', 'entity_id', 'created_at'], 'idx_audit_entity');
            $table->index(['user_id', 'created_at'], 'idx_audit_user');
            $table->index(['action', 'created_at'], 'idx_audit_action');
            $table->index(['reference_type', 'reference_id'], 'idx_audit_reference');
            $table->index(['tenant_id', 'created_at'], 'idx_audit_tenant_created');
        });

        Schema::connection('shared')->create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->string('name', 255);
            $table->string('type', 20);
            $table->string('scope', 20);
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->string('condition_type', 30)->nullable();
            $table->json('condition_value')->nullable();
            $table->unsignedInteger('discount_value');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamps();
            $table->index('tenant_id', 'idx_pr_tenant');
            $table->index(['scope', 'scope_id'], 'idx_pr_scope');
            $table->index('is_active', 'idx_pr_active');
            $table->index(['start_at', 'end_at'], 'idx_pr_dates');
            $table->index('priority', 'idx_pr_priority');
            $table->index(['tenant_id', 'is_active', 'scope', 'scope_id', 'start_at', 'end_at'], 'idx_pr_tenant_active_scope_dates');
            $table->index(['is_active', 'priority', 'start_at'], 'idx_pr_active_priority');
        });
    }

    public function down(): void
    {
        Schema::connection('shared')->dropIfExists('pricing_rules');
        Schema::connection('shared')->dropIfExists('audit_logs');
        Schema::connection('shared')->dropIfExists('stock_movements');
        Schema::connection('shared')->dropIfExists('stock_reservations');
        Schema::connection('shared')->dropIfExists('warehouse_stock');
        Schema::connection('shared')->dropIfExists('product_media');
        Schema::connection('shared')->dropIfExists('variant_attribute_values');
        Schema::connection('shared')->dropIfExists('variants');
        Schema::connection('shared')->dropIfExists('product_attribute_text_values');
        Schema::connection('shared')->dropIfExists('product_attribute_values');
        Schema::connection('shared')->dropIfExists('category_product');
        Schema::connection('shared')->dropIfExists('products');
        Schema::connection('shared')->dropIfExists('warehouses');
        Schema::connection('shared')->dropIfExists('tax_rates');
        Schema::connection('shared')->dropIfExists('tax_categories');
        Schema::connection('shared')->dropIfExists('attribute_values');
        Schema::connection('shared')->dropIfExists('attributes');
        Schema::connection('shared')->dropIfExists('categories');
        Schema::connection('shared')->dropIfExists('brands');
    }
};
