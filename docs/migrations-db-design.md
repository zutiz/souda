# Migrations & Database Design

Authoritative reference for how migrations are organized and how each table reaches its
database in SOUDA's hybrid (shared + dedicated) tenancy. Read this before creating or
editing any migration.

---

## 1. The Three Databases

| Name | Connection | Prod DB | Test DB | Purpose |
|---|---|---|---|---|
| Central | `central` | `souda` | `souda_testing` | Users, tenants, billing, business types, roles, permissions |
| Shared | `shared` | `souda_shared` | `souda_shared` | All tenant business data for Free/Starter/Professional plans: catalog, inventory, orders, stores, settings |
| Dedicated | `mysql` (per tenant) | `souda_tenant_{uuid}` | — | Enterprise tenants get their own DB via stancl/tenancy |

- `config/database.php`: shared connection database = `env('SHARED_DB_DATABASE', 'souda_shared')`.
- `config/tenancy.php`: `shared_connection` = `env('SHARED_DB_CONNECTION', 'shared')`.
- `phpunit.xml`: `DB_DATABASE=souda_testing`, `CENTRAL_DB_DATABASE=souda_testing` — the
  `central` and default `mysql` connections point at the same database **in tests**, so test
  processes must never run in parallel.

---

## 2. Migration Folders

| Folder | Destination DB | Connection style |
|---|---|---|
| `database/migrations/` (root) | central `souda` | plain `Schema::create()` — default connection |
| `database/migrations/central/` | central `souda` (loaded via `loadMigrationsFrom` in `AppServiceProvider`) | plain `Schema::create()` |
| `database/migrations/shared/` | `souda_shared` | **`Schema::connection('shared')->...`** |
| `database/migrations/deprecated/` | dedicated tenant DBs ONLY | plain `Schema::create()` — legacy catalog, NO `tenant_id` |
| `app/Modules/{X}/Database/Migrations/Tenant/` | `souda_shared` (shared mode) **and** dedicated tenant DBs | plain `Schema::create()` — connection-agnostic, WITH `tenant_id` |

### How module `Tenant/` migrations reach both DBs

The SAME files are applied two ways:

1. **Shared mode** — the migrator is pointed at the shared DB:
   ```bash
   php artisan migrate --database=shared --path=app/Modules/Inventory/Database/Migrations/Tenant --force
   ```
   The test bootstrap (`tests/Support/RefreshMultiDatabase::setupSharedDatabase()`) and the
   `migrate:fresh:all` command run every module `Tenant/` folder this way.

2. **Dedicated mode** — the same paths are listed in `config('tenancy.migration_parameters')`
   and run per-tenant via `php artisan tenants:migrate`.

**Consequence:** module migrations must be **connection-agnostic** — never call
`Schema::connection('shared')` inside them. They add `tenant_id` as an ordinary column.

### What NOT to do

- Do NOT create a module migration for a table that already exists in `database/migrations/shared/`.
- Do NOT run `database/migrations/deprecated/` against `souda_shared` — those tables already
  exist there (with `tenant_id`) from `2026_06_06_000001_create_shared_product_tables.php`, and
  the deprecated versions lack `tenant_id`.
- Do NOT delete `database/migrations/deprecated/` — dedicated tenant DBs depend on it (see §4).

---

## 3. Central Migrations

`database/migrations/` + `database/migrations/central/` (the pivot `tenant_user` migration lives
in `central/`, loaded by `AppServiceProvider::boot()` → `loadMigrationsFrom`). Use anonymous
classes, **no typed properties** (PHP 8.4 fatal otherwise).

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_central_table', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_central_table');
    }
};
```

---

## 4. Shared Migrations (`database/migrations/shared/`)

All use `Schema::connection('shared')`. Current files:

| File | Tables | Notes |
|---|---|---|
| `2026_06_05_000001_create_shared_tenant_tables.php` | `tasks`, `tenant_settings` | |
| `2026_06_06_000001_create_shared_product_tables.php` | `brands`, `categories`, `attributes`, `attribute_values`, `tax_categories`, `tax_rates`, `warehouses`, `products`, `category_product`, `product_attribute_values`, `product_attribute_text_values`, `variants`, `variant_attribute_values`, `product_media`, `warehouse_stock`, `stock_reservations`, `stock_movements`, `audit_logs`, `pricing_rules` | Full catalog for shared-mode tenants. Composite uniques: `uq_brands_tenant_slug`, `uq_categories_tenant_slug`, products `UNIQUE (tenant_id, slug)` |
| `2026_06_20_000001_create_shared_business_type_tables.php` | `tenant_configs`, `tenant_module_overrides` | |
| `2026_08_01_000001_add_branding_to_tenant_settings.php` | alters `tenant_settings` | Adds `brand_primary_color`, `brand_accent_color`, `brand_logo_url` (uses `protected $connection = 'shared';`) |

### Uniqueness convention

Shared catalog tables use **composite unique keys** scoped to the tenant:

```php
Schema::connection('shared')->create('brands', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id', 36);
    $table->string('name');
    $table->string('slug');
    // ...
    $table->unique(['tenant_id', 'slug'], 'uq_brands_tenant_slug');
});
```

This is the **preferred** pattern. A few inventory module tables kept **global** unique indexes
(`inventory_warehouses.slug`, `inventory_transfers.reference`, `inventory_counts.reference`).
They only avoid collisions because tests truncate all shared tables between runs — for new
tables, prefer the composite form.

---

## 5. Module Migrations (`app/Modules/{X}/Database/Migrations/Tenant/`)

Plain `Schema::create()`, every table carries `tenant_id`. `migration_parameters` order
(`config/tenancy.php`):

```php
'migration_parameters' => [
    '--force'   => true,
    '--realpath'=> true,
    '--path'    => [
        database_path('migrations/tenant'),                       // reserved (empty)
        database_path('migrations/deprecated'),                   // legacy catalog, NO tenant_id
        app_path('Modules/Store/Database/Migrations/Tenant'),     // stores
        app_path('Modules/Product/Database/Migrations/Tenant'),   // store_product/store_customer/store_warehouse pivots
        app_path('Modules/Inventory/Database/Migrations/Tenant'), // inventory
        app_path('Modules/Order/Database/Migrations/Tenant'),     // orders
    ],
],
```

### Store module

- `2026_06_26_000001_create_stores_table.php` → `stores`
  (`UNIQUE (tenant_id, slug)`, `UNIQUE (tenant_id, code)`)

### Product module (pivots only now)

- `2026_06_26_000020_create_store_product_table.php` → `store_product`
- `2026_06_26_000021_create_store_customer_table.php` → `store_customer`
- `2026_06_26_000022_create_store_warehouse_table.php` → `store_warehouse`

The 19 `2026_05_21_*` catalog migrations were moved to `database/migrations/deprecated/`.

### Inventory module

`inventory_ledger`, `inventory_balances`, `cost_layers`, `stock_reservations`,
`inventory_purchase_suggestions`, `inventory_counts`, `inventory_count_items`,
`inventory_warehouses`, `inventory_bins`, `inventory_transfers`, `inventory_transfer_items`,
`inventory_batches`, `serial_numbers`, `inventory_rules`, `inventory_alerts`,
`demand_forecasts`, `scheduled_task_logs`.

### Order module

`order_number_sequences`, `orders`, `order_items`, `order_status_histories`, `shipments`,
`shipment_items`, `delivery_attempts`, `order_returns`.

---

## 6. Deprecated Migrations (`database/migrations/deprecated/`)

19 legacy `2026_05_21_*` catalog migrations (categories, brands, attributes, attribute_values,
tax_categories, tax_rates, products, category_product, product_attribute_values,
product_attribute_text_values, variants, variant_attribute_values, product_media, warehouses,
warehouse_stock, stock_reservations, stock_movements, audit_logs, pricing_rules).

- **No `tenant_id` column** — they predate shared mode.
- Applied to **dedicated tenant DBs only** via `migration_parameters` (so the Product module
  finds `products` on Enterprise tenants).
- **Never** applied to `souda_shared` — the shared catalog comes from
  `2026_06_06_000001_create_shared_product_tables.php`.

Removing `database/migrations/deprecated` from `migration_parameters` breaks dedicated-mode
tests with `SQLSTATE[HY000]` FK errors (e.g. `store_product_product_id_foreign`).

---

## 7. How DBs Get Built

### Development — `migrate:fresh:all`

`app/Console/Commands/MigrateFreshAll.php`:
1. Drops every table in central `mysql` + shared `souda_shared`.
2. `migrate --path=database/migrations` (central).
3. `migrate --database=shared --path=database/migrations/shared`.
4. `migrate --database=shared` (all module migrations via `loadMigrationsFrom`).
5. Optional `--seed`.

**Do NOT use `php artisan migrate:fresh`** — it only touches the default (`souda`) DB and leaves
`souda_shared` behind, causing "table already exists".

### Tests — `tests/Support/RefreshMultiDatabase.php`

- `setupCentralDatabase()` / `setupSharedDatabase()` create fresh schema per test-process.
- `setupSharedDatabase()` migrates `database/migrations/shared` **and** every module `Tenant/`
  folder with `--database=shared`.
- Between tests, `truncateAllSharedTables()` truncates **every** table in `souda_shared`
  (except `migrations`) with `SET FOREIGN_KEY_CHECKS = 0/1`. This is what guarantees slug /
  reference uniqueness and correct `assertDatabaseCount` across the shared-mode suites.

### Enterprise tenant — `tenants:migrate`

Uses `config('tenancy.migration_parameters')` → deprecated catalog + module `Tenant/` folders.

---

## 8. Resetting / Inspecting

```bash
php artisan migrate:fresh:all --seed     # rebuild central + shared, seed
php artisan migrate --force --database=shared --path=database/migrations/shared
php artisan migrate --database=shared --path=app/Modules/Inventory/Database/Migrations/Tenant --force

# list module migration paths as registered
php artisan config:show tenancy.migration_parameters
```

Inspect actual shared tables (PowerShell mangles `--execute`, so use a temp PHP file):

```php
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (DB::connection('shared')->select('SHOW TABLES') as $row) {
    echo implode('', (array) $row) . "\n";
}
```

---

## 9. Shared Table Catalog (~50 tables, `souda_shared`)

attribute_values, attributes, audit_logs, brands, categories, category_product, cost_layers,
delivery_attempts, demand_forecasts, inventory_alerts, inventory_balances, inventory_batches,
inventory_bins, inventory_count_items, inventory_counts, inventory_ledger,
inventory_purchase_suggestions, inventory_rules, inventory_stock_reservations,
inventory_transfer_items, inventory_transfers, inventory_warehouses, migrations, order_items,
order_number_sequences, order_returns, order_status_histories, orders, pricing_rules,
product_attribute_text_values, product_attribute_values, product_media, products,
scheduled_task_logs, serial_numbers, shipment_items, shipments, stock_movements,
stock_reservations, stores, tasks, tax_categories, tax_rates, tenant_configs,
tenant_module_overrides, tenant_settings, variant_attribute_values, variants, warehouse_stock,
warehouses.
