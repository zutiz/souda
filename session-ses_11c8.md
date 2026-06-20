# New session - 2026-06-20T05:19:25.466Z

**Session ID:** ses_11c87cee5ffeG0wQoY77d9TYmR
**Created:** 6/20/2026, 11:19:25 AM
**Updated:** 6/20/2026, 11:25:16 AM

---

## User

You are a Principal SaaS Architect and Senior Laravel Domain Designer.
Project:
 SOUDA - Multi-Tenant Commerce Operating System for SMEs.
Technology Stack:
* Laravel 13
* PHP 8.3
* React + Inertia
* TypeScript
* MySQL
* Redis
* Tenancy for Laravel
* Modular Monolith Architecture
* Domain Driven Design
Current Situation:
The Product module is already implemented and supports:
* Products
* Categories
* Brands
* Variants
* Inventory
* Warehouses
* Stock Movements
New Requirement:
SOUDA will support multiple business verticals using the same platform.
Initial target industries:
* Grocery
* Pharmacy
* Restaurant
* Salon
* Electronics
* Fashion
* Hardware
* Cosmetics
* Wholesale
Problem:
Different industries require different product fields and workflows.
Examples:
Pharmacy:
* Generic Name
* Strength
* Dosage Form
* Manufacturer
* Batch Number
* Expiry Date
* Prescription Required
Electronics:
* Warranty Period
* IMEI Tracking
* Serial Tracking
* Voltage
Fashion:
* Size
* Color
* Material
* Season
Restaurant:
* Preparation Time
* Recipe Association
* Calories
Salon:
* Service Duration
* Staff Commission
* Service Category
Goal:
Redesign the Product Domain so that:
1. The core product model remains stable.
2. New industries can be added without database redesign.
3. New fields can be configured dynamically.
4. New workflows can be added as extension packs.
5. Multi-tenant isolation remains intact.
6. Future POS, CRM, Billing, Inventory, and Reporting modules remain compatible.
7. Avoid EAV anti-patterns where possible.
8. Optimize for long-term maintainability.
Required Architecture:
Design a system using:
Core Commerce Engine + Dynamic Field Engine + Industry Packs
Expected Structure:
Core Product Module
* Products
* Categories
* Brands
* Variants
* Inventory
Dynamic Schema Engine
* Business Types
* Dynamic Fields
* Dynamic Forms
* Dynamic Validation Rules
* Dynamic UI Metadata
Industry Packs
* Pharmacy Pack
* Restaurant Pack
* Salon Pack
* Electronics Pack
* Fashion Pack
* Grocery Pack
Important Rules:
Do NOT create:
* pharmacy_products table
* restaurant_products table
* salon_products table
Do NOT duplicate product modules.
Use:
* Stable core product schema
* Industry-specific extension mechanism
* Metadata-driven forms
* Metadata-driven validation
* Extensible domain architecture
Generate:
1. Proposed domain architecture
2. Bounded contexts
3. Database schema
4. Migration strategy from existing system
5. Eloquent model architecture
6. Service architecture
7. Dynamic field engine design
8. Validation engine design
9. Form generation architecture
10. Industry pack architecture
11. Inventory integration strategy
12. Reporting integration strategy
13. Search strategy
14. Permission strategy
15. Multi-store compatibility strategy
16. Future POS compatibility strategy
17. API design
18. Frontend architecture
19. Performance considerations
20. Risks and tradeoffs
Before proposing implementation:
* Identify weaknesses in the current product architecture
* Identify scalability limitations
* Identify future SaaS risks
* Identify multi-industry challenges
Then propose the best enterprise-grade solution.

Read the existing code properly and plan the architecture 

Do NOT generate code yet.
Focus on architecture, migration strategy, and long-term extensibility. 

---

## Assistant (Plan · DeepSeek V4 Flash Free · 196.1s)

_Thinking:_

The user wants me to analyze their existing SOUDA - Multi-Tenant Commerce Operating System codebase and design a comprehensive architecture for supporting multiple business verticals (industries) with dynamic fields, industry packs, and extensible domain architecture. 

Let me start by exploring the existing codebase to understand the current product module architecture, then design the migration strategy and new architecture.

Let me first explore the project structure to understand what exists.

**Tool: task**

**Input:**
```json
{
  "description": "Explore codebase structure",
  "prompt": "Thoroughly explore the codebase at /Users/salam/Herd/souda. I need to understand:\n\n1. The overall project structure (directories, modules, etc.)\n2. The existing Product module - all models, migrations, controllers, services, routes, form requests, etc.\n3. The existing multi-tenancy setup (Tenancy for Laravel)\n4. The database schema for products, categories, brands, variants, inventory, warehouses, stock movements\n5. Any existing industry/vertical/business type related code\n6. The frontend structure (React + Inertia pages for product management)\n7. Existing tests for the product domain\n8. Configuration files related to tenancy\n\nPlease read ALL relevant files completely - models, migrations, controllers, services, repositories, form requests, routes, tests, and frontend components. I need full file contents for the product domain.\n\nReturn:\n- Full directory tree (top 3 levels)\n- All model files with complete content\n- All migration files with complete content  \n- All controller files with complete content\n- All form request files with complete content\n- All service files with complete content\n- All route files\n- All frontend page components related to products\n- Test files\n- Tenancy configuration\n- Any existing business type/industry related files\n- The structure of resources/js directory",
  "subagent_type": "explore"
}
```

**Output:**
```
task_id: ses_11c87ae9bffeHjBN08UblVbOEJ (for resuming to continue this task if needed)

<task_result>
Now I have a thorough understanding of the entire codebase. Here is a comprehensive report:

---

## COMPLETE CODEBASE ANALYSIS REPORT

### 1. DIRECTORY TREE (Top 3 Levels)

```
/Users/salam/Herd/souda/
├── app/
│   ├── Actions/
│   │   ├── Auth/
│   │   └── Fortify/
│   ├── Concerns/
│   ├── Console/Commands/Tenant/
│   ├── Events/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   ├── Auth/
│   │   │   └── Settings/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Responses/
│   ├── Jobs/
│   ├── Listeners/
│   ├── Mail/
│   ├── Models/
│   ├── Modules/
│   │   ├── Billing/
│   │   ├── CRM/
│   │   │   ├── Actions/, DTOs/, Enums/, Events/, Exceptions/, Http/, Jobs/, Listeners/, Models/, Policies/, Repositories/, Rules/, Services/, Tests/, Traits/
│   │   ├── Inventory/
│   │   │   ├── Actions/, DTOs/, Enums/, Events/, Exceptions/, Http/, Jobs/, Listeners/, Models/, Policies/, Repositories/, Rules/, Services/, Tests/, Traits/
│   │   ├── Order/
│   │   ├── Product/
│   │   │   ├── Actions/ (empty)
│   │   │   ├── Contracts/
│   │   │   ├── Database/
│   │   │   │   ├── Factories/
│   │   │   │   └── Migrations/Tenant/ (19 migration files)
│   │   │   ├── DTOs/ (18 files)
│   │   │   ├── Enums/ (11 files)
│   │   │   ├── Events/ (14 files)
│   │   │   ├── Exceptions/ (14 files)
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/ (10 files)
│   │   │   │   └── Requests/ (10 files)
│   │   │   ├── Jobs/ (8 files)
│   │   │   ├── Listeners/ (13 files)
│   │   │   ├── Models/ (17 files)
│   │   │   ├── Observers/ (4 files)
│   │   │   ├── Policies/ (4 files)
│   │   │   ├── Repositories/
│   │   │   ├── Rules/ (4 files)
│   │   │   ├── Services/ (20 files)
│   │   │   ├── Tests/ (empty)
│   │   │   ├── Traits/
│   │   │   └── ValueObjects/ (3 files)
│   │   └── Shared/
│   │       ├── Contracts/, DTOs/, Enums/, Events/, Exceptions/, Http/, Jobs/, Listeners/, Models/, Policies/, Repositories/, Rules/, Services/, Tests/, Traits/
│   ├── Providers/
│   ├── Services/
│   └── Tenancy/
│       ├── Contracts/
│       ├── Exceptions/
│       ├── Models/Concerns/
│       ├── Modes/
│       ├── Scopes/
│       └── TenantManager.php
├── bootstrap/
│   ├── app.php
│   └── providers.php
├── config/
│   ├── tenancy.php
│   └── database.php
├── database/
│   └── migrations/
│       ├── shared/
│       │   ├── 2026_06_05_000001_create_shared_tenant_tables.php
│       │   └── 2026_06_06_000001_create_shared_product_tables.php
│       └── tenant/
│           ├── 2026_02_22_094519_create_tasks_table.php
│           └── 2026_05_19_000001_create_tenant_settings_table.php
├── resources/js/
│   ├── actions/
│   │   ├── App/, Illuminate/, Laravel/
│   ├── components/
│   ├── hooks/
│   ├── layouts/
│   │   └── app-layout.tsx
│   ├── lib/
│   ├── modules/
│   │   ├── product/
│   │   │   ├── components/ (14 files)
│   │   │   ├── hooks/ (4 files)
│   │   │   ├── lib/ (3 files)
│   │   │   ├── pages/ (4 files)
│   │   │   └── types/ (5 files)
│   │   └── shared/
│   │       ├── components/ (14 files)
│   │       ├── hooks/ (3 files)
│   │       ├── lib/ (4 files)
│   │       ├── providers/
│   │       └── types/
│   ├── pages/
│   │   ├── Product/
│   │   │   ├── Index.tsx, Create.tsx, Edit.tsx, Show.tsx
│   │   │   ├── Category/ (Index.tsx, Show.tsx)
│   │   │   ├── Brand/Index.tsx
│   │   │   ├── Attribute/Index.tsx
│   │   │   └── Stock/ (Movements.tsx, LowStock.tsx)
│   │   ├── admin/
│   │   ├── auth/
│   │   ├── billing/
│   │   ├── settings/
│   │   ├── tasks/
│   │   ├── team/
│   │   ├── dashboard.tsx
│   │   └── welcome.tsx
│   ├── routes/
│   │   ├── products/index.ts
│   │   ├── brands/, categories/, attributes/, inventory/, stock/, stock-transfers/, etc.
│   └── wayfinder/
│       └── index.ts
├── routes/
│   ├── web.php (public + billing routes)
│   ├── admin.php (admin routes)
│   ├── settings.php (user settings)
│   ├── tenant.php (tenant-scoped routes - products, categories, etc.)
│   └── console.php
└── tests/
    ├── Feature/
    │   ├── Product/
    │   │   ├── ProductCrudTest.php
    │   │   ├── ProductFormTest.php
    │   │   ├── CategoryCrudTest.php
    │   │   └── StockManagementTest.php
    │   ├── Auth/, Admin/, Billing/, Order/, Settings/, Tenant/
    └── Unit/
        ├── Product/
        │   ├── StockCalculationTest.php
        │   └── DTOTest.php
        ├── CRM/, Inventory/, Order/, Shared/
```

---

### 2. PRODUCT MODULE - FULL DETAILS

#### 2.1 Models (17 total, all in `app/Modules/Product/Models/`)

| Model | Table | Key Fields | Tenant Scope |
|-------|-------|-----------|--------------|
| **Product** | `products` (ULID PK) | name, slug, sku, barcode, type, status, base_price, total_quantity, total_reserved, total_available (virtual), metadata, published_at | Yes |
| **Category** | `categories` (auto-increment PK) | name, slug, parent_id, materialized_path, depth, is_active, sort_order | Yes |
| **Brand** | `brands` (auto-increment PK) | name, slug, description, logo_path, website_url, is_active | Yes |
| **Variant** | `variants` (ULID PK) | product_id, sku, barcode, name, price, is_default | Yes |
| **Attribute** | `attributes` (auto-increment PK) | name, slug, frontend_type, is_filterable, is_variant | Yes |
| **AttributeValue** | `attribute_values` | attribute_id, value, swatch_color | Yes |
| **ProductAttributeValue** | `product_attribute_values` | product_id, attribute_id, attribute_value_id | Yes |
| **ProductAttributeTextValue** | `product_attribute_text_values` | product_attribute_value_id, text_value | Yes |
| **ProductMedia** | `product_media` | product_id, variant_id, file_path, file_type, is_primary | Yes |
| **Warehouse** | `warehouses` | name, code, address, is_active, is_default | Yes |
| **WarehouseStock** | `warehouse_stock` | warehouse_id, product_id, variant_id, quantity, reserved_quantity, lock_version | Yes |
| **StockMovement** | `stock_movements` (ULID PK) | warehouse_id, product_id, variant_id, movement_type, quantity, reference | Yes |
| **StockReservation** | `stock_reservations` | warehouse_id, product_id, variant_id, quantity, expires_at, status | Yes |
| **PricingRule** | `pricing_rules` | name, type, scope, scope_id, condition, discount_value, dates | Yes |
| **TaxCategory** | `tax_categories` | name, description | Yes |
| **TaxRate** | `tax_rates` | tax_category_id, name, rate, country, state | Yes |
| **AuditLog** | `audit_logs` (ULID PK) | tenant_id, user_id, entity_type, action, old/new_values | Yes (explicit tenant_id column) |

All models use the `HasTenantScope` trait for shared-mode tenancy isolation.

#### 2.2 Enums (11 total)

- **ProductStatusEnum**: `Draft`, `Active`, `Archived`
- **ProductTypeEnum**: `Simple`, `Configurable`, `Bundle`, `Virtual`
- **MovementTypeEnum**: `Received`, `Sold`, `Return`, `Adjustment`, `TransferIn`, `TransferOut`, `Damaged`, `Expired`
- **StockReservationStatusEnum**: `Active`, `Consumed`, `Expired`, `Cancelled`
- **PricingRuleTypeEnum**: `Fixed`, `Percentage`, `Tiered`
- **PricingRuleScopeEnum**: `Product`, `Category`, `Brand`, `All`
- **PricingRuleConditionEnum**: `Quantity`, `CartTotal`, `CustomerGroup`, `DateRange`
- **BarcodeTypeEnum**: `EAN13`, `UPC`, `Code128`, `QR`
- **AttributeTypeEnum**: `Select`, `MultiSelect`, `Text`, `Textarea`, `Color`, `Swatch`
- **AuditActionEnum**: 8 stock-related audit actions
- **MediaTypeEnum**: `Image`, `Video`, `Document`

#### 2.3 Controllers (10 total)

All controllers use constructor injection of service classes and return Inertia responses:

| Controller | Routes | Key Methods |
|-----------|--------|-------------|
| **ProductController** | `products.*` (resource + extras) | index, create, store, show, edit, update, destroy, archive, restore, publish, duplicate |
| **CategoryController** | `categories.*` | index, store, show, update, destroy, reorder |
| **BrandController** | `brands.*` | index, store, update, destroy |
| **VariantController** | `products.variants.*` | index, store, update, destroy, generate, setDefault |
| **WarehouseController** | `warehouses.*` | index, store, update, destroy, setDefault |
| **StockController** | `stock.*` | index, variantStock, receive, deduct, adjust, transfer, movements, lowStock |
| **AttributeController** | `attributes.*` | index, store, update, destroy, storeValue, updateValue, destroyValue |
| **MediaController** | (no named routes) | store, update, destroy, setPrimary, reorder |
| **TaxController** | `tax.*` | categories, storeCategory, updateCategory, destroyCategory, storeRate, updateRate, destroyRate |
| **PricingRuleController** | `pricing-rules.*` | index, store, update, destroy, toggle |

#### 2.4 Form Requests (10 total)

- **StoreProductRequest** - validates name, type, status, base_price, sku, barcode, dimensions, categories, attributes, metadata
- **UpdateProductRequest** - same as store but ignores current product for unique checks
- **StoreCategoryRequest** - validates name, slug, parent_id (with self-reference guard)
- **StoreBrandRequest** - validates name, slug, website_url
- **StoreVariantRequest** - validates sku, name, price, attribute_value_ids (requires product_id context)
- **StoreWarehouseRequest** - validates name, code, address, is_active, is_default
- **StockAdjustmentRequest** - validates warehouse_id, product_id/variant_id, movement_type, quantity
- **StockTransferRequest** - validates from/to warehouse, product/variant, quantity (with different: rule)
- **StoreAttributeRequest** - validates name, frontend_type, is_filterable, is_variant
- **StorePricingRuleRequest** - validates name, type, scope, scope_id, discount_value

#### 2.5 Services (20 total)

Key services injected into controllers:
- **ProductService** - CRUD, search, listing, publishing, archiving, duplication
- **CategoryService** - tree management, CRUD, reordering
- **BrandService** - CRUD, active listing
- **VariantService** - CRUD, generation from combinations, default variant setting
- **WarehouseService** - CRUD, default setting
- **StockService** - receive, deduct, adjust, transfer, movement history
- **StockLockService** - optimistic locking for concurrent stock operations
- **StockReservationService** - reserve/release stock
- **StockAuditService** - audit trail creation
- **AttributeService** - CRUD for attributes and values
- **MediaService** - upload, update, delete, set primary, reorder
- **TaxService** - CRUD for tax categories and rates
- **PricingRuleService** - CRUD, toggle active, eligibility checking
- **ProductImportService** - bulk import
- **BrandService, CategoryService, etc.** - domain-specific business logic

#### 2.6 Contracts (6 interfaces)

- **ProductResolver** - resolves products by various criteria
- **StockChecker** - checks stock availability
- **PricingCalculator** - calculates effective prices
- **SKUGenerator** - generates SKU strings
- **StockAllocator** - allocates stock across warehouses
- **ProductCatalogService** - catalog-level operations

#### 2.7 Observers (4 total)

- **ProductObserver** - handles product lifecycle events
- **VariantObserver** - handles variant lifecycle events
- **WarehouseStockObserver** - tracks stock changes
- **StockReservationObserver** - manages reservation lifecycle

#### 2.8 Policies (4 total)

- **ProductPolicy** - CRUD authorization
- **CategoryPolicy** - CRUD authorization
- **BrandPolicy** - CRUD authorization
- **WarehousePolicy** - CRUD authorization

#### 2.9 Events (14 total)

Product events: `ProductCreated`, `ProductUpdated`, `ProductDeleted`, `ProductPublished`, `ProductArchived`
Variant events: `VariantCreated`, `VariantUpdated`, `VariantDeleted`
Stock events: `StockUpdated`, `StockDepleted`, `LowStockAlert`, `StockTransferCompleted`
Reservation events: `StockReservationCreated`, `StockReservationExpired`

#### 2.10 Listeners (13 total)

- **IndexProductForSearch** / **UpdateProductSearchIndex** / **RemoveProductFromSearchIndex** - Algolia/Meilisearch integration
- **GenerateProductSKU** - auto-generate SKU on creation
- **UpdateProductStockCache** - refresh product-level stock aggregates
- **MarkProductUnavailable** - mark product out of stock
- **SendLowStockNotification** / **SendStockDepletedNotification** - alert triggers
- **TrackStockReservation** / **ReleaseExpiredStock** / **ExpireStockReservations** - reservation lifecycle
- **DeductProductStock** / **RestoreProductStock** - order fulfillment rollback

#### 2.11 Jobs (8 total)

- `IndexProductJob`, `UpdateProductIndexJob`, `RemoveProductIndexJob`, `ReindexAllProductsJob` - search indexing
- `ImportProductsJob`, `ExportProductsJob` - bulk operations
- `GenerateProductSKUsJob` - batch SKU generation
- `ExpireStockReservationsJob` - scheduled cleanup

#### 2.12 DTOs (18 total)

`ProductDTO`, `ProductSummaryDTO`, `ProductWithStockDTO`, `CategoryDTO`, `BrandDTO`, `VariantDTO`, `AttributeDTO`, `AttributeValueDTO`, `WarehouseDTO`, `WarehouseStockDTO`, `StockMovementDTO`, `StockReservationDTO`, `PricingRuleDTO`, `AllocationResult`, `ProductMediaDTO`, `SKUGenerationDTO`, `TaxCategoryDTO`, `TaxRateDTO`

#### 2.13 Rules (4 total)

- `ValidSKU` - custom SKU format validation
- `ValidBarcode` - barcode checksum validation
- `StockAvailable` - real-time stock availability check
- `DifferentParent` - prevents category self-parenting

#### 2.14 ValueObjects (3 total)

- `ProductSearchCriteria` - search/filter/sort parameter object
- `PriceResult` - calculated price with discount breakdown
- `TaxResult` - calculated tax breakdown

#### 2.15 Factories (7 total)

`ProductFactory`, `CategoryFactory`, `BrandFactory`, `VariantFactory`, `WarehouseFactory`, `WarehouseStockFactory`, `StockReservationFactory`

#### 2.16 Exceptions (14 total)

`ProductNotFoundException`, `CategoryNotFoundException`, `BrandNotFoundException`, `VariantNotFoundException`, `WarehouseNotFoundException`, `InsufficientStockException`, `DuplicateSKUException`, `InvalidBarcodeException`, `InvalidProductTypeException`, `StockReservationExpiredException`, `CircularCategoryException`, `PricingRuleExpiredException`, `OrderBackorderedException`, `StaleModelException`

---

### 3. DATABASE SCHEMA (All Migrations)

#### 3.1 Tenant-Level Migrations (19 files in `app/Modules/Product/Database/Migrations/Tenant/`)

**products table:**
```
ulid id PK, category_id FK, brand_id FK, tax_category_id FK
name(500), slug(500) unique, sku(100) nullable unique, barcode(100) nullable
barcode_type(10), description text, short_description text
type(20) default 'simple', status(20) default 'draft'
base_price unsigned int, compare_at_price unsigned int nullable
cost_price unsigned int nullable, tax_inclusive bool default false
track_inventory bool default true, low_stock_threshold unsigned int default 5
total_quantity unsigned int default 0, total_reserved unsigned int default 0
total_available VIRTUAL AS (total_quantity - total_reserved)
warehouse_count unsigned int default 0
weight/length/width/height decimal(10,2) nullable
metadata JSON nullable, published_at timestamp nullable
timestamps + 11 indexes
```

**categories table:**
```
id PK, parent_id self-referencing FK, name(255), slug(255) unique
description text, image_path(500), materialized_path(500)
depth tinyint, is_active bool, sort_order unsigned int
meta_title(255), meta_description text, timestamps
```

**brands table:**
```
id PK, name(255), slug(255) unique, description text
logo_path(500), website_url(500), is_active bool, timestamps
```

**variants table:**
```
ulid id PK, product_id FK, sku(100) unique, barcode(100) nullable
barcode_type(10), name(500), price unsigned int
compare_at_price/cost_price unsigned int nullable
track_inventory bool, low_stock_threshold unsigned int default 5
weight/length/width/height decimal(10,2) nullable
is_default bool, sort_order unsigned int, timestamps
```

**warehouses table:**
```
id PK, name(255), code(50) unique, address fields
city(100), state(100), postal_code(20), country(100)
phone(30), email(255), is_active bool, is_default bool, timestamps
```

**warehouse_stock table:**
```
id PK, warehouse_id FK, product_id FK nullable, variant_id FK nullable
quantity unsigned int default 0, reserved_quantity unsigned int default 0
available_quantity VIRTUAL AS (quantity - reserved_quantity)
reorder_level unsigned int default 5, lock_version unsigned int default 0
last_movement_at timestamp nullable, timestamps
UNIQUE(warehouse_id, product_id, variant_id)
```

**stock_movements table:**
```
ulid id PK, warehouse_id FK, product_id FK nullable, variant_id FK nullable
movement_type(20), quantity int, reference_type(100), reference_id
notes text, performed_by, snapshot_before/after JSON
audit_log_id(26) nullable, timestamps + 8 indexes
```

**stock_reservations table:**
```
id PK, warehouse_id FK, product_id/variant_id FK nullable
quantity unsigned int, reference_type(100), reference_id
expires_at timestamp, status(20) default 'active', timestamps
UNIQUE(reference_type, reference_id, warehouse_id, product_id, variant_id)
```

**Other tables:** `attributes`, `attribute_values`, `product_attribute_values`, `product_attribute_text_values`, `category_product` (pivot), `variant_attribute_values` (pivot), `product_media`, `tax_categories`, `tax_rates`, `pricing_rules`, `audit_logs`

#### 3.2 Shared DB Migrations (`database/migrations/shared/2026_06_06_000001_create_shared_product_tables.php`)

Mirrors all tenant product tables but adds `tenant_id` column to every table and uses `UNIQUE(tenant_id, slug/ sku/ code)` instead of simple unique constraints.

---

### 4. MULTI-TENANCY SETUP

#### 4.1 Stack: `stancl/tenancy` v3 + Custom Hybrid Strategy

**Core Architecture:**
- **Central DB** (`central` connection, MySQL) - holds users, tenants, billing, plans, app settings
- **Dedicated DB** per tenant - professional/enterprise plans get their own `souda_tenant_{uuid}` database
- **Shared DB** (`shared` connection, MySQL `souda_shared`) - free/starter tenants share one database with `tenant_id` column isolation

**Key Files:**

| File | Purpose |
|------|---------|
| `app/Models/Tenant.php` | Extends Stancl's BaseTenant with `tenancy_mode` field |
| `app/Tenancy/TenantManager.php` | Singleton manager, resolves strategy, initializes/ends tenancy |
| `app/Tenancy/Contracts/TenantModeStrategy.php` | Interface for tenant strategies |
| `app/Tenancy/Modes/DedicatedMode.php` | Calls `tenancy()->initialize()` for dedicated DB tenants |
| `app/Tenancy/Modes/SharedMode.php` | Switches `database.default` to `shared`, configures cache/storage isolation |
| `app/Tenancy/Scopes/TenantScope.php` | Global scope filtering by `tenant_id` on shared-mode models |
| `app/Tenancy/Models/Concerns/HasTenantScope.php` | Trait used by all tenant-scoped models |
| `app/Http/Middleware/InitializeTenancyByUser.php` | Middleware that initializes tenancy based on `$user->tenant_id` |
| `app/Providers/TenancyServiceProvider.php` | Registers strategies, maps events, loads `routes/tenant.php` |
| `config/tenancy.php` | All tenancy configuration |

**Tenancy Flow:**
1. User authenticates
2. `InitializeTenancyByUser` middleware checks `$user->tenant_id`
3. Resolves strategy: if `tenancy_mode === 'dedicated'`, calls `tenancy()->initialize()` (Stancl's native); if `'shared'`, switches DB connection to `shared`
4. All queries on models with `HasTenantScope` trait get `WHERE tenant_id = ?` automatically
5. `ProductServiceProvider` registers all product bindings, policies, observers, events

**Plan to Mode Mapping** (`config/tenancy.php`):
```php
'plan_mode_map' => [
    'free' => 'shared',
    'starter' => 'shared',
    'professional' => 'dedicated', // Note: currently maps to 'shared' but config shows intention
    'enterprise' => 'dedicated',
],
```

**Migration Paths** (`config/tenancy.php`):
```php
'migration_parameters' => [
    '--path' => [
        database_path('migrations/tenant'),        // tasks, settings
        app_path('Modules/Product/Database/Migrations/Tenant'), // all product tables
    ],
],
```

---

### 5. BUSINESS TYPE / INDUSTRY / VERTICAL

**No existing code related to business types, industries, or verticals.** The grep search across all PHP and TypeScript files returned zero relevant results. This is a new domain that would need to be built from scratch.

---

### 6. FRONTEND STRUCTURE (React + Inertia)

#### 6.1 Pages Directory (`resources/js/pages/`)

Product pages are **thin wrappers** that re-export from `resources/js/modules/product/pages/`:

```
pages/Product/
├── Index.tsx           → re-exports product-index
├── Create.tsx          → re-exports product-create
├── Edit.tsx            → re-exports product-edit
├── Show.tsx            → re-exports product-show
├── Category/
│   ├── Index.tsx
│   └── Show.tsx
├── Brand/
│   └── Index.tsx
├── Attribute/
│   └── Index.tsx
└── Stock/
    ├── Movements.tsx
    └── LowStock.tsx
```

#### 6.2 Module Structure (`resources/js/modules/product/`)

```
modules/product/
├── components/
│   ├── product-columns.tsx            - DataTable column definitions
│   ├── product-table.tsx              - DataTable wrapper with pagination/toolbar
│   ├── product-filter-bar.tsx         - Filter controls (status, category, brand)
│   ├── product-bulk-actions.tsx       - Bulk action bar
│   ├── product-form-page.tsx          - Main form orchestrator
│   ├── product-general-section.tsx    - Name, description, category, brand
│   ├── product-pricing-section.tsx    - Price, compare price, cost price, tax
│   ├── product-inventory-section.tsx  - SKU, barcode, stock tracking, quantity
│   ├── product-shipping-section.tsx   - Weight, dimensions (collapsible)
│   ├── product-seo-section.tsx        - Meta title, description, slug (collapsible)
│   ├── product-image-upload.tsx       - Image upload component
│   ├── attribute-manager.tsx          - Product attribute management
│   ├── variant-grid.tsx               - Variants list/table
│   ├── variant-generator-dialog.tsx   - Generate variants from attribute combinations
│   └── variant/ (6 sub-components)    - Variant rows, SKU generator, bulk edit
├── hooks/
│   ├── use-product-form.ts            - React Hook Form + Zod + Inertia submission
│   ├── use-products.ts                - Product listing with filters (Inertia router)
│   ├── use-product-mutations.ts       - Bulk actions, delete
│   ├── use-variant-state.ts           - Variant CRUD state management
│   └── use-variant-bulk-edit.ts       - Batch variant editing
├── lib/
│   ├── map-product.ts                 - Maps between API data & form data
│   ├── variant-utils.ts               - SKU generation, price calculation helpers
│   └── variant-combinations.ts        - Cartesian product of attribute values
├── pages/
│   ├── product-index.tsx              - Product list with filters, bulk actions
│   ├── product-create.tsx             - Create form wrapper
│   ├── product-edit.tsx               - Edit form wrapper
│   └── product-show.tsx               - Product detail view
└── types/
    ├── index.ts                       - Re-exports
    ├── product.ts                     - Product, ProductFilters, BulkAction types
    ├── product-form.ts                - Zod schema + ProductFormData type
    ├── variant.ts                     - VariantRowFormData, VariantGroup types
    ├── variant-sku.ts                 - SKU generation types
    └── variant-bulk-edit.ts           - Bulk edit types
```

#### 6.3 Wayfinder (Typed Routes)

`resources/js/routes/products/index.ts` - Generated TypeScript functions for all product routes (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`) with auto-complete for URL parameters.

---

### 7. EXISTING TESTS

#### 7.1 Feature Tests (4 files)

**`ProductCrudTest.php`** (98 lines):
- Authenticated user can create a product
- Product requires valid name and price
- Product SKU must be unique
- User can update product details
- User can delete a product
- Unauthenticated user cannot access products

**`ProductFormTest.php`** (371 lines) - comprehensive:
- Create page renders with categories and brands
- Can create a product with all fields
- Auto-generates slug from name
- Product requires type and status
- Type must be valid
- Name cannot exceed 500 characters
- Barcode must be unique
- Slug must be unique
- Barcode type must be valid
- Can create configurable/bundle/archived/free products
- Can create product with inventory tracking disabled
- Can create product with category IDs
- User without permission cannot create/update/delete
- Can fully update product details

**`CategoryCrudTest.php`** (64 lines):
- User can create nested categories
- Category cannot be its own parent
- Deleting a category does not delete its products

**`StockManagementTest.php`** (57 lines):
- lockStockRecords sorts warehouse IDs ascending
- lockStockRecords deduplicates warehouse IDs
- lockStockRecords returns records keyed by warehouse_id

#### 7.2 Unit Tests (2 files)

**`StockCalculationTest.php`** (36 lines):
- Available quantity is quantity minus reserved
- Warehouse stock is low when at or below reorder level
- Warehouse stock is not low when above reorder level

**`DTOTest.php`** (50 lines):
- Category DTO can be created from request
- Product DTO can be created from request
- Product DTO handles optional fields

---

### 8. TENANCY CONFIGURATION FILES

- `/Users/salam/Herd/souda/config/tenancy.php` - Full tenancy config (257 lines)
- `/Users/salam/Herd/souda/config/database.php` - Database connections: `central`, `shared`, and template `mysql`
- `/Users/salam/Herd/souda/bootstrap/app.php` - Middleware registration, `InitializeTenancyByUser` prepended before `SubstituteBindings`
- `/Users/salam/Herd/souda/bootstrap/providers.php` - Service providers including `TenancyServiceProvider`, `ProductServiceProvider`
- `/Users/salam/Herd/souda/app/Providers/TenancyServiceProvider.php` - Event bindings, route mapping
- `/Users/salam/Herd/souda/app/Providers/ProductServiceProvider.php` - Product DI bindings, policies, observers, event listeners

---

### 9. KEY FILE ABSOLUTE PATHS

**Product Models:** `/Users/salam/Herd/souda/app/Modules/Product/Models/{Product,Category,Brand,Variant,Warehouse,WarehouseStock,StockMovement,StockReservation,Attribute,AttributeValue,ProductAttributeValue,ProductAttributeTextValue,ProductMedia,PricingRule,TaxCategory,TaxRate,AuditLog}.php`

**Product Controllers:** `/Users/salam/Herd/souda/app/Modules/Product/Http/Controllers/{Product,Category,Brand,Variant,Warehouse,Stock,Attribute,Media,Tax,PricingRule}Controller.php`

**Product Form Requests:** `/Users/salam/Herd/souda/app/Modules/Product/Http/Requests/{StoreProduct,UpdateProduct,StoreCategory,StoreBrand,StoreVariant,StoreWarehouse,StockAdjustment,StockTransfer,StoreAttribute,StorePricingRule}Request.php`

**Product Migrations:** `/Users/salam/Herd/souda/app/Modules/Product/Database/Migrations/Tenant/2026_05_21_0000{01..19}_*.php`

**Shared Product Migrations:** `/Users/salam/Herd/souda/database/migrations/shared/2026_06_06_000001_create_shared_product_tables.php`

**Routes:** `/Users/salam/Herd/souda/routes/tenant.php` (product routes), `/Users/salam/Herd/souda/routes/web.php`, `/Users/salam/Herd/souda/routes/admin.php`

**Tenancy Core:** `/Users/salam/Herd/souda/app/Tenancy/{TenantManager.php, Contracts/TenantModeStrategy.php, Modes/DedicatedMode.php, Modes/SharedMode.php, Scopes/TenantScope.php, Models/Concerns/HasTenantScope.php}`

**Tenancy Middleware:** `/Users/salam/Herd/souda/app/Http/Middleware/InitializeTenancyByUser.php`

**Tenant Model:** `/Users/salam/Herd/souda/app/Models/Tenant.php`

**Config:** `/Users/salam/Herd/souda/config/tenancy.php`, `/Users/salam/Herd/souda/config/database.php`

**Product Service Provider:** `/Users/salam/Herd/souda/app/Providers/ProductServiceProvider.php`

**Frontend Product Pages:** `/Users/salam/Herd/souda/resources/js/modules/product/pages/{product-index,product-create,product-edit,product-show}.tsx`

**Frontend Product Components:** `/Users/salam/Herd/souda/resources/js/modules/product/components/{product-form-page,product-table,product-columns,product-general-section,product-pricing-section,product-inventory-section,product-shipping-section,product-seo-section,product-image-upload,variant-grid,attribute-manager}.tsx`

**Frontend Product Types/Schemas:** `/Users/salam/Herd/souda/resources/js/modules/product/types/{product,product-form,variant,index}.ts`

**Wayfinder Routes:** `/Users/salam/Herd/souda/resources/js/routes/products/index.ts`

**Tests:** `/Users/salam/Herd/souda/tests/Feature/Product/{ProductCrudTest,ProductFormTest,CategoryCrudTest,StockManagementTest}.php`, `/Users/salam/Herd/souda/tests/Unit/Product/{StockCalculationTest,DTOTest}.php`

---

### 10. SUMMARY OF KEY OBSERVATIONS

1. **No business type / industry / vertical code exists** - this is a greenfield area
2. **Product module is comprehensive** - covers full product lifecycle, variants, inventory, pricing rules, categories, brands, attributes, media, tax, stock movements/reservations
3. **Hybrid tenancy** (shared + dedicated DBs) with `tenant_id` column scoping for shared mode and separate databases for dedicated mode
4. **Prices stored as integers** (cents) throughout the system
5. **ULID primary keys** for products, variants, stock movements, audit logs
6. **Optimistic locking** on warehouse_stock via `lock_version` column
7. **Virtual columns** for `total_available` and `available_quantity`
8. **Frontend uses Zod schemas** for form validation matching backend rules
9. **Wayfinder** generates TypeScript route functions for all product routes
10. **Inventory tracking** with reservation system, low-stock alerts, and stock depletion handling
</task_result>
```

**Tool: task**

**Input:**
```json
{
  "description": "Explore modules and services",
  "prompt": "Explore the codebase at /Users/salam/Herd/souda to find:\n\n1. Any existing service layer architecture (Actions, Services, DTOs, etc.)\n2. How the app handles form requests and validation\n3. Existing React/Inertia patterns used (components, pages, forms, layouts)\n4. Any existing dynamic field or EAV implementations\n5. Any existing Module/Plugin/Package structure\n6. The app/Providers directory contents\n7. The bootstrap/app.php configuration\n8. composer.json dependencies\n9. package.json dependencies\n10. Any existing documentation about architecture decisions\n11. How permissions/authorization are handled\n12. Search implementation if any\n13. Reporting implementation if any\n14. POS-related code if any\n15. Multi-store/warehouse relationships\n\nRead all relevant files completely and return their full contents.\n\nFocus on patterns, conventions, and architecture decisions already made in the codebase.",
  "subagent_type": "explore"
}
```

**Output:**
```
task_id: ses_11c87a26affez5cc34YVPiC2kn (for resuming to continue this task if needed)

<task_result>
Now I have a complete picture. Here is my comprehensive analysis of the codebase:

---

## Comprehensive Codebase Exploration Report: `/Users/salam/Herd/souda`

### 1. Service Layer Architecture (Actions, Services, DTOs, etc.)

**Actions:** Located at `app/Actions/` and `app/Modules/{Domain}/Actions/`
- `app/Actions/Fortify/CreateNewUser.php` - Fortify user creation
- `app/Actions/Fortify/ResetUserPassword.php` - Fortify password reset
- `app/Actions/Auth/CreateSocialUser.php` - Social auth user creation
- `app/Modules/Billing/Actions/CreateSubscription.php`
- `app/Modules/Billing/Actions/CancelSubscription.php`
- `app/Modules/Billing/Actions/ActivateSubscription.php`

**Services** are the primary business logic layer — found at two levels:
- **App-level**: `app/Services/SocialAuthService.php`, `app/Services/BillingEmailService.php`
- **Module-level**: Extensive service layers in each module, e.g.:
  - `app/Modules/Product/Services/` — 22 services (ProductService, StockService, VariantService, WarehouseService, TaxService, PricingRuleService, etc.)
  - `app/Modules/Billing/Services/` — 8 services (SubscriptionService, PaymentService, PlanService, SeatService, InvoiceService, etc.)

**Service Contracts/Interfaces:** Used extensively for the Product module:
- `app/Modules/Product/Contracts/` — 6 interfaces: `PricingCalculator`, `ProductCatalogService`, `ProductResolver`, `SKUGenerator`, `StockAllocator`, `StockChecker`
- Billing module also uses contracts: `BillingGatewayInterface`, `PricingStrategy`

**DTOs (Data Transfer Objects):** 29 DTOs across modules, all `readonly` PHP 8.2+ classes:
- `app/Modules/Product/DTOs/` — 16 DTOs (ProductDTO, VariantDTO, WarehouseStockDTO, etc.)
- `app/Modules/Billing/DTOs/` — 5 DTOs (SubscriptionDTO, PlanDTO, PaymentDTO, etc.)
- `app/Modules/Order/DTOs/` — 3 DTOs (OrderDTO, LineItemDTO, OrderAddressDTO)
- `app/Modules/Inventory/DTOs/` — 1 DTO (InventoryMovementDTO)
- `app/Modules/CRM/DTOs/` — 1 DTO (CustomerDTO)
- `app/Modules/Shared/DTOs/` — 1 DTO (EventEnvelope)

**ValueObjects:**
- `app/Modules/Product/ValueObjects/` — 3 ValueObjects (TaxResult, ProductSearchCriteria, PriceResult)

**Key pattern:** DTOs are created via static factory methods `fromModel()` and `fromRequest()`, are `readonly`, and use constructor property promotion.

---

### 2. Form Requests & Validation

**Pattern:** Dedicated Form Request classes for each operation, using array-based validation rules.

App-level form requests:
- `app/Http/Requests/StoreTaskRequest.php` — inline rules array
- `app/Http/Requests/UpdateTaskRequest.php` — inline rules array  
- `app/Http/Requests/InviteTeamMemberRequest.php` — validates `email` + `seat_type` enum
- `app/Http/Requests/Admin/UpdateSocialAuthSettingsRequest.php`
- `app/Http/Requests/Settings/PasswordUpdateRequest.php`, `ProfileDeleteRequest.php`, `ProfileUpdateRequest.php`, `TwoFactorAuthenticationRequest.php`

Module-level form requests (Product module):
- `app/Modules/Product/Http/Requests/StoreProductRequest.php` — comprehensive 20+ field validation with custom messages
- `app/Modules/Product/Http/Requests/UpdateProductRequest.php`
- Plus: StoreAttributeRequest, StoreBrandRequest, StoreCategoryRequest, StoreVariantRequest, StoreWarehouseRequest, StockAdjustmentRequest, StockTransferRequest, StorePricingRuleRequest

**Conventions observed:**
- Use array syntax: `['required', 'string', 'max:255']`
- Custom messages via `messages()` method
- Use `Rule::in()` for enum validation
- `withValidator()` in StoreCategoryRequest for circular parent validation
- BillingController uses inline `$request->validate()` for its subscribe endpoint

---

### 3. React/Inertia Patterns

**Entry Point:**
- `resources/js/app.tsx` — wraps with `QueryProvider` (TanStack React Query), uses `createInertiaApp`, `resolvePageComponent` from `laravel-vite-plugin/inertia-helpers`

**Page Structure (mirrors route structure):**
- Pages are thin re-exports: `resources/js/pages/Product/Index.tsx` simply re-exports from `@/modules/product/pages/product-index`
- Actual logic lives in `resources/js/modules/{domain}/pages/`

**Layouts:**
- `resources/js/layouts/app-layout.tsx` — primary tenant layout wrapping `app/app-sidebar-layout`
- `resources/js/layouts/auth-layout.tsx` — auth pages layout
- `resources/js/layouts/settings/` — settings sub-layouts
- `resources/js/layouts/admin-settings/` — admin sub-layout

**Components:**
- `resources/js/components/ui/` — shadcn-style UI primitives (button, card, dialog, input, select, table, tabs, etc.)
- `resources/js/components/` — app-specific components (app-shell, app-sidebar, nav-*, etc.)

**Key frontend libraries used:**
- `@inertiajs/react` v2 — useForm, Link, router, Head
- `react-hook-form` v7 + `@hookform/resolvers` v5 — for complex forms
- `@tanstack/react-query` v5 — server state management
- `@tanstack/react-table` v8 — data tables
- `react-hook-form` + `zod` v4 — form validation schemas
- `@radix-ui/react-*` — accessible UI primitives
- `@headlessui/react` v2 — additional headless components
- `recharts` v3 — charts
- `lucide-react` — icons
- `cmdk` — command palette
- `class-variance-authority` + `tailwind-merge` + `clsx` — component variant system
- `date-fns` — date utilities
- `tw-animate-css` — CSS animations

**Wayfinder integration:**
- `@laravel/vite-plugin-wayfinder` v0.1.3 — generates TypeScript functions from routes
- Auto-generated files at `resources/js/actions/` and `resources/js/routes/`
- Billing page imports: `import { index, subscribe, cancel, invoices } from '@/actions/App/Http/Controllers/BillingController'`

**Form patterns:**
- Simple forms use Inertia's `useForm` (e.g., task CRUD)
- Complex forms (product) use `react-hook-form` + `zod` (visible in `product-form-page.tsx`)

---

### 4. Dynamic Fields / EAV Implementation

**The Product module has a full EAV (Entity-Attribute-Value) system:**

Models:
- `app/Modules/Product/Models/Attribute.php` — defines attribute metadata (name, slug, frontend_type, is_filterable, is_required, is_variant, sort_order, validation_rules)
- `app/Modules/Product/Models/AttributeValue.php` — predefined values for select/multiselect attributes (value, swatch_color, sort_order)
- `app/Modules/Product/Models/ProductAttributeValue.php` — pivot linking products to attribute values (product_id, attribute_id, attribute_value_id)
- `app/Modules/Product/Models/ProductAttributeTextValue.php` — separate table for free-text attribute values (1:1 with ProductAttributeValue)

**Relationships:**
- Product hasMany ProductAttributeValue → belongsTo Attribute + AttributeValue
- ProductAttributeValue hasOne ProductAttributeTextValue (for free-text values)
- AttributeValue belongsToMany Variant (via `variant_attribute_values`)

**Controller:** `AttributeController` handles CRUD for attributes and their values.

**Key EAV tables (tenant DB migrations):**
- `product_attribute_values` — links product to pre-defined attribute values
- `product_attribute_text_values` — stores free-text attribute values
- `variant_attribute_values` — links variants to attribute values

---

### 5. Module/Plugin/Package Structure

**Modular Monolith architecture** — all modules under `app/Modules/{Domain}/`

**PHP modules (6 total):**
1. **Billing** (`app/Modules/Billing/`) — subscriptions, payments, plans, seat management, gateways (Stripe, SSLCommerz, bKash, Nagad, PortWallet, Manual)
2. **Product** (`app/Modules/Product/`) — products, categories, brands, attributes (EAV), variants, warehouses, stock, pricing rules, taxes, media
3. **Order** (`app/Modules/Order/`) — orders, line items (3 events, 3 DTOs)
4. **Inventory** (`app/Modules/Inventory/`) — inventory movements (3 events, 1 DTO)
5. **CRM** (`app/Modules/CRM/`) — customer management (2 events, 1 DTO)
6. **Shared** (`app/Modules/Shared/`) — cross-cutting concerns (traits, contracts, DTOs, etc.)

**Frontend modules** mirror PHP modules:
- `resources/js/modules/product/` — 4 sub-dirs: components, hooks, pages, types
- `resources/js/modules/billing/` — components, hooks, pages, types
- `resources/js/modules/crm/` — components, hooks, pages, types
- `resources/js/modules/inventory/` — components, hooks, pages, types
- `resources/js/modules/order/` — components, hooks, pages, types
- `resources/js/modules/shared/` — components, hooks, lib, providers, types

**Each PHP module follows consistent internal structure:**
```
Actions/ | Contracts/ | DTOs/ | Database/{Factories,Migrations}/
Drivers/ | Enums/ | Events/ | Exceptions/ | Http/{Controllers,Requests}/
Jobs/ | Listeners/ | Models/ | Observers/ | Policies/ | Rules/ | Services/
Tests/ | Traits/ | ValueObjects/ | Webhooks/
```

**Module communication patterns** (documented in `docs/architectures/03-module-communication.md`):
- **Domain Events** — primary pattern (e.g., OrderCreated triggers DeductProductStock)
- **Service Contracts** — cross-module queries via interfaces (ProductResolver, StockChecker)
- **Action Classes** — cross-module commands
- **DTOs** — data transfer across module boundaries (never pass Eloquent models)

---

### 6. Providers Directory (`app/Providers/`)

6 service providers:
1. **AppServiceProvider** — global config: immutable dates, prohibit destructive commands in production, password defaults
2. **BillingServiceProvider** — registers billing singletons, middleware aliases (`subscription`, `feature`, `seat`), event listeners
3. **FortifyServiceProvider** — authentication views, rate limiting, custom login/register responses
4. **ProductServiceProvider** — registers product services as singletons, binds contracts to implementations, registers policies, observers, and event listeners
5. **OrderServiceProvider** — registers cross-module event listeners (DeductProductStock, RestoreProductStock)
6. **TenancyServiceProvider** — tenant lifecycle events, route mapping for tenant.php, middleware priority

---

### 7. `bootstrap/app.php` Configuration

- Web routes, console routes, health check at `/up`
- Admin routes loaded in `then()` callback via `Route::middleware('web')->group(base_path('routes/admin.php'))`
- Middleware: `EncryptCookies` exempts `appearance` and `sidebar_state`
- CSRF exceptions: `stripe/*`, `billing/webhook/*`, `billing/success/sslcommerz`
- Web middleware appended: `HandleAppearance`, `HandleInertiaRequests`, `AddLinkHeadersForPreloadedAssets`
- `InitializeTenancyByUser` prepended before `SubstituteBindings`

---

### 8. `composer.json` Dependencies

**Core:**
- `php: ^8.4`, `laravel/framework: ^13.0`

**Auth/Tenancy:**
- `laravel/fortify: ^1.30`, `laravel/socialite: ^5.24`
- `stancl/tenancy: ^3.9` (multi-tenancy)
- `spatie/laravel-permission: ^7.2` (roles/permissions)

**Billing:**
- `laravel/cashier: ^16.3` (Stripe)

**Frontend/DevOps:**
- `inertiajs/inertia-laravel: ^2.0`, `laravel/wayfinder: ^0.1.9`
- `laravel/scout: ^11.2` (search)
- `laravel/tinker: ^3.0`

**Dev:**
- `laravel/boost: ^2.2`, `laravel/pail: ^1.2.2`, `laravel/pint: ^1.24`, `laravel/sail: ^1.41`
- `pestphp/pest: ^4.4`, `pestphp/pest-plugin-laravel: ^4.1`
- `fakerphp/faker`, `mockery/mockery`, `nunomaduro/collision`

---

### 9. `package.json` Dependencies

**Core:**
- `react: ^19.2.0`, `react-dom: ^19.2.0`
- `@inertiajs/react: ^2.3.7`
- `typescript: ^5.7.2`, `vite: ^7.0.4`

**UI:**
- `@headlessui/react: ^2.2.0`, `radix-ui: ^1.4.3`
- `tailwindcss: ^4.0.0`, `@tailwindcss/vite: ^4.1.11`
- `lucide-react: ^0.475.0`, `recharts: ^3.7.0`
- `cmdk: ^1.1.1`, `input-otp: ^1.4.2`

**Forms/Data:**
- `react-hook-form: ^7.76.0`, `@hookform/resolvers: ^5.2.2`
- `zod: ^4.4.3`
- `@tanstack/react-table: ^8.21.3`, `@tanstack/react-query: ^5.100.11`
- `@tanstack/react-virtual: ^3.13.25`

**Utilities:**
- `class-variance-authority: ^0.7.1`, `clsx: ^2.1.1`, `tailwind-merge: ^3.0.1`
- `date-fns: ^4.1.0`, `react-day-picker: ^9.13.2`
- `tw-animate-css: ^1.4.0`

**Dev:**
- `@eslint/js: ^9.19.0`, `eslint: ^9.17.0`
- `prettier: ^3.4.2`, `prettier-plugin-tailwindcss: ^0.6.11`
- `@laravel/vite-plugin-wayfinder: ^0.1.3`
- `babel-plugin-react-compiler: ^1.0.0`
- `typescript-eslint: ^8.23.0`

---

### 10. Architecture Documentation

Rich documentation exists at `docs/` and `docs/architectures/`:

**`docs/architectures/`:**
1. `01-recommended-architecture.md` — System overview, layered architecture diagram, DDD principles
2. `02-folder-structure.md` — Complete directory structure reference
3. `03-module-communication.md` — Domain events, service contracts, action classes, DTOs
4. `04-tenant-database-strategy.md` — Multi-database tenancy design
5. `05-queue-strategy.md` — Redis-backed queue with 4 priority levels
6. `06-naming-conventions.md` — Naming conventions across PHP, JS, DB
7. `07-coding-standards.md` — Code formatting, PHPDoc, Eloquent, React, testing standards
8. `08-service-container-bindings.md` — Container bindings, factory/strategy patterns
9. `09-event-listener-strategy.md` — Event-driven architecture
10. `10-package-ecosystem.md` — Future package recommendations
11. `11-development-roadmap.md` — 42-week phased development plan
12. `multi-tenancy.md` — Detailed hybrid (shared+dedicated) tenancy strategy
13. `product-module-design.md` — Detailed product module design with EAV

**`docs/frontend/`:**
1. `01-recommended-frontend-architecture.md`
2. `02-folder-structure.md`
3. `03-component-structure.md`
4. `04-form-architecture.md`
5. `05-table-architecture.md`
6. `06-state-management-strategy.md`
7. `07-api-integration-strategy.md`
8. `08-reusable-ui-patterns.md`
9. `09-product-page-breakdown.md`
10. `10-performance-optimization-strategy.md`

**`docs/subscription-payment-flow.md`** — 867-line comprehensive document covering the complete billing flow.

**`agents/`** — AI agent documentation for specific domains (billing, product, multi-tenancy, testing, etc.)

---

### 11. Permissions/Authorization

**Tech stack:** `spatie/laravel-permission: ^7.2`

**Current implementation:**
- `app/Models/User.php` uses `HasRoles` trait + `CentralConnection`
- `app/Models/Role.php` and `app/Models/Permission.php` extend Spatie models with `CentralConnection`
- `app/Http/Middleware/EnsureAdmin.php` — checks `$request->user()?->hasRole('admin')`
- Roles/permissions live in the **central database**

**Product authorization:**
- `app/Modules/Product/Policies/ProductPolicy.php`, `BrandPolicy.php`, `CategoryPolicy.php`, `WarehousePolicy.php`
- Registered in `ProductServiceProvider::boot()` via `Gate::policy()`
- `ProductController` uses `AuthorizesRequests` trait with explicit `$this->authorize()` calls

**Subscription/Feature gating** (separate from Spatie):
- `EnsureTenantHasSubscription` (aliased `subscription`) — checks subscription accessibility
- `EnsureTenantHasFeature` (aliased `feature:{name}`) — checks plan feature access via `PlanFeatureService`
- `EnsureSeatAvailable` (aliased `seat`) — checks plan max_seats before team invites

**Admin bypass:** Admin routes skip tenant initialization entirely. `InitializeTenancyByUser` explicitly checks for admin routes.

---

### 12. Search Implementation

**Tech stack:** `laravel/scout: ^11.2`

**Product module:**
- `App\Modules\Product\Models\Product` uses `Searchable` trait
- `searchableAs()` returns `'products'` index
- `toSearchableArray()` defines indexed fields (name, slug, sku, barcode, description, base_price, status, type, category_name, brand_name, total_stock, etc.)
- `shouldBeSearchable()` — only indexes active + published products

**Search indexing events/listeners:**
- `ProductCreated` → `IndexProductForSearch` listener
- `ProductUpdated` → `UpdateProductSearchIndex` listener
- `ProductDeleted` → `RemoveProductFromSearchIndex` listener
- Plus queued jobs: `IndexProductJob`, `ReindexAllProductsJob`, `UpdateProductIndexJob`, `RemoveProductIndexJob`

---

### 13. Reporting Implementation

No dedicated reporting module exists yet. It's part of the future roadmap (Phase 6: Dashboard & Analytics). The `app/Modules/Shared/` directory exists with reporting-related items, but the actual implementation appears to be planned, not built.

---

### 14. POS-Related Code

No POS-specific code found in the codebase. The Product module (categories, brands, variants, stock, warehouses) and Order module foundation exist, but no POS-specific controllers, routes, or UI have been implemented.

---

### 15. Multi-Store/Warehouse Relationships

**Warehouse implementation** in `app/Modules/Product/`:

**Models:**
- `app/Modules/Product/Models/Warehouse.php` — name, code, address, contact, is_active, is_default
- `app/Modules/Product/Models/WarehouseStock.php` — warehouse_id, product_id, variant_id, quantity, reserved_quantity, reorder_level, lock_version (optimistic locking)
- `app/Modules/Product/Models/StockReservation.php` — order tracking per warehouse
- `app/Modules/Product/Models/StockMovement.php` — movement tracking with type, reason, reference

**Services:**
- `WarehouseService` — warehouse CRUD
- `StockService` — stock operations (receive, deduct, adjust, transfer)
- `StockReservationService` — reserve/release stock
- `StockLockService` — optimistic locking for concurrent stock operations
- `StockAuditService` — stock auditing

**Controllers:**
- `WarehouseController` — warehouse management
- `StockController` — low stock, movements, transfers

**Stock transfer across warehouses** is supported:
- Route: `POST /stock-transfers` calls `StockController::transfer()`
- `StockMovement` model tracks all in/out movements per warehouse

**Optimistic concurrency:**
- `WarehouseStock` has a `lock_version` column
- `HasOptimisticLocking` trait prevents race conditions on stock updates

**Frontend:**
- `resources/js/pages/Product/Stock/` — LowStock, Movements pages
- `resources/js/modules/inventory/` — Inventory module structure exists with components/hooks/pages/types

---

### Summary of Key Architecture Decisions

1. **Modular monolith** with DDD — 6 modules under `app/Modules/`
2. **Hybrid multi-tenancy** — shared DB for free/starter, dedicated DBs for pro/enterprise
3. **User-based tenant identification** (not domain-based, though domain support is configured)
4. **Central data** — users, tenants, billing, roles/permissions, app settings
5. **Tenant data** — products, orders, inventory, CRM (on subscription activation, not registration)
6. **Layered architecture** — Presentation → Application (Actions/Services/DTOs) → Domain (Modules) → Infrastructure
7. **Event-driven cross-module communication** — events carry DTOs, listeners are queued
8. **Service contracts** for inter-module queries (ProductResolver, StockChecker, PricingCalculator)
9. **DTOs as readonly classes** with `fromModel()` and `fromRequest()` factory methods
10. **EAV for product attributes** — Attribute → AttributeValue → ProductAttributeValue → ProductAttributeTextValue
11. **Spatie permissions** for role-based access, separate subscription/feature gating for plan enforcement
12. **Inertia v2 + React 19** SPA with SSR support, Tailwind CSS v4, Radix UI primitives
13. **Wayfinder** for type-safe route generation from PHP to TypeScript
14. **Strategy pattern** for payment gateways (BillingGatewayInterface) and pricing (PricingStrategy)
15. **Optimistic concurrency control** for stock operations via lock_version
</task_result>
```

---

## Assistant (Plan · DeepSeek V4 Flash Free · 5.5s)

_Thinking:_

Now I have a comprehensive understanding of the existing codebase. Let me now analyze the current architecture, identify weaknesses, and design the new architecture.

Let me read some key files to understand the patterns better, particularly the product model, the existing EAV system, and some key services.

Let me now examine key existing files to understand the current EAV implementation and patterns more deeply.

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Modules/Product/Models/Product.php"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Modules/Product/Models/Product.php</path>
<type>file</type>
<content>
1: <?php
2: 
3: declare(strict_types=1);
4: 
5: namespace App\Modules\Product\Models;
6: 
7: use App\Modules\Product\Database\Factories\ProductFactory;
8: use App\Modules\Product\Enums\ProductStatusEnum;
9: use App\Modules\Product\Enums\ProductTypeEnum;
10: use App\Modules\Product\Traits\HasProductMedia;
11: use App\Modules\Product\Traits\HasProductStock;
12: use App\Modules\Product\Traits\Sluggable;
13: use App\Tenancy\Models\Concerns\HasTenantScope;
14: use Illuminate\Database\Eloquent\Concerns\HasUlids;
15: use Illuminate\Database\Eloquent\Factories\HasFactory;
16: use Illuminate\Database\Eloquent\Model;
17: use Illuminate\Database\Eloquent\Relations\BelongsTo;
18: use Illuminate\Database\Eloquent\Relations\BelongsToMany;
19: use Illuminate\Database\Eloquent\Relations\HasMany;
20: use Laravel\Scout\Searchable;
21: 
22: class Product extends Model
23: {
24:     use HasFactory;
25:     use HasProductMedia;
26:     use HasProductStock;
27:     use HasTenantScope;
28:     use HasUlids;
29:     use Searchable;
30:     use Sluggable;
31: 
32:     protected $fillable = [
33:         'category_id',
34:         'brand_id',
35:         'tax_category_id',
36:         'name',
37:         'slug',
38:         'sku',
39:         'barcode',
40:         'barcode_type',
41:         'description',
42:         'short_description',
43:         'type',
44:         'status',
45:         'base_price',
46:         'compare_at_price',
47:         'cost_price',
48:         'tax_inclusive',
49:         'track_inventory',
50:         'low_stock_threshold',
51:         'total_quantity',
52:         'total_reserved',
53:         'warehouse_count',
54:         'weight',
55:         'length',
56:         'width',
57:         'height',
58:         'metadata',
59:         'published_at',
60:     ];
61: 
62:     protected function casts(): array
63:     {
64:         return [
65:             'tax_inclusive' => 'boolean',
66:             'track_inventory' => 'boolean',
67:             'metadata' => 'array',
68:             'published_at' => 'datetime',
69:             'type' => ProductTypeEnum::class,
70:             'status' => ProductStatusEnum::class,
71:         ];
72:     }
73: 
74:     protected static function newFactory(): ProductFactory
75:     {
76:         return ProductFactory::new();
77:     }
78: 
79:     public function category(): BelongsTo
80:     {
81:         return $this->belongsTo(Category::class);
82:     }
83: 
84:     public function categories(): BelongsToMany
85:     {
86:         return $this->belongsToMany(Category::class, 'category_product');
87:     }
88: 
89:     public function brand(): BelongsTo
90:     {
91:         return $this->belongsTo(Brand::class);
92:     }
93: 
94:     public function taxCategory(): BelongsTo
95:     {
96:         return $this->belongsTo(TaxCategory::class);
97:     }
98: 
99:     public function variants(): HasMany
100:     {
101:         return $this->hasMany(Variant::class);
102:     }
103: 
104:     public function media(): HasMany
105:     {
106:         return $this->hasMany(ProductMedia::class);
107:     }
108: 
109:     public function attributeValues(): HasMany
110:     {
111:         return $this->hasMany(ProductAttributeValue::class);
112:     }
113: 
114:     public function warehouseStock(): HasMany
115:     {
116:         return $this->hasMany(WarehouseStock::class);
117:     }
118: 
119:     public function stockMovements(): HasMany
120:     {
121:         return $this->hasMany(StockMovement::class);
122:     }
123: 
124:     public function pricingRules(): HasMany
125:     {
126:         return $this->hasMany(PricingRule::class, 'scope_id')
127:             ->where('scope', 'product');
128:     }
129: 
130:     public function defaultVariant(): ?Variant
131:     {
132:         return $this->variants()->where('is_default', true)->first();
133:     }
134: 
135:     public function searchableAs(): string
136:     {
137:         return 'products';
138:     }
139: 
140:     public function toSearchableArray(): array
141:     {
142:         return [
143:             'objectID' => (string) $this->id,
144:             'name' => $this->name,
145:             'slug' => $this->slug,
146:             'sku' => $this->sku,
147:             'barcode' => $this->barcode,
148:             'description' => $this->description,
149:             'short_description' => $this->short_description,
150:             'base_price' => $this->base_price,
151:             'status' => $this->status?->value,
152:             'type' => $this->type?->value,
153:             'category_id' => $this->category_id,
154:             'brand_id' => $this->brand_id,
155:             'category_name' => $this->category?->name,
156:             'brand_name' => $this->brand?->name,
157:             'total_stock' => $this->total_available,
158:             'created_at' => $this->created_at?->timestamp,
159:         ];
160:     }
161: 
162:     public function shouldBeSearchable(): bool
163:     {
164:         return $this->status === ProductStatusEnum::Active
165:             && $this->published_at !== null;
166:     }
167: 
168:     public function scopeActive($query)
169:     {
170:         return $query->where('status', ProductStatusEnum::Active);
171:     }
172: 
173:     public function scopeDraft($query)
174:     {
175:         return $query->where('status', ProductStatusEnum::Draft);
176:     }
177: 
178:     public function scopePublished($query)
179:     {
180:         return $query->whereNotNull('published_at')
181:             ->where('published_at', '<=', now());
182:     }
183: }

(End of file - total 183 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Modules/Product/Models/Attribute.php"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Modules/Product/Models/Attribute.php</path>
<type>file</type>
<content>
1: <?php
2: 
3: declare(strict_types=1);
4: 
5: namespace App\Modules\Product\Models;
6: 
7: use App\Modules\Product\Traits\Sluggable;
8: use App\Tenancy\Models\Concerns\HasTenantScope;
9: use Illuminate\Database\Eloquent\Model;
10: use Illuminate\Database\Eloquent\Relations\HasMany;
11: 
12: class Attribute extends Model
13: {
14:     use HasTenantScope;
15:     use Sluggable;
16: 
17:     protected $fillable = [
18:         'name',
19:         'slug',
20:         'frontend_type',
21:         'is_filterable',
22:         'is_required',
23:         'is_variant',
24:         'sort_order',
25:         'validation_rules',
26:     ];
27: 
28:     protected function casts(): array
29:     {
30:         return [
31:             'is_filterable' => 'boolean',
32:             'is_required' => 'boolean',
33:             'is_variant' => 'boolean',
34:             'validation_rules' => 'array',
35:         ];
36:     }
37: 
38:     public function values(): HasMany
39:     {
40:         return $this->hasMany(AttributeValue::class);
41:     }
42: 
43:     public function productValues(): HasMany
44:     {
45:         return $this->hasMany(ProductAttributeValue::class);
46:     }
47: 
48:     public function scopeVariant($query)
49:     {
50:         return $query->where('is_variant', true);
51:     }
52: 
53:     public function scopeFilterable($query)
54:     {
55:         return $query->where('is_filterable', true);
56:     }
57: }

(End of file - total 57 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Modules/Product/Models/ProductAttributeValue.php"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Modules/Product/Models/ProductAttributeValue.php</path>
<type>file</type>
<content>
1: <?php
2: 
3: declare(strict_types=1);
4: 
5: namespace App\Modules\Product\Models;
6: 
7: use App\Tenancy\Models\Concerns\HasTenantScope;
8: use Illuminate\Database\Eloquent\Model;
9: use Illuminate\Database\Eloquent\Relations\BelongsTo;
10: use Illuminate\Database\Eloquent\Relations\HasOne;
11: 
12: class ProductAttributeValue extends Model
13: {
14:     use HasTenantScope;
15: 
16:     protected $table = 'product_attribute_values';
17: 
18:     protected $fillable = [
19:         'product_id',
20:         'attribute_id',
21:         'attribute_value_id',
22:     ];
23: 
24:     public function product(): BelongsTo
25:     {
26:         return $this->belongsTo(Product::class);
27:     }
28: 
29:     public function attribute(): BelongsTo
30:     {
31:         return $this->belongsTo(Attribute::class);
32:     }
33: 
34:     public function attributeValue(): BelongsTo
35:     {
36:         return $this->belongsTo(AttributeValue::class);
37:     }
38: 
39:     public function textValue(): HasOne
40:     {
41:         return $this->hasOne(ProductAttributeTextValue::class, 'product_attribute_value_id');
42:     }
43: }

(End of file - total 43 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Modules/Product/Models/ProductAttributeTextValue.php"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Modules/Product/Models/ProductAttributeTextValue.php</path>
<type>file</type>
<content>
1: <?php
2: 
3: declare(strict_types=1);
4: 
5: namespace App\Modules\Product\Models;
6: 
7: use App\Tenancy\Models\Concerns\HasTenantScope;
8: use Illuminate\Database\Eloquent\Model;
9: use Illuminate\Database\Eloquent\Relations\BelongsTo;
10: 
11: class ProductAttributeTextValue extends Model
12: {
13:     use HasTenantScope;
14: 
15:     protected $table = 'product_attribute_text_values';
16: 
17:     public $timestamps = false;
18: 
19:     protected $fillable = [
20:         'product_attribute_value_id',
21:         'text_value',
22:     ];
23: 
24:     public function productAttributeValue(): BelongsTo
25:     {
26:         return $this->belongsTo(ProductAttributeValue::class);
27:     }
28: }

(End of file - total 28 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Modules/Product/Models/Category.php"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Modules/Product/Models/Category.php</path>
<type>file</type>
<content>
1: <?php
2: 
3: declare(strict_types=1);
4: 
5: namespace App\Modules\Product\Models;
6: 
7: use App\Modules\Product\Database\Factories\CategoryFactory;
8: use App\Modules\Product\Traits\HasMaterializedPath;
9: use App\Modules\Product\Traits\Sluggable;
10: use App\Tenancy\Models\Concerns\HasTenantScope;
11: use Illuminate\Database\Eloquent\Factories\HasFactory;
12: use Illuminate\Database\Eloquent\Model;
13: use Illuminate\Database\Eloquent\Relations\BelongsTo;
14: use Illuminate\Database\Eloquent\Relations\BelongsToMany;
15: use Illuminate\Database\Eloquent\Relations\HasMany;
16: 
17: class Category extends Model
18: {
19:     use HasFactory;
20:     use HasMaterializedPath;
21:     use HasTenantScope;
22:     use Sluggable;
23: 
24:     protected static function newFactory(): CategoryFactory
25:     {
26:         return CategoryFactory::new();
27:     }
28: 
29:     protected $fillable = [
30:         'parent_id',
31:         'name',
32:         'slug',
33:         'description',
34:         'image_path',
35:         'is_active',
36:         'sort_order',
37:         'meta_title',
38:         'meta_description',
39:     ];
40: 
41:     public function parent(): BelongsTo
42:     {
43:         return $this->belongsTo(self::class, 'parent_id');
44:     }
45: 
46:     public function children(): HasMany
47:     {
48:         return $this->hasMany(self::class, 'parent_id');
49:     }
50: 
51:     public function products(): BelongsToMany
52:     {
53:         return $this->belongsToMany(Product::class, 'category_product');
54:     }
55: 
56:     public function allProducts(): BelongsToMany
57:     {
58:         $ids = $this->getDescendantIds();
59:         $ids[] = $this->id;
60: 
61:         return $this->belongsToMany(Product::class, 'category_product')
62:             ->whereIn('category_id', $ids);
63:     }
64: 
65:     public function pricingRules(): HasMany
66:     {
67:         return $this->hasMany(PricingRule::class, 'scope_id')
68:             ->where('scope', 'category');
69:     }
70: }

(End of file - total 70 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Modules/Product/Models/Variant.php"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Modules/Product/Models/Variant.php</path>
<type>file</type>
<content>
1: <?php
2: 
3: declare(strict_types=1);
4: 
5: namespace App\Modules\Product\Models;
6: 
7: use App\Modules\Product\Traits\HasProductStock;
8: use App\Tenancy\Models\Concerns\HasTenantScope;
9: use Illuminate\Database\Eloquent\Concerns\HasUlids;
10: use Illuminate\Database\Eloquent\Model;
11: use Illuminate\Database\Eloquent\Relations\BelongsTo;
12: use Illuminate\Database\Eloquent\Relations\BelongsToMany;
13: use Illuminate\Database\Eloquent\Relations\HasMany;
14: 
15: class Variant extends Model
16: {
17:     use HasProductStock;
18:     use HasTenantScope;
19:     use HasUlids;
20: 
21:     protected $fillable = [
22:         'product_id',
23:         'sku',
24:         'barcode',
25:         'barcode_type',
26:         'name',
27:         'price',
28:         'compare_at_price',
29:         'cost_price',
30:         'track_inventory',
31:         'low_stock_threshold',
32:         'weight',
33:         'length',
34:         'width',
35:         'height',
36:         'is_default',
37:         'sort_order',
38:     ];
39: 
40:     protected function casts(): array
41:     {
42:         return [
43:             'track_inventory' => 'boolean',
44:             'is_default' => 'boolean',
45:         ];
46:     }
47: 
48:     public function product(): BelongsTo
49:     {
50:         return $this->belongsTo(Product::class);
51:     }
52: 
53:     public function attributeValues(): BelongsToMany
54:     {
55:         return $this->belongsToMany(AttributeValue::class, 'variant_attribute_values');
56:     }
57: 
58:     public function media(): HasMany
59:     {
60:         return $this->hasMany(ProductMedia::class);
61:     }
62: 
63:     public function warehouseStock(): HasMany
64:     {
65:         return $this->hasMany(WarehouseStock::class);
66:     }
67: 
68:     public function stockMovements(): HasMany
69:     {
70:         return $this->hasMany(StockMovement::class);
71:     }
72: }

(End of file - total 72 lines)
</content>
```

---

## Assistant (Plan · DeepSeek V4 Flash Free · 4.5s)

_Thinking:_

Now let me read some more files to understand the service layer, form requests, routes, and frontend patterns.

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Modules/Product/Services/ProductService.php"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Modules/Product/Services/ProductService.php</path>
<type>file</type>
<content>
1: <?php
2: 
3: declare(strict_types=1);
4: 
5: namespace App\Modules\Product\Services;
6: 
7: use App\Modules\Product\Contracts\SKUGenerator;
8: use App\Modules\Product\DTOs\ProductDTO;
9: use App\Modules\Product\DTOs\ProductSummaryDTO;
10: use App\Modules\Product\DTOs\ProductWithStockDTO;
11: use App\Modules\Product\Enums\ProductStatusEnum;
12: use App\Modules\Product\Events\ProductArchived;
13: use App\Modules\Product\Events\ProductCreated;
14: use App\Modules\Product\Events\ProductDeleted;
15: use App\Modules\Product\Events\ProductPublished;
16: use App\Modules\Product\Events\ProductUpdated;
17: use App\Modules\Product\Models\Product;
18: use App\Modules\Product\ValueObjects\ProductSearchCriteria;
19: use Illuminate\Contracts\Events\Dispatcher;
20: use Illuminate\Contracts\Pagination\LengthAwarePaginator;
21: use Illuminate\Database\Eloquent\Builder;
22: 
23: class ProductService
24: {
25:     public function __construct(
26:         protected Dispatcher $events,
27:         protected SKUGenerator $skuGenerator,
28:     ) {}
29: 
30:     public function createProduct(ProductDTO $dto): Product
31:     {
32:         $product = Product::query()->create([
33:             'category_id' => $dto->categoryId,
34:             'brand_id' => $dto->brandId,
35:             'tax_category_id' => $dto->taxCategoryId,
36:             'name' => $dto->name,
37:             'slug' => $dto->slug,
38:             'sku' => $dto->sku,
39:             'barcode' => $dto->barcode,
40:             'barcode_type' => $dto->barcodeType,
41:             'description' => $dto->description,
42:             'short_description' => $dto->shortDescription,
43:             'type' => $dto->type->value,
44:             'status' => $dto->status->value,
45:             'base_price' => $dto->basePrice,
46:             'compare_at_price' => $dto->compareAtPrice,
47:             'cost_price' => $dto->costPrice,
48:             'tax_inclusive' => $dto->taxInclusive,
49:             'track_inventory' => $dto->trackInventory,
50:             'low_stock_threshold' => $dto->lowStockThreshold,
51:             'weight' => $dto->dimensions['weight'] ?? null,
52:             'length' => $dto->dimensions['length'] ?? null,
53:             'width' => $dto->dimensions['width'] ?? null,
54:             'height' => $dto->dimensions['height'] ?? null,
55:             'metadata' => $dto->metadata ?? null,
56:             'published_at' => $dto->publishedAt,
57:         ]);
58: 
59:         if ($dto->categoryIds !== null) {
60:             $product->categories()->sync($dto->categoryIds);
61:         }
62: 
63:         if ($dto->attributeValues !== null) {
64:             $this->syncAttributeValues($product, $dto->attributeValues);
65:         }
66: 
67:         $this->events->dispatch(ProductCreated::fromModel($product));
68: 
69:         return $product;
70:     }
71: 
72:     public function updateProduct(Product $product, ProductDTO $dto): Product
73:     {
74:         $product->update([
75:             'category_id' => $dto->categoryId,
76:             'brand_id' => $dto->brandId,
77:             'tax_category_id' => $dto->taxCategoryId,
78:             'name' => $dto->name,
79:             'slug' => $dto->slug,
80:             'sku' => $dto->sku,
81:             'barcode' => $dto->barcode,
82:             'barcode_type' => $dto->barcodeType,
83:             'description' => $dto->description,
84:             'short_description' => $dto->shortDescription,
85:             'type' => $dto->type->value,
86:             'status' => $dto->status->value,
87:             'base_price' => $dto->basePrice,
88:             'compare_at_price' => $dto->compareAtPrice,
89:             'cost_price' => $dto->costPrice,
90:             'tax_inclusive' => $dto->taxInclusive,
91:             'track_inventory' => $dto->trackInventory,
92:             'low_stock_threshold' => $dto->lowStockThreshold,
93:             'weight' => $dto->dimensions['weight'] ?? null,
94:             'length' => $dto->dimensions['length'] ?? null,
95:             'width' => $dto->dimensions['width'] ?? null,
96:             'height' => $dto->dimensions['height'] ?? null,
97:             'published_at' => $dto->publishedAt,
98:         ]);
99: 
100:         if ($dto->categoryIds !== null) {
101:             $product->categories()->sync($dto->categoryIds);
102:         }
103: 
104:         $this->events->dispatch(ProductUpdated::fromModel($product));
105: 
106:         return $product->fresh();
107:     }
108: 
109:     public function deleteProduct(Product $product): bool
110:     {
111:         $id = $product->id;
112:         $sku = $product->sku;
113: 
114:         $product->delete();
115: 
116:         $this->events->dispatch(new ProductDeleted(
117:             productId: $id,
118:             sku: $sku,
119:         ));
120: 
121:         return true;
122:     }
123: 
124:     public function archiveProduct(Product $product): Product
125:     {
126:         $product->update(['status' => ProductStatusEnum::Archived]);
127: 
128:         $this->events->dispatch(ProductArchived::fromModel($product));
129: 
130:         return $product;
131:     }
132: 
133:     public function restoreProduct(Product $product): Product
134:     {
135:         $product->update(['status' => ProductStatusEnum::Draft]);
136: 
137:         return $product;
138:     }
139: 
140:     public function publishProduct(Product $product): Product
141:     {
142:         $product->update([
143:             'status' => ProductStatusEnum::Active,
144:             'published_at' => now(),
145:         ]);
146: 
147:         $this->events->dispatch(ProductPublished::fromModel($product));
148: 
149:         return $product;
150:     }
151: 
152:     public function duplicateProduct(Product $product): Product
153:     {
154:         $clone = $product->replicate()->fill([
155:             'name' => $product->name.' (Copy)',
156:             'slug' => $product->slug.'-copy',
157:             'status' => ProductStatusEnum::Draft,
158:             'published_at' => null,
159:         ]);
160: 
161:         $clone->push();
162: 
163:         foreach ($product->media as $media) {
164:             $clone->media()->create($media->replicate()->toArray());
165:         }
166: 
167:         foreach ($product->variants as $variant) {
168:             $clone->variants()->create($variant->replicate()->toArray());
169:         }
170: 
171:         $categoryIds = $product->categories()->pluck('categories.id')->toArray();
172:         $clone->categories()->sync($categoryIds);
173: 
174:         $this->events->dispatch(ProductCreated::fromModel($clone));
175: 
176:         return $clone;
177:     }
178: 
179:     public function listProducts(ProductSearchCriteria $criteria): LengthAwarePaginator
180:     {
181:         $query = Product::query()
182:             ->with(['category', 'brand', 'primaryMedia']);
183: 
184:         if ($criteria->search !== null) {
185:             $query->where(function (Builder $q) use ($criteria) {
186:                 $q->where('name', 'like', "%{$criteria->search}%")
187:                     ->orWhere('sku', 'like', "%{$criteria->search}%");
188:             });
189:         }
190: 
191:         if ($criteria->categoryId !== null) {
192:             $query->where('category_id', $criteria->categoryId);
193:         }
194: 
195:         if ($criteria->brandId !== null) {
196:             $query->where('brand_id', $criteria->brandId);
197:         }
198: 
199:         if ($criteria->status !== null) {
200:             $query->where('status', $criteria->status->value);
201:         }
202: 
203:         if ($criteria->type !== null) {
204:             $query->where('type', $criteria->type->value);
205:         }
206: 
207:         if ($criteria->minPrice !== null) {
208:             $query->where('base_price', '>=', $criteria->minPrice);
209:         }
210: 
211:         if ($criteria->maxPrice !== null) {
212:             $query->where('base_price', '<=', $criteria->maxPrice);
213:         }
214: 
215:         if ($criteria->sortBy !== null) {
216:             $query->orderBy($criteria->sortBy, $criteria->sortDirection);
217:         } else {
218:             $query->latest();
219:         }
220: 
221:         return $query->paginate($criteria->perPage, ['*'], 'page', $criteria->page);
222:     }
223: 
224:     public function getProductSummary(Product $product): ProductSummaryDTO
225:     {
226:         $product->loadMissing(['category', 'brand', 'media']);
227: 
228:         return ProductSummaryDTO::fromModel($product);
229:     }
230: 
231:     public function getProductWithStock(Product $product): ProductWithStockDTO
232:     {
233:         $product->loadMissing(['warehouseStock.warehouse']);
234: 
235:         $warehouseBreakdown = $product->warehouseStock->map(fn ($ws) => [
236:             'warehouse_id' => $ws->warehouse_id,
237:             'warehouse_name' => $ws->warehouse->name,
238:             'quantity' => $ws->quantity,
239:             'reserved_quantity' => $ws->reserved_quantity,
240:             'available_quantity' => $ws->getAvailableQuantity(),
241:         ])->toArray();
242: 
243:         return new ProductWithStockDTO(
244:             product: ProductDTO::fromModel($product),
245:             totalQuantity: $product->total_quantity,
246:             totalReserved: $product->total_reserved,
247:             totalAvailable: $product->total_available,
248:             warehouseBreakdown: $warehouseBreakdown,
249:         );
250:     }
251: 
252:     protected function syncAttributeValues(Product $product, array $attributeValues): void
253:     {
254:         $product->attributeValues()->delete();
255: 
256:         foreach ($attributeValues as $av) {
257:             $product->attributeValues()->create([
258:                 'attribute_id' => $av['attribute_id'],
259:                 'attribute_value_id' => $av['attribute_value_id'] ?? null,
260:             ]);
261:         }
262:     }
263: }

(End of file - total 263 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Modules/Product/Http/Requests/StoreProductRequest.php"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Modules/Product/Http/Requests/StoreProductRequest.php</path>
<type>file</type>
<content>
1: <?php
2: 
3: declare(strict_types=1);
4: 
5: namespace App\Modules\Product\Http\Requests;
6: 
7: use Illuminate\Foundation\Http\FormRequest;
8: 
9: class StoreProductRequest extends FormRequest
10: {
11:     public function rules(): array
12:     {
13:         return [
14:             'name' => ['required', 'string', 'max:500'],
15:             'slug' => ['nullable', 'string', 'max:500', 'alpha_dash', 'unique:products,slug'],
16:             'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
17:             'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
18:             'barcode_type' => ['nullable', 'string', 'in:ean13,upc,code128,qr'],
19:             'category_id' => ['nullable', 'integer', 'exists:categories,id'],
20:             'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
21:             'tax_category_id' => ['nullable', 'integer', 'exists:tax_categories,id'],
22:             'description' => ['nullable', 'string'],
23:             'short_description' => ['nullable', 'string', 'max:500'],
24:             'type' => ['required', 'string', 'in:simple,configurable,bundle,virtual'],
25:             'status' => ['required', 'string', 'in:draft,active,archived'],
26:             'base_price' => ['required', 'integer', 'min:0'],
27:             'compare_at_price' => ['nullable', 'integer', 'min:0'],
28:             'cost_price' => ['nullable', 'integer', 'min:0'],
29:             'tax_inclusive' => ['boolean'],
30:             'track_inventory' => ['boolean'],
31:             'low_stock_threshold' => ['integer', 'min:0'],
32:             'weight' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
33:             'dimensions' => ['nullable', 'array'],
34:             'category_ids' => ['nullable', 'array'],
35:             'category_ids.*' => ['integer', 'exists:categories,id'],
36:             'attribute_values' => ['nullable', 'array'],
37:             'published_at' => ['nullable', 'date'],
38:             'metadata' => ['nullable', 'array'],
39:         ];
40:     }
41: 
42:     public function messages(): array
43:     {
44:         return [
45:             'sku.unique' => 'This SKU is already in use.',
46:             'base_price.min' => 'Price must be a positive amount.',
47:         ];
48:     }
49: }

(End of file - total 49 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Modules/Product/Http/Controllers/ProductController.php"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Modules/Product/Http/Controllers/ProductController.php</path>
<type>file</type>
<content>
1: <?php
2: 
3: declare(strict_types=1);
4: 
5: namespace App\Modules\Product\Http\Controllers;
6: 
7: use App\Modules\Product\DTOs\ProductDTO;
8: use App\Modules\Product\Http\Requests\StoreProductRequest;
9: use App\Modules\Product\Http\Requests\UpdateProductRequest;
10: use App\Modules\Product\Models\Product;
11: use App\Modules\Product\Services\BrandService;
12: use App\Modules\Product\Services\CategoryService;
13: use App\Modules\Product\Services\ProductService;
14: use App\Modules\Product\ValueObjects\ProductSearchCriteria;
15: use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
16: use Illuminate\Http\RedirectResponse;
17: use Illuminate\Http\Request;
18: use Inertia\Inertia;
19: use Inertia\Response;
20: 
21: class ProductController
22: {
23:     use AuthorizesRequests;
24: 
25:     public function __construct(
26:         protected ProductService $productService,
27:         protected CategoryService $categoryService,
28:         protected BrandService $brandService,
29:     ) {}
30: 
31:     public function index(Request $request): Response
32:     {
33:         $criteria = ProductSearchCriteria::fromRequest($request->all());
34:         $products = $this->productService->listProducts($criteria);
35: 
36:         return Inertia::render('Product/Index', [
37:             'products' => $products,
38:             'filters' => $criteria->toQueryParams(),
39:         ]);
40:     }
41: 
42:     public function create(): Response
43:     {
44:         return Inertia::render('Product/Create', [
45:             'categories' => $this->categoryService->getCategoryTree(),
46:             'brands' => $this->brandService->listActiveBrands(),
47:         ]);
48:     }
49: 
50:     public function store(StoreProductRequest $request): RedirectResponse
51:     {
52:         $this->authorize('create', Product::class);
53: 
54:         $dto = ProductDTO::fromRequest($request->validated());
55:         $product = $this->productService->createProduct($dto);
56: 
57:         return redirect()->route('products.index')
58:             ->with('success', 'Product created successfully.');
59:     }
60: 
61:     public function show(Product $product): Response
62:     {
63:         $product->loadMissing(['category', 'brand', 'variants', 'media', 'warehouseStock.warehouse']);
64: 
65:         return Inertia::render('Product/Show', [
66:             'product' => $product,
67:         ]);
68:     }
69: 
70:     public function edit(Product $product): Response
71:     {
72:         $product->loadMissing(['category', 'brand', 'variants', 'media', 'categories']);
73: 
74:         return Inertia::render('Product/Edit', [
75:             'product' => $product,
76:             'categories' => $this->categoryService->getCategoryTree(),
77:             'brands' => $this->brandService->listActiveBrands(),
78:         ]);
79:     }
80: 
81:     public function update(UpdateProductRequest $request, Product $product): RedirectResponse
82:     {
83:         $this->authorize('update', $product);
84: 
85:         $dto = ProductDTO::fromRequest($request->validated());
86:         $this->productService->updateProduct($product, $dto);
87: 
88:         return redirect()->route('products.show', $product)
89:             ->with('success', 'Product updated successfully.');
90:     }
91: 
92:     public function destroy(Product $product): RedirectResponse
93:     {
94:         $this->authorize('delete', $product);
95: 
96:         $this->productService->deleteProduct($product);
97: 
98:         return redirect()->route('products.index')
99:             ->with('success', 'Product deleted successfully.');
100:     }
101: 
102:     public function archive(Product $product): RedirectResponse
103:     {
104:         $this->productService->archiveProduct($product);
105: 
106:         return redirect()->route('products.index')
107:             ->with('success', 'Product archived successfully.');
108:     }
109: 
110:     public function restore(Product $product): RedirectResponse
111:     {
112:         $this->productService->restoreProduct($product);
113: 
114:         return redirect()->route('products.show', $product)
115:             ->with('success', 'Product restored successfully.');
116:     }
117: 
118:     public function publish(Product $product): RedirectResponse
119:     {
120:         $this->productService->publishProduct($product);
121: 
122:         return redirect()->route('products.show', $product)
123:             ->with('success', 'Product published successfully.');
124:     }
125: 
126:     public function duplicate(Product $product): RedirectResponse
127:     {
128:         $clone = $this->productService->duplicateProduct($product);
129: 
130:         return redirect()->route('products.edit', $clone)
131:             ->with('success', 'Product duplicated successfully.');
132:     }
133: }

(End of file - total 133 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Providers/ProductServiceProvider.php"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Providers/ProductServiceProvider.php</path>
<type>file</type>
<content>
1: <?php
2: 
3: declare(strict_types=1);
4: 
5: namespace App\Providers;
6: 
7: use App\Modules\Product\Contracts\PricingCalculator;
8: use App\Modules\Product\Contracts\ProductCatalogService;
9: use App\Modules\Product\Contracts\ProductResolver;
10: use App\Modules\Product\Contracts\SKUGenerator;
11: use App\Modules\Product\Contracts\StockAllocator;
12: use App\Modules\Product\Contracts\StockChecker;
13: use App\Modules\Product\Events\LowStockAlert;
14: use App\Modules\Product\Events\ProductCreated;
15: use App\Modules\Product\Events\ProductDeleted;
16: use App\Modules\Product\Events\ProductUpdated;
17: use App\Modules\Product\Events\StockDepleted;
18: use App\Modules\Product\Events\StockReservationCreated;
19: use App\Modules\Product\Events\StockReservationExpired;
20: use App\Modules\Product\Events\StockUpdated;
21: use App\Modules\Product\Listeners\GenerateProductSKU;
22: use App\Modules\Product\Listeners\IndexProductForSearch;
23: use App\Modules\Product\Listeners\MarkProductUnavailable;
24: use App\Modules\Product\Listeners\ReleaseExpiredStock;
25: use App\Modules\Product\Listeners\RemoveProductFromSearchIndex;
26: use App\Modules\Product\Listeners\SendLowStockNotification;
27: use App\Modules\Product\Listeners\SendStockDepletedNotification;
28: use App\Modules\Product\Listeners\TrackStockReservation;
29: use App\Modules\Product\Listeners\UpdateProductSearchIndex;
30: use App\Modules\Product\Listeners\UpdateProductStockCache;
31: use App\Modules\Product\Models\Brand;
32: use App\Modules\Product\Models\Category;
33: use App\Modules\Product\Models\Product;
34: use App\Modules\Product\Models\StockReservation;
35: use App\Modules\Product\Models\Variant;
36: use App\Modules\Product\Models\WarehouseStock;
37: use App\Modules\Product\Observers\ProductObserver;
38: use App\Modules\Product\Observers\StockReservationObserver;
39: use App\Modules\Product\Observers\VariantObserver;
40: use App\Modules\Product\Observers\WarehouseStockObserver;
41: use App\Modules\Product\Policies\BrandPolicy;
42: use App\Modules\Product\Policies\CategoryPolicy;
43: use App\Modules\Product\Policies\ProductPolicy;
44: use App\Modules\Product\Policies\WarehousePolicy;
45: use App\Modules\Product\Services\AttributeService;
46: use App\Modules\Product\Services\BrandService;
47: use App\Modules\Product\Services\CategoryService;
48: use App\Modules\Product\Services\DefaultSKUGenerator;
49: use App\Modules\Product\Services\DefaultStockAllocator;
50: use App\Modules\Product\Services\EloquentPricingCalculator;
51: use App\Modules\Product\Services\EloquentProductCatalogService;
52: use App\Modules\Product\Services\EloquentProductResolver;
53: use App\Modules\Product\Services\EloquentStockChecker;
54: use App\Modules\Product\Services\MediaService;
55: use App\Modules\Product\Services\PricingRuleService;
56: use App\Modules\Product\Services\ProductImportService;
57: use App\Modules\Product\Services\ProductService;
58: use App\Modules\Product\Services\StockAuditService;
59: use App\Modules\Product\Services\StockLockService;
60: use App\Modules\Product\Services\StockReservationService;
61: use App\Modules\Product\Services\StockService;
62: use App\Modules\Product\Services\TaxService;
63: use App\Modules\Product\Services\VariantService;
64: use App\Modules\Product\Services\WarehouseService;
65: use Illuminate\Support\Facades\Event;
66: use Illuminate\Support\Facades\Gate;
67: use Illuminate\Support\ServiceProvider;
68: 
69: class ProductServiceProvider extends ServiceProvider
70: {
71:     public function register(): void
72:     {
73:         $this->app->singleton(ProductService::class);
74:         $this->app->singleton(CategoryService::class);
75:         $this->app->singleton(BrandService::class);
76:         $this->app->singleton(VariantService::class);
77:         $this->app->singleton(AttributeService::class);
78:         $this->app->singleton(MediaService::class);
79:         $this->app->singleton(WarehouseService::class);
80:         $this->app->singleton(StockLockService::class);
81:         $this->app->singleton(StockAuditService::class);
82:         $this->app->singleton(StockService::class);
83:         $this->app->singleton(StockReservationService::class);
84:         $this->app->singleton(TaxService::class);
85:         $this->app->singleton(PricingRuleService::class);
86:         $this->app->singleton(ProductImportService::class);
87: 
88:         $this->app->bind(ProductResolver::class, EloquentProductResolver::class);
89:         $this->app->bind(StockChecker::class, EloquentStockChecker::class);
90:         $this->app->bind(PricingCalculator::class, EloquentPricingCalculator::class);
91:         $this->app->bind(SKUGenerator::class, DefaultSKUGenerator::class);
92:         $this->app->bind(StockAllocator::class, DefaultStockAllocator::class);
93:         $this->app->bind(ProductCatalogService::class, EloquentProductCatalogService::class);
94:     }
95: 
96:     public function boot(): void
97:     {
98:         Gate::policy(Product::class, ProductPolicy::class);
99:         Gate::policy(Category::class, CategoryPolicy::class);
100:         Gate::policy(Brand::class, BrandPolicy::class);
101:         Gate::policy(Warehouse::class, WarehousePolicy::class);
102: 
103:         Product::observe(ProductObserver::class);
104:         Variant::observe(VariantObserver::class);
105:         WarehouseStock::observe(WarehouseStockObserver::class);
106:         StockReservation::observe(StockReservationObserver::class);
107: 
108:         Event::listen(ProductCreated::class, IndexProductForSearch::class);
109:         Event::listen(ProductCreated::class, GenerateProductSKU::class);
110:         Event::listen(ProductUpdated::class, UpdateProductSearchIndex::class);
111:         Event::listen(ProductDeleted::class, RemoveProductFromSearchIndex::class);
112:         Event::listen(StockUpdated::class, UpdateProductStockCache::class);
113:         Event::listen(StockDepleted::class, MarkProductUnavailable::class);
114:         Event::listen(StockDepleted::class, SendStockDepletedNotification::class);
115:         Event::listen(LowStockAlert::class, SendLowStockNotification::class);
116:         Event::listen(StockReservationCreated::class, TrackStockReservation::class);
117:         Event::listen(StockReservationExpired::class, ReleaseExpiredStock::class);
118:     }
119: }

(End of file - total 119 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/routes/tenant.php"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/routes/tenant.php</path>
<type>file</type>
<content>
1: <?php
2: 
3: declare(strict_types=1);
4: 
5: use App\Http\Controllers\BillingController;
6: use App\Http\Controllers\TaskController;
7: use App\Http\Controllers\TeamController;
8: use App\Http\Middleware\InitializeTenancyByUser;
9: use App\Modules\Product\Http\Controllers\AttributeController;
10: use App\Modules\Product\Http\Controllers\BrandController;
11: use App\Modules\Product\Http\Controllers\CategoryController;
12: use App\Modules\Product\Http\Controllers\ProductController;
13: use App\Modules\Product\Http\Controllers\StockController;
14: use Illuminate\Support\Facades\Route;
15: use Inertia\Inertia;
16: 
17: /*
18: |--------------------------------------------------------------------------
19: | Tenant Routes
20: |--------------------------------------------------------------------------
21: |
22: | Routes that require an authenticated user with an active tenant.
23: | The InitializeTenancyByUser middleware sets the tenant context
24: | based on the authenticated user's tenant_id.
25: |
26: */
27: 
28: Route::middleware(['web', 'auth', InitializeTenancyByUser::class])->group(function () {
29:     Route::middleware('subscription')->group(function () {
30:         Route::get('/dashboard', function () {
31:             return Inertia::render('dashboard');
32:         })->name('dashboard');
33: 
34:         Route::resource('tasks', TaskController::class)
35:             ->only(['index', 'store', 'update', 'destroy']);
36: 
37:         // Product sub-resources — must be before products resource to prevent 405
38:         Route::prefix('products')->group(function () {
39:             // Categories
40:             Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
41:             Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
42:             Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
43:             Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
44:             Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
45:             Route::post('/categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
46: 
47:             // Brands
48:             Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
49:             Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
50:             Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
51:             Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
52: 
53:             // Attributes
54:             Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
55:             Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store');
56:             Route::put('/attributes/{attribute}', [AttributeController::class, 'update'])->name('attributes.update');
57:             Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy'])->name('attributes.destroy');
58:             Route::post('/attributes/{attribute}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
59:             Route::put('/attributes/values/{value}', [AttributeController::class, 'updateValue'])->name('attributes.values.update');
60:             Route::delete('/attributes/values/{value}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');
61: 
62:             // Inventory
63:             Route::get('/inventory', [StockController::class, 'lowStock'])->name('inventory.index');
64: 
65:             // Stock Transfers
66:             Route::get('/stock-transfers', [StockController::class, 'movements'])->name('stock-transfers.index');
67:             Route::post('/stock-transfers', [StockController::class, 'transfer'])->name('stock-transfers.transfer');
68: 
69:             // Stock movements (redirect target for receive/deduct/adjust)
70:             Route::get('/movements', [StockController::class, 'movements'])->name('stock.movements');
71:         });
72: 
73:         Route::resource('products', ProductController::class)
74:             ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
75: 
76:         Route::get('/team', [TeamController::class, 'index'])->name('team.index');
77:         Route::post('/team/invite', [TeamController::class, 'invite'])->name('team.invite')->middleware('seat');
78:         Route::post('/team/accept/{token}', [TeamController::class, 'accept'])->name('team.accept');
79:         Route::delete('/team/{allocation}', [TeamController::class, 'destroy'])->name('team.destroy');
80:         Route::post('/team/{allocation}/resend', [TeamController::class, 'resend'])->name('team.resend');
81:     });
82: });
83: 
84: // SSLCommerz callback - no auth required (external POST from payment gateway)
85: Route::post('/billing/success/sslcommerz', [BillingController::class, 'sslcommerzSuccess'])->name('billing.success.sslcommerz');
86: Route::post('/billing/webhook/sslcommerz', [BillingController::class, 'sslcommerzWebhook'])->name('billing.webhook.sslcommerz');

(End of file - total 86 lines)
</content>
```

---

## Assistant (Plan · DeepSeek V4 Flash Free · 4.0s)

_Thinking:_

Now let me also look at the existing frontend product form to understand how forms are built.

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/resources/js/modules/product/components/product-form-page.tsx"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/resources/js/modules/product/components/product-form-page.tsx</path>
<type>file</type>
<content>
1: import { useMemo, useState } from 'react';
2: import { useWatch } from 'react-hook-form';
3: import { ProductGeneralSection } from './product-general-section';
4: import { ProductPricingSection } from './product-pricing-section';
5: import { ProductInventorySection } from './product-inventory-section';
6: import { ProductShippingSection } from './product-shipping-section';
7: import { ProductSeoSection } from './product-seo-section';
8: import { ProductImageUpload } from './product-image-upload';
9: import { VariantGrid } from './variant-grid';
10: import { AttributeManager } from './attribute-manager';
11: import { PageHeader } from '@/modules/shared/components/page-header';
12: import { FormActions } from '@/modules/shared/components/form-actions';
13: import { useProductForm } from '../hooks/use-product-form';
14: import type { ProductFormData } from '../types/product-form';
15: import type { VariantGroup, VariantRowFormData, AttributeFormData } from '../types/variant';
16: 
17: type Props = {
18:     mode: 'create' | 'edit';
19:     initialData?: Partial<ProductFormData>;
20:     submitRoute: string;
21:     method?: 'post' | 'put';
22:     categories: { id: string; name: string; parent_id: string | null }[];
23:     brands: { id: string; name: string }[];
24:     onCancel?: () => void;
25:     onSuccess?: () => void;
26: };
27: 
28: export function ProductFormPage({
29:     mode,
30:     initialData,
31:     submitRoute,
32:     method = 'post',
33:     categories,
34:     brands,
35:     onCancel,
36:     onSuccess,
37: }: Props) {
38:     const { form, errors, processing, onChange, handleSubmit } = useProductForm({
39:         initialData,
40:         route: submitRoute,
41:         method,
42:         onSuccess,
43:     });
44: 
45:     const images = useWatch({ control: form.control, name: 'images' });
46:     const variants = useWatch({ control: form.control, name: 'variants' });
47:     const variantGroups = useWatch({ control: form.control, name: 'variantGroups' });
48:     const attributes = useWatch({ control: form.control, name: 'attributes' });
49:     const sku = useWatch({ control: form.control, name: 'sku' });
50: 
51:     const [shippingOpen, setShippingOpen] = useState(false);
52:     const [seoOpen, setSeoOpen] = useState(false);
53: 
54:     const nestedOnChange = useMemo(
55:         () => ({
56:             general: <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) =>
57:                 onChange(field, value),
58:             pricing: <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) =>
59:                 onChange(field, value),
60:             inventory: <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) =>
61:                 onChange(field, value),
62:             shipping: <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) =>
63:                 onChange(field, value),
64:             seo: <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) =>
65:                 onChange(field, value),
66:         }),
67:         [onChange],
68:     );
69: 
70:     return (
71:         <form onSubmit={handleSubmit} noValidate>
72:             <PageHeader
73:                 title={mode === 'create' ? 'Create Product' : 'Edit Product'}
74:                 description={mode === 'create' ? 'Add a new product to your catalog' : 'Update product details'}
75:             />
76: 
77:             <div className="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
78:                 <ProductGeneralSection
79:                     form={form}
80:                     categories={categories}
81:                     brands={brands}
82:                     errors={errors}
83:                     onChange={nestedOnChange.general}
84:                 />
85: 
86:                 <ProductPricingSection
87:                     form={form}
88:                     errors={errors}
89:                     onChange={nestedOnChange.pricing}
90:                 />
91: 
92:                 <ProductInventorySection
93:                     form={form}
94:                     errors={errors}
95:                     onChange={nestedOnChange.inventory}
96:                 />
97: 
98:                 <ProductImageUpload
99:                     images={images ?? []}
100:                     errors={errors}
101:                     onChange={onChange}
102:                 />
103: 
104:                 <VariantGrid
105:                     variants={(variants ?? []) as VariantRowFormData[]}
106:                     variantGroups={(variantGroups ?? []) as VariantGroup[]}
107:                     parentSku={sku}
108:                     productImages={images}
109:                     errors={errors}
110:                     onChange={onChange as (field: 'variants', value: VariantRowFormData[]) => void}
111:                     onGroupsChange={onChange as (field: 'variantGroups', value: VariantGroup[]) => void}
112:                 />
113: 
114:                 <AttributeManager
115:                     attributes={(attributes ?? []) as AttributeFormData[]}
116:                     errors={errors}
117:                     onChange={onChange as (field: 'attributes', value: AttributeFormData[]) => void}
118:                 />
119: 
120:                 <ProductShippingSection
121:                     form={form}
122:                     open={shippingOpen}
123:                     onToggle={() => setShippingOpen((p) => !p)}
124:                     errors={errors}
125:                     onChange={nestedOnChange.shipping}
126:                 />
127: 
128:                 <ProductSeoSection
129:                     form={form}
130:                     open={seoOpen}
131:                     onToggle={() => setSeoOpen((p) => !p)}
132:                     errors={errors}
133:                     onChange={nestedOnChange.seo}
134:                 />
135:             </div>
136: 
137:             <FormActions
138:                 onCancel={onCancel}
139:                 submitLabel={mode === 'create' ? 'Create Product' : 'Save Changes'}
140:                 processing={processing}
141:             />
142:         </form>
143:     );
144: }

(End of file - total 144 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/resources/js/modules/product/types/product-form.ts"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/resources/js/modules/product/types/product-form.ts</path>
<type>file</type>
<content>
1: import { z } from 'zod';
2: import { variantRowSchema, attributeSchema } from './variant';
3: 
4: export const productFormSchema = z.object({
5:     name: z.string().min(1, 'Product name is required').max(255, 'Name is too long'),
6:     description: z.string().optional(),
7:     status: z.enum(['draft', 'active']),
8:     categoryId: z.string().nullable().optional(),
9:     brandId: z.string().nullable().optional(),
10: 
11:     price: z.coerce.number().positive('Price must be greater than 0'),
12:     comparePrice: z.coerce.number().optional().nullable(),
13:     costPrice: z.coerce.number().optional().nullable(),
14:     isTaxable: z.boolean().default(true),
15: 
16:     sku: z.string().min(1, 'SKU is required').max(100),
17:     barcode: z.string().optional().nullable(),
18:     trackStock: z.boolean().default(true),
19:     quantity: z.coerce.number().int().min(0).default(0),
20:     lowStockThreshold: z.coerce.number().int().min(0).default(5),
21:     allowBackorders: z.boolean().default(false),
22: 
23:     weight: z.coerce.number().optional().nullable(),
24:     length: z.coerce.number().optional().nullable(),
25:     width: z.coerce.number().optional().nullable(),
26:     height: z.coerce.number().optional().nullable(),
27:     freeShipping: z.boolean().default(false),
28: 
29:     metaTitle: z.string().max(70).optional().nullable(),
30:     metaDescription: z.string().max(320).optional().nullable(),
31:     slug: z.string().optional().nullable(),
32: 
33:     images: z.array(z.any()).optional().default([]),
34:     variants: z.array(variantRowSchema).optional().default([]),
35:     variantGroups: z
36:         .array(
37:             z.object({
38:                 attributeId: z.string(),
39:                 attributeName: z.string(),
40:                 values: z.array(z.string()),
41:             }),
42:         )
43:         .optional()
44:         .default([]),
45:     attributes: z.array(attributeSchema).optional().default([]),
46: });
47: 
48: export type ProductFormData = z.input<typeof productFormSchema>;
49: export type ProductFormOutput = z.output<typeof productFormSchema>;
50: 
51: export const defaultProductFormValues: ProductFormData = {
52:     name: '',
53:     description: '',
54:     status: 'draft',
55:     categoryId: null,
56:     brandId: null,
57:     price: 0,
58:     comparePrice: null,
59:     costPrice: null,
60:     isTaxable: true,
61:     sku: '',
62:     barcode: null,
63:     trackStock: true,
64:     quantity: 0,
65:     lowStockThreshold: 5,
66:     allowBackorders: false,
67:     weight: null,
68:     length: null,
69:     width: null,
70:     height: null,
71:     freeShipping: false,
72:     metaTitle: null,
73:     metaDescription: null,
74:     slug: null,
75:     images: [],
76:     variants: [],
77:     variantGroups: [],
78:     attributes: [],
79: };

(End of file - total 79 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Modules/Product/DTOs/ProductDTO.php"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Modules/Product/DTOs/ProductDTO.php</path>
<type>file</type>
<content>
1: <?php
2: 
3: declare(strict_types=1);
4: 
5: namespace App\Modules\Product\DTOs;
6: 
7: use App\Modules\Product\Enums\ProductStatusEnum;
8: use App\Modules\Product\Enums\ProductTypeEnum;
9: use App\Modules\Product\Models\Product;
10: use Carbon\CarbonImmutable;
11: use Illuminate\Support\Carbon;
12: 
13: readonly class ProductDTO
14: {
15:     public function __construct(
16:         public ?string $id,
17:         public ?int $categoryId,
18:         public ?int $brandId,
19:         public ?int $taxCategoryId,
20:         public string $name,
21:         public string $slug,
22:         public ?string $sku,
23:         public ?string $barcode,
24:         public ?string $barcodeType,
25:         public ?string $description,
26:         public ?string $shortDescription,
27:         public ProductTypeEnum $type,
28:         public ProductStatusEnum $status,
29:         public int $basePrice,
30:         public ?int $compareAtPrice,
31:         public ?int $costPrice,
32:         public bool $taxInclusive,
33:         public bool $trackInventory,
34:         public int $lowStockThreshold,
35:         public ?array $dimensions,
36:         public ?array $categoryIds,
37:         public ?array $attributeValues,
38:         public ?array $metadata,
39:         public ?CarbonImmutable $publishedAt,
40:     ) {}
41: 
42:     public static function fromModel(Product $product): self
43:     {
44:         return new self(
45:             id: $product->id,
46:             categoryId: $product->category_id,
47:             brandId: $product->brand_id,
48:             taxCategoryId: $product->tax_category_id,
49:             name: $product->name,
50:             slug: $product->slug,
51:             sku: $product->sku,
52:             barcode: $product->barcode,
53:             barcodeType: $product->barcode_type,
54:             description: $product->description,
55:             shortDescription: $product->short_description,
56:             type: $product->type instanceof ProductTypeEnum
57:                 ? $product->type
58:                 : ProductTypeEnum::from($product->type),
59:             status: $product->status instanceof ProductStatusEnum
60:                 ? $product->status
61:                 : ProductStatusEnum::from($product->status),
62:             basePrice: $product->base_price,
63:             compareAtPrice: $product->compare_at_price,
64:             costPrice: $product->cost_price,
65:             taxInclusive: $product->tax_inclusive ?? false,
66:             trackInventory: $product->track_inventory ?? true,
67:             lowStockThreshold: $product->low_stock_threshold,
68:             dimensions: $product->weight !== null ? [
69:                 'weight' => $product->weight,
70:                 'length' => $product->length,
71:                 'width' => $product->width,
72:                 'height' => $product->height,
73:             ] : null,
74:             categoryIds: $product->relationLoaded('categories')
75:                 ? $product->categories->pluck('id')->toArray()
76:                 : null,
77:             attributeValues: $product->relationLoaded('attributeValues')
78:                 ? $product->attributeValues->toArray()
79:                 : null,
80:             metadata: $product->metadata,
81:             publishedAt: $product->published_at instanceof Carbon
82:                 ? CarbonImmutable::instance($product->published_at)
83:                 : null,
84:         );
85:     }
86: 
87:     public static function fromRequest(array $data): self
88:     {
89:         return new self(
90:             id: null,
91:             categoryId: $data['category_id'] ?? null,
92:             brandId: $data['brand_id'] ?? null,
93:             taxCategoryId: $data['tax_category_id'] ?? null,
94:             name: $data['name'],
95:             slug: $data['slug'] ?? '',
96:             sku: $data['sku'] ?? null,
97:             barcode: $data['barcode'] ?? null,
98:             barcodeType: $data['barcode_type'] ?? null,
99:             description: $data['description'] ?? null,
100:             shortDescription: $data['short_description'] ?? null,
101:             type: ProductTypeEnum::from($data['type']),
102:             status: ProductStatusEnum::from($data['status']),
103:             basePrice: (int) $data['base_price'],
104:             compareAtPrice: isset($data['compare_at_price']) ? (int) $data['compare_at_price'] : null,
105:             costPrice: isset($data['cost_price']) ? (int) $data['cost_price'] : null,
106:             taxInclusive: (bool) ($data['tax_inclusive'] ?? false),
107:             trackInventory: (bool) ($data['track_inventory'] ?? true),
108:             lowStockThreshold: (int) ($data['low_stock_threshold'] ?? 5),
109:             dimensions: $data['dimensions'] ?? null,
110:             categoryIds: $data['category_ids'] ?? null,
111:             attributeValues: $data['attribute_values'] ?? null,
112:             metadata: $data['metadata'] ?? null,
113:             publishedAt: isset($data['published_at']) ? CarbonImmutable::parse($data['published_at']) : null,
114:         );
115:     }
116: }

(End of file - total 116 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/resources/js/modules/product/hooks/use-product-form.ts"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/resources/js/modules/product/hooks/use-product-form.ts</path>
<type>file</type>
<content>
1: import { useCallback, useMemo, useState } from 'react';
2: import { useForm } from 'react-hook-form';
3: import { zodResolver } from '@hookform/resolvers/zod';
4: import { router } from '@inertiajs/react';
5: import { mapFormToPayload } from '../lib/map-product';
6: import {
7:     productFormSchema,
8:     defaultProductFormValues,
9:     type ProductFormData,
10:     type ProductFormOutput,
11: } from '../types/product-form';
12: 
13: type FormErrors = Partial<Record<string, string>>;
14: 
15: type Options = {
16:     initialData?: Partial<ProductFormData>;
17:     route: string;
18:     method?: 'post' | 'put';
19:     onSuccess?: () => void;
20:     onError?: (errors: FormErrors) => void;
21: };
22: 
23: export function useProductForm({ initialData, route, method = 'post', onSuccess, onError }: Options) {
24:     const [processing, setProcessing] = useState(false);
25:     const [serverErrors, setServerErrors] = useState<FormErrors>({});
26: 
27:     const form = useForm<ProductFormData>({
28:         resolver: zodResolver(productFormSchema),
29:         defaultValues: { ...defaultProductFormValues, ...initialData },
30:     });
31: 
32:     const errors = useMemo<FormErrors>(() => {
33:         const merged: FormErrors = { ...serverErrors };
34: 
35:         for (const [key, value] of Object.entries(form.formState.errors)) {
36:             if (value?.message) {
37:                 merged[key] = value.message;
38:             }
39:         }
40: 
41:         return merged;
42:     }, [serverErrors, form.formState.errors]);
43: 
44:     const onChange = useCallback(
45:         <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) => {
46:             form.setValue(field, value, { shouldValidate: false, shouldDirty: true });
47:             setServerErrors((prev) => {
48:                 const next = { ...prev };
49:                 delete next[field];
50:                 return next;
51:             });
52:         },
53:         [form],
54:     );
55: 
56:     const handleSubmit = useCallback(
57:         (e?: React.BaseSyntheticEvent) => {
58:             e?.preventDefault();
59: 
60:             form.handleSubmit(
61:                 (data: ProductFormOutput) => {
62:                     setProcessing(true);
63:                     setServerErrors({});
64: 
65:                     const payload = mapFormToPayload(data as ProductFormData);
66: 
67:                     const options = {
68:                         preserveScroll: true,
69:                         onSuccess: () => {
70:                             setProcessing(false);
71:                             onSuccess?.();
72:                         },
73:                         onError: (inertiaErrors: Record<string, string>) => {
74:                             setProcessing(false);
75:                             const mapped = mapServerErrors(inertiaErrors);
76:                             setServerErrors(mapped);
77:                             onError?.(mapped);
78:                         },
79:                         onFinish: () => {
80:                             setProcessing(false);
81:                         },
82:                     };
83: 
84:                     if (method === 'put') {
85:                         router.put(route, payload, options);
86:                     } else {
87:                         router.post(route, payload, options);
88:                     }
89:                 },
90:                 () => {},
91:             )(e);
92:         },
93:         [form, route, method, onSuccess, onError],
94:     );
95: 
96:     const resetForm = useCallback(
97:         (data?: Partial<ProductFormData>) => {
98:             form.reset({ ...defaultProductFormValues, ...data });
99:             setServerErrors({});
100:         },
101:         [form],
102:     );
103: 
104:     return {
105:         form,
106:         errors,
107:         processing,
108:         onChange,
109:         handleSubmit,
110:         resetForm,
111:     };
112: }
113: 
114: function mapServerErrors(inertiaErrors: Record<string, string>): FormErrors {
115:     const mapped: FormErrors = {};
116: 
117:     for (const [key, message] of Object.entries(inertiaErrors)) {
118:         const cleanKey = key
119:             .replace(/\.\d+\./, '.')
120:             .replace(/\.\d+$/, '');
121:         mapped[cleanKey] = message;
122:     }
123: 
124:     return mapped;
125: }

(End of file - total 125 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/docs/architectures/product-module-design.md"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/docs/architectures/product-module-design.md</path>
<type>file</type>
<content>
1: # Product Management Module — Multi-Tenant Design
2: 
3: ## Overview
4: 
5: The Product Management module is a self-contained bounded context within the modular monolith. All product data lives in **tenant-isolated databases**, not the central database.
6: 
7: ## Tenant Database Isolation — Verified
8: 
9: | Concern | Current State | Location |
10: |---------|--------------|----------|
11: | Product migrations | ✅ Tenant DB migrations | `app/Modules/Product/Database/Migrations/Tenant/` |
12: | Product models | ✅ Standard `Model` (no `CentralConnection` trait) | `app/Modules/Product/Models/` |
13: | Provider registration | ✅ Loads tenant migrations via `$this->loadMigrationsFrom(...)` | `app/Providers/ProductServiceProvider.php:99` |
14: | Routes | ✅ Behind `InitializeTenancyByUser` + `subscription` middleware | `routes/tenant.php` |
15: | Central product tables | ✅ None exist | — |
16: 
17: The `ProductServiceProvider` registers:
18: ```php
19: // ProductServiceProvider.php:98-100
20: $this->loadMigrationsFrom(__DIR__.'/../Modules/Product/Database/Migrations/Tenant');
21: ```
22: 
23: Models (e.g., `Product`, `Category`, `Brand`, `Variant`) extend `Model` directly — no `CentralConnection` trait — so they automatically use the tenant DB connection when tenancy is initialized.
24: 
25: ## Data Model
26: 
27: ```
28: ┌──────────────────────────────────────────────────────────────┐
29: │                    Tenant DB (souda_tenant_{uuid})            │
30: │                                                              │
31: │  ┌──────────┐    ┌──────────────┐    ┌──────────────────┐   │
32: │  │ Category │◄───│ category_    │───►│     Product      │   │
33: │  │          │    │ product      │    │                  │   │
34: │  │  • id    │    └──────────────┘    │  • ulid (PK)     │   │
35: │  │  • name  │                        │  • category_id   │   │
36: │  │  • slug  │    ┌──────────────┐    │  • brand_id      │   │
37: │  │  • depth │───►│    Brand     │    │  • name, slug    │   │
38: │  └──────────┘    │              │    │  • sku, barcode  │   │
39: │                  │  • id        │    │  • base_price    │   │
40: │  ┌──────────┐    │  • name      │    │  • status, type  │   │
41: │  │ Variant  │    │  • website   │    │  • total_{qty,   │   │
42: │  │          │    └──────────────┘    │    reserved,     │   │
43: │  │  • id    │                        │    available}    │   │
44: │  │  • sku   │    ┌──────────────┐    │  • dimensions    │   │
45: │  │  • price │───►│ ProductMedia │    │  • published_at  │   │
46: │  │  • stock │    │              │    └──────┬───────────┘   │
47: │  │  • image │    │  • id        │           │              │
48: │  └──────────┘    │  • url       │           │              │
49: │                  │  • sort_order│           │              │
50: │  ┌──────────┐    └──────────────┘           │              │
51: │  │Attribute │                               │              │
52: │  │  • id    │    ┌─────────────────┐        │              │
53: │  │  • name  │    │ WarehouseStock  │◄───────┘              │
54: │  │  • type  │    │                 │                       │
55: │  └────┬─────┘    │  • warehouse_id │    ┌──────────────┐   │
56: │       │          │  • product_id   │    │  Warehouse   │   │
57: │  ┌────▼─────┐    │  • quantity     │    │              │   │
58: │  │Attribute │    │  • reserved     │    │  • id        │   │
59: │  │ Value    │    │  • available    │    │  • name      │   │
60: │  │          │    └─────────────────┘    │  • location  │   │
61: │  │  • id    │                           └──────────────┘   │
62: │  │  • value │    ┌──────────────────┐                       │
63: │  └──────────┘    │  StockMovement   │                       │
64: │                  │                  │    ┌──────────────┐   │
65: │  ┌──────────┐    │  • product_id    │    │ TaxCategory  │   │
66: │  │ Pricing  │    │  • warehouse_id  │    │              │   │
67: │  │ Rule     │    │  • quantity      │    │  • id        │   │
68: │  │          │    │  • type (in/out) │    │  • name      │   │
69: │  │  • id    │    │  • reason        │    └──────┬───────┘   │
70: │  │  • scope │    │  • reference     │           │          │
71: │  └──────────┘    └──────────────────┘     ┌─────▼──────┐   │
72: │                                           │  TaxRate   │   │
73: │  ┌──────────┐    ┌──────────────────┐     │            │   │
74: │  │ AuditLog │    │ StockReservation │     │  • rate    │   │
75: │  │          │    │                  │     │  • type    │   │
76: │  │  • event │    │  • order_id      │     └────────────┘   │
77: │  │  • data  │    │  • expires_at    │                       │
78: │  └──────────┘    └──────────────────┘                       │
79: └──────────────────────────────────────────────────────────────┘
80: ```
81: 
82: ## Module Structure
83: 
84: ```
85: app/Modules/Product/
86: ├── Contracts/
87: │   ├── PricingCalculator.php       — Price calculation strategy
88: │   ├── ProductCatalogService.php   — Catalog query interface
89: │   ├── ProductResolver.php         — Product lookup (by id/sku/barcode)
90: │   ├── SKUGenerator.php            — SKU generation strategy
91: │   ├── StockAllocator.php          — Stock allocation strategy
92: │   └── StockChecker.php            — Stock availability queries
93: ├── Database/
94: │   ├── Factories/
95: │   │   ├── BrandFactory.php
96: │   │   ├── CategoryFactory.php
97: │   │   ├── ProductFactory.php
98: │   │   ├── StockReservationFactory.php
99: │   │   ├── VariantFactory.php
100: │   │   ├── WarehouseFactory.php
101: │   │   └── WarehouseStockFactory.php
102: │   └── Migrations/Tenant/
103: │       ├── 2026_05_21_000001_create_categories_table.php
104: │       ├── 2026_05_21_000002_create_brands_table.php
105: │       ├── 2026_05_21_000003_create_attributes_table.php
106: │       ├── 2026_05_21_000004_create_attribute_values_table.php
107: │       ├── 2026_05_21_000005_create_tax_categories_table.php
108: │       ├── 2026_05_21_000006_create_tax_rates_table.php
109: │       ├── 2026_05_21_000007_create_products_table.php
110: │       ├── 2026_05_21_000008_create_category_product_table.php
111: │       ├── 2026_05_21_000009_create_product_attribute_values_table.php
112: │       ├── 2026_05_21_000010_create_product_attribute_text_values_table.php
113: │       ├── 2026_05_21_000011_create_variants_table.php
114: │       ├── 2026_05_21_000012_create_variant_attribute_values_table.php
115: │       ├── 2026_05_21_000013_create_product_media_table.php
116: │       ├── 2026_05_21_000014_create_warehouses_table.php
117: │       ├── 2026_05_21_000015_create_warehouse_stock_table.php
118: │       ├── 2026_05_21_000016_create_stock_reservations_table.php
119: │       ├── 2026_05_21_000017_create_stock_movements_table.php
120: │       ├── 2026_05_21_000018_create_audit_logs_table.php
121: │       └── 2026_05_21_000019_create_pricing_rules_table.php
122: ├── DTOs/
123: │   ├── ProductDTO.php
124: │   ├── ProductSummaryDTO.php
125: │   ├── ProductWithStockDTO.php
126: │   └── VariantDTO.php
127: ├── Enums/
128: │   ├── ProductStatusEnum.php
129: │   ├── ProductTypeEnum.php
130: │   └── StockMovementType.php
131: ├── Events/
132: │   ├── LowStockAlert.php
133: │   ├── ProductArchived.php
134: │   ├── ProductCreated.php
135: │   ├── ProductDeleted.php
136: │   ├── ProductPublished.php
137: │   ├── ProductUpdated.php
138: │   ├── StockDepleted.php
139: │   ├── StockReservationCreated.php
140: │   ├── StockReservationExpired.php
141: │   ├── StockTransferCompleted.php
142: │   ├── StockUpdated.php
143: │   ├── VariantCreated.php
144: │   ├── VariantDeleted.php
145: │   └── VariantUpdated.php
146: ├── Http/
147: │   ├── Controllers/
148: │   │   ├── AttributeController.php
149: │   │   ├── BrandController.php
150: │   │   ├── CategoryController.php
151: │   │   ├── MediaController.php
152: │   │   ├── PricingRuleController.php
153: │   │   ├── ProductController.php
154: │   │   ├── StockController.php
155: │   │   ├── TaxController.php
156: │   │   ├── VariantController.php
157: │   │   └── WarehouseController.php
158: │   └── Requests/
159: │       ├── StockAdjustmentRequest.php
160: │       ├── StockTransferRequest.php
161: │       ├── StoreAttributeRequest.php
162: │       ├── StoreBrandRequest.php
163: │       ├── StoreCategoryRequest.php
164: │       ├── StorePricingRuleRequest.php
165: │       ├── StoreProductRequest.php
166: │       ├── StoreVariantRequest.php
167: │       ├── StoreWarehouseRequest.php
168: │       └── UpdateProductRequest.php
169: ├── Jobs/
170: │   ├── ExpireStockReservationsJob.php
171: │   ├── ExportProductsJob.php
172: │   ├── GenerateProductSKUsJob.php
173: │   ├── ImportProductsJob.php
174: │   ├── IndexProductJob.php
175: │   ├── ReindexAllProductsJob.php
176: │   ├── RemoveProductIndexJob.php
177: │   └── UpdateProductIndexJob.php
178: ├── Listeners/
179: │   ├── DeductProductStock.php         — Listens to OrderCreated
180: │   ├── ExpireStockReservations.php
181: │   ├── GenerateProductSKU.php
182: │   ├── IndexProductForSearch.php
183: │   ├── MarkProductUnavailable.php
184: │   ├── ReleaseExpiredStock.php
185: │   ├── RemoveProductFromSearchIndex.php
186: │   ├── RestoreProductStock.php        — Listens to OrderCancelled
187: │   ├── SendLowStockNotification.php
188: │   ├── SendStockDepletedNotification.php
189: │   ├── TrackStockReservation.php
190: │   ├── UpdateProductSearchIndex.php
191: │   └── UpdateProductStockCache.php
192: ├── Models/
193: │   ├── Attribute.php
194: │   ├── AttributeValue.php
195: │   ├── AuditLog.php
196: │   ├── Brand.php
197: │   ├── Category.php
198: │   ├── PricingRule.php
199: │   ├── Product.php
200: │   ├── ProductAttributeTextValue.php
201: │   ├── ProductAttributeValue.php
202: │   ├── ProductMedia.php
203: │   ├── StockMovement.php
204: │   ├── StockReservation.php
205: │   ├── TaxCategory.php
206: │   ├── TaxRate.php
207: │   ├── Variant.php
208: │   ├── Warehouse.php
209: │   └── WarehouseStock.php
210: ├── Observers/
211: │   ├── ProductObserver.php
212: │   ├── StockReservationObserver.php
213: │   ├── VariantObserver.php
214: │   └── WarehouseStockObserver.php
215: ├── Policies/
216: │   ├── BrandPolicy.php
217: │   ├── CategoryPolicy.php
218: │   ├── ProductPolicy.php
219: │   └── WarehousePolicy.php
220: ├── Rules/
221: │   ├── DifferentParent.php
222: │   ├── StockAvailable.php
223: │   ├── ValidBarcode.php
224: │   └── ValidSKU.php
225: ├── Services/
226: │   ├── AttributeService.php
227: │   ├── BrandService.php
228: │   ├── CategoryService.php
229: │   ├── DefaultSKUGenerator.php
230: │   ├── DefaultStockAllocator.php
231: │   ├── EloquentPricingCalculator.php
232: │   ├── EloquentProductCatalogService.php
233: │   ├── EloquentProductResolver.php
234: │   ├── EloquentStockChecker.php
235: │   ├── MediaService.php
236: │   ├── PricingRuleService.php
237: │   ├── ProductImportService.php
238: │   ├── ProductService.php
239: │   ├── StockAuditService.php
240: │   ├── StockLockService.php
241: │   ├── StockReservationService.php
242: │   ├── StockService.php
243: │   ├── TaxService.php
244: │   ├── VariantService.php
245: │   └── WarehouseService.php
246: ├── Traits/
247: │   ├── HasBarcode.php
248: │   ├── HasMaterializedPath.php
249: │   ├── HasOptimisticLocking.php
250: │   ├── HasProductMedia.php
251: │   ├── HasProductStock.php
252: │   └── Sluggable.php
253: └── ValueObjects/
254:     ├── PriceResult.php
255:     ├── ProductSearchCriteria.php
256:     └── TaxResult.php
257: ```
258: 
259: ## Tenant Isolation Verification
260: 
261: ### 1. Migrations are Tenant-Scoped
262: 
263: All 19 migration files live in `app/Modules/Product/Database/Migrations/Tenant/` and are registered via the tenancy config, **not** via `loadMigrationsFrom()` (which would leak them into central `php artisan migrate`):
264: 
265: ```php
266: // config/tenancy.php — CORRECT approach
267: 'migration_parameters' => [
268:     '--force' => true,
269:     '--path' => [
270:         database_path('migrations/tenant'),
271:         app_path('Modules/Product/Database/Migrations/Tenant'),
272:     ],
273: ],
274: ```
275: 
276: The path is registered in `config/tenancy.php` under `migration_parameters`, which only applies to `php artisan tenants:migrate` — never to `php artisan migrate` (central). **Do not use `loadMigrationsFrom()` for tenant migrations in service providers**, as it registers them with the global migrator and causes them to run against the central database.
277: 
278: ### 2. Models Use Tenant Connection
279: 
280: Every model in `app/Modules/Product/Models/` extends `Illuminate\Database\Eloquent\Model` without the `CentralConnection` trait. This means they resolve to the `tenant` database connection when tenancy is active.
281: 
282: ```php
283: // Product.php — NO CentralConnection → tenant DB
284: class Product extends Model { ... }
285: 
286: // Category.php — NO CentralConnection → tenant DB
287: class Category extends Model { ... }
288: ```
289: 
290: ### 3. Routes Are Behind Tenancy Middleware
291: 
292: All product routes in `routes/tenant.php` are protected by `InitializeTenancyByUser` and `subscription` middleware:
293: 
294: ```php
295: Route::middleware(['web', 'auth', InitializeTenancyByUser::class])->group(function () {
296:     Route::middleware('subscription')->group(function () {
297:         Route::resource('products', ProductController::class);
298:         // category, brand, attribute, inventory routes...
299:     });
300: });
301: ```
302: 
303: ### 4. No Central Product Data
304: 
305: `database/migrations/` (central) contains no product-related tables. All product data exists exclusively in tenant DBs.
306: 
307: ## Cross-Module Communication
308: 
309: ```
310: OrderCreated
311:     ├──► DeductProductStock (Product/Listeners/)     — Deducts inventory
312:     └──► RestoreProductStock (Product/Listeners/)    — Restores on cancel
313: 
314: ProductCreated
315:     ├──► IndexProductForSearch                       — Meilisearch indexing
316:     └──► GenerateProductSKU                          — Auto SKU generation
317: ```
318: 
319: The Product module exposes contracts (`ProductResolver`, `StockChecker`, `PricingCalculator`, `ProductCatalogService`, `SKUGenerator`, `StockAllocator`) bound to implementations in `ProductServiceProvider` for other modules to consume.
320: 
321: ## Frontend Pages
322: 
323: | Page | Route | Path |
324: |------|-------|------|
325: | Product List | `products.index` | `resources/js/pages/Product/Index.tsx` |
326: | Product Create | `products.create` | `resources/js/pages/Product/Create.tsx` |
327: | Product Detail | `products.show` | `resources/js/pages/Product/Show.tsx` |
328: | Product Edit | `products.edit` | `resources/js/pages/Product/Edit.tsx` |
329: | Categories | `categories.index` | `resources/js/pages/Product/Category/Index.tsx` |
330: | Category Detail | `categories.show` | `resources/js/pages/Product/Category/Show.tsx` |
331: | Brands | `brands.index` | `resources/js/pages/Product/Brand/Index.tsx` |
332: | Attributes | `attributes.index` | `resources/js/pages/Product/Attribute/Index.tsx` |
333: | Low Stock | `inventory.index` | `resources/js/pages/Product/Stock/LowStock.tsx` |
334: | Movements | `stock.movements` | `resources/js/pages/Product/Stock/Movements.tsx` |
335: 
336: ## Tenant Database Provisioning Architecture
337: 
338: Tenant databases are **not** created on registration or first login. They are provisioned on **subscription activation**, following proper SaaS resource-gating.
339: 
340: ### Flow
341: 
342: ```
343: Registration → Tenant record in central DB (no tenant DB)
344:      │
345:      ▼
346: Login → InitializeTenancyByUser middleware
347:      │
348:      ├── Tenant has DB? → Normal tenancy init → proceed
349:      │
350:      └── No DB yet?
351:          ├── Route is /billing*? → Allow through (no tenancy init)
352:          └── Route is NOT billing? → Redirect to /billing
353:      │
354:      ▼
355: User selects plan + pays (billing uses only central models)
356:      │
357:      ▼
358: SubscriptionActivated event dispatched
359:      │
360:      ├──► [1] ProvisionTenantDatabase listener (sync)
361:      │       ├── Creates tenant database
362:      │       └── Runs all tenant migrations (products, categories, etc.)
363:      │
364:      └──► [2] SendSubscriptionNotification listener (queued)
365:               └── Sends confirmation email
366:      │
367:      ▼
368: User redirected → Next request → Middleware finds DB → Tenancy initialized → Dashboard
369: ```
370: 
371: ### Key Components
372: 
373: | Component | File | Role |
374: |-----------|------|------|
375: | `ProvisionTenantDatabase` listener | `app/Listeners/ProvisionTenantDatabase.php` | Creates DB + runs migrations on `SubscriptionActivated` |
376: | `InitializeTenancyByUser` middleware | `app/Http/Middleware/InitializeTenancyByUser.php` | Redirects non-billing routes when tenant has no DB |
377: | Billing routes | `routes/web.php` | No tenancy middleware; uses central models only |
378: | Tenant routes | `routes/tenant.php` | Protected by `InitializeTenancyByUser` + `subscription` |
379: 
380: ### Why This Pattern
381: 
382: | Aspect | Benefit |
383: |--------|---------|
384: | **Resource efficiency** | No wasted tenant DBs for unsubscribed users |
385: | **Security** | No tenant data exists until first payment |
386: | **Clean onboarding** | Registration → billing → provision → access (linear flow) |
387: | **Idempotent provisioning** | Listener checks if DB exists before creating |
388: | **Sync provisioning** | DB ready before user is redirected |
389: 
390: ## Authorization Enforcement
391: 
392: `ProductController` uses the `AuthorizesRequests` trait and explicitly calls `$this->authorize('create', Product::class)`, `$this->authorize('update', $product)`, and `$this->authorize('delete', $product)` in `store()`, `update()`, and `destroy()` respectively. This enforces the `ProductPolicy` gates defined in `app/Modules/Product/Policies/ProductPolicy.php`.
393: 
394: **Permissions are stored in the central database.** Custom `App\Models\Permission` and `App\Models\Role` models extend Spatie's stock models with the `CentralConnection` trait, ensuring all authorization lookups always target the central DB. Permissions are seeded via `database/seeders/RolePermissionSeeder.php`, which creates product permissions (`products.view`, `products.create`, `products.update`, `products.delete`, `products.archive`, `products.publish`, `products.duplicate`, `products.import`, `products.export`) and syncs them to the `admin` role (platform admins) and `tenant` role (tenant users).
395: 
396: Other controllers (`CategoryController`, `BrandController`, etc.) should follow the same pattern to enforce their respective policies via `$this->authorize()` calls.
397: 
398: ### Circular Category Reference
399: 
400: The `StoreCategoryRequest` validates `parent_id` via `withValidator()` — checking that a category cannot be set as its own parent. `CategoryService::validateParent()` throws `CircularCategoryException` as a safety net for circular chain detection.
401: 
402: ## Summary
403: 
404: The Product Management module is correctly designed for tenant database isolation:
405: 
406: - ✅ **19 tenant migrations** — all under tenant DB
407: - ✅ **17 tenant models** — no CentralConnection, auto-resolve to tenant DB
408: - ✅ **10 controllers** — behind tenancy + subscription middleware, `ProductController` enforces `AuthorizesRequests`
409: - ✅ **7 service contracts** — for cross-module consumption
410: - ✅ **14 domain events** — for decoupled cross-module communication
411: - ✅ **13 listeners** — including `DeductProductStock` (Order→Inventory bridge)
412: - ✅ **8 queued jobs** — for async operations (import, export, search indexing)
413: - ✅ **4 observers** — for model lifecycle hooks
414: - ✅ **4 authorization policies** — enforced via `$this->authorize()` in `ProductController`
415: - ✅ **10 frontend pages** — Inertia React SPA pages
416: - ✅ **No central product tables** — zero product data in central DB
417: - ✅ **Tenant DB provisioned on subscription** — not on registration or login

(End of file - total 417 lines)
</content>
```

---

## Assistant (Plan · DeepSeek V4 Flash Free · 5.3s)

_Thinking:_

Now I have a very thorough understanding of the existing codebase. Let me now analyze the weaknesses and design the architecture. Let me also check the existing migration files for the EAV and products table structure.

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Modules/Product/Database/Migrations/Tenant"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Modules/Product/Database/Migrations/Tenant</path>
<type>directory</type>
<entries>
2026_05_21_000001_create_categories_table.php
2026_05_21_000002_create_brands_table.php
2026_05_21_000003_create_attributes_table.php
2026_05_21_000004_create_attribute_values_table.php
2026_05_21_000005_create_tax_categories_table.php
2026_05_21_000006_create_tax_rates_table.php
2026_05_21_000007_create_products_table.php
2026_05_21_000008_create_category_product_table.php
2026_05_21_000009_create_product_attribute_values_table.php
2026_05_21_000010_create_product_attribute_text_values_table.php
2026_05_21_000011_create_variants_table.php
2026_05_21_000012_create_variant_attribute_values_table.php
2026_05_21_000013_create_product_media_table.php
2026_05_21_000014_create_warehouses_table.php
2026_05_21_000015_create_warehouse_stock_table.php
2026_05_21_000016_create_stock_reservations_table.php
2026_05_21_000017_create_stock_movements_table.php
2026_05_21_000018_create_audit_logs_table.php
2026_05_21_000019_create_pricing_rules_table.php

(19 entries)
</entries>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Modules/Product/Enums/ProductTypeEnum.php"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Modules/Product/Enums/ProductTypeEnum.php</path>
<type>file</type>
<content>
1: <?php
2: 
3: declare(strict_types=1);
4: 
5: namespace App\Modules\Product\Enums;
6: 
7: enum ProductTypeEnum: string
8: {
9:     case Simple = 'simple';
10:     case Configurable = 'configurable';
11:     case Bundle = 'bundle';
12:     case Virtual = 'virtual';
13: 
14:     public function label(): string
15:     {
16:         return match ($this) {
17:             self::Simple => 'Simple',
18:             self::Configurable => 'Configurable',
19:             self::Bundle => 'Bundle',
20:             self::Virtual => 'Virtual',
21:         };
22:     }
23: 
24:     public function hasVariants(): bool
25:     {
26:         return $this === self::Configurable;
27:     }
28: 
29:     public function tracksInventory(): bool
30:     {
31:         return $this !== self::Virtual;
32:     }
33: }

(End of file - total 33 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Modules/Product/Contracts"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Modules/Product/Contracts</path>
<type>directory</type>
<entries>
PricingCalculator.php
ProductCatalogService.php
ProductResolver.php
SKUGenerator.php
StockAllocator.php
StockChecker.php

(6 entries)
</entries>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/app/Models/Tenant.php"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/app/Models/Tenant.php</path>
<type>file</type>
<content>
1: <?php
2: 
3: namespace App\Models;
4: 
5: use App\Modules\Billing\Models\Subscription;
6: use Database\Factories\TenantFactory;
7: use Illuminate\Database\Eloquent\Factories\HasFactory;
8: use Illuminate\Database\Eloquent\Relations\BelongsTo;
9: use Illuminate\Database\Eloquent\Relations\HasMany;
10: use Illuminate\Database\Eloquent\Relations\HasOne;
11: use Illuminate\Database\Eloquent\SoftDeletes;
12: use Illuminate\Support\Carbon;
13: use Stancl\Tenancy\Contracts\TenantWithDatabase;
14: use Stancl\Tenancy\Database\Concerns\HasDatabase;
15: use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
16: use Stancl\Tenancy\Database\TenantCollection;
17: 
18: /**
19:  * @property string $id
20:  * @property string|null $name
21:  * @property int|null $owner_id
22:  * @property Carbon|null $trial_ends_at
23:  * @property Carbon $created_at
24:  * @property Carbon $updated_at
25:  * @property Carbon|null $deleted_at
26:  * @property string $tenancy_mode
27:  * @property string|null $database_name
28:  * @property array<string, mixed> $data
29:  */
30: class Tenant extends BaseTenant implements TenantWithDatabase
31: {
32:     /** @use HasFactory<TenantFactory> */
33:     use HasDatabase, HasFactory, SoftDeletes;
34: 
35:     public static function getCustomColumns(): array
36:     {
37:         return [
38:             'id',
39:             'name',
40:             'owner_id',
41:             'trial_ends_at',
42:             'trial_used',
43:             'tenancy_mode',
44:             'database_name',
45:             'created_at',
46:             'updated_at',
47:             'deleted_at',
48:         ];
49:     }
50: 
51:     protected function casts(): array
52:     {
53:         return [
54:             'trial_ends_at' => 'datetime',
55:             'trial_used' => 'boolean',
56:         ];
57:     }
58: 
59:     public function getDatabaseName(): string
60:     {
61:         return $this->database_name ?? 'souda_tenant_'.$this->id;
62:     }
63: 
64:     public function isShared(): bool
65:     {
66:         return $this->tenancy_mode === 'shared';
67:     }
68: 
69:     public function isDedicated(): bool
70:     {
71:         return $this->tenancy_mode === 'dedicated';
72:     }
73: 
74:     public function user(): HasOne
75:     {
76:         return $this->hasOne(User::class);
77:     }
78: 
79:     public function owner(): BelongsTo
80:     {
81:         return $this->belongsTo(User::class, 'owner_id');
82:     }
83: 
84:     public function subscriptions(): HasMany
85:     {
86:         return $this->hasMany(Subscription::class, 'tenant_id', 'id');
87:     }
88: 
89:     public function activeSubscription(): ?Subscription
90:     {
91:         return $this->subscriptions()
92:             ->accessible()
93:             ->latest('id')
94:             ->first();
95:     }
96: 
97:     public function newCollection(array $models = []): TenantCollection
98:     {
99:         return new TenantCollection($models);
100:     }
101: }

(End of file - total 101 lines)
</content>
```

---

## Assistant (Plan · DeepSeek V4 Flash Free · 4.5s)

_Thinking:_

Now let me look at the frontend side more deeply to understand the module system.

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/resources/js/modules/product/pages/product-create.tsx"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/resources/js/modules/product/pages/product-create.tsx</path>
<type>file</type>
<content>
1: import { Head, usePage } from '@inertiajs/react';
2: import AppLayout from '@/layouts/app-layout';
3: import { ProductFormPage } from '../components/product-form-page';
4: import type { BreadcrumbItem } from '@/types';
5: import type { CategoryOption, BrandOption } from '../types';
6: 
7: type CreatePageProps = {
8:     categories: CategoryOption[];
9:     brands: BrandOption[];
10: };
11: 
12: export default function ProductCreate() {
13:     const { categories = [], brands = [] } = usePage<CreatePageProps>().props;
14: 
15:     const breadcrumbs: BreadcrumbItem[] = [
16:         { title: 'Dashboard', href: '/dashboard' },
17:         { title: 'Products', href: '/products' },
18:         { title: 'Create', href: '/products/create' },
19:     ];
20: 
21:     return (
22:         <AppLayout breadcrumbs={breadcrumbs}>
23:             <Head title="Create Product" />
24: 
25:             <ProductFormPage
26:                 mode="create"
27:                 submitRoute="/products"
28:                 method="post"
29:                 categories={categories}
30:                 brands={brands}
31:                 onCancel={() => window.history.back()}
32:                 onSuccess={() => {
33:                     // redirect handled by server
34:                 }}
35:             />
36:         </AppLayout>
37:     );
38: }

(End of file - total 38 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/resources/js/modules/product/lib/map-product.ts"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/resources/js/modules/product/lib/map-product.ts</path>
<type>file</type>
<content>
1: import type { ProductFormData } from '../types/product-form';
2: import type { VariantRowFormData, AttributeFormData, VariantGroup } from '../types/variant';
3: 
4: export function mapProductToFormData(product: Record<string, any>): Partial<ProductFormData> {
5:     const dimensions = product.dimensions ?? {};
6: 
7:     return {
8:         name: product.name ?? '',
9:         description: product.description ?? '',
10:         status: product.status ?? 'draft',
11:         categoryId: product.category_id != null ? String(product.category_id) : null,
12:         brandId: product.brand_id != null ? String(product.brand_id) : null,
13:         price: product.base_price != null ? product.base_price / 100 : 0,
14:         comparePrice: product.compare_at_price != null ? product.compare_at_price / 100 : null,
15:         costPrice: product.cost_price != null ? product.cost_price / 100 : null,
16:         isTaxable: product.tax_inclusive ?? true,
17:         sku: product.sku ?? '',
18:         barcode: product.barcode ?? null,
19:         trackStock: product.track_inventory ?? true,
20:         quantity: product.quantity ?? 0,
21:         lowStockThreshold: product.low_stock_threshold ?? 5,
22:         allowBackorders: product.allow_backorders ?? false,
23:         weight: dimensions.weight ?? null,
24:         length: dimensions.length ?? null,
25:         width: dimensions.width ?? null,
26:         height: dimensions.height ?? null,
27:         freeShipping: product.free_shipping ?? false,
28:         metaTitle: product.meta_title ?? null,
29:         metaDescription: product.meta_description ?? null,
30:         slug: product.slug ?? null,
31:         images: mapImages(product.media ?? product.images ?? []),
32:         variants: mapVariants(product.variants ?? []),
33:         variantGroups: mapVariantGroups(product.variant_groups ?? []),
34:         attributes: mapAttributes(product.attribute_values ?? []),
35:     };
36: }
37: 
38: function mapImages(media: any[]): any[] {
39:     return media.map((m: any, i: number) => ({
40:         id: m.id ?? `existing_${i}`,
41:         preview: m.original_url ?? m.url ?? m.preview ?? '',
42:         isMain: i === 0,
43:     }));
44: }
45: 
46: function mapVariants(variants: any[]): VariantRowFormData[] {
47:     return variants.map((v: any) => ({
48:         id: v.id ?? crypto.randomUUID(),
49:         sku: v.sku ?? '',
50:         barcode: v.barcode ?? '',
51:         price: v.price != null ? v.price / 100 : undefined,
52:         costPrice: v.cost_price != null ? v.cost_price / 100 : undefined,
53:         quantity: v.quantity ?? 0,
54:         weight: v.weight ?? undefined,
55:         isEnabled: v.is_enabled ?? v.status !== 'archived',
56:         image: v.image ?? undefined,
57:         attributes: v.attributes ?? {},
58:     }));
59: }
60: 
61: function mapVariantGroups(groups: any[]): VariantGroup[] {
62:     return (groups ?? []).map((g: any) => ({
63:         attributeId: g.attribute_id ?? g.attributeId ?? crypto.randomUUID(),
64:         attributeName: g.attribute_name ?? g.attributeName ?? '',
65:         values: g.values ?? [],
66:     }));
67: }
68: 
69: function mapAttributes(values: any[]): AttributeFormData[] {
70:     return (values ?? []).map((v: any) => ({
71:         id: v.attribute_id ?? v.id ?? crypto.randomUUID(),
72:         value: v.value ?? v.name ?? '',
73:     }));
74: }
75: 
76: export function mapFormToPayload(data: ProductFormData): Record<string, any> {
77:     const dimensions: Record<string, number> = {};
78:     if (data.weight != null) dimensions.weight = data.weight;
79:     if (data.length != null) dimensions.length = data.length;
80:     if (data.width != null) dimensions.width = data.width;
81:     if (data.height != null) dimensions.height = data.height;
82: 
83:     return {
84:         name: data.name,
85:         description: data.description || null,
86:         slug: data.slug || null,
87:         status: data.status,
88:         type: 'simple',
89:         category_id: data.categoryId ? Number(data.categoryId) : null,
90:         brand_id: data.brandId ? Number(data.brandId) : null,
91:         base_price: Math.round(data.price * 100),
92:         compare_at_price: data.comparePrice != null ? Math.round(data.comparePrice * 100) : null,
93:         cost_price: data.costPrice != null ? Math.round(data.costPrice * 100) : null,
94:         tax_inclusive: data.isTaxable,
95:         sku: data.sku || null,
96:         barcode: data.barcode || null,
97:         track_inventory: data.trackStock,
98:         low_stock_threshold: data.lowStockThreshold,
99:         allow_backorders: data.allowBackorders,
100:         dimensions: Object.keys(dimensions).length > 0 ? dimensions : null,
101:         free_shipping: data.freeShipping,
102:         meta_title: data.metaTitle || null,
103:         meta_description: data.metaDescription || null,
104:         variants: data.variants?.map((v) => ({
105:             ...v,
106:             price: v.price != null ? Math.round(v.price * 100) : undefined,
107:             cost_price: v.costPrice != null ? Math.round(v.costPrice * 100) : undefined,
108:         })),
109:         variant_groups: data.variantGroups,
110:         attribute_values: data.attributes,
111:     };
112: }

(End of file - total 112 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/resources/js/modules/shared/types"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/resources/js/modules/shared/types</path>
<type>directory</type>
<entries>
index.ts

(1 entries)
</entries>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/resources/js/modules/shared/providers"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/resources/js/modules/shared/providers</path>
<type>directory</type>
<entries>
query-provider.tsx

(1 entries)
</entries>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "/Users/salam/Herd/souda/resources/js/modules/shared/components"
}
```

**Output:**
```
<path>/Users/salam/Herd/souda/resources/js/modules/shared/components</path>
<type>directory</type>
<entries>
confirm-dialog.tsx
data-table-column-toggle.tsx
data-table-faceted-filter.tsx
data-table-pagination.tsx
data-table-toolbar.tsx
data-table.tsx
deferred-section.tsx
empty-state.tsx
error-state.tsx
form-actions.tsx
form-section.tsx
image-uploader/
page-header.tsx
pagination.tsx
search-input.tsx
table-skeleton.tsx

(16 entries)
</entries>
```

---

## Assistant (Plan · DeepSeek V4 Flash Free · 130.7s)

_Thinking:_

Now I have a very comprehensive understanding of the existing codebase. Let me now sit down and design the comprehensive architecture document. I need to cover:

1. Weaknesses in current product architecture
2. Scalability limitations
3. Future SaaS risks
4. Multi-industry challenges
5. Then propose the solution covering all 20 points

Let me synthesize everything I've learned and create the architecture plan.

Key observations from the codebase:
1. **Existing EAV**: The product already has an EAV-like system via `Attribute` → `AttributeValue` → `ProductAttributeValue` → `ProductAttributeTextValue`. This is used for general attributes like color, size, etc.
2. **No industry/vertical concept**: Zero existing code for business types or industries.
3. **Hardcoded product schema**: The `products` table has fixed columns for fields like `weight`, `length`, `width`, `height`, `base_price`, `compare_at_price`, `cost_price`, `sku`, `barcode`, etc. These are universal.
4. **10 shared frontend form sections**: `ProductGeneralSection`, `ProductPricingSection`, `ProductInventorySection`, `ProductShippingSection`, `ProductSeoSection` — these are all hardcoded sections.
5. **Zod schema validated frontend form**: `productFormSchema` is fixed with specific fields.
6. **ProductService is concrete**: CRUD operations are direct, not extensible via hooks/pipelines.
7. **No concept of "business type"**: The `Tenant` model does have a `tenancy_mode` field, but nothing about what industry/vertical the tenant operates in.

Now let me design the comprehensive architecture.

## Weaknesses in Current Architecture

1. **Rigid Schema**: Products table has fixed columns. Can't store pharmacy-specific fields (generic_name, strength, dosage_form, etc.) without adding columns or abusing JSON.

2. **Existing EAV is Weak for Industry Use**: The current EAV system (attributes → attribute_values → pivot tables) is designed for simple product attributes (color, size), not complex industry-specific field groups with custom validation, form rendering, and business logic.

3. **No Business Type Concept**: There's no concept of what "kind" of business a tenant runs. This means:
   - Can't customize forms per industry
   - Can't validate differently per industry
   - Can't show/hide fields based on industry
   - Can't trigger industry-specific workflows

4. **Hardcoded Validation**: `StoreProductRequest` has fixed validation rules. New industries would require new form requests or modification of existing ones.

5. **Hardcoded Frontend Forms**: `ProductFormPage` composes hardcoded sections. New industry fields would require new custom components for each industry.

6. **ProductService is Not Extensible**: The service layer has no hook/plugin system. Adding new logic for a specific industry requires modifying core code.

7. **No Industry Pack Concept**: No modular way to add new industries as "packs" or "extensions".

8. **Search Indexing is Generic**: `toSearchableArray()` returns fixed fields. Industry-specific fields aren't indexed for search.

## Architecture Design

### 1. Bounded Contexts

The product domain should be split into:

- **Core Product BC**: Products, Categories, Brands, Variants, Attributes (existing EAV for simple attributes)
- **Industry BC**: Business Types, Industry Packs, Dynamic Fields, Dynamic Forms, Dynamic Validation
- **Inventory BC**: Warehouses, Stock, Movements, Reservations (already separated)
- **Pricing BC**: Pricing Rules, Tax (already somewhat separated)

### 2. Core Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    PRODUCT DOMAIN                            │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │            CORE COMMERCE ENGINE                      │   │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌────────┐ │   │
│  │  │ Products │ │Categories│ │  Brands  │ │Variant │ │   │
│  │  └──────────┘ └──────────┘ └──────────┘ └────────┘ │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │           DYNAMIC FIELD ENGINE (DFE)                 │   │
│  │  ┌────────────┐ ┌──────────┐ ┌──────────────┐      │   │
│  │  │Business    │ │Dynamic   │ │Dynamic Forms │      │   │
│  │  │Types       │ │Fields    │ │& UI Metadata │      │   │
│  │  └────────────┘ └──────────┘ └──────────────┘      │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │               INDUSTRY PACKS                         │   │
│  │  ┌──────────┐ ┌────────────┐ ┌──────────────────┐  │   │
│  │  │Pharmacy  │ │Restaurant  │ │Electronics       │  │   │
│  │  │Pack      │ │Pack        │ │Pack              │  │   │
│  │  ├──────────┤ ├────────────┤ ├──────────────────┤  │   │
│  │  │Fashion   │ │Grocery     │ │Salon             │  │   │
│  │  │Pack      │ │Pack        │ │Pack              │  │   │
│  │  └──────────┘ └────────────┘ └──────────────────┘  │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### 3. Database Schema Design

#### New Tables (Tenant-Level):

**business_types** - Defines available business verticals
- id (PK)
- slug (unique) - e.g., 'grocery', 'pharmacy', 'restaurant'
- name
- description
- is_active
- metadata (JSON) - configuration for this business type

**dynamic_field_groups** - Groups of related fields (e.g., "Pharmacy Details", "Shipping")
- id (PK)
- business_type_id (FK)
- slug (unique within business type)
- name
- description
- sort_order
- ui_component - frontend component to render this group
- is_repeatable - can this group have multiple instances?

**dynamic_fields** - Individual field definitions
- id (PK)
- group_id (FK)
- business_type_id (FK)
- slug (unique within business type) - e.g., 'generic_name', 'strength'
- name
- field_type - text, number, select, multiselect, date, boolean, file, json, etc.
- is_required
- sort_order
- default_value
- options (JSON) - for select fields: [{label, value}]
- validation_rules (JSON) - e.g., {"max": 255, "min": 0, "pattern": "..."}
- ui_metadata (JSON) - placeholder, help_text, section, conditional_logic, etc.
- is_filterable - can this field be used in product filters
- is_searchable - should this field be indexed for search
- is_variant_field - can this field vary per variant

**product_dynamic_values** - Stores dynamic field values for products
- id (PK)
- product_id (FK)
- field_id (FK)
- value_text - text values
- value_int - integer values
- value_float - float/decimal values
- value_date - date values
- value_json - complex values (arrays, objects)
- variant_id (FK nullable) - if field is per-variant

**business_type_product_type_map** - Maps business types to product types
- business_type_id (FK)
- product_type_id (FK) - from ProductTypeEnum

**business_type_category_groups** - Industry-specific category groupings
- business_type_id (FK)
- category_id (FK)
- group_name - e.g., "OTC", "Prescription" for pharmacy

#### Tenant Model Extension:

Add to `tenants` table (or `tenant_data` JSON):
- `business_type_id` (FK → business_types)

### 4. Migration Strategy from Existing System

**Phase 1: Foundation (No Existing Data Migration Needed)**
1. Create `business_types`, `dynamic_field_groups`, `dynamic_fields` tables
2. Seed default business types
3. Add `business_type_id` to tenant configuration
4. Provide admin UI to configure fields per business type

**Phase 2: Data Migration**
1. Create `product_dynamic_values` table
2. Existing products that have `metadata` JSON can be migrated:
   - Script reads existing metadata JSON keys
   - Matches to dynamic field definitions
   - Inserts into product_dynamic_values
3. No data loss - existing columns remain on `products` table

**Phase 3: Form Migration**
1. Existing frontend sections remain untouched
2. Dynamic fields render as additional sections below core sections
3. Gradual migration: core fields stay, industry fields come from DFE

### 5. Eloquent Model Architecture

```php
// Core remains mostly unchanged
class Product extends Model { /* existing fields */ }

// New models
class BusinessType extends Model
{
    public function fieldGroups(): HasMany
    public function fields(): HasMany
}

class DynamicFieldGroup extends Model
{
    public function businessType(): BelongsTo
    public function fields(): HasMany
}

class DynamicField extends Model
{
    public function group(): BelongsTo
    public function businessType(): BelongsTo
    public function values(): HasMany
    
    public function castValue($value): mixed
    public function getValidationRules(): array
}

class ProductDynamicValue extends Model
{
    public function product(): BelongsTo
    public function field(): BelongsTo
    public function variant(): BelongsTo
    
    // Single method to get typed value
    public function getValue(): mixed
}
```

### Product Model Extension:

```php
class Product extends Model
{
    // ... existing code
    
    public function dynamicValues(): HasMany
    {
        return $this->hasMany(ProductDynamicValue::class);
    }
    
    public function getDynamicField(string $slug): mixed
    {
        $field = DynamicField::where('slug', $slug)->first();
        $value = $this->dynamicValues()
            ->where('field_id', $field->id)
            ->first();
        return $value?->getValue();
    }
    
    // Scope for filtering by dynamic field values
    public function scopeWhereDynamicField($query, string $slug, $value)
    {
        // ...
    }
}
```

### 6. Service Architecture

**New Services:**

```php
class DynamicFieldService
{
    public function getFieldsForBusinessType(string $businessType): Collection
    public function getFormStructure(string $businessType): array
    public function validateForBusinessType(array $data, string $businessType): array
    public function saveDynamicValues(Product $product, array $values): void
    public function getDynamicValues(Product $product): Collection
}

class BusinessTypeService
{
    public function registerIndustryPack(IndustryPack $pack): void
    public function getAvailableBusinessTypes(): Collection
    public function configureForTenant(Tenant $tenant, string $businessTypeSlug): void
}

class IndustryPackManager
{
    protected array $packs = [];
    
    public function register(string $name, IndustryPack $pack): void
    public function getPack(string $name): ?IndustryPack
    public function getFormExtensions(string $businessType): array
    public function getValidationExtensions(string $businessType): array
}
```

### 7. Dynamic Field Engine Design

**Field Types:**
- `text` → varchar/text
- `number` → integer/float
- `select` → single option
- `multiselect` → multiple options (JSON)
- `date` → date/datetime
- `boolean` → boolean
- `file` → file upload
- `reference` → FK to another entity (warehouse, category, etc.)
- `compound` → group of sub-fields (JSON)
- `measurement` → value + unit (JSON)

**Storage Strategy:**
Single `product_dynamic_values` table with typed columns:
- `value_text` (TEXT) - for text, select, textarea
- `value_int` (BIGINT) - for integer, boolean
- `value_float` (DECIMAL) - for prices, measurements
- `value_date` (DATETIME) - for dates
- `value_json` (JSON) - for multiselect, compound, complex types

This avoids JSON-only EAV by providing typed columns, keeping queryable data in appropriate types.

### 8. Validation Engine Design

```php
class DynamicValidationEngine
{
    public function buildRules(string $businessType, ?string $productType = null): array
    {
        $fields = DynamicField::whereBusinessType($businessType)->get();
        $rules = [];
        
        foreach ($fields as $field) {
            $fieldRules = [];
            
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }
            
            // Add type-based rules
            $fieldRules = array_merge($fieldRules, $this->getTypeRules($field));
            
            // Add custom validation rules from field definition
            $fieldRules = array_merge($fieldRules, $field->validation_rules ?? []);
            
            // Add industry pack specific rules
            $pack = $this->packManager->getPack($businessType);
            if ($pack) {
                $packRules = $pack->getValidationRules($field->slug);
                $fieldRules = array_merge($fieldRules, $packRules);
            }
            
            $rules["dynamic.{$field->slug}"] = $fieldRules;
        }
        
        return $rules;
    }
}
```

### 9. Form Generation Architecture

**Backend:**
```php
class DynamicFormBuilder
{
    public function buildForm(string $businessType): array
    {
        $groups = DynamicFieldGroup::with('fields')
            ->whereBusinessType($businessType)
            ->orderBy('sort_order')
            ->get();
        
        return $groups->map(fn($group) => [
            'slug' => $group->slug,
            'name' => $group->name,
            'description' => $group->description,
            'ui_component' => $group->ui_component,
            'is_repeatable' => $group->is_repeatable,
            'fields' => $group->fields->map(fn($field) => [
                'slug' => $field->slug,
                'name' => $field->name,
                'field_type' => $field->field_type,
                'is_required' => $field->is_required,
                'default_value' => $field->default_value,
                'options' => $field->options,
                'ui_metadata' => $field->ui_metadata,
            ]),
        ]);
    }
}
```

**Frontend:**
```typescript
// Dynamic Field Renderer
function DynamicFieldsSection({ groups, values, errors, onChange }) {
    return groups.map(group => (
        <DynamicFieldGroup
            key={group.slug}
            group={group}
            values={values}
            errors={errors}
            onChange={onChange}
        />
    ));
}

// Dynamic Field Group
function DynamicFieldGroup({ group, values, errors, onChange }) {
    const GroupComponent = resolveComponent(group.ui_component);
    return (
        <GroupComponent>
            {group.fields.map(field => (
                <DynamicField
                    key={field.slug}
                    field={field}
                    value={values[field.slug]}
                    error={errors[field.slug]}
                    onChange={(val) => onChange(field.slug, val)}
                />
            ))}
        </GroupComponent>
    );
}

// Dynamic Field renders based on field_type
function DynamicField({ field, value, error, onChange }) {
    switch (field.field_type) {
        case 'text': return <Input ... />;
        case 'select': return <Select options={field.options} ... />;
        case 'date': return <DatePicker ... />;
        // etc.
    }
}
```

### 10. Industry Pack Architecture

```php
interface IndustryPack
{
    public function slug(): string;
    public function name(): string;
    public function description(): string;
    public function register(): void;
    
    // Field definitions
    public function fieldGroups(): array;
    
    // Validation extensions
    public function validationRules(): array;
    
    // Workflow extensions (pipelines)
    public function productPipelines(): array;
    
    // Search customization
    public function searchableFields(): array;
    
    // Reporting
    public function reportDefinitions(): array;
    
    // Permission extensions
    public function permissionDefinitions(): array;
}

// Example: Pharmacy Pack
class PharmacyPack implements IndustryPack
{
    public function fieldGroups(): array
    {
        return [
            [
                'slug' => 'pharmacy_details',
                'name' => 'Pharmacy Details',
                'ui_component' => 'FormSection',
                'fields' => [
                    [
                        'slug' => 'generic_name',
                        'name' => 'Generic Name',
                        'field_type' => 'text',
                        'is_required' => true,
                        'validation_rules' => ['max:500'],
                        'ui_metadata' => [
                            'placeholder' => 'Paracetamol',
                            'help_text' => 'International nonproprietary name',
                        ],
                        'is_searchable' => true,
                    ],
                    [
                        'slug' => 'strength',
                        'name' => 'Strength',
                        'field_type' => 'text',
                        'is_required' => true,
                        'validation_rules' => ['max:100'],
                        'ui_metadata' => [
                            'placeholder' => '500mg',
                        ],
                    ],
                    [
                        'slug' => 'dosage_form',
                        'name' => 'Dosage Form',
                        'field_type' => 'select',
                        'options' => [
                            ['label' => 'Tablet', 'value' => 'tablet'],
                            ['label' => 'Capsule', 'value' => 'capsule'],
                            ['label' => 'Syrup', 'value' => 'syrup'],
                            ['label' => 'Injection', 'value' => 'injection'],
                            ['label' => 'Cream', 'value' => 'cream'],
                            ['label' => 'Drops', 'value' => 'drops'],
                        ],
                    ],
                    [
                        'slug' => 'manufacturer',
                        'name' => 'Manufacturer',
                        'field_type' => 'text',
                    ],
                    [
                        'slug' => 'prescription_required',
                        'name' => 'Prescription Required',
                        'field_type' => 'boolean',
                        'default_value' => false,
                    ],
                    [
                        'slug' => 'expiry_date',
                        'name' => 'Expiry Date',
                        'field_type' => 'date',
                        'is_required' => true,
                        'validation_rules' => ['after:today'],
                    ],
                    [
                        'slug' => 'batch_number',
                        'name' => 'Batch Number',
                        'field_type' => 'text',
                    ],
                ],
            ],
        ];
    }
    
    public function productPipelines(): array
    {
        return [
            'before_create' => [
                CheckExpiryDate::class,
                ValidateBatchNumber::class,
            ],
            'after_create' => [
                GenerateBatchTrackingRecord::class,
            ],
        ];
    }
}
```

### 11. Inventory Integration Strategy

The inventory system already supports:
- Warehouses (multi-store)
- Stock with quantity, reserved, available
- Stock movements with tracking
- Stock reservations
- Optimistic locking

For industries:
- **Pharmacy**: Add batch tracking and expiry management as an extension
- **Electronics**: Add IMEI/serial number tracking as extension
- **Restaurant**: Add recipe ingredient stock tracking

```php
// Inventory extension interface
interface InventoryExtension
{
    public function getStockUnit(): string; // 'pieces', 'kg', 'liters', 'meters'
    public function supportsBatchTracking(): bool;
    public function supportsSerialTracking(): bool;
    public function supportsExpiryTracking(): bool;
    
    public function onStockReceive(WarehouseStock $stock, StockMovement $movement): void;
    public function onStockDeduct(WarehouseStock $stock, StockMovement $movement): void;
}

// Pharmacy inventory
// - Each stock movement references batch_number + expiry_date
// - Expired stock is automatically detected and marked
// - FIFO picking based on expiry dates

// Electronics inventory  
// - IMEI numbers stored in a separate serial_numbers table
// - Each sale/shipment picks specific IMEIs
// - Warranty tracking
```

### 12. Reporting Integration Strategy

```php
interface IndustryReportProvider
{
    public function reportDefinitions(): array;
    public function dataSource(string $reportSlug): Builder;
    public function chartConfig(string $reportSlug): array;
}

// Pharmacy Pack provides:
// - Expiry tracking report
// - Prescription vs OTC sales breakdown
// - Top manufacturers

// Fashion Pack provides:
// - Size distribution report
// - Seasonal sales analysis
// - Color popularity report
```

### 13. Search Strategy

```php
// Product model toSearchableArray() extended:
public function toSearchableArray(): array
{
    $base = [
        'objectID' => (string) $this->id,
        'name' => $this->name,
        // ... existing fields
    ];
    
    // Add dynamic field values for searchable fields
    if ($this->relationLoaded('dynamicValues')) {
        foreach ($this->dynamicValues as $dv) {
            if ($dv->field->is_searchable) {
                $base["dynamic_{$dv->field->slug}"] = $dv->getValue();
            }
        }
    }
    
    return $base;
}
```

### 14. Permission Strategy

New permissions scoped to business types:
- `products.view.{business_type}` 
- `products.create.{business_type}`
- `products.update.{business_type}`
- `products.delete.{business_type}`

Or more granular:

```php
// Permission can check if user's tenant business type matches
ProductPolicy::class {
    public function view(User $user, Product $product): bool
    {
        if ($user->tenant->business_type_id !== $product->businessType->id) {
            return false; // Cross-industry access denied
        }
    }
}
```

### 15. Multi-Store Compatibility Strategy

Already supported via existing `Warehouse` model. Each warehouse can be assigned a business type or serve as a store. The dynamic field engine works at the product level, so multi-store just works.

### 16. Future POS Compatibility Strategy

POS module would consume:
- Core Product contract (ProductResolver, StockChecker)
- Dynamic Field Engine for industry-specific field display at checkout
- Industry-specific inventory behavior (batch picking for pharmacy, IMEI scanning for electronics)

### 17. API Design

```php
// New API endpoints

// Business Types
GET /api/business-types
GET /api/business-types/{slug}

// Dynamic Fields
GET /api/business-types/{slug}/form-fields  // Returns form structure

// Product Dynamic Values
GET /api/products/{product}/dynamic-values
PUT /api/products/{product}/dynamic-values

// Multi-industry product creation
POST /api/products
{
    "name": "...",
    "base_price": 1000,
    "type": "simple",
    "dynamic_fields": {
        "generic_name": "Paracetamol",
        "strength": "500mg",
        "dosage_form": "tablet",
        "prescription_required": true
    }
}

// Filtering by dynamic fields
GET /api/products?filter[dynamic.generic_name]=Paracetamol
```

### 18. Frontend Architecture

**Dynamic Form Integration in ProductFormPage:**
```tsx
// New section in product-form-page.tsx
const { formStructure, dynamicValues } = usePage().props;

<ProductFormPage>
    <ProductGeneralSection ... />
    <ProductPricingSection ... />
    <ProductInventorySection ... />
    
    {/* Dynamic Industry Fields */}
    <DynamicFieldsSection
        groups={formStructure}
        values={dynamicValues}
        errors={errors}
        onChange={handleDynamicFieldChange}
    />
    
    <ProductShippingSection ... />
    <ProductSeoSection ... />
</ProductFormPage>
```

```typescript
// Industry-specific form schema generation
function buildDynamicZodSchema(groups: DynamicFieldGroup[]): z.ZodObject {
    const shape: Record<string, z.ZodType> = {};
    
    for (const group of groups) {
        for (const field of group.fields) {
            shape[field.slug] = mapFieldTypeToZod(field);
        }
    }
    
    return z.object(shape);
}
```

### 19. Performance Considerations

1. **N+1 Prevention**: Always eager-load `dynamicValues.field` when listing products
2. **Caching**: Cache form structure per business type (Redis, TTL 1 hour)
3. **Indexing**: Index `product_dynamic_values` on `(field_id, value_text)`, `(field_id, value_int)` for filtering
4. **Batch Operations**: Use bulk insert for dynamic values during import
5. **Virtual Columns**: Consider MySQL virtual columns for frequently filtered dynamic fields
6. **Read Model**: For search-heavy industries, maintain a read-optimized view

### 20. Risks and Tradeoffs

**Risks:**
1. **EAV Query Performance**: Even with typed columns, querying by dynamic field value is slower than direct column access. Mitigation: Cache + limited query patterns.
2. **Complexity**: Dynamic forms + validation + UI rendering add significant complexity.
3. **Migration Burden**: Existing tenants need to be assigned a business type.
4. **Frontend Bundle Size**: Industry packs could increase JS bundle size if all packs are loaded for all tenants. Mitigation: Lazy-load industry packs based on tenant business type.
5. **Validation Surface Area**: Dynamic validation rules are harder to test than static form requests. Mitigation: Unit test the validation engine, integration test each industry pack.

**Tradeoffs:**
1. **Flexibility vs. Performance**: Dynamic fields trade raw query performance for schema flexibility.
2. **Simplicity vs. Power**: More abstraction means more code to maintain.
3. **Core vs. Extension**: Deciding what belongs in core vs. an industry pack requires judgment.

Now let me write this up comprehensively for the user.

Now I have a complete understanding of the existing architecture. Let me present the comprehensive analysis and design.

---

# SOUDA Multi-Industry Product Domain — Architecture Analysis & Design

## Part I: Current Architecture Weaknesses

### 1. Schema Rigidity
The `products` table has 40+ fixed columns. Adding pharmacy-specific fields (generic_name, strength, batch_number) or electronics fields (imei, warranty_period, voltage) requires either:
- Adding columns to the shared products table (pollutes all tenants)
- Abusing the `metadata` JSON column (no validation, no queryability, no typing)

Both approaches fail at scale.

### 2. Existing EAV is Insufficient for Industry Fields
The current `Attribute → AttributeValue → ProductAttributeValue → ProductAttributeTextValue` chain serves well for simple product attributes (color, size, fabric). But it was not designed for:
- **Complex field types**: dates, files, references, measurements with units
- **Conditional logic**: "show field X only if field Y = value Z"
- **Per-field validation rules**: each field needs its own ruleset
- **Frontend rendering metadata**: placeholder, help_text, section grouping
- **Industry-level grouping**: fields belong to business types, not to individual products

### 3. No Business Type Concept
Zero concept of what industry a tenant operates in. This cascades into:
- All tenants get the same product form regardless of industry
- Validation is universal — can't require `generic_name` for pharmacy but not for fashion
- No industry-specific workflows (batch expiry checking, IMEI verification, calorie calculation)
- Reporting can't differentiate between industries
- Search indexing can't include industry-specific fields

### 4. Hardcoded Validation Pipeline
`StoreProductRequest` has a fixed rules array. To add industry-specific validation, you'd need to either:
- Modify the single shared form request (violates OCP)
- Create separate endpoints per industry (duplicates logic)

### 5. Frontend Forms Not Extensible
`ProductFormPage` composes 6 hardcoded sections. Adding industry-specific sections means:
- New component per industry per section
- Modifying the form orchestrator each time
- No dynamic field rendering system

### 6. Service Layer Has No Extension Points
`ProductService::createProduct()` is a single concrete method. Adding industry-specific pre/post logic requires either modification or wrapping — no pipeline/hook architecture exists.

### 7. Search is Fixed
`Product::toSearchableArray()` returns predetermined fields. Industry-specific fields can't be indexed without modifying the core model.

---

## Part II: Proposed Architecture

### High-Level Design

```
┌──────────────────────────────────────────────────────────────────┐
│                     PRODUCT DOMAIN BOUNDED CONTEXT               │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐    │
│  │              CORE COMMERCE ENGINE                         │    │
│  │  ┌──────────┐ ┌───────────┐ ┌────────┐ ┌────────────┐   │    │
│  │  │ Products │ │ Categories│ │ Brands │ │  Variants  │   │    │
│  │  ├──────────┤ ├───────────┤ ├────────┤ ├────────────┤   │    │
│  │  │ Pricing  │ │   Media   │ │  Tax   │ │ Attributes │   │    │
│  │  └──────────┘ └───────────┘ └────────┘ └────────────┘   │    │
│  └──────────────────────────────────────────────────────────┘    │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐    │
│  │              DYNAMIC FIELD ENGINE (DFE)                   │    │
│  │                                                          │    │
│  │  ┌───────────────┐  ┌─────────────────────────────────┐  │    │
│  │  │ BusinessTypes │  │ DynamicForms                    │  │    │
│  │  │ - slug        │  │ → Groups (sections)             │  │    │
│  │  │ - name        │  │ → Fields with typed columns     │  │    │
│  │  │ - is_active   │  │ → Validation rules per field    │  │    │
│  │  └───────┬───────┘  │ → UI metadata per field         │  │    │
│  │          │          │ → Conditional logic              │  │    │
│  │          └──────────┴─────────────────────────────────┘  │    │
│  └──────────────────────────────────────────────────────────┘    │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐    │
│  │              INDUSTRY PACK REGISTRY                       │    │
│  │                                                          │    │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐   │    │
│  │  │Pharmacy  │ │Restaurant│ │Salon     │ │Fashion   │   │    │
│  │  │Pack      │ │Pack      │ │Pack      │ │Pack      │   │    │
│  │  ├──────────┤ ├──────────┤ ├──────────┤ ├──────────┤   │    │
│  │  │Electro-  │ │Grocery   │ │Cosmetics │ │Wholesale │   │    │
│  │  │nics Pack │ │Pack      │ │Pack      │ │Pack      │   │    │
│  │  └──────────┘ └──────────┘ └──────────┘ └──────────┘   │    │
│  └──────────────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────────────┘
```

---

### Bounded Contexts

| Context | Responsibility | Key Models |
|---------|---------------|------------|
| **Core Product** | Product lifecycle, categorization, branding, media, attributes | Product, Category, Brand, Variant, ProductMedia |
| **Dynamic Schema** | Business types, field definitions, form structure, validation rules | BusinessType, DynamicFieldGroup, DynamicField |
| **Pricing** | Pricing rules, tax, discounts | PricingRule, TaxCategory, TaxRate |
| **Inventory** | Stock, warehouses, movements, reservations | Warehouse, WarehouseStock, StockMovement, StockReservation |
| **Industry Pack** | Pack registration, workflow hooks, extensions | IndustryPack interface, pack implementations |

---

### Database Schema (New Tables Only)

```sql
-- Tenant-level tables (in tenant DB)

-- BUSINESS TYPES
CREATE TABLE business_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,          -- 'pharmacy', 'restaurant', etc.
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    icon VARCHAR(100) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    metadata JSON NULL,                          -- default settings, config
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- DYNAMIC FIELD GROUPS (sections in the form)
CREATE TABLE dynamic_field_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_type_id BIGINT UNSIGNED NOT NULL,
    slug VARCHAR(100) NOT NULL,                  -- 'pharmacy_details', 'shipping', etc.
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    ui_component VARCHAR(100) NOT NULL DEFAULT 'FormSection',  -- frontend component name
    is_repeatable BOOLEAN NOT NULL DEFAULT FALSE,
    conditions JSON NULL,                        -- conditional visibility logic
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (business_type_id) REFERENCES business_types(id) ON DELETE CASCADE,
    UNIQUE KEY (business_type_id, slug)
);

-- DYNAMIC FIELD DEFINITIONS
CREATE TABLE dynamic_fields (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    business_type_id BIGINT UNSIGNED NOT NULL,
    slug VARCHAR(100) NOT NULL,                  -- 'generic_name', 'strength', etc.
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    
    -- Field type core
    field_type VARCHAR(50) NOT NULL,             -- text, number, select, multiselect, date, boolean, file, reference, measurement, compound
    is_required BOOLEAN NOT NULL DEFAULT FALSE,
    is_repeatable BOOLEAN NOT NULL DEFAULT FALSE, -- allows array of this field
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    default_value TEXT NULL,
    
    -- Options for select/multiselect
    options JSON NULL,                           -- [{"label": "Tablet", "value": "tablet"}, ...]
    
    -- Validation
    validation_rules JSON NULL,                  -- {"max": 255, "min": 0, "pattern": "^[A-Z]", ...}
    
    -- UI metadata
    ui_metadata JSON NULL,                       -- {"placeholder": "...", "help_text": "...", "section": "main", "width": "full"}
    
    -- Behavior flags
    is_filterable BOOLEAN NOT NULL DEFAULT FALSE,  -- can be used in product listing filters
    is_searchable BOOLEAN NOT NULL DEFAULT FALSE,   -- included in search index
    is_variant_field BOOLEAN NOT NULL DEFAULT FALSE,-- can differ per variant
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (group_id) REFERENCES dynamic_field_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (business_type_id) REFERENCES business_types(id) ON DELETE CASCADE,
    UNIQUE KEY (business_type_id, slug),
    INDEX (group_id, sort_order)
);

-- PRODUCT DYNAMIC VALUES (single table with typed columns — avoids pure JSON EAV)
CREATE TABLE product_dynamic_values (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id CHAR(26) NOT NULL,                -- FK to products (ULID)
    field_id BIGINT UNSIGNED NOT NULL,
    variant_id CHAR(26) NULL,                    -- nullable: per-variant field values
    instance_index INT UNSIGNED NOT NULL DEFAULT 0, -- for repeatable fields
    
    -- Typed value columns (only one is populated per row, based on field_type)
    value_text TEXT NULL,                         -- text, textarea, select
    value_int BIGINT NULL,                        -- integer, boolean (0/1)
    value_float DECIMAL(20, 4) NULL,              -- decimal, price
    value_date DATETIME NULL,                     -- date, datetime
    value_json JSON NULL,                         -- multiselect, compound, file metadata
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES dynamic_fields(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES variants(id) ON DELETE CASCADE,
    UNIQUE KEY (product_id, field_id, variant_id, instance_index),
    INDEX (field_id, value_text(100)),            -- for filtering on text fields
    INDEX (field_id, value_int),                  -- for filtering on numeric fields
    INDEX (field_id, value_float),                -- for filtering on float fields
    INDEX (field_id, value_date)                  -- for filtering on date fields
);

-- CENTRAL TABLE (not tenant-level)
-- business_types are defined centrally, replicated or referenced by tenant
CREATE TABLE central_business_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    icon VARCHAR(100) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    pack_class VARCHAR(255) NULL,                -- IndustryPack implementation class
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Tenant-to-business-type mapping
ALTER TABLE tenants ADD COLUMN business_type_id BIGINT UNSIGNED NULL AFTER tenancy_mode;
```

**Why typed columns instead of pure JSON:**
| Column | When Used | Queryable |
|--------|-----------|-----------|
| `value_text` | text, textarea, select | `WHERE value_text LIKE '%Paracetamol%'` — indexed with prefix |
| `value_int` | integer, boolean | `WHERE value_int = 1` — fully indexed |
| `value_float` | decimal, price, measurement | `WHERE value_float BETWEEN 10 AND 100` — fully indexed |
| `value_date` | date, datetime, expiry | `WHERE value_date < NOW()` — fully indexed |
| `value_json` | multiselect, compound, file references | JSON path queries, slower but rare |

This gives us **indexed, queryable typed columns** for 80%+ of field types, avoiding the pure JSON-EAV performance trap.

---

### Tenant Migration (Existing Schema)

Add `business_type_id` to the tenant:
```sql
-- Central DB migration
ALTER TABLE tenants ADD COLUMN business_type_id BIGINT UNSIGNED NULL;
ALTER TABLE tenants ADD CONSTRAINT fk_tenant_business_type 
    FOREIGN KEY (business_type_id) REFERENCES central_business_types(id);
```

This is a **zero-risk, additive migration**. No existing data is affected. Tenants without a business type get the default form.

---

### Migration Strategy from Existing System

**Phase 1 — Foundation (Safe, Non-breaking)**
1. Add `central_business_types` table (central DB)
2. Add `business_type_id` to `tenants` table (central DB, nullable)
3. Create tenant-level `business_types` tables
4. Seed default business types
5. No schema changes to existing `products` table — zero risk

**Phase 2 — Dynamic Field Definitions (Admin UI)**
1. Create CRUD for business types (super admin)
2. Create CRUD for field groups and fields (per business type)
3. Initial seed: define fields for each target industry
4. Existing product module untouched

**Phase 3 — Form Integration**
1. Backend: pass form structure to product create/edit pages based on tenant's business_type_id
2. Frontend: add `<DynamicFieldsSection>` below existing sections
3. Existing form sections remain — no breaking changes
4. Validation: `StoreProductRequest` merges dynamic validation rules

**Phase 4 — Data Persistence**
1. Create `product_dynamic_values` table
2. On product save, persist dynamic values alongside core fields
3. No data migration needed — existing products have no dynamic values by default

**Phase 5 — Industry Packs (Optional, Phased)**
1. Create `IndustryPack` interface
2. Implement packs one at a time (Pharmacy first, then Restaurant, etc.)
3. Register packs in service provider
4. Each pack auto-seeds its fields and registers its hooks

---

### Eloquent Model Architecture

```php
// === NEW MODELS ===

// Central (not tenant-scoped)
class CentralBusinessType extends Model
{
    protected $connection = 'central';
    protected $table = 'central_business_types';
    
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'business_type_id');
    }
}

// Tenant-scoped
class BusinessType extends Model
{
    use HasTenantScope;
    
    public function fieldGroups(): HasMany
    {
        return $this->hasMany(DynamicFieldGroup::class)->orderBy('sort_order');
    }
    
    public function fields(): HasMany
    {
        return $this->hasMany(DynamicField::class);
    }
}

class DynamicFieldGroup extends Model
{
    use HasTenantScope;
    
    public function businessType(): BelongsTo
    public function fields(): HasMany // ordered by sort_order
}

class DynamicField extends Model
{
    use HasTenantScope;
    
    public function group(): BelongsTo
    public function businessType(): BelongsTo
    public function values(): HasMany
    
    // Typed value accessor
    public function typedValue(ProductDynamicValue $value): mixed
    {
        return match($this->field_type) {
            'text', 'textarea', 'select' => $value->value_text,
            'number', 'boolean' => $value->value_int,
            'decimal', 'price', 'measurement' => $value->value_float,
            'date', 'datetime' => $value->value_date,
            'multiselect', 'compound', 'file' => $value->value_json,
        };
    }
    
    // Build Laravel validation rules
    public function buildValidationRules(bool $required = true): array
    {
        $rules = [];
        $rules[] = $required ? 'required' : 'nullable';
        
        $rules[] = match($this->field_type) {
            'text', 'textarea', 'select' => 'string',
            'number' => 'integer',
            'decimal', 'price' => 'numeric',
            'boolean' => 'boolean',
            'date', 'datetime' => 'date',
            'multiselect' => 'array',
            default => 'string',
        };
        
        if ($this->validation_rules) {
            foreach ($this->validation_rules as $rule) {
                $rules[] = $rule;
            }
        }
        
        return $rules;
    }
}

class ProductDynamicValue extends Model
{
    use HasTenantScope;
    
    public function product(): BelongsTo
    public function field(): BelongsTo
    public function variant(): BelongsTo
    
    public function getTypedValue(): mixed
    {
        return $this->field->typedValue($this);
    }
}

// === EXTENDED CORE MODEL ===
class Product extends Model
{
    // ... existing code stays completely unchanged
    
    // Add: Dynamic field relationship
    public function dynamicValues(): HasMany
    {
        return $this->hasMany(ProductDynamicValue::class);
    }
    
    // Convenience accessor — single string field value by slug
    public function getDynamicField(string $slug): mixed
    {
        foreach ($this->dynamicValues as $dv) {
            if ($dv->field->slug === $slug) {
                return $dv->getTypedValue();
            }
        }
        return null;
    }
    
    // Extended searchable array
    public function toSearchableArray(): array
    {
        $data = [ /* existing fields */ ];
        
        if ($this->relationLoaded('dynamicValues.field')) {
            foreach ($this->dynamicValues as $dv) {
                if ($dv->field->is_searchable) {
                    $data["d_{$dv->field->slug}"] = $dv->getTypedValue();
                }
            }
        }
        
        return $data;
    }
}
```

---

### Service Architecture

```php
// === NEW SERVICES ===

class DynamicFieldService
{
    // Returns form structure for a business type
    public function getFormStructure(string $businessTypeSlug): array;
    
    // Builds complete validation rules for a product request
    public function buildValidationRules(string $businessTypeSlug): array;
    
    // Persists dynamic field values for a product
    public function saveDynamicValues(Product $product, array $values): void;
    
    // Retrieves all dynamic values for a product (keyed by field slug)
    public function getDynamicValues(Product $product): Collection;
    
    // Clears dynamic values (for re-sync)
    public function clearDynamicValues(Product $product): void;
}

class BusinessTypeService
{
    // Assign tenant to a business type
    public function assignToTenant(Tenant $tenant, string $businessTypeSlug): void;
    
    // Get all fields for a tenant's business type
    public function getFieldsForTenant(Tenant $tenant): Collection;
    
    // Check if a dynamic field value is valid for this tenant
    public function validateFieldValue(Tenant $tenant, string $fieldSlug, mixed $value): bool;
}

class IndustryPackManager
{
    private array $packs = [];
    
    // Register a pack (called in service provider)
    public function register(IndustryPack $pack): void;
    
    // Get pack by slug
    public function getPack(string $slug): ?IndustryPack;
    
    // Get all registered packs
    public function getPacks(): array;
    
    // Execute pipeline hooks
    public function executePipeline(string $hook, Product $product, array $context = []): void;
}

// === EXTENDED CORE SERVICE ===
class ProductService
{
    public function __construct(
        protected Dispatcher $events,
        protected SKUGenerator $skuGenerator,
        protected DynamicFieldService $dynamicFieldService,
        protected IndustryPackManager $packManager,
    ) {}
    
    public function createProduct(ProductDTO $dto): Product
    {
        // 1. Create core product fields (unchanged)
        $product = Product::query()->create([...]);
        
        // 2. Save dynamic field values (NEW)
        if ($dto->dynamicFields) {
            $this->dynamicFieldService->saveDynamicValues($product, $dto->dynamicFields);
        }
        
        // 3. Execute industry pack hooks (NEW)
        $this->packManager->executePipeline('after_create', $product);
        
        // 4. Dispatch event (unchanged)
        $this->events->dispatch(ProductCreated::fromModel($product));
        
        return $product;
    }
}
```

---

### Validation Engine Design

```php
class DynamicValidationEngine
{
    public function __construct(
        protected IndustryPackManager $packManager,
    ) {}
    
    // Build complete validation rules for a business type
    public function buildRules(string $businessTypeSlug, ?string $productType = null): array
    {
        $fields = DynamicField::query()
            ->whereHas('group.businessType', fn($q) => $q->where('slug', $businessTypeSlug))
            ->get();
        
        $rules = [];
        
        foreach ($fields as $field) {
            $fieldRules = $field->buildValidationRules();
            
            // Add pack-specific rules
            $pack = $this->packManager->getPack($businessTypeSlug);
            if ($pack) {
                $packRules = $pack->extendValidationRules($field->slug);
                $fieldRules = array_merge($fieldRules, $packRules);
            }
            
            $rules["dynamic_fields.{$field->slug}"] = $fieldRules;
        }
        
        return $rules;
    }
    
    // Merge into existing form request
    public function extendRequest(FormRequest $request, string $businessTypeSlug): void
    {
        // Called via $request->merge() or custom validation
        $request->merge([
            'dynamic_fields' => $request->input('dynamic_fields', []),
        ]);
    }
}

// StoreProductRequest — extended:
class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [ /* existing 20+ rules */ ];
        
        // Merge dynamic field rules based on tenant's business type
        $tenant = $this->user()->tenant;
        if ($tenant->business_type_id) {
            $businessType = BusinessType::find($tenant->business_type_id);
            if ($businessType) {
                $engine = app(DynamicValidationEngine::class);
                $dynamicRules = $engine->buildRules($businessType->slug);
                $rules = array_merge($rules, $dynamicRules);
            }
        }
        
        return $rules;
    }
}
```

---

### Form Generation Architecture

**Backend** — returns form structure to frontend:

```php
// In ProductController::create() and ProductController::edit()
public function create(): Response
{
    $tenant = auth()->user()->tenant;
    $formStructure = [];
    
    if ($tenant->business_type_id) {
        $formStructure = app(DynamicFieldService::class)
            ->getFormStructure($tenant->businessType->slug);
    }
    
    return Inertia::render('Product/Create', [
        'categories' => $this->categoryService->getCategoryTree(),
        'brands' => $this->brandService->listActiveBrands(),
        'form_structure' => $formStructure,
        'dynamic_values' => $existingValues ?? [],
    ]);
}
```

**Frontend** — generic dynamic field renderer:

```typescript
// DynamicFieldsSection — renders all groups for the business type
function DynamicFieldsSection({ 
    groups, 
    values, 
    errors, 
    onChange 
}: DynamicFieldsSectionProps) {
    if (!groups.length) return null;
    
    return groups.map(group => (
        <DynamicFieldGroup
            key={group.slug}
            group={group}
            values={values}
            errors={errors}
            onChange={onChange}
        />
    ));
}

// DynamicFieldGroup — renders a field group section
function DynamicFieldGroup({ group, values, errors, onChange }) {
    const GroupComponent = resolveGroupComponent(group.ui_component);
    
    return (
        <FormSection
            title={group.name}
            description={group.description}
        >
            {group.fields.map(field => (
                <DynamicFieldInput
                    key={field.slug}
                    field={field}
                    value={values[field.slug]}
                    error={errors[field.slug]}
                    onChange={(val) => onChange(`dynamic_fields.${field.slug}`, val)}
                />
            ))}
        </FormSection>
    );
}

// DynamicFieldInput — renders the appropriate input for a field type
function DynamicFieldInput({ field, value, error, onChange }) {
    const Component = FIELD_TYPE_MAP[field.field_type] ?? TextInput;
    
    if (field.conditions) {
        return (
            <ConditionalField
                conditions={field.conditions}
                values={values}
            >
                <Component
                    label={field.name}
                    helpText={field.ui_metadata?.help_text}
                    placeholder={field.ui_metadata?.placeholder}
                    required={field.is_required}
                    options={field.options}
                    value={value ?? field.default_value}
                    error={error}
                    onChange={onChange}
                />
            </ConditionalField>
        );
    }
    
    return (
        <Component
            label={field.name}
            helpText={field.ui_metadata?.help_text}
            placeholder={field.ui_metadata?.placeholder}
            required={field.is_required}
            options={field.options}
            value={value ?? field.default_value}
            error={error}
            onChange={onChange}
        />
    );
}

// Zod schema generation from form structure
function buildDynamicZodSchema(groups: DynamicFieldGroup[]): z.ZodObject {
    const shape: Record<string, z.ZodTypeAny> = {};
    
    for (const group of groups) {
        for (const field of group.fields) {
            let schema = mapFieldType(field.field_type);
            
            if (field.is_required) {
                schema = schema.refine(v => v !== null && v !== undefined && v !== '', {
                    message: `${field.name} is required`,
                });
            } else {
                schema = schema.nullable().optional();
            }
            
            shape[field.slug] = schema;
        }
    }
    
    return z.object(shape);
}
```

**Integration into ProductFormPage:**

```tsx
export function ProductFormPage({ mode, initialData, submitRoute, ... }) {
    const { form_structure, dynamic_values } = usePage().props;
    
    return (
        <form onSubmit={handleSubmit}>
            <ProductGeneralSection ... />
            <ProductPricingSection ... />
            
            {/* Dynamic industry fields injected between core sections */}
            <DynamicFieldsSection
                groups={form_structure}
                values={{ ...dynamic_values }}
                errors={errors}
                onChange={onChange}
            />
            
            <ProductInventorySection ... />
            <ProductShippingSection ... />
            <ProductSeoSection ... />
        </form>
    );
}
```

---

### Industry Pack Architecture

```php
// === INTERFACE ===
interface IndustryPack
{
    public function slug(): string;
    public function name(): string;
    public function description(): string;
    
    // Field groups and fields this pack registers
    public function fieldGroups(): array;
    
    // Extend validation rules for specific fields
    public function extendValidationRules(string $fieldSlug): array;
    
    // Pipeline hooks for product lifecycle
    public function pipelines(): array;
    
    // Additional searchable fields
    public function searchableFields(): array;
    
    // Inventory extensions
    public function inventoryExtensions(): array;
    
    // Report definitions
    public function reportDefinitions(): array;
    
    // Permissions this pack requires
    public function permissions(): array;
    
    // Called when tenant is assigned this business type
    public function onTenantAssigned(Tenant $tenant): void;
}

// === IMPLEMENTATION EXAMPLE ===
class PharmacyPack implements IndustryPack
{
    public function slug(): string { return 'pharmacy'; }
    public function name(): string { return 'Pharmacy'; }
    public function description(): string { return 'Pharmacy industry pack with drug management'; }
    
    public function fieldGroups(): array
    {
        return [
            [
                'slug' => 'pharmacy_details',
                'name' => 'Pharmacy Details',
                'sort_order' => 10,
                'ui_component' => 'FormSection',
                'fields' => [
                    [
                        'slug' => 'generic_name',
                        'name' => 'Generic Name',
                        'field_type' => 'text',
                        'is_required' => true,
                        'validation_rules' => ['max:500'],
                        'ui_metadata' => [
                            'placeholder' => 'Paracetamol',
                            'help_text' => 'International nonproprietary name (INN)',
                        ],
                        'is_searchable' => true,
                        'is_filterable' => true,
                    ],
                    [
                        'slug' => 'strength',
                        'name' => 'Strength',
                        'field_type' => 'text',
                        'is_required' => true,
                        'validation_rules' => ['max:100'],
                        'ui_metadata' => ['placeholder' => '500mg'],
                        'is_filterable' => true,
                    ],
                    [
                        'slug' => 'dosage_form',
                        'name' => 'Dosage Form',
                        'field_type' => 'select',
                        'is_required' => true,
                        'options' => [
                            ['label' => 'Tablet', 'value' => 'tablet'],
                            ['label' => 'Capsule', 'value' => 'capsule'],
                            ['label' => 'Syrup', 'value' => 'syrup'],
                            ['label' => 'Injection', 'value' => 'injection'],
                            ['label' => 'Cream', 'value' => 'cream'],
                            ['label' => 'Drops', 'value' => 'drops'],
                            ['label' => 'Inhaler', 'value' => 'inhaler'],
                            ['label' => 'Suppository', 'value' => 'suppository'],
                        ],
                        'is_filterable' => true,
                    ],
                    [
                        'slug' => 'manufacturer',
                        'name' => 'Manufacturer',
                        'field_type' => 'text',
                        'is_searchable' => true,
                    ],
                    [
                        'slug' => 'prescription_required',
                        'name' => 'Prescription Required',
                        'field_type' => 'boolean',
                        'default_value' => false,
                        'is_filterable' => true,
                    ],
                    [
                        'slug' => 'batch_number',
                        'name' => 'Batch Number',
                        'field_type' => 'text',
                        'is_required' => true,
                    ],
                    [
                        'slug' => 'expiry_date',
                        'name' => 'Expiry Date',
                        'field_type' => 'date',
                        'is_required' => true,
                        'validation_rules' => ['after:today'],
                        'is_filterable' => true,
                    ],
                    [
                        'slug' => 'storage_condition',
                        'name' => 'Storage Condition',
                        'field_type' => 'select',
                        'options' => [
                            ['label' => 'Room Temperature', 'value' => 'room_temp'],
                            ['label' => 'Refrigerated', 'value' => 'refrigerated'],
                            ['label' => 'Frozen', 'value' => 'frozen'],
                            ['label' => 'Controlled Substance', 'value' => 'controlled'],
                        ],
                    ],
                    [
                        'slug' => 'drug_schedule',
                        'name' => 'Drug Schedule',
                        'field_type' => 'select',
                        'options' => [
                            ['label' => 'OTC', 'value' => 'otc'],
                            ['label' => 'Schedule H', 'value' => 'schedule_h'],
                            ['label' => 'Schedule H1', 'value' => 'schedule_h1'],
                            ['label' => 'Schedule X', 'value' => 'schedule_x'],
                            ['label' => 'Narcotic', 'value' => 'narcotic'],
                        ],
                        'is_filterable' => true,
                    ],
                ],
            ],
            [
                'slug' => 'pharmacy_pricing',
                'name' => 'Pricing & Insurance',
                'sort_order' => 20,
                'ui_component' => 'FormSection',
                'fields' => [
                    [
                        'slug' => 'mrp',
                        'name' => 'Maximum Retail Price (MRP)',
                        'field_type' => 'price',
                        'is_required' => true,
                    ],
                    [
                        'slug' => 'gst_slab',
                        'name' => 'GST Slab',
                        'field_type' => 'select',
                        'options' => [
                            ['label' => '0% (Exempted)', 'value' => '0'],
                            ['label' => '5%', 'value' => '5'],
                            ['label' => '12%', 'value' => '12'],
                            ['label' => '18%', 'value' => '18'],
                            ['label' => '28%', 'value' => '28'],
                        ],
                        'is_filterable' => true,
                    ],
                ],
            ],
        ];
    }
    
    public function pipelines(): array
    {
        return [
            'before_validate' => [
                ValidateExpiryDate::class,
                ValidateBatchNumber::class,
            ],
            'after_create' => [
                GenerateBatchTrackingRecord::class,
            ],
            'before_sale' => [
                CheckPrescriptionRequirement::class,
                CheckExpiryAtPOS::class,
            ],
        ];
    }
    
    public function inventoryExtensions(): array
    {
        return [
            'supports_batch_tracking' => true,
            'supports_expiry_tracking' => true,
            'stock_picking_strategy' => 'fifo_by_expiry',
        ];
    }
    
    public function permissions(): array
    {
        return [
            'pharmacy.view_drug_schedule',
            'pharmacy.approve_prescription',
            'pharmacy.manage_controlled_drugs',
        ];
    }
    
    public function onTenantAssigned(Tenant $tenant): void
    {
        // Seed default pharmacy categories
        // Create default pharmacy-specific settings
        // Assign pharmacy-specific roles/permissions
    }
}
```

**Pack Registration:**

```php
// AppServiceProvider or dedicated IndustryServiceProvider
class IndustryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(IndustryPackManager::class);
    }
    
    public function boot(): void
    {
        $manager = $this->app->make(IndustryPackManager::class);
        
        $manager->register(new PharmacyPack());
        $manager->register(new RestaurantPack());
        $manager->register(new ElectronicsPack());
        $manager->register(new FashionPack());
        $manager->register(new SalonPack());
        $manager->register(new GroceryPack());
        $manager->register(new CosmeticsPack());
        $manager->register(new HardwarePack());
        $manager->register(new WholesalePack());
    }
}
```

**Pack Seeding on Tenant Assignment:**
When a tenant is assigned a business type, the pack auto-seeds its fields:

```php
class IndustryPackManager
{
    public function seedForTenant(Tenant $tenant, string $businessTypeSlug): void
    {
        $pack = $this->getPack($businessTypeSlug);
        if (!$pack) return;
        
        $centralBT = CentralBusinessType::where('slug', $businessTypeSlug)->first();
        
        Tenancy::initialize($tenant);
        
        $businessType = BusinessType::create([
            'slug' => $pack->slug(),
            'name' => $pack->name(),
            'description' => $pack->description(),
        ]);
        
        $sortOrder = 0;
        foreach ($pack->fieldGroups() as $groupData) {
            $group = $businessType->fieldGroups()->create([
                'slug' => $groupData['slug'],
                'name' => $groupData['name'],
                'sort_order' => $groupData['sort_order'] ?? $sortOrder++,
                'ui_component' => $groupData['ui_component'] ?? 'FormSection',
            ]);
            
            foreach ($groupData['fields'] as $fieldData) {
                $group->fields()->create($fieldData + [
                    'business_type_id' => $businessType->id,
                ]);
            }
        }
        
        $pack->onTenantAssigned($tenant);
        
        Tenancy::end();
    }
}
```

---

### Inventory Integration Strategy

The existing inventory system (Warehouse → WarehouseStock → StockMovement) is robust. Industry extensions plug in via the `IndustryPack::inventoryExtensions()` method:

| Industry | Inventory Extension | Implementation |
|----------|-------------------|----------------|
| **Pharmacy** | Batch + Expiry tracking | `product_dynamic_values` stores batch_number + expiry_date per stock movement; FIFO picking by expiry |
| **Electronics** | IMEI + Serial tracking | New `serial_numbers` table: each unit tracked individually; warranty start date on sale |
| **Restaurant** | Recipe ingredient tracking | Recipe as "product" with BOM; ingredient stock deducted when recipe is sold |
| **Fashion** | Size/Color matrix stock | Size-color combinations as variants with per-combination stock |
| **Hardware** | Unit conversion (meters, kg) | Measurement tracking: sell by meter/kg with decimal quantity support |

No core inventory tables are modified. Extensions use:
1. Dynamic fields for metadata per product
2. Optional feature flags on `warehouse_stock` (e.g., `batch_number`, `expiry_date`)
3. New extension tables when necessary (e.g., `serial_numbers` for electronics)

---

### Reporting Integration Strategy

```php
interface IndustryReportProvider
{
    public function slug(): string;
    public function reports(): array; // Report definitions
    public function queries(): array; // Eloquent query builders per report
    public function charts(): array;  // Chart configurations
}

// Example: PharmacyPack provides
$reports = [
    'expiring_products' => [
        'name' => 'Expiring Products',
        'description' => 'Products expiring within selected date range',
        'query' => fn() => ProductDynamicValue::query()
            ->whereHas('field', fn($q) => $q->where('slug', 'expiry_date'))
            ->where('value_date', '<=', $endDate)
            ->where('value_date', '>=', $startDate),
    ],
    'prescription_vs_otc' => [
        'name' => 'Prescription vs OTC Sales',
        'description' => 'Sales breakdown by prescription requirement',
        'chart' => ['type' => 'pie', 'group_by' => 'dynamic.prescription_required'],
    ],
];
```

---

### Search Strategy

```php
// Product model extended toSearchableArray
public function toSearchableArray(): array
{
    $data = [
        'objectID' => (string) $this->id,
        'name' => $this->name,
        'slug' => $this->slug,
        'sku' => $this->sku,
        'barcode' => $this->barcode,
        'description' => $this->description,
        'base_price' => $this->base_price,
        'status' => $this->status?->value,
        'type' => $this->type?->value,
        'category_id' => $this->category_id,
        'brand_id' => $this->brand_id,
        'category_name' => $this->category?->name,
        'brand_name' => $this->brand?->name,
        'total_stock' => $this->total_available,
        'created_at' => $this->created_at?->timestamp,
    ];
    
    // Append searchable dynamic fields
    if ($this->relationLoaded('dynamicValues.field')) {
        $pack = app(IndustryPackManager::class)->getPack(
            $this->tenant?->businessType?->slug
        );
        $searchableSlugs = $pack?->searchableFields() ?? [];
        
        foreach ($this->dynamicValues as $dv) {
            if ($dv->field->is_searchable || in_array($dv->field->slug, $searchableSlugs)) {
                $data["d_{$dv->field->slug}"] = $dv->getTypedValue();
            }
        }
    }
    
    return $data;
}
```

Dynamic field values are accessible in Meilisearch/Algolia via the `d_*` prefix, enabling industry-specific search without schema changes.

---

### Permission Strategy

| Permission | Scope | Description |
|-----------|-------|-------------|
| Existing `products.*` | All products | Core CRUD (unchanged) |
| `business_types.manage` | Central admin | CRUD business types and field definitions |
| `industry.{slug}.manage` | Per-tenant | Manage industry-specific configuration |
| `industry.{slug}.fields.*` | Per-tenant | Per-field permission (e.g., pharmaceutical schedule access) |

Packs register their own permissions:

```php
// PharmacyPack::permissions() returns:
[
    'pharmacy.view_drug_schedule',
    'pharmacy.approve_prescription', 
    'pharmacy.manage_controlled_drugs',
]
```

---

### API Design

```php
// === BUSINESS TYPES ===
Route::get('/api/business-types', [BusinessTypeController::class, 'index']);
Route::get('/api/business-types/{businessType}', [BusinessTypeController::class, 'show']);

// === DYNAMIC FIELD DEFINITIONS ===
Route::apiResource('business-types.dynamic-field-groups', DynamicFieldGroupController::class);
Route::apiResource('dynamic-field-groups.dynamic-fields', DynamicFieldController::class);

// === PRODUCT DYNAMIC VALUES ===
Route::get('/products/{product}/dynamic-values', [ProductDynamicValueController::class, 'index']);
Route::put('/products/{product}/dynamic-values', [ProductDynamicValueController::class, 'update']);

// === EXTENDED PRODUCT ENDPOINT ===
// POST /products — now accepts dynamic_fields
{
    "name": "Paracetamol 500mg",
    "base_price": 500,
    "type": "simple",
    "status": "active",
    "category_id": 1,
    "brand_id": 1,
    
    "dynamic_fields": {
        "generic_name": "Paracetamol",
        "strength": "500mg",
        "dosage_form": "tablet",
        "manufacturer": "Square Pharmaceuticals",
        "prescription_required": false,
        "batch_number": "B2026-001",
        "expiry_date": "2028-06-15",
        "storage_condition": "room_temp",
        "drug_schedule": "otc",
        "mrp": 750,
        "gst_slab": "5"
    }
}

// GET /products?filter[dynamic.generic_name]=Paracetamol&filter[dynamic.dosage_form]=tablet
// Filtering by dynamic fields via query string convention
```

---

### Performance Considerations

| Concern | Strategy |
|---------|----------|
| **EAV Query Performance** | Typed columns with dedicated indexes; avoid JSON blob pattern |
| **N+1 Prevention** | Always eager-load `dynamicValues.field` on product listings |
| **Form Caching** | Cache field definitions per business type in Redis (TTL: 1 hour), invalidated on field create/update |
| **Product Listing** | Use Scout/Meilisearch for product search with dynamic fields; avoid SQL joins on dynamic values for searches |
| **Import/Export** | Batch operations on `product_dynamic_values`; use chunked upserts |
| **Frontend Bundle** | Lazy-load industry pack frontend components by tenant business type; never ship all packs to all tenants |
| **Heavy Fields** | Fields with >10k options should use server-side search/autocomplete |
| **Reindexing** | Dynamic field changes trigger scout reindexing only for affected business type |

---

### Risks & Tradeoffs

| Risk | Severity | Mitigation |
|------|----------|------------|
| EAV query performance degrades with scale | Medium | Typed columns + dedicated indexes + Scout for search; dynamic fields not used for high-throughput analytical queries |
| Dynamic validation is harder to test than static form requests | Medium | Unit test validation engine; integration test each industry pack's rules independently |
| Complex conditional form logic confuses users | Low | Keep conditional logic simple (field → value → show/hide) with clear UI indicators |
| Industry pack API must remain stable | Medium | Define `IndustryPack` interface with `@return` types; semantic versioning for packs |
| Existing tenants need business type assignment | Low | Default to a "General" business type; gradual migration per tenant request |
| Frontend bundle bloat | Medium | Dynamic imports per business type; only load packs relevant to the current tenant |
| Cross-industry data consistency | Low | Business type is per-tenant, not per-product; all products in a tenant share the same fields |
| Tenant migration (shared → dedicated DB) needs dynamic field data | Low | `product_dynamic_values` replicates alongside all other tables; no special handling |

---

### Summary: What Changes vs. What Stays

| Component | Status | Notes |
|-----------|--------|-------|
| `products` table | **Unchanged** | All existing columns remain |
| `variants` table | **Unchanged** | Existing variant system untouched |
| Product model | **Extended** | Adds `dynamicValues()` relationship, `toSearchableArray()` extension |
| ProductService | **Extended** | Injects `DynamicFieldService` and `IndustryPackManager` |
| StoreProductRequest | **Extended** | Merges dynamic validation rules |
| ProductController | **Minimally changed** | Passes `form_structure` + `dynamic_values` to Inertia |
| Frontend form | **Extended** | Adds `<DynamicFieldsSection>` between existing sections |
| Existing tests | **Unchanged** | All pass; new tests cover new functionality |
| Inventory, Pricing | **Unchanged** | No changes to existing systems |
| **NEW: BusinessType** | **Added** | Central + tenant tables for industry definitions |
| **NEW: DynamicField** | **Added** | Field definitions with validation, UI metadata, typed storage |
| **NEW: ProductDynamicValue** | **Added** | Typed column storage for dynamic field values |
| **NEW: IndustryPack** | **Added** | Interface + implementations per industry |
| **NEW: DynamicFieldService** | **Added** | Form building, saving, retrieving dynamic values |
| **NEW: DynamicValidationEngine** | **Added** | Rule building from field definitions + pack extensions |

---

### Implementation Roadmap

| Phase | Duration | Deliverables |
|-------|----------|-------------|
| **1 Foundation** | 1 week | Business types tables + models, tenant assignment |
| **2 Field Definitions** | 1 week | Dynamic fields CRUD, validation engine, field type support |
| **3 Backend Integration** | 1 week | DynamicFieldService, Product model extension, StoreProductRequest extension, API endpoints |
| **4 Frontend** | 1.5 weeks | DynamicFieldInput components, DynamicFieldsSection, Zod schema generation, conditional fields |
| **5 First Pack** | 1 week | PharmacyPack implementation + tests |
| **6 Remaining Packs** | 2.5 weeks | Restaurant, Electronics, Fashion, Salon, Grocery, Cosmetics, Hardware, Wholesale packs |
| **7 Inventory Extensions** | 1 week | Batch tracking (pharmacy), IMEI tracking (electronics), recipe BOM (restaurant) |
| **8 Search + Reporting** | 0.5 week | Dynamic field search indexing, report providers |
| **9 Testing + Polish** | 1 week | Integration tests for all packs, performance testing, documentation |

**Total estimated effort: ~8.5 weeks for full implementation.**

---

Shall I proceed with implementation? I recommend starting with Phase 1 (Foundation) which has zero risk to existing functionality.

---

