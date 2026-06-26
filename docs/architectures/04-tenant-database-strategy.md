# Tenant-Aware Database Strategy

## Overview

Hybrid tenancy: shared database (`souda_shared` with `tenant_id` column scoping) for free/starter plans, dedicated MySQL databases for enterprise plans. Central database (`souda`) holds platform-level data. `TenantManager` resolves the appropriate mode per tenant.

## Database Architecture

```
┌──────────────────────────────────────────────────────────┐
│                    Central Database                       │
│  (souda / default connection)                            │
│                                                          │
│  ┌──────────────────────────────────────────────────┐    │
│  │  Authentication & Authorization                   │    │
│  │  - users, password_reset_tokens, sessions          │    │
│  │  - roles, permissions, model_has_*                 │    │
│  └──────────────────────────────────────────────────┘    │
│  ┌──────────────────────────────────────────────────┐    │
│  │  Tenancy                                          │    │
│  │  - tenants (with tenancy_mode, teams as JSON)     │    │
│  └──────────────────────────────────────────────────┘    │
│  ┌──────────────────────────────────────────────────┐    │
│  │  Billing & Subscriptions                          │    │
│  │  - billing_plans, billing_subscriptions           │    │
│  │  - billing_payments, billing_seat_allocations     │    │
│  │  - plans, plan_prices (Cashier mirror)            │    │
│  │  - subscriptions, subscription_items              │    │
│  └──────────────────────────────────────────────────┘    │
│  ┌──────────────────────────────────────────────────┐    │
│  │  Platform Configuration                           │    │
│  │  - app_settings, social_accounts, business_types  │    │
│  └──────────────────────────────────────────────────┘    │
│  ┌──────────────────────────────────────────────────┐    │
│  │  Queue & Cache                                    │    │
│  │  - jobs, job_batches, failed_jobs                 │    │
│  │  - cache, cache_locks                             │    │
│  └──────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│                  Shared Database                          │
│  (souda_shared / shared connection)                      │
│                                                          │
│  ┌──────────────────────────────────────────────────┐    │
│  │  Tenant-Scoped Data (tenant_id column)            │    │
│  │  - tenant_settings (timezone, locale, currency)    │    │
│  │  - tenant_configs (business_type_slug, JSON blob)  │    │
│  │  - tenant_module_overrides                         │    │
│  │  - tasks                                           │    │
│  └──────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────┘

┌──────────────────────┐  ┌──────────────────────┐
│  Tenant DB: tenant_1  │  │  Tenant DB: tenant_2  │
│  (souda_tenant_{uuid})│  │  (souda_tenant_{uuid})│
│  (enterprise only)    │  │  (enterprise only)    │
│                       │  │                       │
│  ┌─────────────────┐ │  │  ┌─────────────────┐ │
│  │  Products       │ │  │  │  Products       │ │
│  │  - products     │ │  │  │  - products     │ │
│  │  - categories   │ │  │  │  - categories   │ │
│  │  - variants     │ │  │  │  - variants     │ │
│  └─────────────────┘ │  │  └─────────────────┘ │
│  ┌─────────────────┐ │  │  ┌─────────────────┐ │
│  │  Orders         │ │  │  │  Orders         │ │
│  └─────────────────┘ │  │  └─────────────────┘ │
│  ┌─────────────────┐ │  │  ┌─────────────────┐ │
│  │  Inventory      │ │  │  │  Inventory      │ │
│  └─────────────────┘ │  │  └─────────────────┘ │
│  ┌─────────────────┐ │  │  ┌─────────────────┐ │
│  │  CRM            │ │  │  │  CRM            │ │
│  └─────────────────┘ │  │  └─────────────────┘ │
└──────────────────────┘  └──────────────────────┘
```

## Central vs Tenant Data Classification

### Central Data (Platform-Level, Shared Across All Tenants)

| Category | Tables | Connection | Rationale |
|----------|--------|------------|-----------|
| **Authentication** | `users`, `sessions`, `password_reset_tokens` | `central` | Users authenticate to platform, not tenant |
| **Authorization** | `roles`, `permissions`, `model_has_*`, `model_has_roles` | `central` | Role definitions are platform-level |
| **Tenancy** | `tenants`, `domains` | `central` | Tenant registry |
| **Billing** | `billing_plans`, `billing_subscriptions`, `billing_payments`, `billing_seat_allocations`, `plans`, `plan_prices`, `subscriptions`, `subscription_items` | `central` | Plans are platform-defined, subscriptions track tenant billing |
| **Platform Config** | `app_settings`, `social_accounts`, `business_types`, `modules`, `business_type_module` | `central` | Platform-wide settings |
| **Infrastructure** | `jobs`, `job_batches`, `failed_jobs`, `cache`, `cache_locks` | `central` | Shared queue and cache |

### Shared Data (Tenant-Scoped in Shared Database)

| Category | Tables | Connection | Rationale |
|----------|--------|------------|-----------|
| **Tenant Settings** | `tenant_settings` | `shared` | Per-tenant locale, currency, branding |
| **Tenant Config** | `tenant_configs` | `shared` | Business type config, module overrides |
| **Tasks** | `tasks` | `shared` | Simple per-tenant data |

All shared tables are scoped by a `tenant_id` column. Queries automatically filtered by `HasTenantScope` global scope.

### Dedicated Tenant Data (Isolated Per Enterprise Tenant)

| Category | Tables | Connection | Rationale |
|----------|--------|------------|-----------|
| **Products** | `products`, `categories`, `brands`, `variants`, `warehouses`, `warehouse_stock`, `stock_movements`, `stock_reservations`, `attributes`, `attribute_values`, `product_attribute_values`, `product_media`, `pricing_rules`, `tax_categories`, `tax_rates`, `audit_logs` | `mysql` (tenant) | Each tenant has their own catalog |
| **Orders** | `orders`, `order_items` | `mysql` (tenant) | Each tenant processes their own orders |
| **CRM** | `contacts`, `interactions` | `mysql` (tenant) | Each tenant has their own customers |

## Tenancy Configuration

### Database Connections (`config/database.php`)

```php
'connections' => [
    'central' => [
        'driver' => 'mysql',
        'database' => env('DB_DATABASE', 'souda'),
        // ...
    ],
    'shared' => [
        'driver' => 'mysql',
        'database' => env('SHARED_DB_DATABASE', 'souda_shared'),
        // ...
    ],
    'mysql' => [
        // Template connection for dedicated tenant DBs
        'database' => '', // Populated by tenancy bootstrapper
        // ...
    ],
],
```

### Database Naming

```php
// config/tenancy.php
'database' => [
    'prefix' => 'souda_tenant_',
    'suffix' => '',
];
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

### Tenant Model

```php
// app/Models/Tenant.php
class Tenant extends BaseTenant
{
    use HasFactory, SoftDeletes, HasDatabase, HasDomains;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(\App\Modules\Billing\Models\Subscription::class);
    }
}
```

### Central Connection Trait

All central models use the `CentralConnection` trait:

```php
// app/Models/User.php
class User extends Authenticatable
{
    use CentralConnection, HasFactory, Notifiable, HasRoles;
}

// app/Modules/Billing/Models/Plan.php
class Plan extends Model
{
    use CentralConnection, HasFactory;
}
```

### HasTenantScope Trait (Shared Mode)

Used by all shared-mode models in `souda_shared`:

```php
// app/Tenancy/Models/Concerns/HasTenantScope.php
public static function bootHasTenantScope(): void
{
    static::addGlobalScope(app(TenantScope::class));

    static::creating(function ($model) {
        $manager = app(TenantManager::class);
        if ($manager->initialized() && $manager->isShared() && ! $model->tenant_id) {
            $model->tenant_id = $manager->id();
        }
    });
}
```

### TenantScope (`app/Tenancy/Scopes/TenantScope.php`)

Adds `WHERE tenant_id = ?` in shared mode. Provides `withoutTenancy()` macro for admin queries.

### TenantManager (`app/Tenancy/TenantManager.php`)

Singleton orchestrator. Key method: `initialize(Tenant $tenant)` resolves mode strategy:

- **SharedMode** — sets `config('database.default')` to `'shared'`, does NOT call `tenancy()->initialize()`
- **DedicatedMode** — calls `tenancy()->initialize($tenant)` (stancl native multi-DB)

### Tenant Model

```php
// app/Models/Tenant.php
class Tenant extends BaseTenant
{
    use HasDatabase, HasDomains;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(\App\Modules\Billing\Models\Subscription::class);
    }
}
```

### Central Connection Trait

All central models use the `CentralConnection` trait:

```php
// app/Models/User.php
class User extends Authenticatable
{
    use CentralConnection, HasFactory, Notifiable, HasRoles;
}

// app/Modules/Billing/Models/Plan.php
class Plan extends Model
{
    use CentralConnection, HasFactory;
}
```

## Tenant Initialization Flow

### Shared Mode Flow

```
1. User authenticates (central DB)
2. InitializeTenancyByUser middleware runs
3. Derives tenant from auth()->user()->tenant_id
4. TenantManager::initialize($tenant) resolves mode → SharedMode
5. SharedMode sets config('database.default') to 'shared'
6. HasTenantScope global scope auto-filters all queries by tenant_id
7. Route is processed within shared tenant context
8. On terminate: default connection reverts to 'central'
```

### Dedicated Mode Flow

```
1. User authenticates (central DB)
2. InitializeTenancyByUser middleware runs
3. Derives tenant from auth()->user()->tenant_id
4. TenantManager::initialize($tenant) resolves mode → DedicatedMode
5. DedicatedMode calls tenancy()->initialize($tenant)
6. Tenancy bootstrappers activate:
   ├── DatabaseTenancyBootstrapper: Switches DB connection to tenant DB
   ├── CacheTenancyBootstrapper: Tags cache with tenant ID
   ├── FilesystemTenancyBootstrapper: Suffixes storage paths
   └── QueueTenancyBootstrapper: Makes queue jobs tenant-aware
7. If tenant DB doesn't exist: create + migrate (JobPipeline)
8. Route is processed within tenant context
9. On terminate: tenancy()->end() reverts to central context
```

## Migration Strategy

### Central Migrations

- Location: `database/migrations/`
- Run once on central database: `php artisan migrate`
- Timestamp-based naming: `YYYY_MM_DD_HHMMSS_description.php`

### Shared Migrations

- Location: `database/migrations/shared/`
- Run manually: `php artisan migrate --database=shared --path=database/migrations/shared`
- Tables use `tenant_id` column for tenant isolation
- Contains: `tenant_settings`, `tenant_configs`, `tasks`

### Dedicated Tenant Migrations

- Location: `database/migrations/tenant/` + module paths
- Run automatically on enterprise tenant creation via `MigrateDatabase` job
- Run manually: `php artisan tenants:migrate`
- Run for specific tenant: `php artisan tenants:migrate --tenants={uuid}`

### Module Tenant Migrations (Hybrid)

For module-specific tenant migrations (e.g., Product module):

```php
// In module service provider boot()
$this->loadMigrationsFrom(__DIR__ . '/Database/Migrations/Tenant');
```

These migrations are registered with the global migrator via `loadMigrationsFrom()` but are designed for tenant DBs. They run on dedicated DBs via `tenants:migrate` (which also picks up the `tenancy.migration_parameters` path if configured). In shared mode, these tables are not used — the shared DB only holds config/settings/tasks tables.

## Model Strategy

### Central Models

```php
// app/Models/User.php
class User extends Authenticatable
{
    use CentralConnection; // Explicitly stays on central DB
}

// app/Modules/Billing/Models/Plan.php
class Plan extends Model
{
    use CentralConnection;
}
```

### Shared Models (souda_shared)

```php
// app/Models/TenantConfig.php (or wherever shared model lives)
class TenantConfig extends Model
{
    use HasTenantScope; // Adds tenant_id global scope + auto-set on create

    protected $connection = 'shared'; // Explicit shared connection
}

// app/Models/Task.php
class Task extends Model
{
    use HasTenantScope;

    protected $connection = 'shared';
}
```

### Dedicated Tenant Models

```php
// app/Modules/Product/Models/Product.php
class Product extends Model
{
    // No CentralConnection trait - uses tenant connection
    // Automatically runs on tenant DB when dedicated tenancy is initialized

    protected $guarded = [];
}

// app/Modules/Product/Models/Category.php
class Category extends Model
{
    // Standard model - tenant connection via tenancy context
}
```

### Cross-Reference Pattern

When tenant data needs to reference central data:

```php
// Store central IDs, not foreign keys
class Product extends Model
{
    // tenant_id is implicit from tenancy context
    // category_id references tenant's own categories table
    // No direct FK to central tables
}
```

## Query Safety Rules

### Rule 1: Always Use CentralConnection on Central Models

```php
// ✗ WRONG - model without CentralConnection may switch to tenant DB
class User extends Authenticatable { }

// ✓ CORRECT
class User extends Authenticatable
{
    use CentralConnection;
}
```

### Rule 2: Shared Models Use HasTenantScope

```php
// ✗ WRONG - model without HasTenantScope leaks data across tenants
class TenantConfig extends Model { }

// ✓ CORRECT
class TenantConfig extends Model
{
    use HasTenantScope;
}
```

### Rule 3: Dedicated Mode Requires tenancy()->initialize()

```php
// When running queries outside middleware context
$tenantId = auth()->user()->tenant_id;

// For dedicated mode tenants:
tenancy()->initialize($tenantId);
try {
    $products = Product::all();
} finally {
    tenancy()->end();
}

// For shared mode tenants:
// TenantManager handles this - no need for manual tenancy init
```

### Rule 4: HasTenantScope Must Be Test-Safe

The `HasTenantScope` trait wraps `app()` calls in try-catch blocks. Test cases reset `Model::$booting` state via reflection to prevent stale boot state:

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

## Cache Strategy

### Tenant-Tagged Cache (Dedicated Mode)

```php
// Cache is automatically tagged with tenant ID by CacheTenancyBootstrapper
Cache::put('products_count', $count, 3600);

// Retrieval is automatically tenant-scoped
$count = Cache::get('products_count');
```

### Central Cache

```php
// For central (shared) cache, use explicit store
Cache::store('central')->put('active_plans', $plans, 3600);
```

### TenantConfig Cache (Both Modes)

```php
// TenantConfigService caches config with 24h TTL
$config = Cache::remember("tenant_config.{$tenantId}", 86400, function () {
    return app(BusinessTypeConfigBuilder::class)->build($tenant);
});

// Invalidate on changes
TenantConfigInvalidated::dispatch($tenant->id); // Clears cache for that tenant
```

## Queue Strategy

### Tenant-Aware Jobs (Dedicated Mode)

```php
class ProcessOrder implements ShouldQueue
{
    use Queueable;

    public $tenantId;

    public function __construct(int $orderId)
    {
        $this->onConnection('redis');
        $this->tenantId = tenancy()->tenant->id;
    }

    public function handle(): void
    {
        // QueueTenancyBootstrapper automatically initializes tenancy
        $order = Order::find($this->orderId);
        // Process within tenant context
    }
}
```

### Tenant-Aware Jobs (Shared Mode)

```php
class ProcessTask implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $tenantId,
        public int $taskId,
    ) {}

    public function handle(TenantManager $manager): void
    {
        $manager->initialize(Tenant::find($this->tenantId));
        // Now operating in shared mode — HasTenantScope filters by tenant_id
        $task = Task::find($this->taskId);
    }
}
```

## Database Creation on Subscription Activation

Tenant databases are created on subscription activation (not on registration) for resource efficiency:

```php
// app/Listeners/ProvisionTenantDatabase.php (listens to SubscriptionActivated)
public function handle(SubscriptionActivated $event): void
{
    $tenant = $event->tenant;

    if ($tenant->tenancy_mode === 'dedicated' && ! $this->databaseExists($tenant)) {
        tenancy()->initialize($tenant);
        CreateDatabase::make()->handle($tenant);
        MigrateDatabase::make()->handle($tenant);
        tenancy()->end();
    }
    // Shared mode tenants don't need DB provisioning
}
```

## Backup & Restore

### Tenant Backup

```bash
# Backup specific tenant
php artisan tenants:run "mysqldump souda_tenant_{uuid} > backup_{uuid}.sql"

# Or use spatie/laravel-backup with tenant awareness
```

### Tenant Restore

```bash
# Restore specific tenant
mysql souda_tenant_{uuid} < backup_{uuid}.sql
```

## Data Deletion

### Tenant Deletion

```php
// TenancyServiceProvider
Events\TenantDeleted::class => [
    function (Events\TenantDeleted $event): void {
        $tenant = $event->tenant;
        if ($tenant instanceof TenantWithDatabase) {
            $manager = $tenant->database()->manager();
            if ($manager->databaseExists($tenant->database()->getName())) {
                $manager->deleteDatabase($tenant);
            }
        }
    },
],
```

### GDPR Compliance

- Tenant deletion removes entire tenant database
- Central user data can be anonymized separately
- Billing records may need retention per legal requirements

## Performance Considerations

| Concern | Strategy |
|---------|----------|
| **Connection pooling** | Use persistent connections for tenant DBs |
| **Migration speed** | Run tenant migrations in batches for bulk operations |
| **Query optimization** | Each tenant DB is smaller, queries are faster |
| **Index strategy** | Same indexes as single-DB, but per-tenant |
| **Connection limit** | Monitor MySQL max_connections for large tenant count |
| **Shared DB load** | Monitor `souda_shared` query volume; shared mode tenants share one DB |
| **TenantConfig caching** | 24h TTL reduces config table reads |
