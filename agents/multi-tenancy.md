# Multi-Tenancy Guide

This application uses `stancl/tenancy` with a **hybrid tenancy model**: shared database for free/starter plans, dedicated database per tenant for professional/enterprise.

## Tenancy Model

- **shared** mode (free/starter plans): single `souda_shared` database with `tenant_id` column isolation via global scopes.
- **dedicated** mode (professional/enterprise plans): each tenant gets their own MySQL database (`souda_tenant_{uuid}`), identical to the original multi-DB mode.
- Central database holds shared data (users, billing, plans, settings).
- Tenant context is derived from the authenticated user's `tenant_id`.
- Admin routes (`/admin/*`) bypass tenant context entirely.

## Registration Flow

When a user registers:

1. A `Tenant` record is created in the central database (`tenancy_mode` defaults to `shared`).
2. A `User` is created and linked via `tenant_id`.
3. On subscription activation, the mode is determined by plan slug:
   - `free`/`starter` → `shared`: tenant settings provisioned in shared DB.
   - `professional`/`enterprise` → `dedicated`: dedicated database created and migrated.

## Tenant Initialization

After authentication, tenancy is initialized via `InitializeTenancyByUser` middleware, which delegates to `TenantManager`:

```
InitializeTenancyByUser middleware
         │
         ├─ Dedicated tenant → TenantManager → DedicatedMode
         │   └── tenancy()->initialize() → stancl bootstrappers
         │       (DB switch, cache tags, storage suffix, queue prefix)
         │
         └─ Shared tenant → TenantManager → SharedMode
             └── No DB switch. Container binding for tenant() helper.
                 Cache prefix, storage prefix applied manually.
                 Global scopes (TenantScope) filter by tenant_id.
```

### Key difference from original pure multi-DB mode

Shared tenants do NOT call `tenancy()->initialize()`. Instead, `SharedMode`:
- Binds `Tenant` into the container so `tenant()` helper works.
- Configures cache prefix isolation.
- Configures storage path isolation.
- Models use `HasTenantScope` trait for automatic `tenant_id` filtering.

## Tenant Bootstrappers

**Dedicated mode** (all four active via stancl):
- `DatabaseTenancyBootstrapper` — Switches DB connection to tenant database.
- `CacheTenancyBootstrapper` — Tags cache keys with tenant ID.
- `FilesystemTenancyBootstrapper` — Suffixes storage paths with tenant ID.
- `QueueTenancyBootstrapper` — Makes queued jobs tenant-aware.

**Shared mode** (manual isolation via `SharedMode`):
- Cache: key prefix `tenant_shared_{id}_`.
- Storage: path suffix `shared/{id}/`.
- Queue: queue name prefix `shared-{id}`.
- Database: `tenant_id` global scope on models.

## Central vs Tenant Models

**Central models** (use `CentralConnection` trait):
- `User`, `Tenant`, `AppSetting`, `SocialAccount`
- All Billing models: `Plan`, `Subscription`, `Payment`, `SeatAllocation`
- All Spatie Permission models

**Tenant models** (dynamic connection):
- `Task`, `TenantSetting` (and future: Product, Order, Inventory, etc.)
- Connection resolves at query time via `getConnectionName()`:
  - Shared mode → `shared` connection.
  - Dedicated mode → default connection (switched by stancl).

**Shared tenant models** use `HasTenantScope` trait which:
- Applies global `TenantScope` filtering by `tenant_id`.
- Auto-sets `tenant_id` on create.

## TenantManager

Central abstraction at `App\Tenancy\TenantManager`:

```php
$manager = app(TenantManager::class);
$manager->initialize($tenant);       // Mode-aware init
$manager->current();                  // Current tenant
$manager->isShared() / isDedicated(); // Mode checks
$manager->id();                       // Current tenant ID
$manager->end();                      // Cleanup
```

Plan→mode mapping is configurable in `config/tenancy.php`:
```php
'plan_mode_map' => [
    'free'         => 'shared',
    'starter'      => 'shared',
    'professional' => 'dedicated',
    'enterprise'   => 'dedicated',
],
```

## Migrations

- **Central migrations** in `database/migrations/` — run via `php artisan migrate`.
- **Tenant migrations** in `database/migrations/tenant/` — run per dedicated tenant DB.
- **Shared migrations** in `database/migrations/shared/` — run via `php artisan tenants:migrate-shared` (creates `souda_shared` tables with `tenant_id` columns).

## Tenant Lifecycle: Upgrade/Downgrade

### Upgrade (shared → dedicated)

Triggered when a tenant on free/starter subscribes to professional/enterprise:
1. Create dedicated database + run migrations.
2. Migrate data: read from shared DB tables, write to dedicated DB.
3. Update `tenancy_mode` to `dedicated`.
4. Dispatch `TenantModeChanged` event.

### Downgrade (dedicated → shared)

Triggered when a professional/enterprise tenant moves to free/starter:
1. Migrate data: read from dedicated DB, write to shared DB with `tenant_id`.
2. Update `tenancy_mode` to `shared`, clear `database_name`.
3. Drop dedicated database.
4. Dispatch `TenantModeChanged` event.

## Console Commands

| Command | Purpose |
|---------|---------|
| `tenants:migrate-mode` | Bulk-migrate tenants between modes |
| `tenants:list-modes` | List all tenants with their mode, plan, database |
| `tenants:migrate-shared` | Run shared DB migrations |

## Key Files

| File | Purpose |
|------|---------|
| `config/tenancy.php` | Hybrid tenancy configuration, plan→mode map |
| `config/database.php` | Central + shared + template DB connections |
| `app/Models/Tenant.php` | Tenant model with `isShared()`/`isDedicated()` |
| `app/Tenancy/TenantManager.php` | Central tenancy abstraction |
| `app/Tenancy/Contracts/TenantModeStrategy.php` | Mode strategy interface |
| `app/Tenancy/Modes/SharedMode.php` | Shared tenant mode (no DB switch) |
| `app/Tenancy/Modes/DedicatedMode.php` | Dedicated tenant mode (delegates to stancl) |
| `app/Tenancy/Models/Concerns/HasTenantScope.php` | Global scope trait |
| `app/Tenancy/Scopes/TenantScope.php` | `tenant_id` global scope |
| `app/Http/Middleware/InitializeTenancyByUser.php` | Mode-aware tenant init |
| `app/Jobs/TenantJob.php` | Tenant-aware base job |
| `app/Jobs/MigrateTenantToDedicated.php` | Shared→Dedicated migration |
| `app/Jobs/MigrateTenantToShared.php` | Dedicated→Shared migration |
| `app/Listeners/ProvisionTenantDatabase.php` | Mode-aware provisioning |
| `app/Events/TenantModeChanged.php` | Mode transition event |

## Security Notes

- Always use `CentralConnection` trait on central models.
- `tenant()` helper works in both modes (bound by DedicatedMode via stancl, by SharedMode via container).
- Subscription gating, feature gating, and seat gating all work transparently across modes.
- Never query tenant models outside initialized tenant context.
- `TenantScope` only applies when tenant is in shared mode (safe for admin queries).
