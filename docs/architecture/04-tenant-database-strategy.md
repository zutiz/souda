# Tenant-Aware Database Strategy

## Overview

Multi-database tenancy with strict isolation between central (shared) and tenant (isolated) data.

## Database Architecture

```
┌─────────────────────────────────────────────────────┐
│                  Central Database                    │
│  (souda_central / default connection)                │
│                                                      │
│  ┌─────────────────────────────────────────────┐    │
│  │  Authentication & Authorization              │    │
│  │  - users, password_reset_tokens, sessions    │    │
│  │  - roles, permissions, model_has_*           │    │
│  └─────────────────────────────────────────────┘    │
│  ┌─────────────────────────────────────────────┐    │
│  │  Tenancy                                     │    │
│  │  - tenants, domains                          │    │
│  └─────────────────────────────────────────────┘    │
│  ┌─────────────────────────────────────────────┐    │
│  │  Billing & Subscriptions                     │    │
│  │  - billing_plans                             │    │
│  │  - billing_subscriptions                     │    │
│  │  - billing_payments                          │    │
│  │  - plans, plan_prices (Cashier mirror)       │    │
│  │  - subscriptions, subscription_items         │    │
│  └─────────────────────────────────────────────┘    │
│  ┌─────────────────────────────────────────────┐    │
│  │  Platform Configuration                      │    │
│  │  - app_settings, social_accounts             │    │
│  └─────────────────────────────────────────────┘    │
│  ┌─────────────────────────────────────────────┐    │
│  │  Queue & Cache                               │    │
│  │  - jobs, job_batches, failed_jobs            │    │
│  │  - cache, cache_locks                        │    │
│  └─────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────┘

┌──────────────────────┐  ┌──────────────────────┐
│  Tenant DB: tenant_1  │  │  Tenant DB: tenant_2  │
│  (souda_tenant_{uuid})│  │  (souda_tenant_{uuid})│
│                       │  │                       │
│  ┌─────────────────┐ │  │  ┌─────────────────┐ │
│  │  Products       │ │  │  │  Products       │ │
│  │  - products     │ │  │  │  - products     │ │
│  │  - categories   │ │  │  │  - categories   │ │
│  │  - variants     │ │  │  │  - variants     │ │
│  └─────────────────┘ │  │  └─────────────────┘ │
│  ┌─────────────────┐ │  │  ┌─────────────────┐ │
│  │  Orders         │ │  │  │  Orders         │ │
│  │  - orders       │ │  │  │  - orders       │ │
│  │  - order_items  │ │  │  │  - order_items  │ │
│  └─────────────────┘ │  │  └─────────────────┘ │
│  ┌─────────────────┐ │  │  ┌─────────────────┐ │
│  │  Inventory      │ │  │  │  Inventory      │ │
│  │  - stock        │ │  │  │  - stock        │ │
│  │  - movements    │ │  │  │  - movements    │ │
│  └─────────────────┘ │  │  └─────────────────┘ │
│  ┌─────────────────┐ │  │  ┌─────────────────┐ │
│  │  CRM            │ │  │  │  CRM            │ │
│  │  - contacts     │ │  │  │  - contacts     │ │
│  │  - interactions │ │  │  │  - interactions │ │
│  └─────────────────┘ │  │  └─────────────────┘ │
│  ┌─────────────────┐ │  │  ┌─────────────────┐ │
│  │  Tenant Users   │ │  │  │  Tenant Users   │ │
│  │  - (user links) │ │  │  │  - (user links) │ │
│  └─────────────────┘ │  │  └─────────────────┘ │
└──────────────────────┘  └──────────────────────┘
```

## Central vs Tenant Data Classification

### Central Data (Shared Across All Tenants)

| Category | Tables | Rationale |
|----------|--------|-----------|
| **Authentication** | `users`, `sessions`, `password_reset_tokens` | Users authenticate to platform, not tenant |
| **Authorization** | `roles`, `permissions`, `model_has_*` | Role definitions are platform-level |
| **Tenancy** | `tenants`, `domains` | Tenant registry |
| **Billing** | `billing_plans`, `billing_subscriptions`, `billing_payments`, `plans`, `plan_prices`, `subscriptions`, `subscription_items` | Plans are platform-defined, subscriptions track tenant billing |
| **Platform Config** | `app_settings`, `social_accounts` | Platform-wide settings |
| **Infrastructure** | `jobs`, `job_batches`, `failed_jobs`, `cache`, `cache_locks` | Shared queue and cache |

### Tenant Data (Isolated Per Tenant)

| Category | Tables | Rationale |
|----------|--------|-----------|
| **Products** | `products`, `categories`, `variants`, `product_images` | Each tenant has their own catalog |
| **Orders** | `orders`, `order_items`, `order_payments` | Each tenant processes their own orders |
| **Inventory** | `stock`, `stock_movements`, `warehouses` | Each tenant manages their own stock |
| **CRM** | `contacts`, `interactions`, `deals`, `pipelines` | Each tenant has their own customers |
| **Business Data** | `tasks`, `reports`, `settings` | Each tenant has their own business data |

## Tenancy Configuration

### Database Naming

```php
// config/tenancy.php
'database' => [
    'prefix' => 'souda_tenant_',
    'suffix' => '',
],

// app/Models/Tenant.php
public function getDatabaseName(): string
{
    return config('tenancy.database.prefix') . $this->id;
}
```

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

```
1. User authenticates (central DB)
2. InitializeTenancyByUser middleware runs
3. Derives tenant from auth()->user()->tenant_id
4. Tenancy bootstrappers activate:
   ├── DatabaseTenancyBootstrapper: Switches DB connection to tenant DB
   ├── CacheTenancyBootstrapper: Tags cache with tenant ID
   ├── FilesystemTenancyBootstrapper: Suffixes storage paths
   └── QueueTenancyBootstrapper: Makes queue jobs tenant-aware
5. If tenant DB doesn't exist: create + migrate (JobPipeline)
6. Route is processed within tenant context
7. On terminate: tenancy()->end() reverts to central context
```

## Migration Strategy

### Central Migrations

- Location: `database/migrations/`
- Run once on central database
- Use standard `php artisan migrate`
- Timestamp-based naming: `YYYY_MM_DD_HHMMSS_description.php`

### Tenant Migrations

- Location: `database/migrations/tenant/`
- Run automatically on tenant creation via `MigrateDatabase` job
- Run manually: `php artisan tenants:migrate`
- Run for specific tenant: `php artisan tenants:migrate --tenants={uuid}`

### Module Tenant Migrations

For module-specific tenant migrations:

```php
// In module service provider
public function boot(): void
{
    if ($this->app->runningInConsole()) {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations/Tenant');
    }
}
```

## Model Strategy

### Central Models

```php
// app/Models/User.php
class User extends Authenticatable
{
    use CentralConnection; // Explicitly stays on central DB

    protected $connection = null; // Uses default (central)
}
```

### Tenant Models

```php
// app/Modules/Products/Models/Product.php
class Product extends Model
{
    // No CentralConnection trait - uses tenant connection
    // Automatically runs on tenant DB when tenancy is initialized

    protected $guarded = [];
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

### Rule 1: Never Query Tenant DB from Central Context

```php
// ✗ WRONG
tenancy()->initialize($tenant);
$products = Product::all(); // May leak if tenancy not properly scoped

// ✓ CORRECT
tenancy()->initialize($tenant);
try {
    $products = Product::all();
} finally {
    tenancy()->end();
}
```

### Rule 2: Always Use CentralConnection on Central Models

```php
// ✗ WRONG - model without CentralConnection may switch to tenant DB
class User extends Authenticatable { }

// ✓ CORRECT
class User extends Authenticatable
{
    use CentralConnection;
}
```

### Rule 3: Tenant Scope in Manual Queries

```php
// When running queries outside middleware context
$tenantId = auth()->user()->tenant_id;

tenancy()->initialize($tenantId);
try {
    $orders = Order::where('status', 'pending')->get();
} finally {
    tenancy()->end();
}
```

## Cache Strategy

### Tenant-Tagged Cache

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

## Queue Strategy

### Tenant-Aware Jobs

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

## Database Creation on Tenant Registration

```php
// TenancyServiceProvider
protected $listen = [
    TenantCreated::class => [
        JobPipeline::make([
            CreateDatabase::class,
            MigrateDatabase::class,
        ])->shouldBeQueued(false)->send(),
    ],
];
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
