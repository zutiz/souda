# Product Management Module — Architectural Plan

> Multi-tenant Laravel SaaS ERP | Phase 1 | `app/Modules/Product/`

---

## 1. Database Schema

All tables live in **tenant databases** (`database/migrations/tenant/`). Prices are stored as integers (smallest currency unit — cents/paisa).

### 1.1 Categories

```
categories
├── id                    BIGINT UNSIGNED PK
├── parent_id             BIGINT UNSIGNED NULL (self-referencing, nullable)
├── name                  VARCHAR(255) NOT NULL
├── slug                  VARCHAR(255) UNIQUE NOT NULL
├── description           TEXT NULL
├── image_path            VARCHAR(500) NULL
├── materialized_path     VARCHAR(500) NULL (e.g., "/1/5/12/")
├── depth                 TINYINT UNSIGNED DEFAULT 0
├── is_active             BOOLEAN DEFAULT true
├── sort_order            INT UNSIGNED DEFAULT 0
├── meta_title            VARCHAR(255) NULL
├── meta_description      TEXT NULL
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP

Indexes:
  - idx_categories_parent_id (parent_id)
  - idx_categories_slug (slug)
  - idx_categories_active (is_active)
  - idx_categories_materialized_path (materialized_path)
```

### 1.2 Brands

```
brands
├── id                    BIGINT UNSIGNED PK
├── name                  VARCHAR(255) NOT NULL
├── slug                  VARCHAR(255) UNIQUE NOT NULL
├── description           TEXT NULL
├── logo_path             VARCHAR(500) NULL
├── website_url           VARCHAR(500) NULL
├── is_active             BOOLEAN DEFAULT true
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP

Indexes:
  - idx_brands_slug (slug)
  - idx_brands_active (is_active)
```

### 1.3 Attributes

```
attributes
├── id                    BIGINT UNSIGNED PK
├── name                  VARCHAR(255) NOT NULL
├── slug                  VARCHAR(255) UNIQUE NOT NULL
├── frontend_type         ENUM('select', 'multi_select', 'text', 'textarea', 'color', 'swatch') NOT NULL
├── is_filterable         BOOLEAN DEFAULT false
├── is_required           BOOLEAN DEFAULT false
├── is_variant            BOOLEAN DEFAULT false (used for variant generation)
├── sort_order            INT UNSIGNED DEFAULT 0
├── validation_rules      JSON NULL
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP

Indexes:
  - idx_attributes_slug (slug)
  - idx_attributes_variant (is_variant)
```

### 1.4 Attribute Values

```
attribute_values
├── id                    BIGINT UNSIGNED PK
├── attribute_id          BIGINT UNSIGNED FK → attributes.id
├── value                 VARCHAR(255) NOT NULL
├── swatch_color          VARCHAR(7) NULL (hex color for swatch type)
├── sort_order            INT UNSIGNED DEFAULT 0
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP

Indexes:
  - idx_attr_values_attribute (attribute_id)
  - uq_attr_values_attribute_value UNIQUE (attribute_id, value)
```

### 1.5 Products

```
products
├── id                    CHAR(26) PK (ULID, sortable)
├── category_id           BIGINT UNSIGNED FK → categories.id NULL
├── brand_id              BIGINT UNSIGNED FK → brands.id NULL
├── tax_category_id       BIGINT UNSIGNED FK → tax_categories.id NULL
├── name                  VARCHAR(500) NOT NULL
├── slug                  VARCHAR(500) UNIQUE NOT NULL
├── sku                   VARCHAR(100) UNIQUE NULL
├── barcode               VARCHAR(100) NULL
├── barcode_type          ENUM('ean13', 'upc', 'code128', 'qr') NULL
├── description           TEXT NULL
├── short_description     TEXT NULL
├── type                  ENUM('simple', 'configurable', 'bundle', 'virtual') DEFAULT 'simple'
├── status                ENUM('draft', 'active', 'archived') DEFAULT 'draft'
├── base_price            INT UNSIGNED NOT NULL (in smallest currency unit)
├── compare_at_price      INT UNSIGNED NULL
├── cost_price            INT UNSIGNED NULL
├── tax_inclusive         BOOLEAN DEFAULT false
├── track_inventory       BOOLEAN DEFAULT true
├── low_stock_threshold   INT UNSIGNED DEFAULT 5
├── total_quantity        INT UNSIGNED DEFAULT 0 (materialized, updated by observer)
├── total_reserved        INT UNSIGNED DEFAULT 0 (materialized, updated by observer)
├── total_available       GENERATED ALWAYS AS (total_quantity - total_reserved) STORED
├── warehouse_count       INT UNSIGNED DEFAULT 0 (how many warehouses carry this product)
├── weight                DECIMAL(10,2) NULL (kg)
├── length                DECIMAL(10,2) NULL (cm)
├── width                 DECIMAL(10,2) NULL (cm)
├── height                DECIMAL(10,2) NULL (cm)
├── metadata              JSON NULL
├── published_at          TIMESTAMP NULL
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP

Indexes:
  - idx_products_category (category_id)
  - idx_products_brand (brand_id)
  - idx_products_sku (sku)
  - idx_products_barcode (barcode)
  - idx_products_status (status)
  - idx_products_type (type)
  - idx_products_slug (slug)
  - idx_products_active_status (status, published_at)
  - idx_products_active_category_created (status, category_id, created_at DESC)
  - idx_products_active_brand_created (status, brand_id, created_at DESC)
  - idx_products_active_slug (status, slug)
  - idx_products_total_available (total_available)
```

### 1.6 Product-Attribute Values (Pivot)

```
product_attribute_values
├── id                    BIGINT UNSIGNED PK
├── product_id            CHAR(26) FK → products.id
├── attribute_id          BIGINT UNSIGNED FK → attributes.id
├── attribute_value_id    BIGINT UNSIGNED FK → attribute_values.id NULL
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP

Indexes:
  - idx_pav_product (product_id)
  - idx_pav_attribute (attribute_id)
  - uq_pav_product_attribute UNIQUE (product_id, attribute_id)
```

> **Note:** `text_value` moved to separate table (`product_attribute_text_values`) to prevent row overflow on the hot pivot table.

### 1.6b Product Attribute Text Values (NEW)

```
product_attribute_text_values
├── product_attribute_value_id  BIGINT UNSIGNED PK/FK → product_attribute_values.id
└── text_value                  TEXT NOT NULL
```

### 1.7 Product-Category (Pivot — many-to-many)

```
category_product
├── category_id           BIGINT UNSIGNED FK → categories.id
└── product_id            BIGINT UNSIGNED FK → products.id

Primary Key: (category_id, product_id)
```

### 1.8 Variants

```
variants
├── id                    CHAR(26) PK (ULID, sortable)
├── product_id            CHAR(26) FK → products.id
├── sku                   VARCHAR(100) UNIQUE NOT NULL
├── barcode               VARCHAR(100) NULL
├── barcode_type          ENUM('ean13', 'upc', 'code128', 'qr') NULL
├── name                  VARCHAR(500) NOT NULL (e.g., "T-Shirt / Red / Large")
├── price                 INT UNSIGNED NOT NULL (overrides base_price if set)
├── compare_at_price      INT UNSIGNED NULL
├── cost_price            INT UNSIGNED NULL
├── track_inventory       BOOLEAN DEFAULT true
├── low_stock_threshold   INT UNSIGNED DEFAULT 5
├── weight                DECIMAL(10,2) NULL
├── length                DECIMAL(10,2) NULL
├── width                 DECIMAL(10,2) NULL
├── height                DECIMAL(10,2) NULL
├── is_default            BOOLEAN DEFAULT false
├── sort_order            INT UNSIGNED DEFAULT 0
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP

Indexes:
  - idx_variants_product (product_id)
  - idx_variants_sku (sku)
  - idx_variants_barcode (barcode)
  - idx_variants_product_sku (product_id, sku)
```

### 1.9 Variant-Attribute Value (Pivot)

```
variant_attribute_values
├── variant_id            BIGINT UNSIGNED FK → variants.id
└── attribute_value_id    BIGINT UNSIGNED FK → attribute_values.id

Primary Key: (variant_id, attribute_value_id)
Indexes:
  - idx_vav_attribute_value (attribute_value_id)
```

### 1.10 Product Media

```
product_media
├── id                    BIGINT UNSIGNED PK
├── product_id            CHAR(26) FK → products.id
├── variant_id            CHAR(26) FK → variants.id NULL (media specific to variant)
├── file_path             VARCHAR(500) NOT NULL
├── file_type             ENUM('image', 'video', 'document') DEFAULT 'image'
├── mime_type             VARCHAR(100) NOT NULL
├── file_size             INT UNSIGNED NOT NULL (bytes)
├── alt_text              VARCHAR(255) NULL
├── is_primary            BOOLEAN DEFAULT false
├── sort_order            INT UNSIGNED DEFAULT 0
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP

Indexes:
  - idx_media_product (product_id)
  - idx_media_variant (variant_id)
  - idx_media_primary (product_id, is_primary)
```

### 1.11 Warehouses

```
warehouses
├── id                    BIGINT UNSIGNED PK
├── name                  VARCHAR(255) NOT NULL
├── code                  VARCHAR(50) UNIQUE NOT NULL
├── address_line_1        VARCHAR(255) NULL
├── address_line_2        VARCHAR(255) NULL
├── city                  VARCHAR(100) NULL
├── state                 VARCHAR(100) NULL
├── postal_code           VARCHAR(20) NULL
├── country               VARCHAR(100) NULL
├── phone                 VARCHAR(30) NULL
├── email                 VARCHAR(255) NULL
├── is_active             BOOLEAN DEFAULT true
├── is_default            BOOLEAN DEFAULT false
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP

Indexes:
  - idx_warehouses_code (code)
  - idx_warehouses_active (is_active)
```

### 1.12 Warehouse Stock

```
warehouse_stock
├── id                    BIGINT UNSIGNED PK
├── warehouse_id          BIGINT UNSIGNED FK → warehouses.id
├── product_id            CHAR(26) FK → products.id NULL
├── variant_id            CHAR(26) FK → variants.id NULL
├── quantity              INT UNSIGNED DEFAULT 0
├── reserved_quantity     INT UNSIGNED DEFAULT 0
├── available_quantity    GENERATED ALWAYS AS (quantity - reserved_quantity) STORED
├── reorder_level         INT UNSIGNED DEFAULT 5
├── lock_version          INT UNSIGNED DEFAULT 0 (optimistic locking)
├── last_movement_at      TIMESTAMP NULL
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP

Indexes:
  - uq_warehouse_stock_location UNIQUE (warehouse_id, product_id, variant_id)
  - idx_ws_warehouse (warehouse_id)
  - idx_ws_product (product_id)
  - idx_ws_variant (variant_id)
  - idx_ws_available (warehouse_id, available_quantity)
  - idx_ws_product_variant (product_id, variant_id)
  - idx_ws_low_stock (reorder_level, quantity)

Check:
  - CK_ws_reserved_lte_quantity: reserved_quantity <= quantity
```

### 1.13 Stock Movements

```
stock_movements
├── id                    CHAR(26) PK (ULID, sortable)
├── warehouse_id          BIGINT UNSIGNED FK → warehouses.id
├── product_id            CHAR(26) FK → products.id NULL
├── variant_id            CHAR(26) FK → variants.id NULL
├── movement_type         ENUM('received', 'sold', 'return', 'adjustment', 'transfer_in', 'transfer_out', 'damaged', 'expired') NOT NULL
├── quantity              INT NOT NULL (positive for in, negative for out)
├── reference_type        VARCHAR(100) NULL (e.g., 'order', 'purchase_order', 'adjustment')
├── reference_id          BIGINT UNSIGNED NULL
├── notes                 TEXT NULL
├── performed_by          BIGINT UNSIGNED NULL (user_id from central, stored for audit)
├── snapshot_before       JSON NULL (warehouse_stock state before movement)
├── snapshot_after        JSON NULL (warehouse_stock state after movement)
├── audit_log_id          BIGINT UNSIGNED NULL (links to audit_logs)
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP

Indexes:
  - idx_sm_warehouse (warehouse_id)
  - idx_sm_product (product_id)
  - idx_sm_variant (variant_id)
  - idx_sm_type (movement_type)
  - idx_sm_reference (reference_type, reference_id)
  - idx_sm_created (created_at)
  - idx_sm_product_created (product_id, variant_id, created_at DESC)
  - idx_sm_reference_lookup (reference_type, reference_id, created_at DESC)
  - idx_sm_warehouse_created (warehouse_id, created_at DESC)

Partitioning:
  - RANGE by month on UNIX_TIMESTAMP(created_at)
  - Monthly archival job moves records >13 months to stock_movements_archive
```

### 1.14 Tax Categories

```
tax_categories
├── id                    BIGINT UNSIGNED PK
├── name                  VARCHAR(255) NOT NULL
├── description           TEXT NULL
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

### 1.15 Tax Rates

```
tax_rates
├── id                    BIGINT UNSIGNED PK
├── tax_category_id       BIGINT UNSIGNED FK → tax_categories.id
├── name                  VARCHAR(255) NOT NULL
├── rate                  DECIMAL(5,2) NOT NULL (percentage, e.g., 15.00 for 15%)
├── country               VARCHAR(100) NULL (NULL = all countries)
├── state                 VARCHAR(100) NULL (NULL = all states)
├── postal_code           VARCHAR(20) NULL (NULL = all postal codes)
├── is_compound           BOOLEAN DEFAULT false
├── is_active             BOOLEAN DEFAULT true
├── priority              INT UNSIGNED DEFAULT 1
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP

Indexes:
  - idx_tr_category (tax_category_id)
  - idx_tr_active (is_active)
  - idx_tr_location (country, state, postal_code)
```

### 1.16 Pricing Rules

```
pricing_rules
├── id                    BIGINT UNSIGNED PK
├── name                  VARCHAR(255) NOT NULL
├── type                  ENUM('fixed', 'percentage', 'tiered') NOT NULL
├── scope                 ENUM('product', 'category', 'brand', 'all') NOT NULL
├── scope_id              BIGINT UNSIGNED NULL (product_id, category_id, or brand_id)
├── condition_type        ENUM('quantity', 'cart_total', 'customer_group', 'date_range') NULL
├── condition_value       JSON NULL
├── discount_value        INT UNSIGNED NOT NULL (cents for fixed, basis points for percentage)
├── start_at              TIMESTAMP NULL
├── end_at                TIMESTAMP NULL
├── is_active             BOOLEAN DEFAULT true
├── priority              INT UNSIGNED DEFAULT 0 (higher = applied first)
├── max_uses              INT UNSIGNED NULL
├── used_count            INT UNSIGNED DEFAULT 0
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP

Indexes:
  - idx_pr_scope (scope, scope_id)
  - idx_pr_active (is_active)
  - idx_pr_dates (start_at, end_at)
  - idx_pr_priority (priority DESC)
  - idx_pr_active_scope_dates (is_active, scope, scope_id, start_at, end_at)
  - idx_pr_active_priority (is_active, priority DESC, start_at)
```

### 1.17 Stock Reservations (NEW)

```
stock_reservations
├── id                    BIGINT UNSIGNED PK
├── warehouse_id          BIGINT UNSIGNED FK → warehouses.id
├── product_id            CHAR(26) FK → products.id NULL
├── variant_id            CHAR(26) FK → variants.id NULL
├── quantity              INT UNSIGNED NOT NULL
├── reference_type        VARCHAR(100) NOT NULL (e.g., 'order', 'cart')
├── reference_id          BIGINT UNSIGNED NOT NULL
├── expires_at            TIMESTAMP NOT NULL
├── status                ENUM('active', 'consumed', 'expired', 'cancelled') DEFAULT 'active'
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP

Indexes:
  - idx_sr_reference (reference_type, reference_id)
  - idx_sr_expires (status, expires_at)
  - idx_sr_product (product_id, variant_id, status)
  - uq_sr_reference UNIQUE (reference_type, reference_id, warehouse_id, product_id, variant_id)
```

### 1.18 Audit Logs (NEW — stock-only audit trail)

```
audit_logs
├── id                    CHAR(26) PK (ULID, sortable)
├── tenant_id             VARCHAR(36) NOT NULL (stored as string, no FK)
├── user_id               BIGINT UNSIGNED NULL (central DB user)
├── user_name             VARCHAR(255) NULL (denormalized for self-containment)
├── entity_type           VARCHAR(100) NOT NULL ('warehouse_stock', 'stock_movement')
├── entity_id             BIGINT UNSIGNED NOT NULL
├── action                ENUM('stock_received', 'stock_deducted', 'stock_adjusted', 'stock_transferred', 'stock_damaged', 'stock_expired', 'stock_reserved', 'stock_released') NOT NULL
├── old_values            JSON NULL
├── new_values            JSON NULL
├── changed_fields        JSON NULL (e.g., ["quantity", "reserved_quantity"])
├── reference_type        VARCHAR(100) NULL (e.g., 'order', 'import', 'api')
├── reference_id          VARCHAR(100) NULL
├── ip_address            VARCHAR(45) NULL
├── user_agent            VARCHAR(500) NULL
├── created_at            TIMESTAMP

Indexes:
  - idx_audit_entity (entity_type, entity_id, created_at DESC)
  - idx_audit_user (user_id, created_at DESC)
  - idx_audit_action (action, created_at DESC)
  - idx_audit_reference (reference_type, reference_id)
  - idx_audit_tenant_created (tenant_id, created_at DESC)

Partitioning:
  - RANGE by month on UNIX_TIMESTAMP(created_at)
  - Monthly archival job moves records >13 months to audit_logs_archive
```

---

## 2. Eloquent Model Relationships

> **Note:** `Product`, `Variant`, and `StockMovement` use ULID IDs (`HasUlids` trait). All other models use BIGINT auto-increment.

```
Category
├── parent(): BelongsTo → Category (self-referencing)
├── children(): HasMany → Category
├── products(): BelongsToMany → Product (via category_product)
└── pricingRules(): HasMany → PricingRule (scope='category')

Brand
├── products(): HasMany → Product
└── pricingRules(): HasMany → PricingRule (scope='brand')

Attribute
├── values(): HasMany → AttributeValue
└── productValues(): HasMany → ProductAttributeValue

AttributeValue
├── attribute(): BelongsTo → Attribute
├── productValues(): HasMany → ProductAttributeValue
└── variants(): BelongsToMany → Variant (via variant_attribute_values)

Product (ULID)
├── category(): BelongsTo → Category (primary)
├── categories(): BelongsToMany → Category (via category_product)
├── brand(): BelongsTo → Brand
├── taxCategory(): BelongsTo → TaxCategory
├── variants(): HasMany → Variant
├── media(): HasMany → ProductMedia
├── attributeValues(): HasMany → ProductAttributeValue
├── warehouseStock(): HasMany → WarehouseStock
├── stockMovements(): HasMany → StockMovement
├── pricingRules(): HasMany → PricingRule (scope='product')
└── parent(): BelongsTo → Product (for bundle children, nullable)

Variant (ULID)
├── product(): BelongsTo → Product
├── attributeValues(): BelongsToMany → AttributeValue (via variant_attribute_values)
├── media(): HasMany → ProductMedia (variant-specific)
└── warehouseStock(): HasMany → WarehouseStock

ProductMedia
├── product(): BelongsTo → Product
└── variant(): BelongsTo → Variant (nullable)

Warehouse
├── stock(): HasMany → WarehouseStock
├── movements(): HasMany → StockMovement
└── reservations(): HasMany → StockReservation

WarehouseStock
├── warehouse(): BelongsTo → Warehouse
├── product(): BelongsTo → Product (nullable)
├── variant(): BelongsTo → Variant (nullable)
├── movements(): HasMany → StockMovement
└── reservations(): HasMany → StockReservation

StockMovement (ULID)
├── warehouse(): BelongsTo → Warehouse
├── product(): BelongsTo → Product (nullable)
├── variant(): BelongsTo → Variant (nullable)
└── auditLog(): BelongsTo → AuditLog (nullable)

StockReservation
├── warehouse(): BelongsTo → Warehouse
├── product(): BelongsTo → Product (nullable)
├── variant(): BelongsTo → Variant (nullable)
└── scope isActive(): Builder (filters active, non-expired reservations)

TaxCategory
├── rates(): HasMany → TaxRate
└── products(): HasMany → Product

TaxRate
└── taxCategory(): BelongsTo → TaxCategory

PricingRule
└── scopeModel(): MorphTo → Product|Category|Brand (polymorphic)

AuditLog (ULID)
└── stockMovement(): HasOne → StockMovement
```

---

## 3. DTO Structure

All DTOs are `readonly` classes with `fromModel()` and `fromRequest()` factories.

```php
// app/Modules/Product/DTOs/

// CategoryDTO
readonly class CategoryDTO
{
    public function __construct(
        public ?int $id,
        public ?int $parentId,
        public string $name,
        public string $slug,
        public ?string $description,
        public bool $isActive,
        public int $sortOrder,
        public ?string $metaTitle,
        public ?string $metaDescription,
        public ?array $children,
    ) {}

    public static function fromModel(Category $category): self { ... }
    public static function fromRequest(StoreCategoryRequest $request): self { ... }
}

// BrandDTO
readonly class BrandDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public ?string $websiteUrl,
        public bool $isActive,
    ) {}

    public static function fromModel(Brand $brand): self { ... }
}

// AttributeDTO
readonly class AttributeDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $slug,
        public AttributeTypeEnum $frontendType,
        public bool $isFilterable,
        public bool $isRequired,
        public bool $isVariant,
        public ?array $values,
    ) {}

    public static function fromModel(Attribute $attribute): self { ... }
}

// AttributeValueDTO
readonly class AttributeValueDTO
{
    public function __construct(
        public int $id,
        public int $attributeId,
        public string $value,
        public ?string $swatchColor,
        public int $sortOrder,
    ) {}

    public static function fromModel(AttributeValue $value): self { ... }
}

// ProductDTO
readonly class ProductDTO
{
    public function __construct(
        public ?int $id,
        public ?int $categoryId,
        public ?int $brandId,
        public ?int $taxCategoryId,
        public string $name,
        public string $slug,
        public ?string $sku,
        public ?string $barcode,
        public ?BarcodeTypeEnum $barcodeType,
        public ?string $description,
        public ?string $shortDescription,
        public ProductTypeEnum $type,
        public ProductStatusEnum $status,
        public int $basePrice,
        public ?int $compareAtPrice,
        public ?int $costPrice,
        public bool $taxInclusive,
        public bool $trackInventory,
        public int $lowStockThreshold,
        public ?array $dimensions,
        public ?array $categoryIds,
        public ?array $attributeValues,
        public ?array $metadata,
        public ?array $media,
        public ?CarbonImmutable $publishedAt,
    ) {}

    public static function fromModel(Product $product): self { ... }
    public static function fromRequest(StoreProductRequest $request): self { ... }
}

// VariantDTO
readonly class VariantDTO
{
    public function __construct(
        public ?int $id,
        public int $productId,
        public string $sku,
        public ?string $barcode,
        public ?BarcodeTypeEnum $barcodeType,
        public string $name,
        public int $price,
        public ?int $compareAtPrice,
        public ?int $costPrice,
        public bool $trackInventory,
        public int $lowStockThreshold,
        public ?array $dimensions,
        public bool $isDefault,
        public array $attributeValueIds,
    ) {}

    public static function fromModel(Variant $variant): self { ... }
}

// ProductMediaDTO
readonly class ProductMediaDTO
{
    public function __construct(
        public ?int $id,
        public int $productId,
        public ?int $variantId,
        public string $filePath,
        public MediaTypeEnum $fileType,
        public string $mimeType,
        public int $fileSize,
        public ?string $altText,
        public bool $isPrimary,
        public int $sortOrder,
    ) {}

    public static function fromModel(ProductMedia $media): self { ... }
}

// WarehouseDTO
readonly class WarehouseDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $code,
        public ?array $address,
        public ?string $phone,
        public ?string $email,
        public bool $isActive,
        public bool $isDefault,
    ) {}

    public static function fromModel(Warehouse $warehouse): self { ... }
}

// WarehouseStockDTO
readonly class WarehouseStockDTO
{
    public function __construct(
        public ?int $id,
        public int $warehouseId,
        public ?int $productId,
        public ?int $variantId,
        public int $quantity,
        public int $reservedQuantity,
        public int $availableQuantity,
        public int $reorderLevel,
    ) {}

    public static function fromModel(WarehouseStock $stock): self { ... }
}

// StockMovementDTO
readonly class StockMovementDTO
{
    public function __construct(
        public ?int $id,
        public int $warehouseId,
        public ?int $productId,
        public ?int $variantId,
        public MovementTypeEnum $movementType,
        public int $quantity,
        public ?string $referenceType,
        public ?int $referenceId,
        public ?string $notes,
        public ?int $performedBy,
    ) {}

    public static function fromModel(StockMovement $movement): self { ... }
}

// TaxCategoryDTO
readonly class TaxCategoryDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $description,
        public ?array $rates,
    ) {}

    public static function fromModel(TaxCategory $category): self { ... }
}

// TaxRateDTO
readonly class TaxRateDTO
{
    public function __construct(
        public ?int $id,
        public int $taxCategoryId,
        public string $name,
        public float $rate,
        public ?string $country,
        public ?string $state,
        public ?string $postalCode,
        public bool $isCompound,
        public bool $isActive,
        public int $priority,
    ) {}

    public static function fromModel(TaxRate $rate): self { ... }
}

// PricingRuleDTO
readonly class PricingRuleDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public PricingRuleTypeEnum $type,
        public PricingRuleScopeEnum $scope,
        public ?int $scopeId,
        public ?PricingRuleConditionEnum $conditionType,
        public ?array $conditionValue,
        public int $discountValue,
        public ?CarbonImmutable $startAt,
        public ?CarbonImmutable $endAt,
        public bool $isActive,
        public int $priority,
        public ?int $maxUses,
        public int $usedCount,
    ) {}

    public static function fromModel(PricingRule $rule): self { ... }
}

// ProductSummaryDTO (lightweight for listings)
readonly class ProductSummaryDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public ?string $sku,
        public ?string $thumbnailUrl,
        public int $basePrice,
        public ProductStatusEnum $status,
        public ProductTypeEnum $type,
        public ?string $categoryName,
        public ?string $brandName,
        public int $totalStock,
        public ?string $publishedAt,
    ) {}

    public static function fromModel(Product $product): self { ... }
}

// ProductWithStockDTO (product + aggregate stock info)
readonly class ProductWithStockDTO
{
    public function __construct(
        public ProductDTO $product,
        public int $totalQuantity,
        public int $totalReserved,
        public int $totalAvailable,
        public array $warehouseBreakdown,
    ) {}
}

// SKU Generation DTO
readonly class SKUGenerationDTO
{
    public function __construct(
        public int $productId,
        public string $productSku,
        public array $variants,
    ) {}
}
```

---

## 4. Repository Contracts

> **Note:** Per project convention (`02-folder-structure.md`), this project does NOT use the Repository pattern. Services operate directly on Eloquent models. The contracts below are provided as **service-level interfaces** for cross-module communication, not data-access repositories.

```php
// app/Modules/Product/Contracts/

interface ProductResolver
{
    public function findById(int $id): ?Product;
    public function findBySlug(string $slug): ?Product;
    public function findBySku(string $sku): ?Product;
    public function findByBarcode(string $barcode): ?Product;
    public function findActive(int $id): ?Product;
    public function search(ProductSearchCriteria $criteria): LengthAwarePaginator;
}

interface StockChecker
{
    public function getAvailableQuantity(int $productId, ?int $variantId = null, ?int $warehouseId = null): int;
    public function getTotalAvailable(int $productId, ?int $variantId = null): int;
    public function isAvailable(int $productId, ?int $variantId = null, int $quantity = 1): bool;
    public function getLowStockProducts(int $threshold = null): Collection;
    public function getWarehouseStock(int $productId, ?int $variantId = null): Collection;
}

interface PricingCalculator
{
    public function calculatePrice(int $productId, ?int $variantId = null, ?array $context = null): PriceResult;
    public function calculateTax(int $priceAmount, ?int $taxCategoryId = null, ?array $location = null): TaxResult;
    public function getApplicableRules(int $productId, ?int $variantId = null): Collection;
}

interface SKUGenerator
{
    public function generateForProduct(Product $product): string;
    public function generateForVariant(Variant $variant): string;
    public function isUnique(string $sku, ?int $excludeProductId = null): bool;
}
```

### Supporting Value Objects

```php
readonly class ProductSearchCriteria
{
    public function __construct(
        public ?string $search,
        public ?int $categoryId,
        public ?int $brandId,
        public ?ProductStatusEnum $status,
        public ?ProductTypeEnum $type,
        public ?int $minPrice,
        public ?int $maxPrice,
        public ?array $attributeFilters,
        public ?string $sortBy,
        public string $sortDirection,
        public int $perPage,
        public int $page,
    ) {}
}

readonly class PriceResult
{
    public function __construct(
        public int $basePrice,
        public int $finalPrice,
        public int $discountAmount,
        public ?PricingRule $appliedRule,
        public bool $taxInclusive,
    ) {}
}

readonly class TaxResult
{
    public function __construct(
        public int $netAmount,
        public int $taxAmount,
        public int $grossAmount,
        public array $appliedRates,
    ) {}
}
```

---

## 5. Service Layer Design

### 5.1 ProductService

```php
class ProductService
{
    public function __construct(
        protected EventDispatcher $events,
        protected SKUGenerator $skuGenerator,
    ) {}

    public function createProduct(ProductDTO $dto): Product
    public function updateProduct(Product $product, ProductDTO $dto): Product
    public function deleteProduct(Product $product): bool
    public function archiveProduct(Product $product): Product
    public function restoreProduct(Product $product): Product
    public function publishProduct(Product $product): Product
    public function duplicateProduct(Product $product): Product
    public function listProducts(ProductSearchCriteria $criteria): LengthAwarePaginator
    public function getProductSummary(Product $product): ProductSummaryDTO
    public function getProductWithStock(Product $product): ProductWithStockDTO
}
```

### 5.2 CategoryService

```php
class CategoryService
{
    public function createCategory(CategoryDTO $dto): Category
    public function updateCategory(Category $category, CategoryDTO $dto): Category
    public function deleteCategory(Category $category): bool
    public function getCategoryTree(): Collection
    public function reorderCategories(array $order): void
}
```

### 5.3 BrandService

```php
class BrandService
{
    public function createBrand(BrandDTO $dto): Brand
    public function updateBrand(Brand $brand, BrandDTO $dto): Brand
    public function deleteBrand(Brand $brand): bool
    public function listActiveBrands(): Collection
}
```

### 5.4 VariantService

```php
class VariantService
{
    public function __construct(
        protected SKUGenerator $skuGenerator,
    ) {}

    public function createVariant(VariantDTO $dto): Variant
    public function updateVariant(Variant $variant, VariantDTO $dto): Variant
    public function deleteVariant(Variant $variant): bool
    public function generateVariants(Product $product, array $attributeCombinations): Collection
    public function setDefaultVariant(Product $product, Variant $variant): Product
}
```

### 5.5 AttributeService

```php
class AttributeService
{
    public function createAttribute(AttributeDTO $dto): Attribute
    public function updateAttribute(Attribute $attribute, AttributeDTO $dto): Attribute
    public function deleteAttribute(Attribute $attribute): bool
    public function getVariantAttributes(): Collection
    public function getFilterableAttributes(): Collection
    public function addValue(Attribute $attribute, string $value, ?string $swatchColor = null): AttributeValue
    public function deleteValue(AttributeValue $value): bool
}
```

### 5.6 MediaService

```php
class MediaService
{
    public function uploadMedia(Product $product, UploadedFile $file, ?Variant $variant = null): ProductMedia
    public function updateMedia(ProductMedia $media, array $data): ProductMedia
    public function deleteMedia(ProductMedia $media): bool
    public function setPrimaryMedia(Product $product, ProductMedia $media): Product
    public function reorderMedia(Product $product, array $order): void
    public function uploadBulk(Product $product, array $files): Collection
}
```

### 5.7 WarehouseService

```php
class WarehouseService
{
    public function createWarehouse(WarehouseDTO $dto): Warehouse
    public function updateWarehouse(Warehouse $warehouse, WarehouseDTO $dto): Warehouse
    public function deleteWarehouse(Warehouse $warehouse): bool
    public function listActiveWarehouses(): Collection
    public function setDefaultWarehouse(Warehouse $warehouse): Warehouse
}
```

### 5.8 StockService

```php
class StockService
{
    public function __construct(
        protected EventDispatcher $events,
        protected StockReservationService $reservations,
    ) {}

    /**
     * All stock operations use SELECT ... FOR UPDATE with optimistic locking.
     * Each operation records before/after snapshots for audit trail.
     */
    public function receiveStock(int $warehouseId, ?int $productId, ?int $variantId, int $quantity, ?string $notes = null): StockMovement
    public function deductStock(int $warehouseId, ?int $productId, ?int $variantId, int $quantity, ?string $referenceType = null, ?int $referenceId = null, ?string $notes = null): StockMovement
    public function adjustStock(int $warehouseId, ?int $productId, ?int $variantId, int $newQuantity, string $notes = null): StockMovement
    public function transferStock(int $fromWarehouseId, int $toWarehouseId, ?int $productId, ?int $variantId, int $quantity, ?string $notes = null): array
    public function reserveStock(int $warehouseId, ?int $productId, ?int $variantId, int $quantity, string $referenceType, int $referenceId, ?CarbonImmutable $expiresAt = null): StockReservation
    public function releaseReservedStock(int $reservationId): void
    public function consumeReservation(int $reservationId): void
    public function allocateForOrder(array $lineItems): AllocationResult
    public function recordDamaged(int $warehouseId, ?int $productId, ?int $variantId, int $quantity, ?string $notes = null): StockMovement
    public function recordExpired(int $warehouseId, ?int $productId, ?int $variantId, int $quantity, ?string $notes = null): StockMovement
    public function getMovementHistory(?int $productId, ?int $variantId, ?int $warehouseId): LengthAwarePaginator
    public function deductForOrder(OrderDTO $order): void
    public function restoreForOrder(OrderDTO $order): void
}
```

### 5.8b StockReservationService (NEW)

```php
class StockReservationService
{
    public function reserve(int $warehouseId, ?int $productId, ?int $variantId, int $quantity, string $referenceType, int $referenceId, ?CarbonImmutable $expiresAt = null): StockReservation
    public function consume(StockReservation $reservation): void
    public function cancel(StockReservation $reservation): void
    public function expireOldReservations(): int
    public function getActiveReservations(?int $productId, ?int $variantId = null): Collection
    public function getAvailableQuantity(int $warehouseId, ?int $productId, ?int $variantId = null): int
    public function cleanupExpiredReservations(): int
}
```

### 5.9 TaxService

```php
class TaxService
{
    public function createTaxCategory(TaxCategoryDTO $dto): TaxCategory
    public function updateTaxCategory(TaxCategory $category, TaxCategoryDTO $dto): TaxCategory
    public function deleteTaxCategory(TaxCategory $category): bool
    public function createTaxRate(TaxRateDTO $dto): TaxRate
    public function updateTaxRate(TaxRate $rate, TaxRateDTO $dto): TaxRate
    public function deleteTaxRate(TaxRate $rate): bool
    public function calculateTaxForProduct(int $priceAmount, Product $product, ?array $location = null): TaxResult
    public function getApplicableRates(?int $taxCategoryId = null, ?array $location = null): Collection
}
```

### 5.10 PricingRuleService

```php
class PricingRuleService
{
    public function createRule(PricingRuleDTO $dto): PricingRule
    public function updateRule(PricingRule $rule, PricingRuleDTO $dto): PricingRule
    public function deleteRule(PricingRule $rule): bool
    public function toggleActive(PricingRule $rule): PricingRule
    public function getActiveRulesForProduct(Product $product, ?Variant $variant = null): Collection
    public function applyRules(int $basePrice, Collection $rules): PriceResult
    public function incrementUsage(PricingRule $rule): void
    public function expireOldRules(): int
}
```

### 5.11 ProductImportService

```php
class ProductImportService
{
    public function __construct(
        protected ProductService $productService,
        protected VariantService $variantService,
    ) {}

    public function validateCSV(string $filePath): ImportValidationResult
    public function importCSV(string $filePath, array $options = []): ImportResult
    public function exportProducts(ProductSearchCriteria $criteria, string $format = 'csv'): string
}
```

---

## 6. Events and Listeners

### 6.1 Domain Events

```php
// Product lifecycle events
ProductCreated          → carries ProductDTO, CarbonImmutable $occurredAt
ProductUpdated          → carries ProductDTO, array $changedAttributes
ProductDeleted          → carries int $productId, string $sku
ProductArchived         → carries ProductDTO
ProductPublished        → carries ProductDTO

// Variant events
VariantCreated          → carries VariantDTO
VariantUpdated          → carries VariantDTO
VariantDeleted          → carries int $variantId, int $productId

// Stock events
StockUpdated            → carries StockMovementDTO, int $previousAvailable, int $newAvailable, ?array $snapshotBefore, ?array $snapshotAfter
StockDepleted           → carries int $productId, ?int $variantId, int $warehouseId
LowStockAlert           → carries int $productId, ?int $variantId, int $warehouseId, int $availableQuantity, int $threshold
StockTransferCompleted  → carries int $productId, ?int $variantId, int $fromWarehouseId, int $toWarehouseId, int $quantity
StockReservationCreated → carries int $reservationId, int $productId, ?int $variantId, int $quantity, string $referenceType, int $referenceId
StockReservationExpired → carries int $reservationId, int $productId, ?int $variantId
```

All events are `readonly` classes with `fromModel()` factory methods.

### 6.2 Event → Listener Registration

Registered in `ProductServiceProvider::boot()`:

```
ProductCreated        → IndexProductForSearch (default queue)
ProductCreated        → GenerateProductSKU (sync)
ProductUpdated        → UpdateProductSearchIndex (default queue)
ProductDeleted        → RemoveProductFromSearchIndex (default queue)
StockUpdated          → UpdateProductStockCache (sync)
StockDepleted         → MarkProductUnavailable (high queue)
StockDepleted         → SendStockDepletedNotification (default queue)
LowStockAlert         → SendLowStockNotification (default queue)
```

### 6.3 Cross-Module Event Listening

Product module listens to events from other modules via `#[AsEventListener]` attribute:

```
OrderCreated (from Orders)    → DeductProductStock listener (high queue)
OrderCancelled (from Orders)  → RestoreProductStock listener (high queue)
```

### 6.4 Listener Details

```
IndexProductForSearch         → queue: default, tries: 3, dispatches IndexProductJob
UpdateProductSearchIndex      → queue: default, tries: 3, dispatches UpdateProductIndexJob
RemoveProductFromSearchIndex  → queue: default, tries: 3, dispatches RemoveProductIndexJob
GenerateProductSKU            → queue: sync (must complete before response)
UpdateProductStockCache       → queue: sync, updates tenant-scoped cache
MarkProductUnavailable        → queue: high, tries: 3, archives product if all stock depleted
SendStockDepletedNotification → queue: default, tries: 3, notifies tenant admins
SendLowStockNotification      → queue: default, tries: 3, notifies when below threshold
ExpireStockReservations       → queue: low, tries: 1, scheduled hourly, expires stale reservations
```

### 6.5 Cross-Module Listener (Idempotent)

```php
class DeductProductStock implements ShouldQueue
{
    public $queue = 'critical';
    public $tries = 5;
    public $backoff = [10, 30, 60, 120, 300];

    public function handle(OrderCreated $event): void
    {
        $idempotencyKey = 'stock-deduct-' . $event->order->orderId;

        if (Cache::has($idempotencyKey)) {
            return; // Already processed
        }

        try {
            $this->stockService->deductForOrder($event->order);
            Cache::put($idempotencyKey, true, 86400);
        } catch (InsufficientStockException $e) {
            $this->events->dispatch(new OrderBackordered($event->order->orderId));
            throw $e;
        }
    }

    public function failed(OrderCreated $event, Throwable $e): void
    {
        Cache::forget('stock-deduct-' . $event->order->orderId);
        Log::error('Stock deduction permanently failed', [
            'order_id' => $event->order->orderId,
            'error' => $e->getMessage(),
        ]);
    }
}

class RestoreProductStock implements ShouldQueue
{
    public $queue = 'critical';
    public $tries = 5;
    public $backoff = [10, 30, 60, 120, 300];

    public function handle(OrderCancelled $event): void
    {
        $idempotencyKey = 'stock-restore-' . $event->order->orderId;

        if (Cache::has($idempotencyKey)) {
            return;
        }

        $this->stockService->restoreForOrder($event->order);
        Cache::put($idempotencyKey, true, 86400);
    }
}
```

---

## 7. Validation Rules

### 7.1 Form Request Classes

```
StoreProductRequest
  name: required, string, max:500
  slug: nullable, string, max:500, alpha_dash, unique:products,slug
  sku: nullable, string, max:100, unique:products,sku, ValidSKU
  barcode: nullable, string, max:100, unique:products,barcode, ValidBarcode
  barcode_type: nullable, enum:ean13,upc,code128,qr
  category_id: nullable, exists:categories,id
  brand_id: nullable, exists:brands,id
  tax_category_id: nullable, exists:tax_categories,id
  description: nullable, string
  short_description: nullable, string, max:500
  type: required, enum:simple,configurable,bundle,virtual
  status: required, enum:draft,active,archived
  base_price: required, integer, min:0
  compare_at_price: nullable, integer, min:0
  cost_price: nullable, integer, min:0
  tax_inclusive: boolean
  track_inventory: boolean
  low_stock_threshold: integer, min:0
  weight: nullable, numeric, min:0, max:999999.99
  dimensions: nullable, array (length, width, height)
  category_ids: nullable, array, exists:categories,id
  attribute_values: nullable, array
  published_at: nullable, date
  metadata: nullable, array

UpdateProductRequest → same as StoreProductRequest with unique rules excluding current ID

StoreVariantRequest
  product_id: required, exists:products,id
  sku: required, string, max:100, unique:variants,sku
  barcode: nullable, string, max:100, unique:variants,barcode, ValidBarcode
  name: required, string, max:500
  price: required, integer, min:0
  compare_at_price: nullable, integer, min:0
  cost_price: nullable, integer, min:0
  track_inventory: boolean
  low_stock_threshold: integer, min:0
  attribute_value_ids: required, array, exists:attribute_values,id
  is_default: boolean
  dimensions: nullable, array

StoreCategoryRequest
  name: required, string, max:255
  slug: nullable, string, max:255, alpha_dash, unique:categories,slug
  parent_id: nullable, exists:categories,id
  description: nullable, string
  is_active: boolean
  sort_order: integer, min:0
  meta_title: nullable, string, max:255
  meta_description: nullable, string, max:500

  withValidator(): checks parent_id != current category's own id
  CircularCategoryException thrown by CategoryService as safety net

StoreBrandRequest
  name: required, string, max:255
  slug: nullable, string, max:255, alpha_dash, unique:brands,slug
  description: nullable, string
  website_url: nullable, url, max:500
  is_active: boolean

StoreAttributeRequest
  name: required, string, max:255
  slug: nullable, string, max:255, alpha_dash, unique:attributes,slug
  frontend_type: required, enum:select,multi_select,text,textarea,color,swatch
  is_filterable: boolean
  is_required: boolean
  is_variant: boolean
  validation_rules: nullable, array

StoreWarehouseRequest
  name: required, string, max:255
  code: required, string, max:50, alpha_dash, unique:warehouses,code
  address_line_1: nullable, string, max:255
  city: nullable, string, max:100
  state: nullable, string, max:100
  postal_code: nullable, string, max:20
  country: nullable, string, max:100
  phone: nullable, string, max:30
  email: nullable, email, max:255
  is_active: boolean
  is_default: boolean

StockAdjustmentRequest
  warehouse_id: required, exists:warehouses,id
  product_id: required_without:variant_id, exists:products,id
  variant_id: required_without:product_id, exists:variants,id
  movement_type: required, enum:received,adjustment,damaged,expired
  quantity: required, integer, min:1
  notes: nullable, string, max:1000

StockTransferRequest
  from_warehouse_id: required, exists:warehouses,id
  to_warehouse_id: required, exists:warehouses,id, Different:from_warehouse_id
  product_id: required_without:variant_id, exists:products,id
  variant_id: required_without:product_id, exists:variants,id
  quantity: required, integer, min:1, lte:available_quantity
  notes: nullable, string, max:1000

StorePricingRuleRequest
  name: required, string, max:255
  type: required, enum:fixed,percentage,tiered
  scope: required, enum:product,category,brand,all
  scope_id: required_if:scope,product,category,brand, integer, exists
  condition_type: nullable, enum:quantity,cart_total,customer_group,date_range
  condition_value: nullable, array (required if condition_type set)
  discount_value: required, integer, min:0
  start_at: nullable, date
  end_at: nullable, date, after:start_at
  is_active: boolean
  priority: integer, min:0
  max_uses: nullable, integer, min:1
```

### 7.2 Custom Validation Rules

```
ValidSKU
  Pattern: /^[A-Za-z0-9\-_]{3,100}$/
  Message: "The :attribute must be 3-100 alphanumeric characters with dashes or underscores."

ValidBarcode
  Validates EAN-13 (13 digits with checksum), UPC (12 digits), Code128, QR
  Message: "The :attribute is not a valid barcode."

DifferentParent
  Enforced via StoreCategoryRequest::withValidator() — checks parent_id != own id
  CategoryService::validateParent() throws CircularCategoryException as safety net
  Message: "A category cannot be its own parent."

StockAvailable
  Validates requested quantity <= available_quantity in warehouse_stock
  Message: "Insufficient stock available in the selected warehouse."
```

---

## 8. API Endpoints

All routes are tenant-scoped (`routes/tenant.php`), behind `auth` + `InitializeTenancyByUser` middleware.

`ProductController` uses the `AuthorizesRequests` trait and enforces authorization via `ProductPolicy` on `store()`, `update()`, and `destroy()`. Other controllers (`CategoryController`, `BrandController`, etc.) should follow the same pattern.

**Critical note — Spatie Permission connection**: Custom `App\Models\Permission` and `App\Models\Role` models extend Spatie's stock models with the `CentralConnection` trait. This ensures all permission/role queries always target the central database, even after `tenancy()->initialize()` switches the default connection to the tenant database. See `config/permission.php` for model bindings.

### 8.1 Products

| Method | Route | Name | Description | Permission |
|--------|-------|------|-------------|------------|
| GET | `/products` | `products.index` | List products (paginated, filterable, sortable) | `products.view` (via policy) |
| GET | `/products/create` | `products.create` | Create product form page | — |
| POST | `/products` | `products.store` | Create a product | `products.create` (via `$this->authorize()`) |
| GET | `/products/{product}` | `products.show` | View product details | `products.view` (via policy) |
| GET | `/products/{product}/edit` | `products.edit` | Edit product form page | — |
| PUT | `/products/{product}` | `products.update` | Update a product | `products.update` (via `$this->authorize()`) |
| DELETE | `/products/{product}` | `products.destroy` | Delete a product | `products.delete` (via `$this->authorize()`) |
| POST | `/products/{product}/archive` | `products.archive` | Archive a product | `products.archive` (via policy) |
| POST | `/products/{product}/restore` | `products.restore` | Restore archived product | — |
| POST | `/products/{product}/publish` | `products.publish` | Publish a draft product | `products.publish` (via policy) |
| POST | `/products/{product}/duplicate` | `products.duplicate` | Duplicate a product | `products.duplicate` (via policy) |
| POST | `/products/import` | `products.import` | Import products from CSV | `products.import` (via policy) |
| GET | `/products/export` | `products.export` | Export products to CSV | `products.export` (via policy) |
| POST | `/products/bulk-action` | `products.bulk` | Bulk actions (delete, archive, update) | — |

### 8.2 Categories

| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/categories` | `categories.index` | List categories (tree structure) |
| POST | `/categories` | `categories.store` | Create a category |
| GET | `/categories/{category}` | `categories.show` | View category details |
| PUT | `/categories/{category}` | `categories.update` | Update a category |
| DELETE | `/categories/{category}` | `categories.destroy` | Delete a category |
| POST | `/categories/reorder` | `categories.reorder` | Reorder categories |

### 8.3 Brands

| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/brands` | `brands.index` | List brands |
| POST | `/brands` | `brands.store` | Create a brand |
| PUT | `/brands/{brand}` | `brands.update` | Update a brand |
| DELETE | `/brands/{brand}` | `brands.destroy` | Delete a brand |

### 8.4 Attributes

| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/attributes` | `attributes.index` | List attributes |
| POST | `/attributes` | `attributes.store` | Create an attribute |
| PUT | `/attributes/{attribute}` | `attributes.update` | Update an attribute |
| DELETE | `/attributes/{attribute}` | `attributes.destroy` | Delete an attribute |
| POST | `/attributes/{attribute}/values` | `attributes.values.store` | Add attribute value |
| PUT | `/attributes/values/{value}` | `attributes.values.update` | Update attribute value |
| DELETE | `/attributes/values/{value}` | `attributes.values.destroy` | Delete attribute value |

### 8.5 Variants

| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/products/{product}/variants` | `products.variants.index` | List product variants |
| POST | `/products/{product}/variants` | `products.variants.store` | Create a variant |
| PUT | `/products/{product}/variants/{variant}` | `products.variants.update` | Update a variant |
| DELETE | `/products/{product}/variants/{variant}` | `products.variants.destroy` | Delete a variant |
| POST | `/products/{product}/variants/generate` | `products.variants.generate` | Generate variants from attribute combinations |
| POST | `/products/{product}/variants/{variant}/default` | `products.variants.default` | Set default variant |

### 8.6 Media

| Method | Route | Name | Description |
|--------|-------|------|-------------|
| POST | `/products/{product}/media` | `products.media.store` | Upload product media |
| PUT | `/products/media/{media}` | `products.media.update` | Update media metadata |
| DELETE | `/products/media/{media}` | `products.media.destroy` | Delete media |
| POST | `/products/{product}/media/{media}/primary` | `products.media.primary` | Set as primary media |
| POST | `/products/{product}/media/reorder` | `products.media.reorder` | Reorder media |

### 8.7 Warehouses

| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/warehouses` | `warehouses.index` | List warehouses |
| POST | `/warehouses` | `warehouses.store` | Create a warehouse |
| PUT | `/warehouses/{warehouse}` | `warehouses.update` | Update a warehouse |
| DELETE | `/warehouses/{warehouse}` | `warehouses.destroy` | Delete a warehouse |
| POST | `/warehouses/{warehouse}/default` | `warehouses.default` | Set as default warehouse |

### 8.8 Stock

| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/products/{product}/stock` | `products.stock.index` | View product stock across warehouses |
| GET | `/products/{product}/variants/{variant}/stock` | `products.variants.stock` | View variant stock |
| POST | `/stock/receive` | `stock.receive` | Receive stock |
| POST | `/stock/deduct` | `stock.deduct` | Deduct stock |
| POST | `/stock/adjust` | `stock.adjust` | Manual stock adjustment |
| POST | `/stock/transfer` | `stock.transfer` | Transfer stock between warehouses |
| POST | `/stock/damaged` | `stock.damaged` | Record damaged stock |
| POST | `/stock/expired` | `stock.expired` | Record expired stock |
| GET | `/stock/movements` | `stock.movements` | Stock movement history |
| GET | `/stock/low-stock` | `stock.low-stock` | Low stock alert list |

### 8.9 Tax

| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/tax/categories` | `tax.categories.index` | List tax categories |
| POST | `/tax/categories` | `tax.categories.store` | Create tax category |
| PUT | `/tax/categories/{category}` | `tax.categories.update` | Update tax category |
| DELETE | `/tax/categories/{category}` | `tax.categories.destroy` | Delete tax category |
| GET | `/tax/categories/{category}/rates` | `tax.rates.index` | List tax rates for category |
| POST | `/tax/rates` | `tax.rates.store` | Create tax rate |
| PUT | `/tax/rates/{rate}` | `tax.rates.update` | Update tax rate |
| DELETE | `/tax/rates/{rate}` | `tax.rates.destroy` | Delete tax rate |

### 8.10 Pricing Rules

| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/pricing-rules` | `pricing-rules.index` | List pricing rules |
| POST | `/pricing-rules` | `pricing-rules.store` | Create pricing rule |
| PUT | `/pricing-rules/{rule}` | `pricing-rules.update` | Update pricing rule |
| DELETE | `/pricing-rules/{rule}` | `pricing-rules.destroy` | Delete pricing rule |
| POST | `/pricing-rules/{rule}/toggle` | `pricing-rules.toggle` | Toggle rule active |

---

## 9. Queue Jobs

```
IndexProductJob
  queue: default, tries: 3, timeout: 30, backoff: [10, 30, 60]
  constructor: (int $productId), stores: $tenantId
  handle(): Sends product data to Meilisearch/Laravel Scout

UpdateProductIndexJob
  queue: default, tries: 3, timeout: 30
  constructor: (int $productId), stores: $tenantId
  handle(): Updates existing search index entry

RemoveProductIndexJob
  queue: default, tries: 3, timeout: 30
  constructor: (int $productId), stores: $tenantId
  handle(): Removes product from search index

ImportProductsJob
  queue: high, tries: 1, timeout: 300
  constructor: (string $filePath, int $userId, array $options)
  stores: $tenantId
  handle(): Processes CSV row by row, uses Job batching for progress tracking

ExportProductsJob
  queue: low, tries: 1, timeout: 300
  constructor: (ProductSearchCriteria $criteria, string $format, int $userId)
  stores: $tenantId
  handle(): Generates CSV/Excel file, notifies user when ready

ReindexAllProductsJob
  queue: low, tries: 1, timeout: 600, ShouldBeUnique
  uniqueId: 'product-reindex-{tenantId}'
  handle(): Full reindex of all active products (batched by 100)

GenerateProductSKUsJob
  queue: default, tries: 3
  constructor: (int $productId), stores: $tenantId
  handle(): Generates SKUs for product and all variants
```

### Job Batching for Imports

```php
$batch = Bus::batch(
    collect($rows)->map(fn($row) => new ImportSingleProductRow($row, $options))
)
->name('product-import-' . $this->tenantId)
->then(fn(Batch $batch) => /* notify complete */)
->catch(fn(Batch $batch, Throwable $e) => /* notify failed */)
->finally(fn(Batch $batch) => /* cleanup temp file */)
->dispatch();
```

---

## 10. Search Indexing Strategy

### 10.1 Package: Laravel Scout + Meilisearch

> **Enterprise decision:** Single shared index with `tenant_id` filter (not per-tenant indexes). Scales to ~5,000 tenants without index management overhead. For 10,000+ tenants, switch to sharded indexes via `crc32(tenant_id) % 16`.

```php
class Product extends Model
{
    use Searchable, HasUlids;

    public function searchableAs(): string
    {
        return 'products';  // Single shared index
    }

    public function toSearchableArray(): array
    {
        return [
            'objectID' => (string) $this->id,
            'tenant_id' => tenancy()->tenant->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'base_price' => $this->base_price,
            'status' => $this->status->value,
            'type' => $this->type->value,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'category_name' => $this->category?->name,
            'brand_name' => $this->brand?->name,
            'attributes' => $this->attributeValues->map(fn($v) => [
                'attribute_id' => $v->attribute_id,
                'attribute_slug' => $v->attribute->slug,
                'value' => $v->text_value ?? $v->attributeValue?->value,
            ])->toArray(),
            'variants' => $this->variants->map(fn($v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'name' => $v->name,
                'price' => $v->price,
                'attribute_values' => $v->attributeValues->pluck('id')->toArray(),
            ])->toArray(),
            'total_stock' => $this->total_available,
            'is_active' => $this->status === ProductStatusEnum::Active,
            'published_at' => $this->published_at?->timestamp,
            'created_at' => $this->created_at->timestamp,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === ProductStatusEnum::Active
            && $this->published_at !== null;
    }
}
```

### 10.2 Searchable Attributes (Meilisearch)

```
searchableAttributes: [name, sku, barcode, short_description, description, category_name, brand_name, variants.sku, variants.name]
filterableAttributes: [tenant_id, status, type, category_id, brand_id, base_price, attributes.attribute_id, attributes.value, variants.attribute_values, total_stock, is_active]
sortableAttributes: [name, base_price, total_stock, created_at, published_at]
```

### 10.3 Tenant Isolation in Search

All queries include tenant filter automatically:

```php
// All searches scoped to current tenant
Product::search('shirt')
    ->where('tenant_id', tenancy()->tenant->id)
    ->get();

// For multi-tenant admin searches (central context)
Product::search('shirt')  // no tenant filter
    ->paginate();
```

### 10.4 Indexing Flow

```
ProductCreated → IndexProductForSearch listener → dispatch IndexProductJob → Scout::makeAllSearchable()
ProductUpdated → UpdateProductSearchIndex listener → dispatch UpdateProductIndexJob → $product->searchable()
ProductDeleted → RemoveProductFromSearchIndex listener → dispatch RemoveProductIndexJob → $product->unsearchable()
```

### 10.5 Sharding Strategy (10,000+ tenants)

```php
public function searchableAs(): string
{
    $shard = crc32(tenancy()->tenant->id) % 16;
    return 'products_shard_' . $shard;
}

// Query automatically routes to correct shard
Product::search('shirt')
    ->where('tenant_id', tenancy()->tenant->id)
    ->get();
```

---

## 11. Suggested Policies/Permissions

### 11.1 Permissions

```
products.view, products.create, products.update, products.delete, products.archive, products.publish, products.duplicate, products.import, products.export, products.bulk-actions
categories.view, categories.create, categories.update, categories.delete
brands.view, brands.create, brands.update, brands.delete
attributes.view, attributes.create, attributes.update, attributes.delete
variants.view, variants.create, variants.update, variants.delete
media.upload, media.update, media.delete
stock.view, stock.receive, stock.deduct, stock.adjust, stock.transfer, stock.view-movements, stock.view-low-stock
warehouses.view, warehouses.create, warehouses.update, warehouses.delete
tax.view, tax.manage
pricing-rules.view, pricing-rules.create, pricing-rules.update, pricing-rules.delete
```

### 11.2 Role Presets

```
Admin → all product permissions
Manager → products.*, categories.*, brands.*, attributes.*, variants.*, media.*, stock.*, warehouses.view, tax.view, pricing-rules.*
Sales → products.view, categories.view, brands.view, stock.view, stock.view-movements, stock.view-low-stock
Warehouse Staff → stock.view, stock.receive, stock.deduct, stock.transfer, stock.view-movements, stock.view-low-stock, warehouses.view, products.view
Viewer → products.view, categories.view, brands.view, stock.view
```

### 11.2b RolePermissionSeeder (Actual Implementation)

Permissions and roles live in the **central database**. The `database/seeders/RolePermissionSeeder.php` creates all product permissions and syncs them to both the `admin` and `tenant` roles:

```php
$permissions = [
    'products.view', 'products.create', 'products.update', 'products.delete',
    'products.archive', 'products.publish', 'products.duplicate',
    'products.import', 'products.export',
];

$admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
$admin->syncPermissions(collect($permissions)->map(fn (string $name) =>
    Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'])
));

$tenantRole = Role::firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
$tenantRole->syncPermissions(collect($permissions)->map(fn (string $name) =>
    Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'])
));
```

The seeder is called from `DatabaseSeeder` and runs before `AdminRoleSeeder`. Only product permissions are currently seeded — all other permission groups (categories, brands, stock, etc.) need to be added when those modules are implemented.

**Role assignments:**
- `admin` role → platform/SaaS administrators (`admin@admin.com`)
- `tenant` role → tenant users (`test@example.com` via `TenantDatabaseSeeder`)

### 11.3 ProductPolicy

```php
class ProductPolicy
{
    public function viewAny(User $user): bool
    public function view(User $user, Product $product): bool
    public function create(User $user): bool
    public function update(User $user, Product $product): bool
    public function delete(User $user, Product $product): bool
    public function archive(User $user, Product $product): bool
    public function publish(User $user, Product $product): bool
    public function duplicate(User $user, Product $product): bool
    public function import(User $user): bool
    public function export(User $user): bool

    // Feature-gated
    public function manageInventory(User $user): bool → tenantHasFeature('inventory_management')
    public function manageWarehouses(User $user): bool → tenantHasFeature('multi_warehouse')
    public function managePricingRules(User $user): bool → tenantHasFeature('advanced_pricing')
}
```

### 11.4 Plan Feature Gates

```json
{
    "starter": {
        "features": ["products", "categories", "brands", "basic_stock"],
        "limits": { "max_products": 100, "max_variants_per_product": 3 }
    },
    "professional": {
        "features": ["products", "categories", "brands", "inventory_management", "multi_warehouse", "barcode", "tax_management", "basic_pricing"],
        "limits": { "max_products": 1000, "max_variants_per_product": 10, "max_warehouses": 3 }
    },
    "enterprise": {
        "features": ["products", "categories", "brands", "inventory_management", "multi_warehouse", "barcode", "tax_management", "advanced_pricing", "product_import", "product_export", "bulk_actions"],
        "limits": { "max_products": -1, "max_variants_per_product": -1, "max_warehouses": -1 }
    }
}
```

---

## 12. Testing Strategy

### 12.1 Test Organization

```
tests/Feature/Product/
├── ProductCrudTest.php
├── CategoryCrudTest.php
├── BrandCrudTest.php
├── AttributeCrudTest.php
├── VariantManagementTest.php
├── MediaManagementTest.php
├── StockManagementTest.php
├── StockReservationTest.php (NEW)
├── WarehouseCrudTest.php
├── TaxManagementTest.php
├── PricingRuleTest.php
├── ProductImportTest.php
├── ProductSearchTest.php
├── BulkActionsTest.php
├── ProductPolicyTest.php
├── CrossModuleIntegrationTest.php
└── StockAuditTrailTest.php (NEW)

tests/Unit/Product/
├── DTOTest.php
├── SKUGeneratorTest.php
├── PricingCalculatorTest.php
├── TaxCalculatorTest.php
├── BarcodeValidationTest.php
├── StockCalculationTest.php
├── ProductEnumTest.php
├── OptimisticLockingTest.php (NEW)
└── MaterializedPathTest.php (NEW)
```

### 12.2 Key Test Cases

#### Product CRUD (Feature)
- authenticated user can create a product
- product requires valid name and price
- product SKU must be unique
- product barcode must be valid format
- user can update product details
- user can archive/duplicate/restore a product
- archived products excluded from search
- unauthenticated user cannot access products
- product creation scoped to current tenant

#### Category Management (Feature)
- user can create nested categories
- category cannot be its own parent
- category cannot have circular parent chain
- deleting a category does not delete its products
- category tree returns correct hierarchy
- materialized path enables efficient descendant queries
- category depth is correctly calculated

#### Variant Management (Feature)
- user can create variants for a configurable product
- variant SKU must be unique across all variants
- generating variants creates all attribute combinations
- deleting a product deletes its variants
- variant inherits product defaults when not overridden

#### Stock Management (Feature)
- user can receive/deduct/adjust stock
- stock deduction creates a movement record
- cannot deduct more stock than available
- user can transfer stock between warehouses
- transfer reduces source and increases destination
- low stock alert fires when quantity falls below threshold
- reserved stock reduces available quantity
- stock movements scoped to tenant
- concurrent stock deductions do not oversell (optimistic locking)
- stock reservation expires after TTL
- stock reservation can be consumed or cancelled
- materialized stock totals on product update correctly

#### Stock Reservations (Feature)
- user can reserve stock for an order
- reservation reduces available quantity
- reservation can be consumed (order confirmed)
- reservation can be cancelled (order cancelled)
- expired reservations are auto-released by scheduled job
- reservation prevents double-allocation of same stock
- available quantity = quantity - reserved_quantity - active_reservations

#### Stock Audit Trail (Feature)
- every stock movement creates an audit log entry
- audit log captures before/after state as JSON
- audit log includes user, IP, user agent
- stock movement snapshots can reconstruct point-in-time state
- audit logs are partitioned by month
- archived audit logs remain queryable

#### SKU Generation (Unit)
- SKU generator produces unique codes
- SKU generator uses product name prefix when configured
- SKU generator handles variant attribute combinations
- SKU generator respects max length
- SKU uniqueness check excludes current product

#### Barcode Validation (Unit)
- EAN-13 barcode passes with valid checksum
- EAN-13 barcode fails with invalid checksum
- UPC barcode validates 12 digits
- Code128 barcode accepts alphanumeric
- QR code accepts any string

#### Pricing Calculator (Unit)
- fixed/percentage/tiered discount rules reduce price correctly
- pricing rules respect date range and max usage
- higher priority rules applied first
- tax calculation returns correct net and gross amounts
- compound tax rates calculated correctly

#### Cross-Module Integration (Feature)
- OrderCreated event triggers stock deduction
- OrderCancelled event triggers stock restoration
- stock depletion triggers LowStockAlert event
- product created event dispatches search indexing job
- tenant isolation prevents cross-tenant data access

#### Multi-Tenant Isolation (Feature)
- tenant A cannot see tenant B products
- product search returns only current tenant results
- stock movements isolated per tenant
- categories isolated per tenant

### 12.3 Factory Definitions

```
ProductFactory → name, slug, type: simple, status: active, base_price, track_inventory: true
  states: draft, active, archived, configurable, bundle

CategoryFactory → name, slug, is_active: true
  states: withParent, inactive

BrandFactory → name, slug, is_active: true

AttributeFactory → name, slug, frontend_type: select, is_variant: true

AttributeValueFactory → value

VariantFactory → sku, name, price
  states: withAttributes

WarehouseFactory → name, code, is_active: true

WarehouseStockFactory → quantity, reserved_quantity: 0

StockMovementFactory → movement_type: received, quantity
  states: sold, return, adjustment, transfer_in, transfer_out, damaged, expired

StockReservationFactory → quantity, reference_type: 'order', expires_at: future
  states: active, consumed, expired, cancelled

AuditLogFactory → entity_type: 'stock_movement', action: 'stock_received'

TaxCategoryFactory → name

TaxRateFactory → name, rate, is_active: true

PricingRuleFactory → name, type: percentage, scope: all, discount_value, is_active: true
```

### 12.4 Multi-Tenant Testing

Use the existing `RefreshMultiDatabase` trait:

```php
uses(RefreshMultiDatabase::class);

test('tenant isolation', function () {
    $tenantA = createTenant();
    tenancy()->initialize($tenantA);
    $productA = Product::factory()->create();
    tenancy()->end();

    $tenantB = createTenant();
    tenancy()->initialize($tenantB);
    expect(Product::find($productA->id))->toBeNull();
    tenancy()->end();
});
```

---

## 13. Migration Order

Tenant migrations must run in this order (foreign key dependencies):

```
1.  create_categories_table (+ materialized_path, depth)
2.  create_brands_table
3.  create_attributes_table
4.  create_attribute_values_table
5.  create_tax_categories_table
6.  create_tax_rates_table
7.  create_products_table (+ ULID, materialized stock totals)
8.  create_category_product_table
9.  create_product_attribute_values_table (no text_value)
10. create_product_attribute_text_values_table (NEW)
11. create_variants_table (+ ULID)
12. create_variant_attribute_values_table
13. create_product_media_table
14. create_warehouses_table
15. create_warehouse_stock_table (+ lock_version, last_movement_at)
16. create_stock_reservations_table (NEW)
17. create_stock_movements_table (+ ULID, snapshots, audit_log_id, partitioning)
18. create_audit_logs_table (NEW, partitioning)
19. create_pricing_rules_table
```

---

## 14. Enums

```
ProductTypeEnum: Simple, Configurable, Bundle, Virtual
ProductStatusEnum: Draft, Active, Archived
AttributeTypeEnum: Select, MultiSelect, Text, Textarea, Color, Swatch
BarcodeTypeEnum: EAN13, UPC, Code128, QR
MovementTypeEnum: Received, Sold, Return, Adjustment, TransferIn, TransferOut, Damaged, Expired
MediaTypeEnum: Image, Video, Document
PricingRuleTypeEnum: Fixed, Percentage, Tiered
PricingRuleScopeEnum: Product, Category, Brand, All
PricingRuleConditionEnum: Quantity, CartTotal, CustomerGroup, DateRange
StockReservationStatusEnum: Active, Consumed, Expired, Cancelled
AuditActionEnum: StockReceived, StockDeducted, StockAdjusted, StockTransferred, StockDamaged, StockExpired, StockReserved, StockReleased
```

---

## 15. Module Service Provider

```php
class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Singletons
        $this->app->singleton(ProductService::class);
        $this->app->singleton(CategoryService::class);
        $this->app->singleton(BrandService::class);
        $this->app->singleton(VariantService::class);
        $this->app->singleton(AttributeService::class);
        $this->app->singleton(MediaService::class);
        $this->app->singleton(WarehouseService::class);
        $this->app->singleton(StockService::class);
        $this->app->singleton(StockReservationService::class);
        $this->app->singleton(TaxService::class);
        $this->app->singleton(PricingRuleService::class);
        $this->app->singleton(ProductImportService::class);

        // Contracts
        $this->app->bind(ProductResolver::class, EloquentProductResolver::class);
        $this->app->bind(StockChecker::class, EloquentStockChecker::class);
        $this->app->bind(PricingCalculator::class, EloquentPricingCalculator::class);
        $this->app->bind(SKUGenerator::class, DefaultSKUGenerator::class);
        $this->app->bind(StockAllocator::class, DefaultStockAllocator::class);
    }

    public function boot(): void
    {
        // Load tenant migrations
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../Modules/Product/Database/Migrations/Tenant');
        }

        // Register policies
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Warehouse::class, WarehousePolicy::class);

        // Register events
        $events = $this->app->make('events');
        $events->listen(ProductCreated::class, IndexProductForSearch::class);
        $events->listen(ProductCreated::class, GenerateProductSKU::class);
        $events->listen(ProductUpdated::class, UpdateProductSearchIndex::class);
        $events->listen(ProductDeleted::class, RemoveProductFromSearchIndex::class);
        $events->listen(StockUpdated::class, UpdateProductStockCache::class);
        $events->listen(StockDepleted::class, MarkProductUnavailable::class);
        $events->listen(StockDepleted::class, SendStockDepletedNotification::class);
        $events->listen(LowStockAlert::class, SendLowStockNotification::class);
        $events->listen(StockReservationCreated::class, TrackStockReservation::class);
        $events->listen(StockReservationExpired::class, ReleaseExpiredStock::class);
    }
}
```

---

## 16. Cross-Module Contracts (for Orders module)

```php
interface ProductCatalogService
{
    public function getProduct(int $id): ?ProductDTO;
    public function getVariant(int $id): ?VariantDTO;
    public function getProductBySku(string $sku): ?ProductDTO;
    public function getProductsByIds(array $ids): Collection;
    public function calculateProductTax(int $productId, ?int $variantId = null, ?array $location = null): TaxResult;
    public function calculateProductPrice(int $productId, ?int $variantId = null, ?int $quantity = null): PriceResult;
}

interface StockAllocator
{
    /**
     * Allocate stock for order line items across warehouses.
     * Strategies: SingleWarehouse, NearestWarehouse, SplitAllocation, FIFO
     */
    public function allocate(array $lineItems): AllocationResult;
}
```

This contract is bound in `ProductServiceProvider` and consumed by the Orders module — the Orders module never imports Product models directly.

---

## 17. Observers

```
ProductObserver
  created → dispatches ProductCreated event, updates category product count
  updated → dispatches ProductUpdated event with changed attributes
  deleted → dispatches ProductDeleted event, cascades to variants + media

VariantObserver
  created → dispatches VariantCreated event
  updated → dispatches VariantUpdated event
  deleted → dispatches VariantDeleted event

WarehouseStockObserver
  updated → checks thresholds, dispatches StockDepleted/LowStockAlert if needed
  updated → updates product's materialized total_quantity/total_reserved via Product::refreshStockTotals()

StockMovementObserver
  created → records audit log entry, dispatches StockUpdated event with snapshots

StockReservationObserver
  created → dispatches StockReservationCreated event
  updated → if status changed to expired, dispatches StockReservationExpired event
```

Register in `ProductServiceProvider::boot()`:
```php
Product::observe(ProductObserver::class);
Variant::observe(VariantObserver::class);
WarehouseStock::observe(WarehouseStockObserver::class);
StockMovement::observe(StockMovementObserver::class);
StockReservation::observe(StockReservationObserver::class);
```

---

## 18. Traits

```
HasProductStock
  getTotalStock(), getAvailableStock(), getReservedStock(), isInStock(), isLowStock(), getWarehouseStock()
  refreshStockTotals() → recalculates materialized totals on product

HasProductMedia
  getPrimaryMedia(), getMedia(), getThumbnailUrl(), hasMedia()

HasBarcode
  generateBarcode(), getBarcodeImageUrl(), validateBarcode()

Sluggable
  generateSlug(), ensureUniqueSlug()

HasOptimisticLocking (NEW)
  Uses lock_version column for concurrent update protection
  Increments lock_version on each save
  Throws StaleModelException if version mismatch during save
  Usage: class WarehouseStock extends Model { use HasOptimisticLocking; }

HasMaterializedPath (NEW)
  Maintains materialized_path and depth on Category model
  rebuildPath() → recalculates path from parent chain
  getDescendants() → WHERE materialized_path LIKE "/{$this->materialized_path}%"
  getChildren() → WHERE parent_id = {$this->id}
```

---

## 19. Exception Classes

```
ProductNotFoundException extends DomainException
VariantNotFoundException extends DomainException
CategoryNotFoundException extends DomainException
BrandNotFoundException extends DomainException
WarehouseNotFoundException extends DomainException
InsufficientStockException extends DomainException
InvalidBarcodeException extends DomainException
DuplicateSKUException extends DomainException
InvalidProductTypeException extends DomainException
CircularCategoryException extends DomainException
PricingRuleExpiredException extends DomainException
StaleModelException extends DomainException (optimistic locking conflict)
StockReservationExpiredException extends DomainException
OrderBackorderedException extends DomainException
```

---

## 20. File Structure Summary

```
app/Modules/Product/
├── Actions/
│   ├── CreateProduct.php
│   ├── UpdateProduct.php
│   ├── DeleteProduct.php
│   ├── ArchiveProduct.php
│   ├── DuplicateProduct.php
│   ├── GenerateVariants.php
│   └── ImportProducts.php
├── Contracts/
│   ├── ProductResolver.php
│   ├── StockChecker.php
│   ├── PricingCalculator.php
│   ├── SKUGenerator.php
│   ├── ProductCatalogService.php
│   └── StockAllocator.php (NEW)
├── Database/Migrations/Tenant/
│   └── (19 migrations)
├── DTOs/
│   ├── CategoryDTO.php
│   ├── BrandDTO.php
│   ├── AttributeDTO.php
│   ├── AttributeValueDTO.php
│   ├── ProductDTO.php
│   ├── VariantDTO.php
│   ├── ProductMediaDTO.php
│   ├── WarehouseDTO.php
│   ├── WarehouseStockDTO.php
│   ├── StockMovementDTO.php
│   ├── StockReservationDTO.php (NEW)
│   ├── TaxCategoryDTO.php
│   ├── TaxRateDTO.php
│   ├── PricingRuleDTO.php
│   ├── ProductSummaryDTO.php
│   ├── ProductWithStockDTO.php
│   ├── SKUGenerationDTO.php
│   └── AllocationResult.php (NEW)
├── Enums/ (11 enums)
├── Events/ (14 events)
├── Exceptions/ (14 exceptions)
├── Http/
│   ├── Controllers/ (10 controllers)
│   └── Requests/ (10 form requests)
├── Jobs/
│   ├── IndexProductJob.php
│   ├── UpdateProductIndexJob.php
│   ├── RemoveProductIndexJob.php
│   ├── ImportProductsJob.php
│   ├── ExportProductsJob.php
│   ├── ReindexAllProductsJob.php
│   ├── GenerateProductSKUsJob.php
│   └── ExpireStockReservationsJob.php (NEW)
├── Listeners/ (12 listeners)
├── Models/ (16 models)
│   ├── Category.php (HasMaterializedPath)
│   ├── Brand.php
│   ├── Attribute.php
│   ├── AttributeValue.php
│   ├── Product.php (HasUlids, Searchable)
│   ├── Variant.php (HasUlids)
│   ├── ProductMedia.php
│   ├── ProductAttributeValue.php
│   ├── ProductAttributeTextValue.php (NEW)
│   ├── Warehouse.php
│   ├── WarehouseStock.php (HasOptimisticLocking)
│   ├── StockMovement.php (HasUlids)
│   ├── StockReservation.php (NEW)
│   ├── TaxCategory.php
│   ├── TaxRate.php
│   ├── PricingRule.php
│   └── AuditLog.php (NEW, HasUlids)
├── Observers/ (5 observers)
├── Policies/ (4 policies)
├── Rules/ (4 custom rules)
├── Services/ (12 services)
│   └── StockReservationService.php (NEW)
├── Tests/
│   ├── Feature/ (17 test files)
│   └── Unit/ (9 test files)
├── Traits/ (6 traits)
└── ValueObjects/ (4 value objects)
```

---

## 21. Enterprise Improvements Summary

### Applied Changes

| Area | Change | Impact |
|------|--------|--------|
| **IDs** | ULID for products, variants, stock_movements | Merge-safe, distributed-safe, time-sortable |
| **Search** | Shared Meilisearch index + tenant_id filter | Scales to ~5,000 tenants (sharding for 10,000+) |
| **Inventory** | Optimistic locking (lock_version) on warehouse_stock | Prevents overselling under concurrent load |
| **Inventory** | Stock reservations table with TTL expiry | Prevents abandoned cart stock holds |
| **Inventory** | Materialized stock totals on products table | Eliminates aggregation on every listing query |
| **Inventory** | Atomic stock deduction with SELECT FOR UPDATE | Race-condition-free order processing |
| **Inventory** | StockAllocator strategy interface | Multi-warehouse order fulfillment |
| **Categories** | Materialized path + depth | Eliminates recursive tree queries (N+1) |
| **Pivot** | text_value moved to separate table | Prevents row overflow on hot pivot |
| **Audit** | Stock-only audit_logs with JSON before/after | Compliance, debugging, point-in-time reconstruction |
| **Audit** | snapshot_before/snapshot_after on stock_movements | Full stock state history per movement |
| **Performance** | 12 new composite covering indexes | 3-10x faster common queries |
| **Partitioning** | stock_movements + audit_logs by month | Keeps hot data small, enables archival |
| **Order Scaling** | Idempotent stock deduction with idempotency key | Prevents double-deduction on retries |
| **Order Scaling** | Saga pattern with compensation actions | Handles partial failures gracefully |
| **Events** | StockUpdated carries before/after snapshots | Rich audit trail for stock changes |
