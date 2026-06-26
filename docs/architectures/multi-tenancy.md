# Multi-Tenancy Strategy

## Overview

This application uses `stancl/tenancy` v3 in **hybrid mode**: free/starter plan tenants share a single `souda_shared` database with `tenant_id` column isolation, while professional/enterprise tenants receive their own isolated MySQL database. Central/shared platform data lives in the `souda` (central) database. Tenant identification is user-based (derived from the authenticated user's `tenant_id`).

---

## 1. Database Architecture

### Mode: Hybrid (Shared + Dedicated)

```
┌──────────────────────────────────────────────────────────────┐
│                   Central Database (souda)                    │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  Authentication & Authorization                       │    │
│  │  users, password_reset_tokens, sessions              │    │
│  │  roles, permissions, model_has_roles                 │    │
│  └──────────────────────────────────────────────────────┘    │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  Tenancy Registry (with tenancy_mode column)          │    │
│  │  tenants, domains                                    │    │
│  └──────────────────────────────────────────────────────┘    │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  Billing & Subscriptions (Central)                   │    │
│  │  billing_plans, billing_subscriptions               │    │
│  │  billing_payments                                   │    │
│  │  billing_seat_allocations                           │    │
│  │  plans, plan_prices (Cashier mirrors)               │    │
│  │  subscriptions, subscription_items (Cashier)        │    │
│  └──────────────────────────────────────────────────────┘    │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  Platform Configuration                              │    │
│  │  app_settings, social_accounts                      │    │
│  └──────────────────────────────────────────────────────┘    │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  Infrastructure                                      │    │
│  │  jobs, job_batches, failed_jobs                     │    │
│  └──────────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────┐
│         Shared Database (souda_shared)              │
│                                                    │
│  tasks (tenant_id index)                           │
│  tenant_settings (tenant_id index)                 │
│  products, categories, brands (future)             │
│  orders, inventory, crm (future)                   │
│  All tables have tenant_id column + global scope   │
└────────────────────────────────────────────────────┘

┌────────────────────────────┐  ┌────────────────────────────┐
│  Tenant DB: souda_tenant_1  │  │  Tenant DB: souda_tenant_2  │
│  (premium only)             │  │  (premium only)             │
│                            │  │                            │
│  tasks                     │  │  tasks                     │
│  products (future)         │  │  products (future)         │
│  orders (future)           │  │  orders (future)           │
│  inventory (future)        │  │  inventory (future)        │
│  crm_data (future)         │  │  crm_data (future)         │
└────────────────────────────┘  └────────────────────────────┘
```

### Connection Configuration (`config/database.php`)

```php
// Central connection — always uses the central database
'central' => [
    'driver' => 'mysql',
    'host' => env('CENTRAL_DB_HOST', env('DB_HOST')),
    'database' => env('CENTRAL_DB_DATABASE', env('DB_DATABASE', 'souda')),
    // ...
],

// Template connection — cloned by the package for each tenant
'mysql' => [
    'driver' => 'mysql',
    // ...
],
```

### Tenancy Configuration (`config/tenancy.php`)

```php
'mode' => 'multi', // stancl mode (dedicated tenants only)

'shared_connection' => env('SHARED_DB_CONNECTION', 'shared'),

'database' => [
    'central_connection' => env('CENTRAL_DB_CONNECTION', 'central'),
    'template_tenant_connection' => env('TENANT_DB_CONNECTION_TEMPLATE', 'mysql'),
    'prefix' => env('TENANT_DB_PREFIX', 'souda_tenant_'),
    'suffix' => '',
],

'plan_mode_map' => [
    'free'         => 'shared',
    'starter'      => 'shared',
    'professional' => 'dedicated',
    'enterprise'   => 'dedicated',
],
```

### Central vs Tenant Data

| Category | Location | Tables | Rationale |
|----------|----------|--------|-----------|
| **Authentication** | Central | `users`, `sessions` | Users authenticate to the platform |
| **Authorization** | Central | `roles`, `permissions`, `model_has_roles` | Role definitions are platform-level |
| **Tenancy** | Central | `tenants`, `domains` | Tenant registry (includes `tenancy_mode`) |
| **Billing** | Central | `billing_plans`, `billing_subscriptions`, `billing_payments`, `billing_seat_allocations` | Plans are platform-defined, subscriptions track tenant billing |
| **Platform Config** | Central | `app_settings`, `social_accounts` | Platform-wide settings |
| **Infrastructure** | Central | `jobs`, `job_batches`, `failed_jobs` | Queue storage (central) |
| **Tenant Data (Shared)** | Shared DB | `tasks`, `tenant_settings` (with `tenant_id`) | free/starter tenants |
| **Tenant Data (Dedicated)** | Tenant DB | `tasks`, `tenant_settings`, products, orders, etc. | professional/enterprise tenants |

### Tenant Model

```php
// app/Models/Tenant.php
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasFactory, SoftDeletes;

    public static function getCustomColumns(): array
    {
        return [
            'id', 'name', 'owner_id',
            'trial_ends_at', 'trial_used',
            'tenancy_mode', 'database_name', // hybrid mode columns
            'created_at', 'updated_at', 'deleted_at',
        ];
    }

    public function getDatabaseName(): string
    {
        // Overridable via database_name column; falls back to prefix convention
        return $this->database_name ?? 'souda_tenant_'.$this->id;
    }

    public function isShared(): bool
    {
        return $this->tenancy_mode === 'shared';
    }

    public function isDedicated(): bool
    {
        return $this->tenancy_mode === 'dedicated';
    }

    public function user(): HasOne { ... }
    public function owner(): BelongsTo { ... }
    public function subscriptions(): HasMany { ... }
    public function activeSubscription(): ?Subscription { ... }
}
```

---

## 2. Migration Strategy

### Central Migrations

- **Location:** `database/migrations/`
- **Run via:** `php artisan migrate`
- **When:** On initial deploy and on every central schema change
- **Includes:** All central tables (users, tenants, billing, etc.)

### Tenant Migrations

- **Location:** `database/migrations/tenant/`
- **Run via:** `php artisan tenants:migrate` (for all tenants)
- **Per tenant:** `php artisan tenants:migrate --tenants={uuid}`
- **Auto on creation:** `TenantCreated` event triggers `CreateDatabase` + `MigrateDatabase` via `JobPipeline`

### Module Tenant Migrations

Modules can register their own tenant migrations:

```php
// In module service provider
public function boot(): void
{
    if ($this->app->runningInConsole()) {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations/Tenant');
    }
}
```

### Tenant Migration Workflow

```
TenantCreated event
       │
       ▼
JobPipeline::make([CreateDatabase, MigrateDatabase])
       │
       ├── CreateDatabase — creates souda_tenant_{uuid}
       └── MigrateDatabase — runs database/migrations/tenant/*.php

Migration files standard naming:
    2026_05_19_000001_create_products_table.php
    2026_05_19_000002_create_categories_table.php
```

### Seeding

```php
// TenancyServiceProvider migration_parameters
'migration_parameters' => [
    '--force' => true,
],
'seeder_parameters' => [
    '--class' => 'TenantDatabaseSeeder',
    '--force' => true,
],
```

### Model Connection Strategy

**Central models** (shared data):

```php
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class User extends Authenticatable
{
    use CentralConnection;
}

class Plan extends Model
{
    use CentralConnection;
}

class Subscription extends Model
{
    use CentralConnection;
}

class SeatAllocation extends Model
{
    use CentralConnection;
}
```

**Tenant models** (isolated data):

```php
// No CentralConnection trait — automatically uses tenant connection
class Product extends Model
{
    protected $guarded = [];
}
```

---

## 3. Tenant Resolver Strategy

### User-Based Identification

The application uses **user-based** tenant identification, not domain or subdomain-based. The tenant is derived from the authenticated user's `tenant_id`.

### Flow

```
1. User authenticates (credentials checked against central DB users table)
2. Middleware: InitializeTenancyByUser::class
3. Derives tenant from auth()->user()->tenant_id
4. Fetches the Tenant model from central DB
5. Calls tenancy()->initialize($tenant)
6. Bootstrappers activate (DB, Cache, Filesystem, Queue)
7. Route is processed within tenant context
8. On terminate: tenancy()->end() reverts to central context
```

### Custom Middleware: `InitializeTenancyByUser`

```php
// app/Http/Middleware/InitializeTenancyByUser.php

class InitializeTenancyByUser
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for admin routes
        if ($this->isAdminRoute($request)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user?->tenant_id && ! tenancy()->initialized) {
            $tenant = $user->tenant;

            if (! $tenant) {
                abort(403, 'Tenant not found.');
            }

            // Initialize tenancy and auto-create DB if missing
            tenancy()->initialize($tenant);

            if (! $this->isTenantDatabaseMigrated($tenant)) {
                $this->ensureTenantDatabaseExists($tenant);
            }
        }

        if ($user && ! tenancy()->initialized) {
            abort(403, 'Tenant context could not be established.');
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }
    }
}
```

### Registration in `bootstrap/app.php`

```php
$middleware->prependToPriorityList(
    before: SubstituteBindings::class,
    prepend: InitializeTenancyByUser::class,
);
```

### Route Grouping

```php
// routes/tenant.php — loaded after tenancy is initialized
Route::middleware(['web', 'auth', InitializeTenancyByUser::class])->group(function () {
    Route::get('/dashboard', ...);
    Route::resource('tasks', TaskController::class);

    // Subscription-gated routes
    Route::middleware('subscription')->group(function () {
        Route::get('/dashboard', ...);
        Route::resource('tasks', TaskController::class);

        // Team management (invite gated by seat middleware)
        Route::get('/team', [TeamController::class, 'index']);
        Route::post('/team/invite', [TeamController::class, 'invite'])->middleware('seat');
    });
});
```

### Admin Routes Bypass

Admin routes (`/admin/*`) skip tenant initialization entirely, running on the central database:

```php
// routes/admin.php — no InitializeTenancyByUser middleware
Route::middleware(['web', 'auth', EnsureAdmin::class])
    ->prefix('admin')
    ->group(function () {
        // All admin routes
    });
```

---

## 4. Queue Handling Strategy

### QueueTenancyBootstrapper

The `QueueTenancyBootstrapper` is active, making queued jobs tenant-aware:

```php
// config/tenancy.php
'bootstrappers' => [
    DatabaseTenancyBootstrapper::class,
    CacheTenancyBootstrapper::class,
    FilesystemTenancyBootstrapper::class,
    QueueTenancyBootstrapper::class,
],
```

### How It Works

When a job is dispatched within a tenant context, the bootstrapper stores the tenant ID. When the worker processes the job, it re-initializes the tenant context before running `handle()`.

### Job Pattern

```php
class ProcessTenantData implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $tenantId,
        public int $entityId,
    ) {
        // Tenant ID must be stored explicitly
        $this->tenantId = tenancy()->tenant->id;
    }

    public function handle(): void
    {
        // QueueTenancyBootstrapper re-initializes tenancy automatically
        $entity = SomeTenantModel::find($this->entityId);
        // Process within tenant context
    }

    public function failed(Throwable $e): void
    {
        Log::error('Job failed for tenant: ' . $this->tenantId, [
            'error' => $e->getMessage(),
        ]);
    }
}
```

### Explicit Tenant Initialization (for cross-tenant jobs)

```php
class GenerateAllTenantReports implements ShouldQueue
{
    public function handle(): void
    {
        $tenants = Tenant::cursor();

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);
            try {
                $this->generateReport($tenant);
            } finally {
                tenancy()->end();
            }
        }
    }
}
```

### Queue Driver

The current default queue driver is `database` (`QUEUE_CONNECTION=database`). For production with Redis, set `QUEUE_CONNECTION=redis`.

---

## 5. Cache Isolation

### CacheTenancyBootstrapper

Each cache key is automatically tagged with the tenant ID:

```php
// config/tenancy.php
'cache' => [
    'tag_base' => 'tenant', // Results in tag: tenant_{uuid}
],
```

### Usage

```php
// Automatically scoped to current tenant
Cache::put('products_count', 42, 3600);
$count = Cache::get('products_count');

// Flush only current tenant's cache
Cache::flush(); // Only flushes tagged keys for this tenant
```

### Central Cache

For data that should be shared across tenants (e.g., active plans), use a separate store or explicitly bypass the tag:

```php
// Use central store for shared data
Cache::store('central')->put('active_plans', $plans, 3600);

// Or store it with a prefix that won't be tagged
Cache::put('global:active_plans', $plans, 3600); // Not tenant-scoped
```

### Important

Cache tags require a taggable cache driver (Redis, Memcached). File and database caches do not support tags. For production, use Redis for cache.

---

## 6. Storage Isolation

### FilesystemTenancyBootstrapper

File storage paths are suffixed per tenant:

```php
// config/tenancy.php
'filesystem' => [
    'suffix_base' => 'tenant',
    'disks' => ['local', 'public'],
    'root_override' => [
        'local' => '%storage_path%/app/',
        'public' => '%storage_path%/app/public/',
    ],
    'suffix_storage_path' => true,
    'asset_helper_tenancy' => false,
],
```

### How It Works

When tenancy is initialized:
- `storage_path()` returns `storage/tenant/{uuid}/...` (suffixed)
- `local` disk root becomes `storage/tenant/{uuid}/app/`
- `public` disk root becomes `storage/tenant/{uuid}/app/public/`

### Storage Usage

```php
// Automatically stored in tenant-scoped path
Storage::disk('public')->put('logo.png', $contents);
// Actual path: storage/tenant/{uuid}/app/public/logo.png

// Retrieve tenant-specific file
$url = Storage::disk('public')->url('logo.png');
```

### Shared (Global) Files

```php
// Use global_asset() for non-tenant assets
$assetUrl = global_asset('build/assets/app.css');

// Or use a non-tenant disk
Storage::disk('local')->put('global/config.json', $contents);
```

### Asset Helper Tenancy

Asset helper tenancy is disabled (`'asset_helper_tenancy' => false`), meaning `asset()` calls return global URLs by default. Use `tenant_asset()` for tenant-specific assets.

---

## 7. Recommended Tenancy Lifecycle

```
Registration
     │
     ▼
────────────────────────────────────────────────────
1. CREATE TENANT
────────────────────────────────────────────────────
     │
     ├── User registers via Fortify or SocialAuth
     ├── Tenant record created (central DB)
     │   ├── id (UUID)
     │   ├── name
     │   ├── owner_id → user.id
     │   └── trial_ends_at (set based on plan config)
     ├── User created with tenant_id
     └── Roles assigned (default "user" role)
     │
     ▼
────────────────────────────────────────────────────
2. TENANT DATABASE CREATION (on first login)
────────────────────────────────────────────────────
     │
     ├── InitializeTenancyByUser middleware runs
     ├── Tenant DB: souda_tenant_{uuid} created
     ├── Tenant migrations: database/migrations/tenant/ run
     ├── Tenant seeder: TenantDatabaseSeeder runs
     └── Tenancy bootstrapped:
         ├── DatabaseTenancyBootstrapper
         ├── CacheTenancyBootstrapper
         ├── FilesystemTenancyBootstrapper
         └── QueueTenancyBootstrapper
     │
     ▼
────────────────────────────────────────────────────
3. ACTIVE TENANCY (during requests)
────────────────────────────────────────────────────
     │
     ├── All queries on tenant models → tenant DB
     ├── All cache keys → tagged with tenant ID
     ├── All storage paths → suffixed with tenant ID
     ├── All queued jobs → tagged with tenant ID
     ├── Subscription check → EnsureSubscribed middleware
     └── Feature check → EnsureTenantHasFeature middleware
     │
     ▼
────────────────────────────────────────────────────
4. SUBSCRIPTION LIFE CYCLE
────────────────────────────────────────────────────
     │
     ├── Trial → Accessible (trial_enabled + trial_ends_at)
     ├── Active → Accessible (paid, within expiry)
     ├── Grace → Accessible (3 day grace after expiry)
     ├── Expired → Blocked
     ├── Cancelled → Blocked
     └── PendingPayment → Blocked
     │
     ▼
────────────────────────────────────────────────────
5. REQUEST COMPLETION
────────────────────────────────────────────────────
     │
     ├── tenancy()->end() called by middleware terminate()
     ├── Bootstrappers revert to central context
     └── Next request starts fresh tenancy initialization
     │
     ▼
────────────────────────────────────────────────────
6. TENANT DELETION
────────────────────────────────────────────────────
     │
     ├── Tenant marked as deleted (SoftDeletes)
     ├── TenantDeleted event fires
     └── Tenant database deleted (inline closure)
```

---

## 8. Security Best Practices

### Tenant Isolation Enforcement

| Layer | Mechanism | Coverage |
|-------|-----------|----------|
| **Database** | Multi-DB mode | Complete data isolation |
| **Models** | `CentralConnection` trait on central models | Prevents tenant-DB queries for shared data |
| **Middleware** | `InitializeTenancyByUser` | Enforces tenant context on every request |
| **Queries** | No cross-tenant queries possible by design | Each DB is separate |
| **Cache** | `CacheTenancyBootstrapper` tags | Prevents cache leakage |
| **Filesystem** | `FilesystemTenancyBootstrapper` suffixes | Prevents file leakage |
| **Queue** | `QueueTenancyBootstrapper` | Prevents job context leakage |

### Critical Rules

#### 1. Always Use CentralConnection on Central Models

```php
// ✓ CORRECT
class User extends Authenticatable
{
    use CentralConnection;
}

// ✗ WRONG — model may query tenant DB when tenancy is active
class User extends Authenticatable { }
```

#### 2. Never Query Tenant Data Outside Tenant Context

```php
// ✓ CORRECT
tenancy()->initialize($tenant);
try {
    $products = Product::all();
} finally {
    tenancy()->end();
}

// ✗ WRONG — may query wrong DB
$products = Product::all(); // Which DB? Depends on current tenancy state
```

#### 3. Admin Routes Bypass Tenant Context

Admin routes run on the central database without tenant initialization. Admin middleware explicitly checks for this:

```php
// InitializeTenancyByUser
if ($this->isAdminRoute($request)) {
    return $next($request);
}
```

#### 4. Store Tenant ID in Queued Jobs

```php
class ProcessOrder implements ShouldQueue
{
    public $tenantId;

    public function __construct()
    {
        $this->tenantId = tenancy()->tenant->id;
    }
}
```

#### 5. Webhook Endpoints Are CSRF-Exempted

```php
// bootstrap/app.php
$middleware->validateCsrfTokens(except: [
    'stripe/*',
    'billing/webhook/*',
    'billing/success/sslcommerz',
]);
```

#### 6. Subscription Gate All Tenant Business Routes

```php
// routes/tenant.php
Route::middleware('subscription')->group(function () {
    Route::get('/dashboard', ...);
    Route::resource('tasks', TaskController::class);
});
```

### Non-User-Editable Tenant Context

Tenant context is derived from the authenticated user's `tenant_id`, which is set during registration and never from user-submitted request data. This prevents tenant context tampering.

---

## 9. Recommended Middleware Structure

### Middleware Registration Order (in `bootstrap/app.php`)

```
1. HandleAppearance
2. HandleInertiaRequests
3. AddLinkHeadersForPreloadedAssets
4. InitializeTenancyByUser (prepended before SubstituteBindings)
5. SubstituteBindings
6. 'auth' middleware (on route groups)
7. EnsureAdmin (on admin routes)
8. EnsureSubscribed / subscription (on tenant routes)
9. EnsureTenantHasFeature / feature:{feature_name} (on specific routes)
10. EnsureSeatAvailable / seat (on team invite routes)
```

### Full Middleware Stack for a Typical Tenant Request

```
web group:
  ├── EncryptCookies
  ├── AddQueuedCookiesToResponse
  ├── StartSession
  ├── ShareErrorsFromSession
  ├── ValidateCsrfToken
  ├── HandleAppearance
  ├── HandleInertiaRequests
  └── AddLinkHeadersForPreloadedAssets

auth group (tenant routes):
  └── Authenticate

tenancy initialization (prepended before SubstituteBindings):
  └── InitializeTenancyByUser
      ├── Checks isAdminRoute() → skip if true
      ├── Derives tenant from auth()->user()->tenant_id
      ├── Initializes tenancy (auto-creates DB if needed)
      └── On terminate: tenancy()->end()

subscription gate (optional):
  └── EnsureSubscribed / subscription
      ├── Bypasses for admin users
      └── Redirects to /billing if no accessible subscription

feature gate (optional):
  └── EnsureTenantHasFeature
      ├── Checks PlanFeatureService::requireFeature()
      └── Redirects or returns 403
```

### Middleware Reference

| Middleware | Alias | Purpose | Applies To |
|-----------|-------|---------|------------|
| `InitializeTenancyByUser` | — | Derives tenant from user, initializes DB | All web routes (except admin) |
| `EnsureAdmin` | `admin` | Checks `user->hasRole('admin')` | `/admin/*` routes |
| `EnsureSubscribed` | `subscription` | Checks `tenantHasAccessibleSubscription()` | Protected tenant routes |
| `EnsureTenantHasSubscription` | — | Checks subscription access (Alias: `subscription`) | Protected tenant routes |
| `EnsureTenantHasFeature` | `feature` | Checks plan feature access (`feature:name`) | Feature-specific routes |
| `EnsureSeatAvailable` | `seat` | Checks plan `max_seats` limit before adding users | Team invite routes |
| `HandleInertiaRequests` | — | Shares tenant data to Inertia | All web routes |
| `HandleAppearance` | — | Handles dark/light mode | All web routes |

---

## 10. Billing/Subscription Integration Strategy

### Data Location

All billing data lives in the **central database** because:
- Plans are platform-defined and shared
- Subscription status determines tenant access
- Payment records are financial data needing central oversight

```php
// All billing models use CentralConnection
class Plan extends Model { use CentralConnection; }
class Subscription extends Model { use CentralConnection; }
class Payment extends Model { use CentralConnection; }
```

### Subscription-Aware Tenant Lifecycle

```
┌──────────────────────────────────────────────────────────────────┐
│                     Tenant Onboarding Flow                        │
│                                                                  │
│  1. User registers → Tenant created (trial_ends_at set)         │
│  2. User redirected to /billing                                  │
│  3. User selects plan + gateway                                  │
│  4. POST /billing/subscribe                                      │
│     ├── If trial: Subscription created with Trial status         │
│     └── If no trial: Subscription with PendingPayment status     │
│  5. User completes payment via gateway                           │
│  6. Webhook/callback → Payment received                          │
│     ├── Subscription activated (Active status)                   │
│     ├── SubscriptionActivated event dispatched                   │
│     └── Tenant marked as trial_used = true                       │
│  7. User now has access to tenant features                       │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│                     Access Gating                                │
│                                                                  │
│  Middleware stack for protected routes:                          │
│  auth → InitializeTenancyByUser → subscription → route           │
│                                                                  │
│  SubscriptionStatus::isAccessible():                             │
│  - Trial:   ✓ (within trial period)                             │
│  - Active:  ✓ (paid, within expiry)                             │
│  - Grace:   ✓ (expired but within grace period)                 │
│  - Expired: ✗                                                    │
│  - Cancelled: ✗                                                  │
│  - PendingPayment: ✗                                             │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│                     Feature Gating                               │
│                                                                  │
│  Route::middleware('feature:inventory_management')               │
│                                                                  │
│  PlanFeatureService::requireFeature(tenantId, feature):          │
│  1. Gets tenant's active subscription                            │
│  2. Gets subscription's plan                                     │
│  3. Checks plan.features JSON array contains feature             │
│  4. If not: throws FeatureNotAccessibleException                 │
└──────────────────────────────────────────────────────────────────┘
```

### Subscription States

```php
enum SubscriptionStatus: string
{
    case Trial = 'trial';             // Free trial period
    case Active = 'active';           // Paid and active
    case Grace = 'grace';             // Expired but within grace period
    case Expired = 'expired';         // Fully expired
    case Cancelled = 'cancelled';     // User cancelled
    case PendingPayment = 'pending_payment'; // Awaiting payment

    public function isAccessible(): bool
    {
        return in_array($this, [self::Trial, self::Active, self::Grace]);
    }

    public function requiresPayment(): bool
    {
        return in_array($this, [self::PendingPayment, self::Expired]);
    }
}
```

### Feature-Based Access Control

Plans define features as a JSON array:

```json
{
    "features": ["products", "orders", "inventory", "crm", "reports"],
    "limits": {
        "max_products": 100,
        "max_orders": 1000,
        "max_contacts": 500
    }
}
```

```php
// Route gating
Route::middleware('feature:inventory_management')->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index']);
});

// Programmatic gating
PlanFeatureService::requireFeature($tenantId, 'inventory_management');
PlanFeatureService::tenantHasFeature($tenantId, 'inventory_management');
```

### Billing Events

| Event | Dispatched When | Listener |
|-------|----------------|----------|
| `SubscriptionActivated` | Subscription becomes active | `SendSubscriptionNotification::handleSubscriptionActivated` |
| `SubscriptionCancelled` | User cancels | `SendSubscriptionNotification::handleSubscriptionCancelled` |
| `SubscriptionExpired` | Subscription expires | `SendSubscriptionNotification::handleSubscriptionExpired` |
| `PaymentReceived` | Payment completed | `SendSubscriptionNotification::handlePaymentReceived` |
| `PaymentFailed` | Payment failed | `SendSubscriptionNotification::handlePaymentFailed` |
| `SeatAllocated` | Seat assigned (active or pending) | `RecalculateSeatUsage::handleSeatAllocated` |
| `SeatReleased` | Seat released | `RecalculateSeatUsage::handleSeatReleased` |
| `SeatOverageInvoiced` | Overage invoice generated | — (extensible) |

### Grace Period

Configurable via `BILLING_GRACE_PERIOD_DAYS` (default: 3 days). During the grace period, the tenant retains access but the status is `Grace`.

```php
'grace_period_days' => env('BILLING_GRACE_PERIOD_DAYS', 3),
```

---

## Multi-Domain Support

### Domain Model

The application uses stancl/tenancy's domain model for multi-domain support:

```php
// config/tenancy.php
'domain_model' => Domain::class,
```

### Domain Assignment

Each tenant can have one or more domains associated:

```php
$tenant->domains()->create(['domain' => 'mystore.example.com']);
```

### Domain Identification (Future)

While the current implementation uses user-based identification, switching to domain-based is straightforward:

```php
// Replace InitializeTenancyByUser with InitializeTenancyByDomain
Route::middleware(['web', 'auth', InitializeTenancyByDomain::class])->group(function () {
    // Tenant routes
});
```

### Central Domains

```php
'central_domains' => [
    '127.0.0.1',
    'localhost',
    'app.souda.com', // Production central domain
],
```

### Custom Domain Flow

```
User registers custom domain
       │
       ▼
1. Verify DNS points to app
2. Create Domain record: $tenant->domains()->create(['domain' => 'custom.com'])
3. Add SSL certificate
4. Route resolves via InitializeTenancyByDomain (when enabled)
```

---

## Bootstrappers Summary

| Bootstrapper | Effect | Status |
|-------------|--------|--------|
| `DatabaseTenancyBootstrapper` | Switches DB connection to tenant DB | Active |
| `CacheTenancyBootstrapper` | Tags cache keys with tenant ID | Active |
| `FilesystemTenancyBootstrapper` | Suffixes storage paths with tenant ID | Active |
| `QueueTenancyBootstrapper` | Makes queued jobs tenant-aware | Active |
| `RedisTenancyBootstrapper` | Prefixes Redis keys with tenant ID | Inactive (needs phpredis) |

---

## Key Files Reference

| File | Purpose |
|------|---------|
| `config/tenancy.php` | Tenancy configuration |
| `config/database.php` | Central + template DB connections |
| `app/Models/Tenant.php` | Tenant model with DB naming, relationships |
| `app/Providers/TenancyServiceProvider.php` | Event listeners, route mapping, middleware priority |
| `app/Http/Middleware/InitializeTenancyByUser.php` | User-based tenant initialization |
| `app/Http/Middleware/EnsureSubscribed.php` | Subscription access gate |
| `app/Http/Middleware/EnsureTenantHasFeature.php` | Feature access gate |
| `app/Modules/Billing/Http/Middleware/EnsureSeatAvailable.php` | Seat limit gate (alias: `seat`) |
| `app/Modules/Billing/Models/SeatAllocation.php` | Seat allocation model (central) |
| `bootstrap/app.php` | Middleware registration order |
| `routes/tenant.php` | Tenant-scoped routes |
| `routes/admin.php` | Admin routes (no tenant context) |
| `database/migrations/tenant/` | Tenant-specific migrations |
