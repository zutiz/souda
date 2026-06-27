# Multi-Store Architecture

## Overview

This document defines the architecture for adding **multi-store (multi-location)** support to SOUDA. It enables a single tenant to operate multiple store locations under one account, with one shared product catalog, shared CRM, and per-location pricing/inventory/orders/domains.

The design is informed by global multi-location standards at Square, Lightspeed, Shopify, Toast, and Clover — all of which use a **shared catalog with per-location operations** model for retail chains and multi-branch businesses.

---

## 1. Core Concept

```
Platform (souda)
│
└── Tenant (one owner, one dedicated database)
    │
    ├── Store A (default)  ← included in subscription
    ├── Store B             ← extra monthly fee
    ├── Store C             ← extra monthly fee
    │   ...
    │
    ├── Products (master catalog — shared)
    ├── Customers (CRM — shared)
    ├── Categories / Brands / Attributes (shared)
    ├── Warehouses (physical stock locations)
    ├── Orders (per-store via store_id)
    └── Domains (per-store via store_id)
```

### Key Principle

**One tenant = one database = many store locations.** Products, customers, categories, brands, and attributes are created once in the tenant's database and can be selectively activated/priced per store. Orders, POS sessions, pricing rules, and domains are per-store.

---

## 2. Global Standards Analysis

### Industry Comparison

| Platform | Catalog | Inventory | Customers | Pricing | Domains | Target |
|---|---|---|---|---|---|---|
| **Square** | Shared | Per-location | Shared | Per-location | Per-location | Retail chains |
| **Lightspeed** | Shared | Per-location | Shared | Per-location | Per-location | Mid-market retail |
| **Shopify Multi-Location** | Shared | Per-location | Shared | Per-market | Per-location | Retail + ecommerce |
| **Toast** | Shared | Per-location | Shared | Per-location | Per-location | Restaurants |
| **Clover** | Shared | Per-location | Shared | Per-location | Per-location | SMB retail |
| **→ SOUDA (this design)** | **Shared** | **Per-warehouse** | **Shared** | **Per-store pivot** | **Per-store** | **Multi-vertical SMEs** |

### Why Shared Catalog for SOUDA

1. **Operational efficiency** — A bakery opening a second location doesn't re-enter 500 products. They select which existing products to sell there.
2. **Inventory reality** — Products are purchased centrally from suppliers. Warehouses serve multiple stores. Stock transfers between locations are routine.
3. **Customer continuity** — A customer who visits Store A and Store B is recognized as the same person. Unified loyalty, returns, and history.
4. **Supplier management** — One purchase order, distributed across stores.
5. **Consolidated reporting** — Owner sees data across all stores, then drills down per location.

The fully-separate "multi-store" model (separate catalogs, separate CRM) is reserved for franchise independence or international expansion — which are better served by creating separate tenants.

---

## 3. Data Model

### 3.1 Stores Table (Tenant DB)

```php
Schema::create('stores', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('tenant_id');          // from tenancy context
    $table->string('name', 255);
    $table->string('slug', 255)->unique();
    $table->string('code', 50)->unique();
    $table->string('email', 255)->nullable();
    $table->string('phone', 30)->nullable();
    $table->string('address_line_1', 255)->nullable();
    $table->string('address_line_2', 255)->nullable();
    $table->string('city', 100)->nullable();
    $table->string('state', 100)->nullable();
    $table->string('postal_code', 20)->nullable();
    $table->string('country', 100)->nullable();
    $table->string('timezone', 50)->default('UTC');
    $table->string('currency', 3)->default('BDT');
    $table->string('locale', 10)->default('en');
    $table->string('status', 20)->default('active'); // active, inactive, paused, provisioning
    $table->boolean('is_default')->default(false);
    $table->json('business_hours')->nullable();
    $table->json('config')->nullable();        // store-level settings overrides
    $table->json('pos_settings')->nullable();  // POS layout, tender types, etc.
    $table->unsignedSmallInteger('sort_order')->default(0);
    $table->timestamps();
    $table->softDeletes();

    $table->index('tenant_id');
    $table->index('status');
    $table->index(['tenant_id', 'is_default']);
});
```

### 3.2 Product-Store Pivot

Products exist once in the master catalog. This pivot controls per-store visibility and pricing.

```php
Schema::create('store_product', function (Blueprint $table) {
    $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
    $table->foreignUlid('product_id')->constrained('products')->cascadeOnDelete();
    $table->integer('price')->nullable();          // store-specific price override
    $table->integer('compare_at_price')->nullable();
    $table->boolean('is_visible')->default(true);
    $table->boolean('is_featured')->default(false);
    $table->string('status')->default('active');   // active, draft, archived
    $table->unsignedSmallInteger('sort_order')->default(0);
    $table->timestamps();

    $table->primary(['store_id', 'product_id']);
    $table->index('store_id');
    $table->index('product_id');
    $table->index('is_visible');
});
```

### 3.3 Customer-Store Pivot

Customers are shared across stores. This pivot tracks per-store relationship data.

```php
Schema::create('store_customer', function (Blueprint $table) {
    $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
    $table->foreignUlid('customer_id')->constrained('customers')->cascadeOnDelete();
    $table->string('loyalty_number')->nullable();
    $table->unsignedInteger('loyalty_points')->default(0);
    $table->unsignedInteger('total_visits')->default(0);
    $table->unsignedBigInteger('total_spent')->default(0);
    $table->timestamp('last_visit_at')->nullable();
    $table->json('tags')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->primary(['store_id', 'customer_id']);
    $table->index('customer_id');
    $table->index('loyalty_number');
});
```

### 3.4 Store-Warehouse Pivot

Warehouses are tenant-level physical stock locations. This pivot controls which stores draw from which warehouses.

```php
Schema::create('store_warehouse', function (Blueprint $table) {
    $table->foreignUlid('store_id')->constrained('stores')->cascadeOnDelete();
    $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
    $table->boolean('is_default_for_receiving')->default(false);
    $table->boolean('is_default_for_fulfillment')->default(false);
    $table->unsignedSmallInteger('sort_order')->default(0);
    $table->timestamps();

    $table->primary(['store_id', 'warehouse_id']);
    $table->index('warehouse_id');
});
```

### 3.5 Orders — Add `store_id`

```php
Schema::table('orders', function (Blueprint $table) {
    $table->string('store_id', 36)->after('tenant_id');
    $table->foreign('store_id')->references('id')->on('stores');
    $table->index('store_id');
});

Schema::table('order_items', function (Blueprint $table) {
    $table->string('store_id', 36)->after('order_id')->nullable();
    // or inherit from order
});
```

### 3.6 Pricing Rules — Add `store_id`

```php
Schema::table('pricing_rules', function (Blueprint $table) {
    $table->string('store_id', 36)->nullable()->after('id');
    $table->foreign('store_id')->references('id')->on('stores');
    $table->index('store_id');
    // null = applies to all stores
});
```

### 3.7 Domains — Add `store_id`

```php
Schema::table('domains', function (Blueprint $table) {
    $table->string('store_id', 36)->nullable()->after('tenant_id');
    $table->index('store_id');
});
```

### 3.8 POS Sessions — Add `store_id`

```php
Schema::create('pos_sessions', function (Blueprint $table) {
    // ...
    $table->string('store_id', 36);
    $table->foreign('store_id')->references('id')->on('stores');
    $table->index('store_id');
});
```

### 3.9 Central DB: Store Allocations (Billing)

```php
Schema::create('billing_store_allocations', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id');
    $table->foreignId('subscription_id')->constrained('billing_subscriptions')->cascadeOnDelete();
    $table->string('store_id', 36);       // ULID from tenant DB store
    $table->string('store_code', 50);
    $table->string('status', 20);          // active, released
    $table->timestamp('allocated_at')->nullable();
    $table->timestamp('released_at')->nullable();
    $table->timestamp('billing_start_at')->nullable();
    $table->timestamps();

    $table->foreign('tenant_id')->references('id')->on('tenants');
    $table->index('tenant_id');
    $table->index(['tenant_id', 'status']);
    $table->unique(['tenant_id', 'store_id']);  // one allocation per store
});
```

---

## 4. Store Context Middleware

### Resolution Chain

```php
// App\Modules\Store\Http\Middleware\InitializeStoreContext
// Priority: runs AFTER InitializeTenancyByUser

class InitializeStoreContext
{
    public function handle(Request $request, Closure $next): Response
    {
        // Resolution priority:
        // 1. Route parameter: /{store}/products
        // 2. Custom domain: domains table with store_id
        // 3. Subdomain: {store-slug}.app.souda.com
        // 4. Session: user's last active store
        // 5. Fallback: tenant's default store (is_default = true)

        $store = $this->resolveFromRoute($request)
            ?? $this->resolveFromDomain($request)
            ?? $this->resolveFromSubdomain($request)
            ?? $this->resolveFromSession($request)
            ?? $this->resolveDefault();

        if (! $store) {
            return redirect()->route('stores.create');
        }

        if (! $store->isActive()) {
            return redirect()->route('stores.select')
                ->with('error', 'This store is not active.');
        }

        app(StoreContextManager::class)->initialize($store);

        // Store in session for subsequent requests
        session(['current_store_id' => $store->id]);

        return $next($request);
    }
}
```

### Registration in `bootstrap/app.php`

```php
$middleware->prependToPriorityList(
    before: SubstituteBindings::class,
    prepend: InitializeTenancyByUser::class,
);

// Store context initializes after tenancy, before route binding
$middleware->appendToPriorityList(
    after: InitializeTenancyByUser::class,
    prepend: InitializeStoreContext::class,
);
```

### StoreContextManager (Singleton)

```php
// App\Modules\Store\Services\StoreContextManager

class StoreContextManager
{
    protected ?Store $currentStore = null;
    protected bool $initialized = false;

    public function initialize(Store $store): void
    {
        $this->currentStore = $store;
        $this->initialized = true;
    }

    public function end(): void
    {
        $this->currentStore = null;
        $this->initialized = false;
    }

    public function current(): ?Store
    {
        return $this->currentStore;
    }

    public function id(): ?string
    {
        return $this->currentStore?->id;
    }

    public function initialized(): bool
    {
        return $this->initialized;
    }
}
```

---

## 5. Store-Scoped Queries

### Using the StoreContextManager

```php
// In controllers
class ProductController
{
    public function index(StoreContextManager $storeContext): Response
    {
        $storeId = $storeContext->id();

        $products = Product::whereHas('stores', function ($q) use ($storeId) {
            $q->where('store_product.store_id', $storeId)
              ->where('store_product.is_visible', true);
        })->paginate();

        return Inertia::render('Product/Index', [
            'products' => $products,
            'currentStore' => $storeContext->current(),
        ]);
    }
}
```

### Global Scope Approach (Alternative)

For automatic filtering without explicit scoping in every controller:

```php
// In Product model boot()
protected static function booted(): void
{
    static::addGlobalScope('store', function (Builder $query) {
        $storeId = app(StoreContextManager::class)->id();
        if ($storeId) {
            $query->whereHas('stores', fn ($q) =>
                $q->where('store_product.store_id', $storeId)
            );
        }
    });
}
```

---

## 6. Billing Model

### Plan Configuration (Central `billing_plans` table)

```php
Schema::table('billing_plans', function (Blueprint $table) {
    $table->unsignedSmallInteger('default_stores')->default(1)->after('max_seats');
    $table->unsignedInteger('store_price')->default(0)->after('default_stores');
});
```

### Per-Plan Store Limits

| Plan | Included Stores | Store Price (monthly) | Feature Flag |
|---|---|---|---|
| Free | 1 | N/A | Not available |
| Starter | 1 | 500 BDT | `multi_store` |
| Professional | 3 | 500 BDT | `multi_store` |
| Enterprise | 5 | 350 BDT | `multi_store` |

### Billing Calculation

```php
// In SubscriptionService or StoreBillingService

public function calculateStoreAmount(Tenant $tenant, Plan $plan): array
{
    $activeStores = Store::whereTenantId($tenant->id)->count();
    $extraStores = max(0, $activeStores - $plan->default_stores);
    $storeAmount = $extraStores * $plan->store_price;

    return [
        'active_stores' => $activeStores,
        'default_stores' => $plan->default_stores,
        'extra_stores' => $extraStores,
        'store_amount' => $storeAmount,
    ];
}
```

### Store Allocation Lifecycle

```
Store Created
    │
    ├── active_store_count <= plan.default_stores
    │   └── No charge (within plan allowance)
    │
    └── active_store_count > plan.default_stores
        ├── Create StoreAllocation (status: active)
        ├── Prorated charge for remainder of billing cycle
        └── On renewal: extra_store_amount added to invoice

Store Deleted
    │
    └── Release StoreAllocation (status: released, released_at: now)
        ├── Credit applied to next invoice
        └── No charge going forward
```

### Billing Middleware Check

```php
// EnsureStoreLimit middleware (alias: 'store-limit')
class EnsureStoreLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (! $tenant) {
            return redirect()->route('billing');
        }

        $plan = $tenant->activeSubscription()?->plan;

        if (! $plan || ! $this->planHasFeature($plan, 'multi_store')) {
            $existingStores = Store::whereTenantId($tenant->id)->count();
            if ($existingStores >= 1) {
                return redirect()->route('billing')
                    ->with('error', 'Multi-store requires an upgraded plan.');
            }
        }

        return $next($request);
    }
}
```

---

## 7. Store Onboarding

### Default Store Creation (ProvisioningPipeline Step)

A new `CreateDefaultStoreStep` is added as the 11th step in the existing provisioning pipeline:

```php
class CreateDefaultStoreStep implements ProvisioningStep
{
    public function __construct(
        private readonly TenantTemplateRegistry $templateRegistry,
        private readonly StoreService $storeService,
    ) {}

    public function handle(ProvisioningContext $context): void
    {
        $tenant = $context->tenant;
        $template = $this->templateRegistry->get($context->businessTypeSlug);

        $store = Store::create([
            'tenant_id' => $tenant->id,
            'name' => ($tenant->name ?? 'Store'),
            'slug' => Str::slug($tenant->name ?? 'store'),
            'code' => strtoupper(Str::random(6)),
            'status' => 'active',
            'is_default' => true,
            'timezone' => config('app.timezone', 'UTC'),
            'currency' => 'BDT',
            'pos_settings' => $template->posDefaults(),
        ]);

        // Link to default warehouse
        $defaultWarehouse = Warehouse::where('is_default', true)->first();
        if ($defaultWarehouse) {
            $store->warehouses()->attach($defaultWarehouse->id, [
                'is_default_for_receiving' => true,
                'is_default_for_fulfillment' => true,
            ]);
        }

        // Link all existing products to the new store
        Product::select('id')->orderBy('id')->each(function ($product) use ($store) {
            DB::table('store_product')->insert([
                'store_id' => $store->id,
                'product_id' => $product->id,
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        // Link all existing customers to the new store
        Customer::select('id')->orderBy('id')->each(function ($customer) use ($store) {
            DB::table('store_customer')->insert([
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        session(['current_store_id' => $store->id]);
    }

    public function rollback(ProvisioningContext $context): void
    {
        Store::where('tenant_id', $context->tenant->id)->delete();
    }

    public function label(): string
    {
        return 'Creating default store';
    }
}
```

### Adding Extra Stores

```
Owner clicks "Add Store" in settings
    │
    ▼
POST /stores → StoreController@store
    │
    ├── Validate plan has 'multi_store' feature
    ├── Create Store (status: provisioning)
    ├── Create default warehouse for store (or assign existing)
    ├── Seed store defaults from TenantTemplate:
    │   ├── POS layout, tender types
    │   ├── Timezone, currency
    │   └── Config overrides
    ├── If over default store count → Create StoreAllocation
    ├── Set up default domain entry
    ├── Mark store as active
    └── Redirect to store selector
```

### Migration for Existing Single-Store Tenants

```php
Artisan::command('stores:migrate-existing', function () {
    $this->info('Migrating existing tenants to multi-store...');

    Tenant::where('tenancy_mode', 'dedicated')
        ->orderBy('id')
        ->each(function (Tenant $tenant) {
            tenancy()->initialize($tenant);

            $hasStore = Store::exists();

            if (! $hasStore) {
                $defaultStore = Store::create([
                    'name' => $tenant->name ?? 'Main Store',
                    'slug' => Str::slug($tenant->name ?? 'main-store'),
                    'code' => strtoupper(Str::random(6)),
                    'status' => 'active',
                    'is_default' => true,
                    'timezone' => config('app.timezone', 'UTC'),
                    'currency' => 'BDT',
                ]);

                // Link all products
                Product::select('id')->orderBy('id')->each(fn ($p) =>
                    DB::table('store_product')->insertOrIgnore([
                        'store_id' => $defaultStore->id,
                        'product_id' => $p->id,
                        'is_visible' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );

                // Link all customers
                Customer::select('id')->orderBy('id')->each(fn ($c) =>
                    DB::table('store_customer')->insertOrIgnore([
                        'store_id' => $defaultStore->id,
                        'customer_id' => $c->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );

                $this->info("  Created default store for tenant: {$tenant->id}");
            }

            tenancy()->end();
        });

    $this->info('Migration complete.');
})->purpose('Add default stores to existing single-store tenants');
```

---

## 8. Routing Architecture

### Store Selection Routes (No Store Context)

```php
// routes/tenant.php — outside store context

Route::middleware(['web', 'auth', InitializeTenancyByUser::class])->group(function () {
    Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
    Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create');
    Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');
    Route::post('/stores/{store}/switch', [StoreController::class, 'switch'])->name('stores.switch');
    Route::get('/stores/{store}/settings', [StoreSettingsController::class, 'index'])->name('stores.settings');
    Route::put('/stores/{store}/settings', [StoreSettingsController::class, 'update'])->name('stores.settings.update');
});
```

### Store-Scoped Routes

```php
// routes/tenant.php — within store context

Route::middleware([
    'web', 'auth', InitializeTenancyByUser::class,
    InitializeStoreContext::class, 'subscription',
])->prefix('{store}')->group(function () {

    Route::get('/dashboard', fn () => Inertia::render('dashboard'))->name('dashboard');

    // Products (scoped to store via middleware)
    Route::resource('products', ProductController::class);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('/categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');

    // Brands
    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

    // Orders
    Route::resource('orders', OrderController::class);

    // Customers
    Route::resource('customers', CustomerController::class);

    // POS
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/pos/register/open', [POSController::class, 'openRegister'])->name('pos.register.open');
    Route::post('/pos/register/close', [POSController::class, 'closeRegister'])->name('pos.register.close');

    // Inventory / Stock
    Route::get('/inventory', [StockController::class, 'lowStock'])->name('inventory.index');
    Route::post('/stock-transfers', [StockController::class, 'transfer'])->name('stock-transfers.transfer');
});
```

### Domain Resolution

```
Request: https://bakery.mystore.com/products
    │
    ├── Central domains check
    │   └── Not a central domain → Tenant domain lookup
    │
    ├── Tenant resolved via domains.tenant_id
    │   └── Tenant: mystore (dedicated DB)
    │
    ├── Store resolved via domains.store_id
    │   └── Store: Bakery Branch
    │
    ├── InitializeTenancyByUser middleware
    ├── InitializeStoreContext middleware (from domain)
    └── Route processed within tenant + store context
```

---

## 9. Changes to Existing Modules

### 9.1 TenantTemplate Interface

Add `storeDefaults()` to all templates:

```php
interface TenantTemplate
{
    // ... existing methods ...

    public function storeDefaults(): array;
}

// Example implementation (BakeryTemplate):
public function storeDefaults(): array
{
    return [
        'pos_layout' => 'grid',
        'tender_types' => ['cash', 'card', 'mobile_banking'],
        'has_weight_scale' => true,
        'supports_fractional_quantity' => true,
        'timezone' => 'Asia/Dhaka',
        'currency' => 'BDT',
        'default_order_status' => 'pending',
        'receipt_footer' => null,
    ];
}
```

### 9.2 IndustryPack Interface (Optional)

Packs can define store-level feature flags:

```php
interface IndustryPack
{
    // ... existing methods ...

    public function storeConfig(): array;  // NEW — store-level defaults per industry
}
```

### 9.3 ProductController

All product queries must account for store context:

```php
class ProductController
{
    public function __construct(
        protected ProductService $productService,
        protected StoreContextManager $storeContext,
    ) {}

    public function index(): Response
    {
        $storeId = $this->storeContext->id();

        $products = Product::whereHas('stores', fn ($q) =>
            $q->where('store_product.store_id', $storeId)
              ->where('store_product.is_visible', true)
        )->with(['stores' => fn ($q) =>
            $q->where('store_id', $storeId)
        ])->paginate();

        return Inertia::render('Product/Index', [...]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->productService->createProduct($request->validated());

        // Link product to current store
        DB::table('store_product')->insert([
            'store_id' => $this->storeContext->id(),
            'product_id' => $product->id,
            'price' => $request->input('price'),
            'is_visible' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('products.index');
    }
}
```

### 9.4 OrderController

Orders automatically store which store they belong to:

```php
class OrderController
{
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = Order::create([
            'tenant_id' => tenant('id'),
            'store_id' => $this->storeContext->id(),  // auto-populated
            'customer_id' => $request->customer_id,
            // ...
        ]);

        return redirect()->route('orders.show', $order);
    }
}
```

### 9.5 HandleInertiaRequests

Share current store to frontend:

```php
class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        $storeContext = app(StoreContextManager::class);

        return [
            ...parent::share($request),
            'current_store' => fn () => $storeContext->initialized()
                ? [
                    'id' => $storeContext->id(),
                    'name' => $storeContext->current()->name,
                    'slug' => $storeContext->current()->slug,
                    'code' => $storeContext->current()->code,
                    'timezone' => $storeContext->current()->timezone,
                    'currency' => $storeContext->current()->currency,
                    'status' => $storeContext->current()->status,
                    'is_default' => $storeContext->current()->is_default,
                ]
                : null,
            'stores' => fn () => $request->user()?->tenant
                ? Store::whereTenantId(tenant('id'))
                    ->orderBy('sort_order')
                    ->get(['id', 'name', 'slug', 'code', 'status', 'is_default'])
                : [],
        ];
    }
}
```

### 9.6 Product Model

Add `stores()` relationship:

```php
class Product extends Model
{
    // ... existing code ...

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_product')
            ->withPivot(['price', 'compare_at_price', 'is_visible', 'is_featured', 'status', 'sort_order'])
            ->withTimestamps();
    }

    public function scopeForStore(Builder $query, string $storeId): void
    {
        $query->whereHas('stores', fn ($q) =>
            $q->where('store_product.store_id', $storeId)
        );
    }

    public function scopeVisibleInStore(Builder $query, string $storeId): void
    {
        $query->whereHas('stores', fn ($q) =>
            $q->where('store_product.store_id', $storeId)
              ->where('store_product.is_visible', true)
        );
    }
}
```

---

## 10. Frontend Architecture

### Store Switcher Component

```tsx
// resources/js/components/store-switcher.tsx
import { router, usePage } from '@inertiajs/react';
import { ChevronDown, Store } from 'lucide-react';

interface Store {
    id: string;
    name: string;
    slug: string;
    code: string;
    status: string;
    is_default: boolean;
}

export function StoreSwitcher() {
    const { current_store, stores } = usePage().props;

    if (!stores || stores.length <= 1) return null;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="flex items-center gap-2">
                    <Store className="h-4 w-4" />
                    <span>{current_store?.name}</span>
                    <ChevronDown className="h-3 w-3" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start">
                {stores.map((store: Store) => (
                    <DropdownMenuItem
                        key={store.id}
                        onClick={() => router.post(
                            route('stores.switch', { store: store.id })
                        )}
                        disabled={store.id === current_store?.id}
                    >
                        <div className="flex items-center justify-between w-full">
                            <span>{store.name}</span>
                            <span className="text-xs text-muted-foreground">
                                {store.code}
                            </span>
                        </div>
                    </DropdownMenuItem>
                ))}
                <DropdownMenuSeparator />
                <DropdownMenuItem onClick={() => router.get(route('stores.create'))}>
                    + Add Store
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
```

### Store-Aware Data Fetching

```typescript
// resources/js/hooks/use-store-context.ts
import { usePage } from '@inertiajs/react';

export function useStoreContext() {
    const { current_store, stores } = usePage().props;

    return {
        store: current_store as Store | null,
        stores: stores as Store[],
        isMultiStore: (stores as Store[])?.length > 1,
    };
}

// Usage in product listing
const { store } = useStoreContext();
const { data: products } = useProducts({ store_id: store?.id });
```

### Store-Specific UI States

- If tenant has only 1 store → no store switcher shown (backward compatible)
- If multi-store enabled → store switcher appears in the header
- Store switching updates Inertia's `location.visit()` or `router.post()` to the switch endpoint

---

## 11. Implementation Phases

### Phase 1: Foundation

| Task | Files |
|---|---|
| Create `Store` model | `app/Modules/Store/Models/Store.php` |
| Create `StoreStatusEnum` | `app/Modules/Store/Enums/StoreStatusEnum.php` |
| Create `stores` migration (tenant DB) | `app/Modules/Store/Database/Migrations/Tenant/...` |
| Create `StoreContextManager` singleton | `app/Modules/Store/Services/StoreContextManager.php` |
| Create `InitializeStoreContext` middleware | `app/Modules/Store/Http/Middleware/InitializeStoreContext.php` |
| Register middleware in `bootstrap/app.php` | `bootstrap/app.php` |
| Create `StoreServiceProvider` | `app/Modules/Store/Providers/StoreServiceProvider.php` |
| Container bindings in `StoreServiceProvider` | `StoreContextManager` as singleton |

### Phase 2: Store CRUD

| Task | Files |
|---|---|
| `StoreController` | `app/Modules/Store/Http/Controllers/StoreController.php` |
| `StoreStoreRequest` | `app/Modules/Store/Http/Requests/StoreStoreRequest.php` |
| `StorePolicy` | `app/Modules/Store/Policies/StorePolicy.php` |
| `StoreService` | `app/Modules/Store/Services/StoreService.php` |
| Store Inertia pages | `resources/js/pages/stores/index.tsx`, `create.tsx`, `settings.tsx` |

### Phase 3: Product Scoping

| Task | Files |
|---|---|
| `store_product` migration | `app/Modules/Product/Database/Migrations/Tenant/...` |
| Update `Product` model | Add `stores()` BelongsToMany |
| Update `ProductController` | Scope queries to current store |
| Update `ProductService` | Link product to store on creation |
| Update frontend product pages | Pass store context |

### Phase 4: Customer Scoping

| Task | Files |
|---|---|
| `store_customer` migration | Tenant migration |
| Update `Customer` model | Add `stores()` BelongsToMany |
| Update `CustomerController` | Scope queries to current store |
| Update frontend CRM pages | Pass store context |

### Phase 5: Order Scoping

| Task | Files |
|---|---|
| `add_store_id_to_orders` migration | Tenant migration |
| Update `Order` model | Add `store_id` |
| Update `OrderController` | Auto-set store_id on create |
| Update frontend order pages | Filter by store |

### Phase 6: Warehouse Linking

| Task | Files |
|---|---|
| `store_warehouse` migration | Tenant migration |
| Update `Warehouse` model | Add `stores()` BelongsToMany |
| Update `StockController` | Filter available stock by store |
| Update stock transfer logic | Account for store-warehouse mapping |

### Phase 7: Domain Scoping

| Task | Files |
|---|---|
| `add_store_id_to_domains` migration | Central DB |
| Update domain resolution in middleware | Check `domains.store_id` |
| Update frontend settings UI | Per-store domain management |

### Phase 8: Billing

| Task | Files |
|---|---|
| `billing_store_allocations` migration | Central DB |
| `add_store_pricing_to_billing_plans` migration | Central DB |
| `StoreAllocation` model | `app/Modules/Billing/Models/StoreAllocation.php` |
| `StoreBillingService` | `app/Modules/Billing/Services/StoreBillingService.php` |
| Update `SubscriptionService` | Include store amount in billing |
| `EnsureStoreLimit` middleware | `app/Modules/Billing/Http/Middleware/EnsureStoreLimit.php` |

### Phase 9: Onboarding

| Task | Files |
|---|---|
| `CreateDefaultStoreStep` | `app/Modules/Onboarding/Services/CreateDefaultStoreStep.php` |
| Register step in `ProvisioningPipeline` | Update constructor array |
| Update `TenantTemplate` interface | Add `storeDefaults()` |
| Implement `storeDefaults()` on all 16 templates | All template files |

### Phase 10: Migration Script

| Task | Files |
|---|---|
| `stores:migrate-existing` Artisan command | `app/Modules/Store/Console/Commands/MigrateExistingStores.php` |

### Phase 11: Frontend Polish

| Task | Files |
|---|---|
| `store-switcher.tsx` component | `resources/js/components/store-switcher.tsx` |
| `use-store-context.ts` hook | `resources/js/hooks/use-store-context.ts` |
| Update `HandleInertiaRequests` | Share `current_store` + `stores` |
| Update module nav items | Highlight current store |

---

## 12. Key Risks & Mitigations

| Risk | Mitigation |
|---|---|
| **Existing products not linked to stores** | Migration script (`stores:migrate-existing`) links all products to the default store |
| **Query performance with store-product joins** | Indexes on `store_product.store_id`, `store_product.product_id`, `store_product.is_visible` |
| **User confusion with store context** | Clear store indicator in header; store name visible on all pages |
| **Store switching loses state** | Session-based store ID; redirect to dashboard on switch |
| **Billing proration edge cases** | Follow same pattern as seat billing; unit-test all proration scenarios |
| **Inventory complexity** | Warehouses remain tenant-level; stores are assigned to warehouses via pivot |
| **Domain resolution conflicts** | Domains are unique; store-level domains checked after tenant-level |
| **Free plan tenants with stores** | Feature-gated; free plan does not include `multi_store` feature |
| **Test coverage** | Follow existing `HasTenantScope` test-safe patterns; reset booted state in `setUp()` |

---

## 13. Store Model Reference

```php
// App\Modules\Store\Models\Store

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'code', 'email', 'phone',
        'address_line_1', 'address_line_2', 'city', 'state',
        'postal_code', 'country',
        'timezone', 'currency', 'locale',
        'status', 'is_default',
        'business_hours', 'config', 'pos_settings',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'business_hours' => 'array',
            'config' => 'array',
            'pos_settings' => 'array',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'store_product')
            ->withPivot(['price', 'compare_at_price', 'is_visible', 'is_featured', 'status', 'sort_order'])
            ->withTimestamps();
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'store_customer')
            ->withPivot(['loyalty_number', 'loyalty_points', 'total_visits', 'total_spent', 'last_visit_at', 'tags', 'notes'])
            ->withTimestamps();
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'store_warehouse')
            ->withPivot(['is_default_for_receiving', 'is_default_for_fulfillment'])
            ->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'store_id');
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class, 'store_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function scopeDefault(Builder $query): void
    {
        $query->where('is_default', true);
    }
}
```

---

## 14. Key Container Bindings

| Abstract | Concrete | Type |
|---|---|---|
| `StoreContextManager` | `StoreContextManager` | singleton |
| `StoreService` | `StoreService` | singleton |
| `StoreBillingService` | `StoreBillingService` | singleton |

---

## 15. Service Provider Registration

```php
// app/Modules/Store/Providers/StoreServiceProvider.php

class StoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StoreContextManager::class);
        $this->app->singleton(StoreService::class);
        $this->app->singleton(StoreBillingService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations/Tenant');
    }
}
```

Registered in `bootstrap/providers.php` after `TenancyServiceProvider`:

```php
App\Providers\AppServiceProvider::class,
App\Providers\FortifyServiceProvider::class,
App\Providers\ProductServiceProvider::class,
App\Providers\TenancyServiceProvider::class,
App\Providers\BillingServiceProvider::class,
App\Providers\IndustryServiceProvider::class,
App\Providers\OnboardingServiceProvider::class,
App\Modules\Store\Providers\StoreServiceProvider::class,  // NEW
```
