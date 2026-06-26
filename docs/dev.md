# SOUDA — Developer Documentation

## Overview

SOUDA is a multi-tenant, multi-vertical business management platform built with Laravel 12, Inertia.js v2 (React), and a hybrid tenancy architecture. It supports 16 industry verticals through a pluggable Business Type Engine with an onboarding wizard.

**Tech Stack:**
- PHP 8.4.16, Laravel 12
- React 19 + Inertia.js v2 + Tailwind CSS v4
- MySQL (central + shared + per-tenant databases)
- stancl/tenancy v3 (multi-database mode)
- Laravel Fortify v1 (authentication)
- Laravel Cashier (Stripe integration)
- Spatie Laravel Permission (roles)
- Laravel Scout + Algolia (search)

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                         HTTP Request                                │
└─────────────────────────────────────────────────────────────────────┘
                              │
                    ┌─────────▼─────────┐
                    │   Middleware Stack │
                    │  web, auth, tenancy│
                    │  subscription, ... │
                    └─────────┬─────────┘
                              │
              ┌───────────────┼───────────────┐
              │               │               │
              ▼               ▼               ▼
     ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
     │  Central DB  │ │  Shared DB   │ │  Dedicated   │
     │   (souda)    │ │ (souda_shared)│ │  Tenant DBs  │
     │              │ │              │ │ (souda_tenant)│
     │  users       │ │  tasks       │ │  products     │
     │  tenants     │ │  tenant_     │ │  inventory    │
     │  billing     │ │  settings    │ │  orders       │
     │  business_   │ │  tenant_     │ │  ...          │
     │  types       │ │  configs     │ │               │
     └──────────────┘ └──────────────┘ └──────────────┘
```

### Database Architecture

| Connection | Database | Purpose |
|---|---|---|
| `central` | `souda` | Users, tenants, billing, business types, roles |
| `shared` | `souda_shared` | Shared-mode tenant data (tenant_settings, tasks) |
| `mysql` | (template) | Template connection for dedicated tenant DBs |

### Tenancy Modes

| Mode | Database | Isolation | Plans |
|---|---|---|---|
| `shared` | `souda_shared` | `tenant_id` column | Free, Starter, Professional |
| `dedicated` | `souda_tenant_{uuid}` | Separate database | Enterprise |

**Plan-to-Mode Mapping** (`config/tenancy.php`):
```php
'plan_mode_map' => [
    'free'         => 'shared',
    'starter'      => 'shared',
    'professional' => 'shared',
    'enterprise'   => 'dedicated',
],
```

---

## Directory Structure

```
app/
├── Actions/
│   ├── Auth/          (CreateSocialUser)
│   └── Fortify/       (CreateNewUser, ResetUserPassword)
├── Console/Commands/  (ExpireSubscriptions, ImportStripeData, etc.)
├── Events/            (TenantModeChanged)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/     (Users, Plans, Settings)
│   │   ├── Auth/      (SocialAuthController)
│   │   ├── BillingController
│   │   ├── Settings/  (Profile, Password, 2FA, ConnectedAccounts)
│   │   ├── TaskController
│   │   └── TeamController
│   ├── Middleware/    (7 custom middleware)
│   ├── Requests/     (Form requests)
│   └── Responses/    (RegisterResponse, LoginResponse)
├── Jobs/              (MigrateTenantToDedicated/Shared)
├── Listeners/         (ProvisionTenantDatabase, StripeEventListener)
├── Models/            (User, Tenant, TenantSetting, Role, Permission, etc.)
├── Modules/
│   ├── Billing/       (Plans, Subscriptions, Payments, Gateways)
│   ├── BusinessType/  (Industry packs, config engine)
│   ├── CRM/           (In progress)
│   ├── Inventory/     (In progress)
│   ├── Onboarding/    (TenantTemplates, ProvisioningPipeline, steps, events)
│   ├── Order/         (Events only)
│   ├── Product/       (Products, variants, stock, categories, brands)
│   └── Shared/        (DTOs, contracts, traits)
├── Providers/         (8 service providers)
├── Services/          (SocialAuthService, BillingEmailService)
└── Tenancy/
    ├── Contracts/     (TenantModeStrategy)
    ├── Modes/         (SharedMode, DedicatedMode)
    ├── Scopes/        (TenantScope)
    └── TenantManager.php

bootstrap/
├── app.php           (Middleware, route groups)
└── providers.php     (Service provider registration)

config/
├── tenancy.php       (Hybrid mode config, plan_mode_map)
├── billing.php       (Gateways, currency)
├── fortify.php       (Auth features)
├── social-auth.php   (Social providers)
├── permission.php    (Spatie roles)
└── database.php      (Central, shared, mysql connections)

database/
├── migrations/           (~35 central migrations)
├── migrations/tenant/    (2 tenant migrations)
├── migrations/shared/    (2 shared + product + business type migrations)
└── seeders/              (All seeders, business types, plans, modules)

resources/js/
├── pages/
│   ├── auth/             (login, register, forgot-password, etc.)
│   ├── billing/          (plan selection, checkout)
│   ├── onboarding/       (business-type picker, provisioning progress)
│   └── dashboard/
├── components/
│   ├── app-sidebar.tsx   (dynamic module-based sidebar)
│   ├── module-nav-items.ts (11-module nav map)
│   └── ui/               (shadcn/ui components)
├── hooks/
│   ├── use-tenant-config.ts (useTenantConfig, useModuleEnabled, useEnabledModules)
│   └── ...
├── modules/
│   └── dashboard/
│       └── components/
│           └── industry-widgets.tsx (16-industry greeting + quick actions)
├── layouts/
│   ├── auth-layout.tsx
│   └── app-layout.tsx

routes/
├── web.php           (Home, social auth, billing)
├── admin.php         (Admin panel)
├── tenant.php        (Dashboard, products, tasks, team)
├── onboarding.php    (Onboarding wizard)
├── settings.php      (Profile, password, 2FA)
└── console.php       (Scheduled tasks)
```

---

## Authentication

### Stack

Laravel Fortify v1 (headless) with Inertia React views. Custom login/register response classes handle Inertia redirects.

### Fortify Features

```php
Features::registration(),
Features::resetPasswords(),
Features::emailVerification(),
Features::twoFactorAuthentication(['confirm' => true, 'confirmPassword' => true]),
```

### Custom Actions

**CreateNewUser** (`app/Actions/Fortify/CreateNewUser.php`):
1. Validates name, email, password, optional `business_type_slug`
2. Creates Tenant (defaults to `shared` mode)
3. Creates User with `tenant_id`
4. Updates Tenant `owner_id`
5. Stores `business_type_slug` in session for post-registration onboarding
6. Sends welcome email

**ResetUserPassword** (`app/Actions/Fortify/ResetUserPassword.php`):
- Validates and updates password

### Register Response

**RegisterResponse** (`app/Http/Responses/RegisterResponse.php`):
- Redirects to `/onboarding` for the multi-step wizard (not directly to `/dashboard`)

### Social Authentication

**Providers:** Google, GitHub (via `config/social-auth.php`)

**Enabled by:** `AppSetting::getBoolean('social_auth_enabled')`

**Routes:**
```
GET /auth/{provider}/redirect   -> SocialAuthController@redirect
GET /auth/{provider}/callback   -> SocialAuthController@callback
```

**Flow:**
1. Socialite redirect -> callback
2. Checks linking intent (session)
3. Finds existing SocialAccount -> auto-login
4. If new: creates via `CreateSocialUser` (auto-verifies email, random password)
5. Creates SocialAccount record

### Two-Factor Authentication

Handled by Fortify's `TwoFactorAuthenticatable` trait on User model. Routes at `/two-factor-challenge`, settings at `/settings/two-factor`.

### Password Reset

Standard Fortify flow with email notifications. Rate limited to 6 attempts/minute.

### Models

**User** (`app/Models/User.php`): `CentralConnection`, `HasRoles` (Spatie), `TwoFactorAuthenticatable`, `SoftDeletes`
- Relations: `tenant()` (BelongsTo Tenant), `socialAccounts()` (HasMany SocialAccount)

**SocialAccount** (`app/Models/SocialAccount.php`): Central connection, stores provider credentials.

---

## Onboarding System

### Overview

After registration, users go through a multi-step onboarding wizard that:
1. Selects a business type (16 industry cards)
2. Runs a 10-step provisioning pipeline to configure the tenant
3. Redirects to the dashboard with industry-specific configuration

### Onboarding Routes (`routes/onboarding.php`)

```
GET    /onboarding                          -> OnboardingController@start
POST   /onboarding/select-type              -> OnboardingController@selectType
GET    /onboarding/provision                -> OnboardingController@provision
POST   /onboarding/run                      -> OnboardingController@run
GET    /onboarding/{tenant}/progress         -> OnboardingController@progress
GET    /api/business-types                   -> BusinessTypeController@index
```

### Onboarding Flow

```
Registration
  → CreateNewUser (stores business_type_slug in session)
  → RegisterResponse (redirects to /onboarding)
  → OnboardingController@start — shows business type picker
  → OnboardingController@selectType — validates + stores type
  → OnboardingController@provision — creates tenant record
  → OnboardingController@run — executes ProvisioningPipeline
    → 10 steps run sequentially with progress tracking
    → On failure: rolls back completed steps, marks tenant as failed
  → On success: marks tenant onboarded, redirects to dashboard
```

### ProvisioningPipeline (`app/Modules/Onboarding/Services/ProvisioningPipeline.php`)

10-step pipeline that provisions a new tenant:

| # | Step Class | Purpose |
|---|-----------|---------|
| 1 | `CreateTenantStep` | Creates the tenant record in central DB |
| 2 | `AssignBusinessTypeStep` | Assigns `business_type_id` to tenant |
| 3 | `ProvisionModulesStep` | Enables modules based on business type |
| 4 | `CreatePermissionsStep` | Creates role-permission mappings |
| 5 | `SeedDefaultDataStep` | Seeds initial data (categories, etc.) |
| 6 | `ConfigureProductSchemaStep` | Sets product schema fields per industry |
| 7 | `ConfigureDashboardStep` | Sets dashboard widget layout |
| 8 | `ConfigurePOSStep` | Configures POS defaults |
| 9 | `CreateDefaultTeamStep` | Creates default team roles |
| 10 | `BuildTenantConfigStep` | Builds and caches the tenant config |

Each step implements `ProvisioningStep`:
```php
interface ProvisioningStep
{
    public function handle(ProvisioningContext $context): void;
    public function rollback(ProvisioningContext $context): void;
    public function label(): string;
}
```

**Rollback:** If any step fails, all completed steps are rolled back in reverse order. Tenant is marked as `failed` with error details.

### Onboarding Events

| Event | Dispatched |
|---|---|
| `OnboardingStarted` | Pipeline begins |
| `OnboardingStepCompleted` | Each step completes |
| `OnboardingCompleted` | All 10 steps succeed |
| `OnboardingFailed` | Any step throws |

### Tenant Model Onboarding Fields

On `tenants` table (migration `2026_06_20_000100`):
- `onboarding_status` — `pending` / `in_progress` / `completed` / `failed`
- `onboarding_progress` — JSON array of step results
- `onboarded_at` — timestamp of completion

### TenantTemplate System

Each business type has a `TenantTemplate` that defines its default configuration:

```php
interface TenantTemplate
{
    public function businessType(): string;
    public function defaultCategories(): array;
    public function productSchema(): array;
    public function dashboardLayout(): array;
    public function posDefaults(): array;
    public function defaultTeam(): array;
    public function notificationDefaults(): array;
    public function initialData(): array;
}
```

**All 16 templates are registered in `OnboardingServiceProvider::boot()`:**

| Template Class | Slug | Specialty |
|---|---|---|
| `PharmacyTemplate` | pharmacy | Drug schedules, prescriptions, expiry tracking |
| `RestaurantTemplate` | restaurant | Dietary tags, allergens, kitchen display, table mgmt |
| `GroceryTemplate` | grocery | Perishables, weight/piece units |
| `SalonTemplate` | salon | Duration, booking requirement, gender |
| `BakeryTemplate` | bakery | Batch dates, ingredients, dietary tags |
| `CafeTemplate` | cafe | Temperature, size options, caffeine info |
| `ElectronicsTemplate` | electronics | Brand, model, warranty, serial numbers |
| `FashionTemplate` | fashion | Sizes, colors, material, season, gender |
| `CosmeticsTemplate` | cosmetics | Shade, volume, skin type, brand |
| `HardwareTemplate` | hardware | Weight, material, unit type |
| `WholesaleTemplate` | wholesale | Min order qty, bulk pricing, case qty |
| `DistributionTemplate` | distribution | Cargo weight/volume, hazmat, routing |
| `AgroShopTemplate` | agro_shop | Organic, crop type, season, unit type |
| `BookstoreTemplate` | bookstore | Author, ISBN, publisher, genre, language |
| `SpaTemplate` | spa | Duration, room type, gender, prep time |
| `DefaultTemplate` | general | Basic categories, core fields |

Registered via `TenantTemplateRegistry` singleton. Lookup by slug:
```php
$template = $registry->getOrFail('pharmacy');
$categories = $template->defaultCategories();
```

---

## Tenant System

### TenantManager (`app/Tenancy/TenantManager.php`)

Singleton. Central orchestrator for all tenancy operations.

```php
class TenantManager
{
    public function initialize(Tenant $tenant): void       // Resolves strategy, calls initialize
    public function end(): void                             // Tears down tenant context
    public function current(): ?Tenant                     // Current tenant or null
    public function isShared(): bool                        // Delegates to strategy
    public function isDedicated(): bool                     // Delegates to strategy
    public function databaseConnection(): string            // 'shared' or 'mysql'
    public function guessModeFromPlan(string $planSlug): string  // Maps plan slug to mode
    public function resolveStrategy(?Tenant $tenant): TenantModeStrategy
}
```

### Strategies

**SharedMode** (`app/Tenancy/Modes/SharedMode.php`):
- Sets `config('database.default')` to `'shared'`
- Registers tenant in container
- Configures cache prefix: `tenant_shared_{id}`
- Configures storage prefix: `shared/{id}`
- Does NOT call `tenancy()->initialize()` (stancl's DB bootstrapping is for dedicated only)

**DedicatedMode** (`app/Tenancy/Modes/DedicatedMode.php`):
- Calls `tenancy()->initialize($tenant)` (stancl native multi-DB init)

### TenantScope (`app/Tenancy/Scopes/TenantScope.php`)

Global scope that applies `WHERE tenant_id = ?` on queries for shared-mode models. Only applies when TenantManager is initialized AND in shared mode. Provides `withoutTenancy()` macro.

### HasTenantScope Trait (`app/Tenancy/Models/Concerns/HasTenantScope.php`)

Used by all shared-mode models. Auto-fills `tenant_id` on creation, adds global scope, provides `tenant()` relationship. Uses `app()` helper wrapped in try-catch to gracefully handle unit test environments without a booted application.

```php
public static function bootHasTenantScope(): void
{
    try {
        static::addGlobalScope(app(TenantScope::class));
    } catch (\Throwable) {
        // No app context available (e.g., unit tests)
    }
    // ...
}
```

### InitializeTenancyByUser Middleware (`app/Http/Middleware/InitializeTenancyByUser.php`)

Applied to all tenant routes. Skips admin routes. Gets tenant from `$request->user()->tenant`. For dedicated: initializes via TenantManager. Catches `TenantDatabaseDoesNotExistException` and redirects to billing.

### Tenant Provisioning Flow

```
SubscriptionActivated
  → ProvisionTenantDatabase::handle()
    → guessModeFromPlan($planSlug)
    → assignBusinessType($tenant, $subscription)
      → assigns first active BusinessType if none set
    → if shared: provisionSharedTenant() (inserts tenant_settings)
    → if dedicated: upgradeToDedicated() (create DB, migrate, copy)
    → if upgrade: handles shared→dedicated migration
    → if downgrade: handles dedicated→shared migration
```

### Tenant Migration Paths (`config/tenancy.php`)

```php
'migration_parameters' => [
    '--path' => [
        database_path('migrations/tenant'),
        app_path('Modules/Product/Database/Migrations/Tenant'),
        app_path('Modules/BusinessType/Database/Migrations/Tenant'),
    ],
],
```

---

## Product Module

### Directory: `app/Modules/Product/`

### Models (17 models)

**Product** — ULID PK, `ProductTypeEnum` (simple/configurable/bundle/virtual), `ProductStatusEnum` (draft/active/archived). Has `Searchable` (Scout/Algolia), slugs, media, stock relationships. Relations: category, categories (pivot), brand, taxCategory, variants, media, attributeValues, warehouseStock, pricingRules.

**Variant** — ULID, belongs to Product. Has own SKU, barcode, price, stock tracking.

**Category** — Hierarchical via `HasMaterializedPath`. Parent/children tree, BelongsToMany products via `category_product` pivot.

**Brand** — Simple CRUD, scoped `active()`.

**Warehouse** — Physical locations. `is_default` flag.

**WarehouseStock** — Tracks quantity + reserved_quantity per warehouse+product+variant. Uses `HasOptimisticLocking` (lock_version for concurrency).

**StockReservation** — Statuses: active/consumed/expired/cancelled. Tracks reservations with expiration.

**Other Models:** Attribute, AttributeValue, ProductAttributeValue, ProductAttributeTextValue, ProductMedia, StockMovement, AuditLog, TaxCategory, TaxRate, PricingRule.

### Enums (all in `app/Modules/Product/Enums/`)
- ProductStatusEnum, ProductTypeEnum, MovementTypeEnum, StockReservationStatusEnum, BarcodeTypeEnum, MediaTypeEnum, AuditActionEnum, PricingRuleScopeEnum, PricingRuleTypeEnum, PricingRuleConditionEnum, AttributeTypeEnum

### Controllers

**ProductController**: index, create, store, show, edit, update, destroy, archive, restore, publish, duplicate
**CategoryController**: index, store, show, update, destroy, reorder
**BrandController**: index, store, update, destroy
**AttributeController**: index, store, update, destroy, storeValue, updateValue, destroyValue
**StockController**: lowStock, movements, transfer

### Services (20 classes)

ProductService, CategoryService, BrandService, VariantService, AttributeService, MediaService, WarehouseService, StockService, StockLockService, StockAuditService, StockReservationService, TaxService, PricingRuleService, ProductImportService, plus contracts: DefaultSKUGenerator, DefaultStockAllocator, EloquentPricingCalculator, EloquentProductCatalogService, EloquentProductResolver, EloquentStockChecker.

### Events (14 events)

ProductCreated, ProductUpdated, ProductDeleted, ProductArchived, ProductPublished, VariantCreated, VariantUpdated, VariantDeleted, StockUpdated, StockDepleted, LowStockAlert, StockReservationCreated, StockReservationExpired, StockTransferCompleted.

### Listeners & Observers

Observers: ProductObserver, VariantObserver, WarehouseStockObserver, StockReservationObserver.
13 listeners handle search indexing, stock notifications, reservation lifecycle.

### Policies

ProductPolicy, CategoryPolicy, BrandPolicy, WarehousePolicy.

---

## Billing System

### Directory: `app/Modules/Billing/`

### Models

**Plan** (`billing_plans`, central):
| Column | Type |
|---|---|
| id, name, slug (unique) | |
| monthly_price, yearly_price | integer |
| currency (default BDT) | string(3) |
| features, limits | JSON |
| is_active, popular, display_order | boolean/integer |
| trial_enabled, trial_days, trial_without_card | boolean/integer |
| pricing_model, default_seats, seat_price, max_seats | |

**Subscription** (`billing_subscriptions`, central):
| Column | Type |
|---|---|
| id, tenant_id, plan_id, gateway | |
| status (SubscriptionStatus enum) | string |
| billing_cycle (BillingCycle enum) | string |
| amount, currency | integer/string |
| starts_at, expires_at, next_billing_at, trial_ends_at, cancelled_at | timestamps |
| metadata | JSON |

Statuses: Trial → Active → Grace → Expired | Cancelled | PendingPayment

**Payment** (`billing_payments`, central):
| Column | Type |
|---|---|
| id, subscription_id, tenant_id, gateway | |
| transaction_id (nullable) | string |
| amount, currency | integer/string |
| status (PaymentStatus enum) | string |
| payload (JSON), paid_at | |

Statuses: Pending → Completed | Failed | Refunded

**SeatAllocation** (`billing_seat_allocations`, central):
- Tracks user seats per subscription
- SeatType: owner, admin, staff
- Status: active, pending, released

### Payment Gateways

All implement `BillingGatewayInterface` (`app/Modules/Billing/Contracts/`):

| Driver | Status |
|---|---|
| SSLCommerzDriver | ✅ Fully implemented |
| StripeDriver | ❌ Stub |
| BKashDriver | ❌ Stub |
| NagadDriver | ❌ Stub |
| PortWalletDriver | ❌ Stub |
| ManualDriver | ✅ Local/manual payments |

### Services

**BillingManager** — Singleton gateway driver registry. `driver($gateway)` resolves gateway by name.

**SubscriptionService** — Core subscription lifecycle:
- `createSubscription()` — Creates subscription, initiates payment or trial
  - If trial available (trial_without_card): immediately activates
  - If `$amount === 0` (Free plan): immediately activates without payment
  - Otherwise: initiates payment via gateway
- `activateSubscription()` — Sets Active, dispatches `SubscriptionActivated`
- `verifyAndActivate()` — Verifies payment, activates
- `cancelSubscription()` — Gateway + local cancellation
- `tenantHasAccessibleSubscription()` — Boolean check
- `tenantHasFeature()` — Checks plan features
- `tenantHasReachedLimit()` — Checks plan limits

**PaymentService** — Records payments, marks complete/failed.

**PlanService** — CRUD for plans.

**SeatService** — Allocate, release, count seats.

### Events (9 events)

| Event | When |
|---|---|
| `SubscriptionActivated` | Subscription becomes active (trial or paid) |
| `SubscriptionCancelled` | Subscription cancelled |
| `SubscriptionExpired` | Grace period ends |
| `PaymentReceived` | Payment completes |
| `PaymentFailed` | Payment fails |
| `InvoiceGenerated` | Invoice created |
| `SeatAllocated` | Seat assigned |
| `SeatReleased` | Seat released |
| `SeatOverageInvoiced` | Overage invoiced |

### Stripe Integration

Laravel Cashier configured for Stripe. Separate `plans` and `plan_prices` tables (central) synced from Stripe via webhooks. `StripeEventListener` handles `WebhookReceived` from Cashier.

---

## Business Type Engine

### Directory: `app/Modules/BusinessType/`

### Architecture

15 industry packs implementing `IndustryPack` interface. Each pack defines: modules, menus, permissions, POS config, dashboard widgets, reports, settings, feature flags. Packs are registered in `IndustryServiceProvider::boot()`.

### Key Classes

| Class | Purpose |
|---|---|
| `IndustryPackRegistry` | In-memory registry of all 15 packs |
| `BusinessTypeConfigBuilder` | Assembles `TenantConfig` from pack + plan gating + tenant overrides |
| `BusinessTypeEngine` | Caching (24h TTL), config resolution, business type assignment |
| `TenantConfig` (readonly VO) | Injected via DI, available app-wide |

### Container Bindings (`IndustryServiceProvider`)

| Abstract | Concrete | Type |
|---|---|---|
| `IndustryPackRegistry` | `IndustryPackRegistry` | singleton |
| `BusinessTypeEngine` | `BusinessTypeEngine` | singleton |
| `BusinessTypeConfigBuilder` | `BusinessTypeConfigBuilder` | singleton |
| `TenantConfig` | closure (per-request resolution) | bind |

### TenantConfig API

```php
$config->businessType;       // string — 'pharmacy', 'restaurant', etc.
$config->enabledModules;     // string[] — ['product', 'inventory', 'pos', ...]
$config->menus;              // array — navigation structure
$config->permissions;        // array — role → permissions map
$config->fieldDefinitions;   // array — custom field definitions
$config->dashboardWidgets;   // array — widget configs
$config->posConfig;          // array — POS layout/fields
$config->reportDefinitions;  // array — report configs
$config->settings;           // array — merged settings + features

// Helper methods
$config->hasModule('kitchen');        // bool
$config->hasFeature('batch_tracking'); // bool
$config->toArray();                   // serialized for storage/API
```

### All Industry Packs

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

### Frontend Integration

Tenant config is shared via Inertia in `HandleInertiaRequests`:

```php
'tenant_config' => fn () => [
    'business_type' => $config->businessType,
    'modules' => $config->enabledModules,
],
```

Consumed by:
- `use-tenant-config.ts` — `useTenantConfig()`, `useModuleEnabled()`, `useEnabledModules()`
- `module-nav-items.ts` — 11-module nav map, `buildModuleNavItems(enabledModules)`
- `app-sidebar.tsx` — renders dynamic nav items per enabled modules
- `industry-widgets.tsx` — `IndustryGreeting` (16 industries with icons) + `ModuleQuickActions`

---

## Frontend Architecture

### Tech Stack

React 19 + Inertia.js v2 + Tailwind CSS v4 + shadcn/ui components.

### Key Hooks

**`use-tenant-config.ts`** (`resources/js/hooks/use-tenant-config.ts`):
```typescript
const config = useTenantConfig();            // TenantConfig | null
const enabled = useModuleEnabled('kitchen');  // boolean
const modules = useEnabledModules();          // string[]
```

### Module Navigation

**`module-nav-items.ts`** maps 11 module slugs to nav definitions:
- product, inventory, order, pos, crm, billing, team, supplier, kitchen, appointment, reporting

Used by `app-sidebar.tsx` to dynamically build sidebar navigation based on `tenant_config.modules`.

### Industry Dashboard Widgets

**`industry-widgets.tsx`** provides:
- `IndustryGreeting` — industry-specific welcome card with icon (16 industries)
- `ModuleQuickActions` — up to 6 quick-action cards from enabled modules

---

## Routes

| Route File | Prefix | Middleware | Purpose |
|---|---|---|---|
| `routes/web.php` | `/` | `web` | Home, social auth, billing |
| `routes/admin.php` | `/admin` | `web, auth, EnsureAdmin` | Admin panel |
| `routes/tenant.php` | (none) | `web, auth, InitializeTenancyByUser, subscription` | Dashboard, products, tasks, team |
| `routes/onboarding.php` | (none) | `web` | Onboarding wizard |
| `routes/settings.php` | (included) | `auth` | Profile, password, 2FA |
| `routes/console.php` | -- | -- | Schedules |

### Key Tenant Routes

```
GET    /dashboard                  -> DashboardController
GET    /products                   -> ProductController@index
POST   /products                   -> ProductController@store
GET    /products/{product}         -> ProductController@show
PUT    /products/{product}         -> ProductController@update
DELETE /products/{product}         -> ProductController@destroy
GET    /categories                 -> CategoryController@index
GET    /brands                     -> BrandController@index
GET    /inventory/low-stock        -> StockController@lowStock
GET    /stock/movements            -> StockController@movements
POST   /stock/transfer             -> StockController@transfer
GET    /tasks                      -> TaskController@index
GET    /billing                    -> BillingController@index
POST   /billing/subscribe          -> BillingController@subscribe
```

### Onboarding Routes

```
GET    /onboarding                     -> show business type picker
POST   /onboarding/select-type         -> select business type
GET    /onboarding/provision           -> show provisioning progress
POST   /onboarding/run                 -> start provisioning pipeline
GET    /onboarding/{tenant}/progress   -> poll provisioning progress
GET    /api/business-types             -> list active business types
```

### Key Admin Routes

```
GET    /admin/users                -> Admin/UserController
GET    /admin/pricing              -> Admin/PlanController
GET    /admin/settings             -> Admin/SettingController
```

---

## Middleware

| Middleware | Alias | Purpose |
|---|---|---|
| `InitializeTenancyByUser` | — | Initializes tenant context from auth user |
| `EnsureAdmin` | — | Checks `hasRole('admin')` |
| `EnsureTenantHasSubscription` | `subscription` | Redirects to billing if no subscription |
| `EnsureTenantHasFeature` | `feature` | Checks plan features |
| `EnsureSeatAvailable` | `seat` | Checks seat limit |
| `HandleInertiaRequests` | — | Inertia shared props incl. tenant_config |
| `HandleAppearance` | — | Appearance cookie |

**Priority:** `InitializeTenancyByUser` runs before `SubstituteBindings`.

---

## Key Flows

### Registration Flow

```
POST /register (name, email, password, business_type_slug)
  → Fortify -> CreateNewUser
    → Tenant::create(['name' => "{$name}'s Account"])
      → tenancy_mode defaults to 'shared'
      → business_type_id is null
    → User::create([..., 'tenant_id' => $tenant->id])
    → $tenant->update(['owner_id' => $user->id])
    → Stores business_type_slug in session
    → Send welcome email
  → RegisterResponse redirects to /onboarding
  → OnboardingController@start — business type picker page
  → User selects industry card
  → OnboardingController@selectType — stores type
  → OnboardingController@provision — creates tenant record
  → ProvisioningPipeline runs 10 steps
  → On completion: redirect to /dashboard with tenant_config
```

### Subscription Flow

```
POST /billing/subscribe (plan_id, gateway)
  → SubscriptionService::createSubscription()

  Case A: Trial (no card required) — starter/professional/enterprise
    → status = Trial
    → activateSubscription() immediately
      → status = Active
      → dispatches SubscriptionActivated
      → ProvisionTenantDatabase::handle()
        → provisions shared or dedicated DB
        → assigns business type if none set
    → Returns checkoutUrl = null

  Case B: Free plan ($0)
    → status = PendingPayment → amount check → immediate activation
    → Same as Case A activation flow
    → Returns checkoutUrl = null

  Case C: Paid, no trial
    → status = PendingPayment
    → Gateway creates payment session
    → Returns checkoutUrl

  After payment callback:
    → verifyAndActivate() -> PaymentReceived -> activateSubscription()
```

### Tenant Initialization (Per-Request)

```
InitializeTenancyByUser::handle()
  → $tenant = $request->user()->tenant
  → $manager->initialize($tenant)
    → resolveStrategy(): if $tenant->isDedicated() -> DedicatedMode else SharedMode
    → strategy->initialize():
      → SharedMode: sets DB default to 'shared', configures cache/storage
      → DedicatedMode: calls tenancy()->initialize($tenant)
```

---

## Development Setup

### Prerequisites

- PHP 8.4.16+
- MySQL 8.0+
- Node.js 20+
- Composer
- Laravel Herd (or Valet)

### Installation

```bash
git clone <repo> souda && cd souda
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### Database Setup

```bash
# Create central database
mysql -u root -e "CREATE DATABASE souda CHARACTER SET utf8mb4"

# Create shared database (REQUIRED for shared-mode tenants)
mysql -u root -e "CREATE DATABASE souda_shared CHARACTER SET utf8mb4"

# Run central migrations
php artisan migrate

# Run shared migrations
php artisan migrate --database=shared --path=database/migrations/shared

# Seed all data
php artisan db:seed
```

### Running

```bash
npm run dev          # Vite dev server
php artisan serve    # Laravel dev server (or use Herd)
```

### Seeding

```bash
# Full seed (all seeders run)
php artisan db:seed

# Individual seeders
php artisan db:seed --class=BusinessTypeSeeder
php artisan db:seed --class=ModuleSeeder
php artisan db:seed --class=BusinessTypeModuleSeeder
php artisan db:seed --class=PlanSeeder
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=AdminRoleSeeder
```

### Testing

```bash
# Run specific test file
php artisan test --compact --filter="FeatureGatingTest"

# Run all tests
php artisan test --compact
```

---

## Database Schema Summary

### Central (`souda`)

```
users                   — id, name, email, password, tenant_id, email_verified_at, ...
tenants                 — id (uuid), name, owner_id, tenancy_mode, business_type_id,
                          onboarding_status, onboarding_progress, onboarded_at, ...
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
domains                 — (stancl tenancy)
```

### Shared (`souda_shared`)

```
tenant_settings         — id, tenant_id, timezone, locale, currency, logo, ...
tasks                   — id, tenant_id, title, description, is_completed
tenant_configs          — id, tenant_id, business_type_slug, config (JSON), config_hash
tenant_module_overrides — id, tenant_id, module_slug, is_enabled, settings (JSON)
```

### Dedicated (per-tenant)

All tables from `database/migrations/tenant/` + `Modules/Product/Database/Migrations/Tenant/` + `Modules/BusinessType/Database/Migrations/Tenant/`.

Includes: products, variants, categories, brands, warehouses, warehouse_stock, stock_movements, stock_reservations, product_media, attributes, attribute_values, product_attribute_values, pricing_rules, tax_categories, tax_rates, audit_logs, tenant_settings, tasks, tenant_configs, tenant_module_overrides.

---

## Service Providers

Registered in `bootstrap/providers.php` in this order:

| Provider | Purpose |
|---|---|
| `AppServiceProvider` | CarbonImmutable, DB commands, password rules |
| `FortifyServiceProvider` | Custom auth actions, Inertia views, rate limiting, business types in register view |
| `ProductServiceProvider` | Product services, events, observers, policies |
| `TenancyServiceProvider` | TenantManager binding, middleware, routes |
| `BillingServiceProvider` | Billing services, gateway drivers, events |
| `IndustryServiceProvider` | Industry packs, TenantConfig binding |
| `OnboardingServiceProvider` | TenantTemplateRegistry, ProvisioningPipeline, 16 templates, onboarding routes |

---

## Known Issues

### 3 StockCalculationTest Failures

Require MySQL-specific features. Pre-existing.

### PHP 8.4 Anonymous Migrations

Anonymous migration classes must not have typed properties. The base `Migration` class declares `$connection` without a type.

### `orderBy` Required for `each()` (Laravel 12)

Always add `->orderBy('id')` before `->each()` on query builders. See `ProvisionTenantDatabase::migrateSharedDataToDedicated()` for the pattern.

### Shared Database Must Exist

The `souda_shared` database must exist before any shared-mode tenant operations. Create it if missing:

```bash
php artisan tinker --execute="DB::statement('CREATE DATABASE IF NOT EXISTS souda_shared CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');"
```

---

## Events & Listeners Map

```
SubscriptionActivated
  → ProvisionTenantDatabase (sync — provisions DB, assigns business type)
  → SendSubscriptionNotification (email)

PaymentReceived
  → SendSubscriptionNotification (email)
  → GenerateInvoice (queued)

ProductCreated
  → IndexProductForSearch (Algolia)
  → GenerateProductSKU

StockUpdated
  → UpdateProductStockCache
  → SendLowStockNotification
  → MarkProductUnavailable (if depleted)

TenantModeChanged
  → (future: reindex, flush caches, etc.)

OnboardingStarted
  → (logged, progress tracked)

OnboardingStepCompleted
  → (progress tracked)

OnboardingCompleted
  → (tenant marked complete, redirect triggered)

OnboardingFailed
  → (tenant marked failed, rollback triggered)
```

---

## Key Design Principles

### No Hardcoded Business Type Logic
- All industry-specific behavior is encapsulated in `IndustryPack` and `TenantTemplate` interfaces
- No `if ($slug === 'pharmacy')`, no `switch/case`, no per-industry tables
- Adding a new industry = implement interfaces + register in providers

### Module Registry Pattern
- Modules self-register capabilities (menus, permissions, dashboard widgets)
- Industry packs determine which modules are enabled per tenant
- Future: `artisan module:cache`, `artisan module:list`

### Immutable Value Objects
- `TenantConfig` is a read-only DTO — all mutability happens through the builder
- Config flows: IndustryPack default → BusinessTypeConfigBuilder → plan gating → tenant overrides

### Service Provider Boot Order
`IndustryServiceProvider` runs LAST among core providers. `OnboardingServiceProvider` runs last overall. This ensures all other providers (Product, Tenancy, Billing) have booted before industry packs and onboarding templates register.
