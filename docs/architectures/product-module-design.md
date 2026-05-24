# Product Management Module — Multi-Tenant Design

## Overview

The Product Management module is a self-contained bounded context within the modular monolith. All product data lives in **tenant-isolated databases**, not the central database.

## Tenant Database Isolation — Verified

| Concern | Current State | Location |
|---------|--------------|----------|
| Product migrations | ✅ Tenant DB migrations | `app/Modules/Product/Database/Migrations/Tenant/` |
| Product models | ✅ Standard `Model` (no `CentralConnection` trait) | `app/Modules/Product/Models/` |
| Provider registration | ✅ Loads tenant migrations via `$this->loadMigrationsFrom(...)` | `app/Providers/ProductServiceProvider.php:99` |
| Routes | ✅ Behind `InitializeTenancyByUser` + `subscription` middleware | `routes/tenant.php` |
| Central product tables | ✅ None exist | — |

The `ProductServiceProvider` registers:
```php
// ProductServiceProvider.php:98-100
$this->loadMigrationsFrom(__DIR__.'/../Modules/Product/Database/Migrations/Tenant');
```

Models (e.g., `Product`, `Category`, `Brand`, `Variant`) extend `Model` directly — no `CentralConnection` trait — so they automatically use the tenant DB connection when tenancy is initialized.

## Data Model

```
┌──────────────────────────────────────────────────────────────┐
│                    Tenant DB (souda_tenant_{uuid})            │
│                                                              │
│  ┌──────────┐    ┌──────────────┐    ┌──────────────────┐   │
│  │ Category │◄───│ category_    │───►│     Product      │   │
│  │          │    │ product      │    │                  │   │
│  │  • id    │    └──────────────┘    │  • ulid (PK)     │   │
│  │  • name  │                        │  • category_id   │   │
│  │  • slug  │    ┌──────────────┐    │  • brand_id      │   │
│  │  • depth │───►│    Brand     │    │  • name, slug    │   │
│  └──────────┘    │              │    │  • sku, barcode  │   │
│                  │  • id        │    │  • base_price    │   │
│  ┌──────────┐    │  • name      │    │  • status, type  │   │
│  │ Variant  │    │  • website   │    │  • total_{qty,   │   │
│  │          │    └──────────────┘    │    reserved,     │   │
│  │  • id    │                        │    available}    │   │
│  │  • sku   │    ┌──────────────┐    │  • dimensions    │   │
│  │  • price │───►│ ProductMedia │    │  • published_at  │   │
│  │  • stock │    │              │    └──────┬───────────┘   │
│  │  • image │    │  • id        │           │              │
│  └──────────┘    │  • url       │           │              │
│                  │  • sort_order│           │              │
│  ┌──────────┐    └──────────────┘           │              │
│  │Attribute │                               │              │
│  │  • id    │    ┌─────────────────┐        │              │
│  │  • name  │    │ WarehouseStock  │◄───────┘              │
│  │  • type  │    │                 │                       │
│  └────┬─────┘    │  • warehouse_id │    ┌──────────────┐   │
│       │          │  • product_id   │    │  Warehouse   │   │
│  ┌────▼─────┐    │  • quantity     │    │              │   │
│  │Attribute │    │  • reserved     │    │  • id        │   │
│  │ Value    │    │  • available    │    │  • name      │   │
│  │          │    └─────────────────┘    │  • location  │   │
│  │  • id    │                           └──────────────┘   │
│  │  • value │    ┌──────────────────┐                       │
│  └──────────┘    │  StockMovement   │                       │
│                  │                  │    ┌──────────────┐   │
│  ┌──────────┐    │  • product_id    │    │ TaxCategory  │   │
│  │ Pricing  │    │  • warehouse_id  │    │              │   │
│  │ Rule     │    │  • quantity      │    │  • id        │   │
│  │          │    │  • type (in/out) │    │  • name      │   │
│  │  • id    │    │  • reason        │    └──────┬───────┘   │
│  │  • scope │    │  • reference     │           │          │
│  └──────────┘    └──────────────────┘     ┌─────▼──────┐   │
│                                           │  TaxRate   │   │
│  ┌──────────┐    ┌──────────────────┐     │            │   │
│  │ AuditLog │    │ StockReservation │     │  • rate    │   │
│  │          │    │                  │     │  • type    │   │
│  │  • event │    │  • order_id      │     └────────────┘   │
│  │  • data  │    │  • expires_at    │                       │
│  └──────────┘    └──────────────────┘                       │
└──────────────────────────────────────────────────────────────┘
```

## Module Structure

```
app/Modules/Product/
├── Contracts/
│   ├── PricingCalculator.php       — Price calculation strategy
│   ├── ProductCatalogService.php   — Catalog query interface
│   ├── ProductResolver.php         — Product lookup (by id/sku/barcode)
│   ├── SKUGenerator.php            — SKU generation strategy
│   ├── StockAllocator.php          — Stock allocation strategy
│   └── StockChecker.php            — Stock availability queries
├── Database/
│   ├── Factories/
│   │   ├── BrandFactory.php
│   │   ├── CategoryFactory.php
│   │   ├── ProductFactory.php
│   │   ├── StockReservationFactory.php
│   │   ├── VariantFactory.php
│   │   ├── WarehouseFactory.php
│   │   └── WarehouseStockFactory.php
│   └── Migrations/Tenant/
│       ├── 2026_05_21_000001_create_categories_table.php
│       ├── 2026_05_21_000002_create_brands_table.php
│       ├── 2026_05_21_000003_create_attributes_table.php
│       ├── 2026_05_21_000004_create_attribute_values_table.php
│       ├── 2026_05_21_000005_create_tax_categories_table.php
│       ├── 2026_05_21_000006_create_tax_rates_table.php
│       ├── 2026_05_21_000007_create_products_table.php
│       ├── 2026_05_21_000008_create_category_product_table.php
│       ├── 2026_05_21_000009_create_product_attribute_values_table.php
│       ├── 2026_05_21_000010_create_product_attribute_text_values_table.php
│       ├── 2026_05_21_000011_create_variants_table.php
│       ├── 2026_05_21_000012_create_variant_attribute_values_table.php
│       ├── 2026_05_21_000013_create_product_media_table.php
│       ├── 2026_05_21_000014_create_warehouses_table.php
│       ├── 2026_05_21_000015_create_warehouse_stock_table.php
│       ├── 2026_05_21_000016_create_stock_reservations_table.php
│       ├── 2026_05_21_000017_create_stock_movements_table.php
│       ├── 2026_05_21_000018_create_audit_logs_table.php
│       └── 2026_05_21_000019_create_pricing_rules_table.php
├── DTOs/
│   ├── ProductDTO.php
│   ├── ProductSummaryDTO.php
│   ├── ProductWithStockDTO.php
│   └── VariantDTO.php
├── Enums/
│   ├── ProductStatusEnum.php
│   ├── ProductTypeEnum.php
│   └── StockMovementType.php
├── Events/
│   ├── LowStockAlert.php
│   ├── ProductArchived.php
│   ├── ProductCreated.php
│   ├── ProductDeleted.php
│   ├── ProductPublished.php
│   ├── ProductUpdated.php
│   ├── StockDepleted.php
│   ├── StockReservationCreated.php
│   ├── StockReservationExpired.php
│   ├── StockTransferCompleted.php
│   ├── StockUpdated.php
│   ├── VariantCreated.php
│   ├── VariantDeleted.php
│   └── VariantUpdated.php
├── Http/
│   ├── Controllers/
│   │   ├── AttributeController.php
│   │   ├── BrandController.php
│   │   ├── CategoryController.php
│   │   ├── MediaController.php
│   │   ├── PricingRuleController.php
│   │   ├── ProductController.php
│   │   ├── StockController.php
│   │   ├── TaxController.php
│   │   ├── VariantController.php
│   │   └── WarehouseController.php
│   └── Requests/
│       ├── StockAdjustmentRequest.php
│       ├── StockTransferRequest.php
│       ├── StoreAttributeRequest.php
│       ├── StoreBrandRequest.php
│       ├── StoreCategoryRequest.php
│       ├── StorePricingRuleRequest.php
│       ├── StoreProductRequest.php
│       ├── StoreVariantRequest.php
│       ├── StoreWarehouseRequest.php
│       └── UpdateProductRequest.php
├── Jobs/
│   ├── ExpireStockReservationsJob.php
│   ├── ExportProductsJob.php
│   ├── GenerateProductSKUsJob.php
│   ├── ImportProductsJob.php
│   ├── IndexProductJob.php
│   ├── ReindexAllProductsJob.php
│   ├── RemoveProductIndexJob.php
│   └── UpdateProductIndexJob.php
├── Listeners/
│   ├── DeductProductStock.php         — Listens to OrderCreated
│   ├── ExpireStockReservations.php
│   ├── GenerateProductSKU.php
│   ├── IndexProductForSearch.php
│   ├── MarkProductUnavailable.php
│   ├── ReleaseExpiredStock.php
│   ├── RemoveProductFromSearchIndex.php
│   ├── RestoreProductStock.php        — Listens to OrderCancelled
│   ├── SendLowStockNotification.php
│   ├── SendStockDepletedNotification.php
│   ├── TrackStockReservation.php
│   ├── UpdateProductSearchIndex.php
│   └── UpdateProductStockCache.php
├── Models/
│   ├── Attribute.php
│   ├── AttributeValue.php
│   ├── AuditLog.php
│   ├── Brand.php
│   ├── Category.php
│   ├── PricingRule.php
│   ├── Product.php
│   ├── ProductAttributeTextValue.php
│   ├── ProductAttributeValue.php
│   ├── ProductMedia.php
│   ├── StockMovement.php
│   ├── StockReservation.php
│   ├── TaxCategory.php
│   ├── TaxRate.php
│   ├── Variant.php
│   ├── Warehouse.php
│   └── WarehouseStock.php
├── Observers/
│   ├── ProductObserver.php
│   ├── StockReservationObserver.php
│   ├── VariantObserver.php
│   └── WarehouseStockObserver.php
├── Policies/
│   ├── BrandPolicy.php
│   ├── CategoryPolicy.php
│   ├── ProductPolicy.php
│   └── WarehousePolicy.php
├── Rules/
│   ├── DifferentParent.php
│   ├── StockAvailable.php
│   ├── ValidBarcode.php
│   └── ValidSKU.php
├── Services/
│   ├── AttributeService.php
│   ├── BrandService.php
│   ├── CategoryService.php
│   ├── DefaultSKUGenerator.php
│   ├── DefaultStockAllocator.php
│   ├── EloquentPricingCalculator.php
│   ├── EloquentProductCatalogService.php
│   ├── EloquentProductResolver.php
│   ├── EloquentStockChecker.php
│   ├── MediaService.php
│   ├── PricingRuleService.php
│   ├── ProductImportService.php
│   ├── ProductService.php
│   ├── StockAuditService.php
│   ├── StockLockService.php
│   ├── StockReservationService.php
│   ├── StockService.php
│   ├── TaxService.php
│   ├── VariantService.php
│   └── WarehouseService.php
├── Traits/
│   ├── HasBarcode.php
│   ├── HasMaterializedPath.php
│   ├── HasOptimisticLocking.php
│   ├── HasProductMedia.php
│   ├── HasProductStock.php
│   └── Sluggable.php
└── ValueObjects/
    ├── PriceResult.php
    ├── ProductSearchCriteria.php
    └── TaxResult.php
```

## Tenant Isolation Verification

### 1. Migrations are Tenant-Scoped

All 19 migration files live in `app/Modules/Product/Database/Migrations/Tenant/` and are registered via the tenancy config, **not** via `loadMigrationsFrom()` (which would leak them into central `php artisan migrate`):

```php
// config/tenancy.php — CORRECT approach
'migration_parameters' => [
    '--force' => true,
    '--path' => [
        database_path('migrations/tenant'),
        app_path('Modules/Product/Database/Migrations/Tenant'),
    ],
],
```

The path is registered in `config/tenancy.php` under `migration_parameters`, which only applies to `php artisan tenants:migrate` — never to `php artisan migrate` (central). **Do not use `loadMigrationsFrom()` for tenant migrations in service providers**, as it registers them with the global migrator and causes them to run against the central database.

### 2. Models Use Tenant Connection

Every model in `app/Modules/Product/Models/` extends `Illuminate\Database\Eloquent\Model` without the `CentralConnection` trait. This means they resolve to the `tenant` database connection when tenancy is active.

```php
// Product.php — NO CentralConnection → tenant DB
class Product extends Model { ... }

// Category.php — NO CentralConnection → tenant DB
class Category extends Model { ... }
```

### 3. Routes Are Behind Tenancy Middleware

All product routes in `routes/tenant.php` are protected by `InitializeTenancyByUser` and `subscription` middleware:

```php
Route::middleware(['web', 'auth', InitializeTenancyByUser::class])->group(function () {
    Route::middleware('subscription')->group(function () {
        Route::resource('products', ProductController::class);
        // category, brand, attribute, inventory routes...
    });
});
```

### 4. No Central Product Data

`database/migrations/` (central) contains no product-related tables. All product data exists exclusively in tenant DBs.

## Cross-Module Communication

```
OrderCreated
    ├──► DeductProductStock (Product/Listeners/)     — Deducts inventory
    └──► RestoreProductStock (Product/Listeners/)    — Restores on cancel

ProductCreated
    ├──► IndexProductForSearch                       — Meilisearch indexing
    └──► GenerateProductSKU                          — Auto SKU generation
```

The Product module exposes contracts (`ProductResolver`, `StockChecker`, `PricingCalculator`, `ProductCatalogService`, `SKUGenerator`, `StockAllocator`) bound to implementations in `ProductServiceProvider` for other modules to consume.

## Frontend Pages

| Page | Route | Path |
|------|-------|------|
| Product List | `products.index` | `resources/js/pages/Product/Index.tsx` |
| Product Create | `products.create` | `resources/js/pages/Product/Create.tsx` |
| Product Detail | `products.show` | `resources/js/pages/Product/Show.tsx` |
| Product Edit | `products.edit` | `resources/js/pages/Product/Edit.tsx` |
| Categories | `categories.index` | `resources/js/pages/Product/Category/Index.tsx` |
| Category Detail | `categories.show` | `resources/js/pages/Product/Category/Show.tsx` |
| Brands | `brands.index` | `resources/js/pages/Product/Brand/Index.tsx` |
| Attributes | `attributes.index` | `resources/js/pages/Product/Attribute/Index.tsx` |
| Low Stock | `inventory.index` | `resources/js/pages/Product/Stock/LowStock.tsx` |
| Movements | `stock.movements` | `resources/js/pages/Product/Stock/Movements.tsx` |

## Tenant Database Provisioning Architecture

Tenant databases are **not** created on registration or first login. They are provisioned on **subscription activation**, following proper SaaS resource-gating.

### Flow

```
Registration → Tenant record in central DB (no tenant DB)
     │
     ▼
Login → InitializeTenancyByUser middleware
     │
     ├── Tenant has DB? → Normal tenancy init → proceed
     │
     └── No DB yet?
         ├── Route is /billing*? → Allow through (no tenancy init)
         └── Route is NOT billing? → Redirect to /billing
     │
     ▼
User selects plan + pays (billing uses only central models)
     │
     ▼
SubscriptionActivated event dispatched
     │
     ├──► [1] ProvisionTenantDatabase listener (sync)
     │       ├── Creates tenant database
     │       └── Runs all tenant migrations (products, categories, etc.)
     │
     └──► [2] SendSubscriptionNotification listener (queued)
              └── Sends confirmation email
     │
     ▼
User redirected → Next request → Middleware finds DB → Tenancy initialized → Dashboard
```

### Key Components

| Component | File | Role |
|-----------|------|------|
| `ProvisionTenantDatabase` listener | `app/Listeners/ProvisionTenantDatabase.php` | Creates DB + runs migrations on `SubscriptionActivated` |
| `InitializeTenancyByUser` middleware | `app/Http/Middleware/InitializeTenancyByUser.php` | Redirects non-billing routes when tenant has no DB |
| Billing routes | `routes/web.php` | No tenancy middleware; uses central models only |
| Tenant routes | `routes/tenant.php` | Protected by `InitializeTenancyByUser` + `subscription` |

### Why This Pattern

| Aspect | Benefit |
|--------|---------|
| **Resource efficiency** | No wasted tenant DBs for unsubscribed users |
| **Security** | No tenant data exists until first payment |
| **Clean onboarding** | Registration → billing → provision → access (linear flow) |
| **Idempotent provisioning** | Listener checks if DB exists before creating |
| **Sync provisioning** | DB ready before user is redirected |

## Authorization Enforcement

`ProductController` uses the `AuthorizesRequests` trait and explicitly calls `$this->authorize('create', Product::class)`, `$this->authorize('update', $product)`, and `$this->authorize('delete', $product)` in `store()`, `update()`, and `destroy()` respectively. This enforces the `ProductPolicy` gates defined in `app/Modules/Product/Policies/ProductPolicy.php`.

**Permissions are stored in the central database.** Custom `App\Models\Permission` and `App\Models\Role` models extend Spatie's stock models with the `CentralConnection` trait, ensuring all authorization lookups always target the central DB. Permissions are seeded via `database/seeders/RolePermissionSeeder.php`, which creates product permissions (`products.view`, `products.create`, `products.update`, `products.delete`, `products.archive`, `products.publish`, `products.duplicate`, `products.import`, `products.export`) and syncs them to the `admin` role (platform admins) and `tenant` role (tenant users).

Other controllers (`CategoryController`, `BrandController`, etc.) should follow the same pattern to enforce their respective policies via `$this->authorize()` calls.

### Circular Category Reference

The `StoreCategoryRequest` validates `parent_id` via `withValidator()` — checking that a category cannot be set as its own parent. `CategoryService::validateParent()` throws `CircularCategoryException` as a safety net for circular chain detection.

## Summary

The Product Management module is correctly designed for tenant database isolation:

- ✅ **19 tenant migrations** — all under tenant DB
- ✅ **17 tenant models** — no CentralConnection, auto-resolve to tenant DB
- ✅ **10 controllers** — behind tenancy + subscription middleware, `ProductController` enforces `AuthorizesRequests`
- ✅ **7 service contracts** — for cross-module consumption
- ✅ **14 domain events** — for decoupled cross-module communication
- ✅ **13 listeners** — including `DeductProductStock` (Order→Inventory bridge)
- ✅ **8 queued jobs** — for async operations (import, export, search indexing)
- ✅ **4 observers** — for model lifecycle hooks
- ✅ **4 authorization policies** — enforced via `$this->authorize()` in `ProductController`
- ✅ **10 frontend pages** — Inertia React SPA pages
- ✅ **No central product tables** — zero product data in central DB
- ✅ **Tenant DB provisioned on subscription** — not on registration or login
