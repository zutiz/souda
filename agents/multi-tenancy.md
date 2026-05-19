# Multi-Tenancy Guide

This application uses `stancl/tenancy` in **multi-database mode** for full data isolation.

## Tenancy Model

- Each tenant gets their own MySQL database (`souda_tenant_{uuid}`).
- Central database holds shared data (users, billing, plans, settings).
- Tenant databases hold operational business data (tasks, future modules).
- Tenant context is derived from the authenticated user's `tenant_id`.
- Admin routes (`/admin/*`) bypass tenant context entirely.

## Registration Flow

When a user registers:

1. A `Tenant` record is created in the central database.
2. A `User` is created and linked via `tenant_id`.
3. On first request, the tenant database is auto-created and migrated.

This flow runs in a database transaction.

## Tenant Initialization

After authentication, tenancy is initialized via `InitializeTenancyByUser` middleware:

1. Derives tenant from `auth()->user()->tenant_id`.
2. Initializes tenancy and auto-creates the tenant DB if missing.
3. Activates bootstrappers: Database, Cache, Filesystem, Queue.
4. On request completion: `tenancy()->end()` reverts to central context.

## Tenant Bootstrappers

All four bootstrappers are active:
- `DatabaseTenancyBootstrapper` — Switches DB connection to tenant database
- `CacheTenancyBootstrapper` — Tags cache keys with tenant ID
- `FilesystemTenancyBootstrapper` — Suffixes storage paths with tenant ID
- `QueueTenancyBootstrapper` — Makes queued jobs tenant-aware

## Central vs Tenant Models

**Central models** (use `CentralConnection` trait):
- `User`, `Tenant`, `AppSetting`, `SocialAccount`
- All Billing models: `Plan`, `Subscription`, `Payment`, `SeatAllocation`
- All Spatie Permission models

**Tenant models** (no trait, run on tenant DB):
- `Task` (and future: Product, Order, Inventory, etc.)

## Migrations

- **Central migrations** in `database/migrations/` — run via `php artisan migrate`
- **Tenant migrations** in `database/migrations/tenant/` — run via `php artisan tenants:migrate`
- Auto-migrated on tenant creation via `TenantCreated` event

## Security Notes

- Always use `CentralConnection` trait on central models to prevent leaking to tenant DB.
- Never query tenant models outside initialised tenant context.
- Tenant context is derived from authenticated user state, not user-editable request input.
- Subscription gating ensures unpaid/expired tenants cannot access protected features.
- Seat gating (`EnsureSeatAvailable`) prevents exceeding plan seat limits on team invites.
- Feature gating (`EnsureTenantHasFeature`) controls granular plan-based access.

## Domain Support

- Domain model is configured but currently unused (user-based identification).
- Switch to domain-based: use `InitializeTenancyByDomain` middleware.
- Custom domains can be assigned via `$tenant->domains()->create(['domain' => '...'])`.

## Reference

For full details, see `docs/architecture/multi-tenancy.md`.
