# SOUDA — AI Agent Guide

This file is for AI coding agents. It defines exact conventions, patterns, guardrails, and templates for working on the entire SOUDA platform.

---

## Project Identity

- **App**: SOUDA — multi-tenant, multi-vertical business management platform
- **PHP**: 8.4.16
- **Laravel**: 12
- **React**: 19 + Inertia.js v2 + Tailwind CSS v4
- **Tenancy**: hybrid (shared `souda_shared` + per-tenant dedicated DBs), using stancl/tenancy v3
- **Auth**: Laravel Fortify v1 (headless) with Inertia React views
- **Packs**: 15 industry packs in `IndustryPack` interface
- **Templates**: 16 `TenantTemplate` implementations per business type
- **Config caching**: 24h TTL in Laravel cache + `tenant_configs` table
- **Database**: MySQL only (central + shared + dedicated per Enterprise)

---

## Global Agent Guardrails (MUST FOLLOW)

### NEVER
- Add hardcoded business type checks (`if ($slug === 'pharmacy')`)
- Use `switch`/`case` on industry slugs
- Create per-industry tables or columns
- Use EAV patterns
- Modify core module code when adding a new industry
- Use `DB::` when an Eloquent relationship exists
- Add inline validation — use Form Requests
- Store per-industry logic in controllers, services, or views
- Add comments in code (use PHPDoc blocks for complex logic instead)
- Allow empty `__construct()` with zero params (unless private)
- Use `env()` outside config files — use `config()` always
- Commit secrets, .env files, or credentials

### ALWAYS
- Implement `IndustryPack` interface for new industries
- Create `TenantTemplate` for new business types
- Register packs in `IndustryServiceProvider::boot()`
- Register templates in `OnboardingServiceProvider::boot()`
- Seed central DB when adding a new business type (BusinessTypeSeeder + BusinessTypeModuleSeeder)
- Run Pint after modifying PHP files: `vendor/bin/pint --format agent`
- Use `php artisan make:` commands (with `--no-interaction` + correct options)
- Use proper return type hints on all methods
- Use constructor property promotion
- Use curly braces for control structures (even single-line)
- Use Form Request classes for validation
- Prefer Eloquent relationships over raw queries
- Use PHP 8 typed properties throughout
- Add `->orderBy('id')` before `->each()` (Laravel 12 requirement)

---

## Architecture Overview

```
┌──────────────────────────────────────────────────────────────────────┐
│                         HTTP Request                                 │
└──────────────────────────────────────────────────────────────────────┘
                               │
                      ┌────────▼────────┐
                      │  Middleware:     │
                      │  web, auth,      │
                      │  tenancy, sub    │
                      └────────┬────────┘
                               │
               ┌───────────────┼───────────────┐
               │               │               │
               ▼               ▼               ▼
      ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
      │  Central DB  │ │  Shared DB   │ │  Dedicated   │
      │   (souda)    │ │(souda_shared)│ │  Tenant DBs  │
      │  users       │ │  tenant_     │ │  products    │
      │  tenants     │ │  settings    │ │  inventory   │
      │  billing     │ │  tasks       │ │  orders      │
      │  business_   │ │  tenant_     │ │  categories  │
      │  types       │ │  configs     │ │  ...         │
      └──────────────┘ └──────────────┘ └──────────────┘
```

### Database Connections

| Name | Database | Purpose | Models use |
|---|---|---|---|
| `central` | `souda` | Users, tenants, billing, business types, roles | `CentralConnection` trait |
| `shared` | `souda_shared` | Shared-mode tenant data | `HasTenantScope` trait |
| `mysql` | (template) | Template for dedicated tenant DBs | Normal Eloquent |

### Tenancy Modes

| Mode | Storage | Isolation | Plans |
|---|---|---|---|
| `shared` | `souda_shared` table | `tenant_id` column | Free, Starter, Professional |
| `dedicated` | `souda_tenant_{uuid}` DB | Separate database | Enterprise |

---

## Authentication Patterns

### Fortify Setup

Fortify is headless (no Jetstream). Custom Inertia views in `resources/js/pages/auth/`.

**Registration Includes Business Type:**
```php
// Register view passes business types for dropdown
Fortify::registerView(fn () => Inertia::render('auth/register', [
    'businessTypes' => BusinessType::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'slug', 'name', 'description', 'icon']),
]));
```

**CreateNewUser** (`app/Actions/Fortify/CreateNewUser.php`):
1. Validates `name`, `email`, `password`, optional `business_type_slug` (validated against `business_types` table)
2. Creates Tenant → User → updates Tenant owner
3. Stores `business_type_slug` in session: `session()->put('onboarding.business_type', $slug)`
4. Redirects to `/onboarding` via RegisterResponse

**RegisterResponse** (`app/Http/Responses/RegisterResponse.php`):
```php
return redirect()->intended('/onboarding');
```

### Social Auth

Providers: Google, GitHub. Guarded by `AppSetting::getBoolean('social_auth_enabled')`.
Routes in `routes/web.php`: `/auth/{provider}/redirect`, `/auth/{provider}/callback`.

### Rate Limiting

Login: 6/min per email+IP. Two-factor challenge: 5/min.

---

## Tenant System Patterns

### TenantManager (`app/Tenancy/TenantManager.php`)

Singleton central orchestrator. Key method: `initialize(Tenant $tenant)` resolves mode strategy.

### Mode Strategies

**SharedMode** — sets `config('database.default')` to `'shared'`, does NOT call `tenancy()->initialize()`.
**DedicatedMode** — calls `tenancy()->initialize($tenant)` (stancl native multi-DB).

### HasTenantScope Trait

Used by all shared-mode models (in `app/Tenancy/Models/Concerns/`). Uses `app()` helper with try-catch:

```php
public static function bootHasTenantScope(): void
{
    try {
        static::addGlobalScope(app(TenantScope::class));
    } catch (\Throwable) {
        // No app context (unit tests)
    }
    static::creating(function ($model) {
        try {
            $manager = app(TenantManager::class);
            if ($manager->initialized() && $manager->isShared() && ! $model->tenant_id) {
                $model->tenant_id = $manager->id();
            }
        } catch (\Throwable) {
            // No app context
        }
    });
}
```

### TenantScope (`app/Tenancy/Scopes/TenantScope.php`)

Adds `WHERE tenant_id = ?` in shared mode. Provides `withoutTenancy()` macro.

### InitializeTenancyByUser Middleware

Gets tenant from `$request->user()->tenant`, calls `TenantManager::initialize()`. Runs before `SubstituteBindings`.

---

## Product Module Patterns

### Models (17)

All in `app/Modules/Product/Models/`. Most have `HasTenantScope`, `CentralConnection`, or normal Eloquent.

**Product** — ULID PK, `ProductTypeEnum`, `ProductStatusEnum`, `Searchable` (Scout/Algolia), slugs, media.
**Variant** — ULID, belongsTo Product, own SKU/barcode/price/stock.
**Category** — hierarchical via `HasMaterializedPath`, BelongsToMany via `category_product` pivot.
**WarehouseStock** — HasOptimisticLocking (`lock_version`), tracks qty + reserved_qty.
**StockReservation** — statuses: active/consumed/expired/cancelled.

### Controllers

ProductController (CRUD + archive/restore/publish/duplicate), CategoryController, BrandController, AttributeController, StockController.

### Services (20)

ProductService, CategoryService, BrandService, VariantService, AttributeService, MediaService, WarehouseService, StockService, StockLockService, StockAuditService, StockReservationService, TaxService, PricingRuleService, ProductImportService + contract implementations.

### Events (14)

ProductCreated/Updated/Deleted/Archived/Published, VariantCreated/Updated/Deleted, StockUpdated/Depleted, LowStockAlert, StockReservationCreated/Expired, StockTransferCompleted.

### Observers

ProductObserver, VariantObserver, WarehouseStockObserver, StockReservationObserver.

### Policies

ProductPolicy, CategoryPolicy, BrandPolicy, WarehousePolicy.

---

## Onboarding System Patterns

### Flow

```
Registration → /onboarding (business type picker)
  → POST /onboarding/select-type (stores in session)
  → GET /onboarding/provision (shows progress UI)
  → POST /onboarding/run (runs 10-step pipeline)
  → redirect /dashboard
```

### Routes (`routes/onboarding.php`)

```
GET    /onboarding                    -> OnboardingController@start
POST   /onboarding/select-type        -> OnboardingController@selectType
GET    /onboarding/provision          -> OnboardingController@provision
POST   /onboarding/run                -> OnboardingController@run
GET    /onboarding/{tenant}/progress  -> OnboardingController@progress
GET    /api/business-types             -> BusinessTypeController@index
```

### TenantTemplate Contract (`app/Modules/Onboarding/Contracts/TenantTemplate.php`)

```php
interface TenantTemplate
{
    public function businessType(): string;
    public function defaultCategories(): array;
    public function productSchema(): array;     // fields, sections, search_columns, list_columns
    public function dashboardLayout(): array;   // widgets with width/order
    public function posDefaults(): array;       // layout, tender_types, specialty settings
    public function defaultTeam(): array;       // roles array
    public function notificationDefaults(): array;
    public function initialData(): array;
}
```

All 16 templates in `app/Modules/Onboarding/Templates/`, registered in `OnboardingServiceProvider::boot()`.

### ProvisioningStep Contract (`app/Modules/Onboarding/Contracts/ProvisioningStep.php`)

```php
interface ProvisioningStep
{
    public function handle(ProvisioningContext $context): void;
    public function rollback(ProvisioningContext $context): void;
    public function label(): string;
}
```

### 10 Pipeline Steps (in `app/Modules/Onboarding/Services/`)

| Step | Label | What It Does |
|---|---|---|
| `CreateTenantStep` | Initializing workspace | Sets onboarding_status to 'provisioning' |
| `AssignBusinessTypeStep` | Assigning business type | Sets business_type_id on tenant |
| `ProvisionModulesStep` | Enabling modules | Enables modules per business type |
| `CreatePermissionsStep` | Creating roles | Creates role-permission mappings |
| `SeedDefaultDataStep` | Seeding data | Seeds initial categories, defaults |
| `ConfigureProductSchemaStep` | Configuring product fields | Sets product schema fields |
| `ConfigureDashboardStep` | Setting up dashboard | Adds industry-specific widget layout |
| `ConfigurePOSStep` | Configuring POS | Sets POS defaults per industry |
| `CreateDefaultTeamStep` | Setting up team | Creates default team roles |
| `BuildTenantConfigStep` | Building config | Builds + caches TenantConfig |

### Onboarding Events (in `app/Modules/Onboarding/Events/`)

- `OnboardingStarted` — pipeline begins
- `OnboardingStepCompleted` — each step completes
- `OnboardingCompleted` — all 10 steps succeed, tenant `onboarding_status = 'completed'`
- `OnboardingFailed` — step throws, all completed steps rolled back in reverse

### ProvisioningPipeline (`app/Modules/Onboarding/Services/ProvisioningPipeline.php`)

```php
$pipeline->run($tenant, $businessTypeSlug, $planData = []);
// Or resume from a failed step:
$pipeline->resumeFrom($tenant, $stepClass, $planData = []);
```

---

## Business Type Engine Patterns

### IndustryPack Contract (`app/Modules/BusinessType/Contracts/IndustryPack.php`)

```php
interface IndustryPack
{
    public function slug(): string;
    public function name(): string;
    public function description(): string;
    public function modules(): array;           // ['module_slug' => ['required' => bool]]
    public function menus(): array;             // navigation structure
    public function permissions(): array;       // role => permissions map
    public function posConfig(): array;         // layout, tender_types, features
    public function dashboardWidgets(): array;  // widgets with component/title/width
    public function reportDefinitions(): array; // reports with permission/filters
    public function defaultSettings(): array;   // currency, default markup, etc.
    public function featureFlags(): array;      // key => bool
    public function onTenantAssigned(Tenant $tenant): void;
    public function onTenantRemoved(Tenant $tenant): void;
}
```

### Config Resolution Chain

```
IndustryPack defaults
  → BusinessTypeConfigBuilder::build()
    → applyPlanGating() (check plan features/limits)
    → applyTenantOverrides() (tenant_configs table overrides)
  → cached 24h in Laravel cache + tenant_configs table
```

### TenantConfig API

```php
$config->businessType;        // string slug
$config->enabledModules;      // string[]
$config->menus;               // array
$config->permissions;         // array
$config->fieldDefinitions;    // array
$config->dashboardWidgets;    // array
$config->posConfig;           // array
$config->reportDefinitions;   // array
$config->settings;            // array (merged)

// Helpers
$config->hasModule('kitchen');         // bool
$config->hasFeature('batch_tracking'); // bool
```

---

## Billing System Patterns

### Subscription Status Lifecycle

```
PendingPayment → (payment received) → Active
Trial          → (auto on create)  → Active
Active         → (cancelled)       → Grace
Grace          → (expired)         → Expired
                                     Cancelled (immediate)
```

### SubscriptionService::createSubscription() Logic

```php
// 1. Calculate amount from plan price
$amount = $plan->monthly_price;

// 2. Check trial availability
$trialAvailable = $plan->trial_enabled && $tenant && !$tenant->trial_used;

// 3. Determine path:
if ($trialAvailable && $plan->trial_without_card) {
    // → Immediate activation (Trial→Active)
}
if ($amount === 0) {
    // → Immediate activation for Free plan
}
// Otherwise → Payment gateway flow
```

### Payment Gateways

All implement `BillingGatewayInterface`. Drivers: sslcommerz (✅), stripe (❌ stub), bkash (❌ stub), nagad (❌ stub), portwallet (❌ stub), manual (✅).

---

## Frontend Integration Patterns

### Inertia Shared Data (`HandleInertiaRequests`)

```php
'tenant_config' => fn () => [
    'business_type' => $config->businessType,
    'modules'       => $config->enabledModules,
],
```

### Hooks (`resources/js/hooks/use-tenant-config.ts`)

```typescript
const config = useTenantConfig();            // TenantConfig | null
const enabled = useModuleEnabled('kitchen');  // boolean
const modules = useEnabledModules();          // string[]
```

### Module Nav Map (`resources/js/components/module-nav-items.ts`)

11 modules with icons, labels, sub-items. `buildModuleNavItems(enabledModules)` returns filtered NavItem[].

| Slug | Label | Routes |
|---|---|---|
| product | Products | /products, /products/categories, /products/brands, /products/attributes |
| inventory | Inventory | /products/inventory, /products/stock-transfers |
| order | Orders | /orders |
| pos | POS | /pos |
| crm | CRM | /crm/customers, /crm/segments |
| billing | Billing | /billing, /billing/invoices |
| team | Team | /team |
| supplier | Suppliers | /suppliers, /suppliers/purchase-orders |
| kitchen | Kitchen | /kitchen |
| appointment | Appointments | /appointments, /appointments/services |
| reporting | Reports | /reports |

### Industry Widgets (`resources/js/modules/dashboard/components/industry-widgets.tsx`)

- `IndustryGreeting` — industry-specific welcome card with icon (16 industries)
- `ModuleQuickActions` — up to 6 action cards from enabled modules

---

## File Creation Patterns

### New TenantTemplate

Create at `app/Modules/Onboarding/Templates/{Name}Template.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class BakeryTemplate implements TenantTemplate
{
    public function businessType(): string { return 'bakery'; }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Breads', 'children' => [
                ['name' => 'White Bread'],
                ['name' => 'Whole Wheat'],
            ]],
            ['name' => 'Pastries', 'children' => [
                ['name' => 'Croissants'],
                ['name' => 'Muffins'],
            ]],
        ];
    }

    public function productSchema(): array
    {
        return [
            'fields' => [
                ['slug' => 'weight_g', 'label' => 'Weight (g)', 'type' => 'decimal',
                 'required' => false, 'section' => 'details', 'order' => 1],
                ['slug' => 'expiry_date', 'label' => 'Expiry Date', 'type' => 'date',
                 'required' => true, 'section' => 'production', 'order' => 2],
            ],
            'sections' => [
                'details' => ['title' => 'Product Details', 'order' => 1],
                'production' => ['title' => 'Production', 'order' => 2],
            ],
            'search_columns' => ['name', 'sku'],
            'list_columns' => ['name', 'price', 'stock', 'weight_g'],
        ];
    }

    public function dashboardLayout(): array
    {
        return [
            ['widget' => 'daily_production', 'width' => 'half', 'order' => 1],
            ['widget' => 'daily_revenue', 'width' => 'half', 'order' => 2],
        ];
    }

    public function posDefaults(): array
    {
        return [
            'layout' => 'grid',
            'has_weight_scale' => true,
            'supports_fractional_quantity' => true,
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function defaultTeam(): array
    {
        return ['roles' => ['admin', 'manager', 'baker', 'cashier']];
    }

    public function notificationDefaults(): array
    {
        return [
            'email_notifications' => true,
            'low_stock_alerts' => true,
            'expiry_alerts' => true,
        ];
    }

    public function initialData(): array { return []; }
}
```

### New Industry Pack

Create at `app/Modules/BusinessType/Packs/{Name}Pack.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class NewIndustryPack implements IndustryPack
{
    public function slug(): string { return 'new_industry'; }
    public function name(): string { return 'New Industry'; }
    public function description(): string { return 'Description'; }

    public function modules(): array
    {
        return [
            'product' => ['required' => true],
            'inventory' => ['required' => true],
            'order' => ['required' => true],
            'pos' => ['required' => true],
            'crm' => ['required' => true],
            'billing' => ['required' => true],
            'team' => ['required' => true],
            'reporting' => ['required' => true],
        ];
    }

    public function menus(): array
    {
        return [
            'main' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'LayoutDashboard'],
                ['label' => 'Products', 'route' => 'products.index', 'icon' => 'Package'],
                ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Package'],
                ['label' => 'Sales', 'route' => 'pos.index', 'icon' => 'ShoppingCart'],
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'Users'],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3'],
                ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'Settings'],
            ],
        ];
    }

    public function permissions(): array
    {
        return [
            'admin' => ['products.*', 'inventory.*', 'pos.*', 'orders.*', 'customers.*', 'reports.*', 'settings.*'],
            'manager' => ['products.*', 'inventory.view', 'pos.*', 'orders.*', 'customers.view', 'reports.view'],
            'staff' => ['pos.create', 'orders.create', 'customers.create', 'inventory.view'],
        ];
    }

    public function posConfig(): array
    {
        return [
            'layout' => 'grid',
            'product_search_columns' => ['name', 'sku', 'barcode'],
            'quick_actions' => [],
            'checkout_fields' => [],
            'batch_picking' => false,
            'show_expiry_warning' => false,
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function dashboardWidgets(): array
    {
        return [
            'today_sales' => [
                'component' => 'TodaySalesSummary',
                'title' => "Today's Sales",
                'width' => 'half',
                'permission' => 'pos.view',
            ],
            'recent_orders' => [
                'component' => 'RecentOrdersList',
                'title' => 'Recent Orders',
                'width' => 'half',
                'permission' => 'orders.view',
            ],
        ];
    }

    public function reportDefinitions(): array
    {
        return [
            'sales-summary' => [
                'name' => 'Sales Summary',
                'description' => 'Sales breakdown by product and time period',
                'permission' => 'reports.view',
                'filters' => ['date_range'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
        ];
    }

    public function defaultSettings(): array
    {
        return [
            'currency' => 'BDT',
            'default_markup_percentage' => 20,
        ];
    }

    public function featureFlags(): array { return []; }

    public function onTenantAssigned(Tenant $tenant): void {}
    public function onTenantRemoved(Tenant $tenant): void {}
}
```

### New ProvisioningStep

Place in `app/Modules/Onboarding/Services/`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Modules\Onboarding\Contracts\ProvisioningStep;
use App\Modules\Onboarding\Data\ProvisioningContext;

class MyStep implements ProvisioningStep
{
    public function handle(ProvisioningContext $context): void
    {
        // $context->tenant, $context->businessTypeSlug, $context->planData
    }

    public function rollback(ProvisioningContext $context): void
    {
        // Undo handle() effects
    }

    public function label(): string
    {
        return 'Human-readable step name';
    }
}
```

Register in `ProvisioningPipeline` constructor step array.

### New Service Using TenantConfig

```php
use App\Modules\BusinessType\ValueObjects\TenantConfig;

class SomeController
{
    public function __invoke(TenantConfig $config)
    {
        if ($config->hasModule('kitchen')) {
            // ...
        }
    }
}
```

### New Migration (Central DB)

Use anonymous class, no typed properties:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_table', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_table');
    }
};
```

### New Migration (Tenant DB)

Place in `app/Modules/BusinessType/Database/Migrations/Tenant/`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_table', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_table');
    }
};
```

### New Frontend Component in Industry Widgets

Add to `industryMap` in `industry-widgets.tsx`:

```typescript
new_industry: {
    name: 'New Industry',
    icon: Package,
    greeting: 'Welcome to your New Industry',
    description: 'Description of the industry.',
},
```

---

## Registration Steps

### Adding a New Business Type (Industry Pack + Template)

#### 1. Create the Industry Pack

Create `app/Modules/BusinessType/Packs/{Name}Pack.php` implementing `IndustryPack` (see template above).

#### 2. Create the TenantTemplate

Create `app/Modules/Onboarding/Templates/{Name}Template.php` implementing `TenantTemplate` (see template above).

#### 3. Register in Providers

**IndustryServiceProvider::boot():**
```php
$registry->register(new NewIndustryPack);
```

**OnboardingServiceProvider::boot():**
```php
$registry->register(new NewIndustryTemplate);
```

#### 4. Add seed data

**`database/seeders/BusinessTypeSeeder.php`** — add entry:
```php
[
    'slug' => 'new_industry',
    'name' => 'New Industry',
    'description' => 'Description',
    'icon' => 'SomeIcon',
    'is_active' => true,
    'config_template' => ['default_modules' => ['product', 'inventory', 'pos', 'crm']],
],
```

**`database/seeders/BusinessTypeModuleSeeder.php`** — add mapping:
```php
'new_industry' => [
    'product' => true,
    'inventory' => true,
    'order' => true,
    'pos' => true,
    'crm' => true,
    'billing' => true,
    'team' => true,
    'reporting' => true,
],
```

#### 5. Add frontend widgets

Add to `industryMap` in `industry-widgets.tsx` and `industryMap` in `business-type.tsx`.

#### 6. Run seeders

```bash
php artisan db:seed --class=BusinessTypeSeeder
php artisan db:seed --class=BusinessTypeModuleSeeder
```

### Adding a New ProvisioningStep

1. Create step class in `app/Modules/Onboarding/Services/` implementing `ProvisioningStep`
2. Inject into `ProvisioningPipeline` constructor (Laravel auto-resolves)
3. Add to `$this->steps` array in the constructor body

---

## Verification Checklists

### After Business Type / Pack Change

```bash
# 1. PHP syntax check
php -l app/Modules/BusinessType/Packs/{PackName}.php
php -l app/Modules/Onboarding/Templates/{TemplateName}.php

# 2. Code style
vendor/bin/pint --format agent

# 3. Run relevant tests
php artisan test --compact --filter="FeatureGatingTest"

# 4. Verify seeders run clean
php artisan db:seed --class=BusinessTypeSeeder
php artisan db:seed --class=BusinessTypeModuleSeeder

# 5. Verify templates register
php artisan tinker --execute="
app()->register(App\Providers\OnboardingServiceProvider::class);
echo count(app(App\Modules\Onboarding\Services\TenantTemplateRegistry::class)->all()) . ' templates\n';"
```

### After Provider/Config Changes

```bash
# 1. PHP syntax
php -l app/Providers/{ProviderName}.php

# 2. Code style
vendor/bin/pint --format agent

# 3. Check provider boot
php artisan tinker --execute="app()->register(App\Providers\OnboardingServiceProvider::class); echo 'OK\n';"

# 4. Clear + warm caches
php artisan optimize:clear
```

### After Any PHP Changes

```bash
vendor/bin/pint --format agent
php -l {changed_file}
php artisan test --compact --filter="{RelevantTest}"
```

---

## Critical Gotchas

### PHP 8.4 Typed Properties in Migrations

Anonymous migration classes **must not** have typed properties. The `Migration` base class declares `$connection` without a type. Using `public string $connection` will cause a fatal error. Always use `Schema::create()` directly.

### `orderBy` Required for `each()` (Laravel 12)

Always add `->orderBy('id')` before `->each()` on query builders:
```php
DB::table('tenant_settings')
    ->where('tenant_id', $tenant->id)
    ->orderBy('id')              // REQUIRED
    ->each(function ($row) { ... });
```

### Tenant vs Central Connection

- **Central models** (BusinessType, Module, User, Plan, Subscription, Payment) use `CentralConnection` trait — they query the central MySQL database
- **Shared models** (TenantConfig, TenantModuleOverride, Task, TenantSetting) use `HasTenantScope` trait — they query `souda_shared` with `tenant_id` isolation
- **Dedicated models** (Product, Variant, Category, Brand, Warehouse, etc.) use normal Eloquent — they query the dedicated tenant DB

### Service Provider Boot Order (`bootstrap/providers.php`)

```php
AppServiceProvider::class,
FortifyServiceProvider::class,     // Auth actions, Inertia views
ProductServiceProvider::class,     // Products, observers, policies
TenancyServiceProvider::class,     // TenantManager, middleware
BillingServiceProvider::class,     // Billing services, gateways
IndustryServiceProvider::class,    // Industry packs, TenantConfig
OnboardingServiceProvider::class,  // TenantTemplates, ProvisioningPipeline
```

IndustryServiceProvider runs last among core providers. OnboardingServiceProvider runs last overall.

### Shared Database Must Exist

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS souda_shared CHARACTER SET utf8mb4"
php artisan migrate --force --database=shared --path=database/migrations/shared
```

### Cache Invalidation

After changing a tenant's business type or module overrides:
```php
app(BusinessTypeEngine::class)->invalidateConfig($tenant);
```

### Plan-to-Mode Mapping (`config/tenancy.php`)

```php
'plan_mode_map' => [
    'free'         => 'shared',
    'starter'      => 'shared',
    'professional' => 'shared',
    'enterprise'   => 'dedicated',
],
```

### $0 Payment Auto-Activation

Free plan (`$amount === 0`) activates immediately in `SubscriptionService::createSubscription()` without going through payment gateway. This was added as a fix — the subscription goes directly to `Active` and dispatches `SubscriptionActivated`.

### HasTenantScope Must Be Test-Safe

The `HasTenantScope` trait wraps `app()` calls in try-catch blocks. This prevents unit test failures when no Laravel application is booted. A `TestCase::setUp()` also resets `Model::$booting` via reflection to prevent stale boot state across tests:

```php
protected function setUp(): void
{
    parent::setUp();
    Model::clearBootedModels();
    $reflection = new ReflectionClass(Model::class);
    $bootingProperty = $reflection->getProperty('booting');
    $bootingProperty->setAccessible(true);
    $bootingProperty->setValue(null, []);
}
```

Do NOT remove the try-catch guards or the setUp() reset — they fix 239 pre-existing test failures.

---

## Key Classes Reference

### Debugging Quick Reference

| Problem | Inspect |
|---|---|
| Config not applying | `BusinessTypeConfigBuilder::build()`, `applyTenantOverrides()` |
| Cache serving stale data | `BusinessTypeEngine::getEffectiveConfig()` |
| Pack not found | `IndustryPackRegistry::get()`, `IndustryServiceProvider::boot()` |
| Template not found | `TenantTemplateRegistry::get()`, `OnboardingServiceProvider::boot()` |
| Module not showing | `resolveModules()` in `BusinessTypeConfigBuilder` |
| Tenant assignment issues | `BusinessTypeEngine::assignBusinessType()` |
| Provisioning step fails | `ProvisioningPipeline::handleFailure()`, `onboarding_progress` JSON |
| Subscription stuck pending | `SubscriptionService::createSubscription()`, amount/status check |
| Test fails in Feature suite | `HasTenantScope` guard, `Model::$booting` reset in `TestCase::setUp()` |
| Route not found for tenant | `InitializeTenancyByUser` middleware not applied, tenant not set up |

### Container Bindings

| Abstract | Concrete | Type |
|---|---|---|
| `IndustryPackRegistry` | `IndustryPackRegistry` | singleton |
| `BusinessTypeEngine` | `BusinessTypeEngine` | singleton |
| `BusinessTypeConfigBuilder` | `BusinessTypeConfigBuilder` | singleton |
| `TenantConfig` | closure (per-request) | bind |
| `TenantTemplateRegistry` | `TenantTemplateRegistry` | singleton |
| `ProvisioningPipeline` | `ProvisioningPipeline` | singleton |
| `TenantManager` | `TenantManager` | singleton |
| `BillingManager` | `BillingManager` | singleton |
| `SubscriptionService` | `SubscriptionService` | singleton |

### All TenantTemplates (16)

| Class | Slug | File |
|---|---|---|
| `PharmacyTemplate` | pharmacy | `app/Modules/Onboarding/Templates/PharmacyTemplate.php` |
| `RestaurantTemplate` | restaurant | `app/Modules/Onboarding/Templates/RestaurantTemplate.php` |
| `GroceryTemplate` | grocery | `app/Modules/Onboarding/Templates/GroceryTemplate.php` |
| `SalonTemplate` | salon | `app/Modules/Onboarding/Templates/SalonTemplate.php` |
| `BakeryTemplate` | bakery | `app/Modules/Onboarding/Templates/BakeryTemplate.php` |
| `CafeTemplate` | cafe | `app/Modules/Onboarding/Templates/CafeTemplate.php` |
| `ElectronicsTemplate` | electronics | `app/Modules/Onboarding/Templates/ElectronicsTemplate.php` |
| `FashionTemplate` | fashion | `app/Modules/Onboarding/Templates/FashionTemplate.php` |
| `CosmeticsTemplate` | cosmetics | `app/Modules/Onboarding/Templates/CosmeticsTemplate.php` |
| `HardwareTemplate` | hardware | `app/Modules/Onboarding/Templates/HardwareTemplate.php` |
| `WholesaleTemplate` | wholesale | `app/Modules/Onboarding/Templates/WholesaleTemplate.php` |
| `DistributionTemplate` | distribution | `app/Modules/Onboarding/Templates/DistributionTemplate.php` |
| `AgroShopTemplate` | agro_shop | `app/Modules/Onboarding/Templates/AgroShopTemplate.php` |
| `BookstoreTemplate` | bookstore | `app/Modules/Onboarding/Templates/BookstoreTemplate.php` |
| `SpaTemplate` | spa | `app/Modules/Onboarding/Templates/SpaTemplate.php` |
| `DefaultTemplate` | general | `app/Modules/Onboarding/Templates/DefaultTemplate.php` |

### All Industry Packs (15)

| Pack Class | Slug | Specialty Modules | Key Feature Flags |
|---|---|---|---|
| `PharmacyPack` | pharmacy | supplier, reporting | batch_tracking, expiry_tracking, prescription_management, drug_schedule_management, insurance_billing |
| `RestaurantPack` | restaurant | kitchen, supplier, reporting | table_management, menu_management, kitchen_display, recipe_management |
| `SalonPack` | salon | appointment, reporting | service_booking, staff_scheduling, commission_tracking, membership |
| `SpaPack` | spa | appointment, reporting | service_booking, staff_scheduling, membership, package_management |
| `ElectronicsPack` | electronics | supplier, reporting | serial_number_tracking, warranty_management, repair_tracking |
| `FashionPack` | fashion | reporting | variant_size_color, seasonal_collections |
| `GroceryPack` | grocery | supplier, reporting | weight_based_pricing, perishable_tracking |
| `CafePack` | cafe | reporting | quick_service, recipe_management |
| `BakeryPack` | bakery | reporting | batch_baking, recipe_costing |
| `CosmeticsPack` | cosmetics | reporting | shade_variants, ingredient_tracking |
| `HardwarePack` | hardware | supplier, reporting | bulk_pricing, unit_conversion |
| `WholesalePack` | wholesale | supplier, reporting | bulk_orders, tiered_pricing |
| `DistributionPack` | distribution | supplier (no pos) | logistics, fleet_management, route_management |
| `AgroShopPack` | agro_shop | supplier, reporting | seasonal_inventory, seed_feed_tracking |
| `BookstorePack` | bookstore | reporting | isbn_management, author_publisher_management |

---

## Event & Listener Map

| Event | Listener(s) |
|---|---|
| `SubscriptionActivated` | `ProvisionTenantDatabase` (sync), `SendSubscriptionNotification` (sync) |
| `PaymentReceived` | `SendSubscriptionNotification`, `GenerateInvoice` (queued) |
| `PaymentFailed` | `SendSubscriptionNotification` |
| `SubscriptionCancelled` | `SendSubscriptionNotification` |
| `SubscriptionExpired` | `SendSubscriptionNotification` |
| `ProductCreated` | `IndexProductForSearch`, `GenerateProductSKU` |
| `ProductUpdated` | `UpdateProductSearchIndex` |
| `ProductDeleted` | `RemoveProductFromSearchIndex` |
| `StockUpdated` | `UpdateProductStockCache` |
| `StockDepleted` | `MarkProductUnavailable`, `SendStockDepletedNotification` |
| `LowStockAlert` | `SendLowStockNotification` |
| `StockReservationCreated` | `TrackStockReservation` |
| `StockReservationExpired` | `ReleaseExpiredStock` |
| `OnboardingStarted` | (progress tracked on tenant) |
| `OnboardingStepCompleted` | (progress tracked on tenant) |
| `OnboardingCompleted` | (tenant marked complete) |
| `OnboardingFailed` | (tenant rolled back) |

---

## Database Schema

### Central (`souda`) — Key Tables

```
tenants                 — id (uuid), name, owner_id, tenancy_mode, business_type_id,
                          onboarding_status, onboarding_progress, onboarded_at, ...
users                   — id, name, email, password, tenant_id, email_verified_at, ...
billing_plans           — id, name, slug, monthly_price, yearly_price, features, limits, trial_...
billing_subscriptions   — id, tenant_id, plan_id, gateway, status, billing_cycle, amount, ...
billing_payments        — id, subscription_id, tenant_id, gateway, transaction_id, amount, status
billing_seat_allocations — id, tenant_id, subscription_id, user_id, seat_type, status
business_types          — id, slug, name, description, icon, is_active, pack_class, config_template
modules                 — id, slug, name, description, version, dependencies, is_core
business_type_module    — business_type_id, module_id, is_required, config_defaults
roles / permissions     — Spatie permission tables
app_settings            — key, value
social_accounts         — user_id, provider, provider_user_id
```

### Shared (`souda_shared`) — Key Tables

```
tenant_settings         — id, tenant_id, timezone, locale, currency, logo, ...
tasks                   — id, tenant_id, title, description, is_completed
tenant_configs          — id, tenant_id, business_type_slug, config (JSON), config_hash
tenant_module_overrides — id, tenant_id, module_slug, is_enabled, settings (JSON)
```

### Dedicated (per-tenant) — Key Tables

products, variants, categories, brands, warehouses, warehouse_stock, stock_movements, stock_reservations, product_media, attributes, attribute_values, product_attribute_values, pricing_rules, tax_categories, tax_rates, audit_logs + shared tables copied on upgrade.

---

## Onboarding Fields on Tenants Table

Migration `2026_06_20_000100_add_onboarding_fields_to_tenants_table`:
```php
$table->string('onboarding_status')->default('pending');
$table->json('onboarding_progress')->nullable();
$table->timestamp('onboarded_at')->nullable();
```

Status values: `pending`, `provisioning`, `completed`, `failed`.
Progress schema: array of `{step, status, index, timestamp}` objects.

---

## Route Summary

| File | Prefix | Middleware |
|---|---|---|
| `routes/web.php` | `/` | web |
| `routes/admin.php` | `/admin` | web, auth, EnsureAdmin |
| `routes/tenant.php` | (none) | web, auth, InitializeTenancyByUser, subscription |
| `routes/onboarding.php` | (none) | web |
| `routes/settings.php` | (included) | auth |
| `routes/console.php` | -- | -- |
