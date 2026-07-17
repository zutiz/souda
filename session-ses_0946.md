# New session - 2026-07-16T15:36:50.763Z

**Session ID:** ses_0946d32b4ffeE37bWsEDL6ISgU
**Created:** 7/16/2026, 9:36:50 PM
**Updated:** 7/16/2026, 9:51:41 PM

---

## User

first read the docs\agents.md file deeply... then also read other md files from .cursor, agents and docs folder to be informed current updates of the project. 
for now just be informed about our architectural plan.
the next major differentiator for Souda should not be "Inventory Management"—it should be "Automated Inventory Management."

Vision

Instead of asking users to update stock manually, Souda should behave like an intelligent inventory assistant.

The user should only perform business operations (purchase, sale, return, production, transfer), and the inventory should update automatically.

Phase 1 — Core Inventory Engine (Highest Priority)

This becomes the heart of every industry.

Inventory Ledger
Stock Movement (immutable ledger)
Inventory Balance
Cost Layer (FIFO/LIFO/Weighted Average)
Reservation
Allocation
Batch/Lot
Serial Number
Expiry
Warehouse
Bin/Shelf
Audit Trail

No stock should ever be edited directly.

Everything comes from Stock Movements.

Phase 2 — Inventory Automation Engine

Instead of:

Product → Qty

Use

Business Event
      ↓
Inventory Engine
      ↓
Stock Movement
      ↓
Inventory Balance
      ↓
Notifications
      ↓
Reports

Example events:

Purchase Approved
POS Sale
Online Order
Return
Damage
Adjustment
Transfer
Manufacturing
Recipe Consumption
Bundle Sale
Stock Count

Inventory updates automatically.

Phase 3 — Smart Inventory Features

Every business loves automation.

Auto Reorder
Current Stock
↓
Below Reorder Level
↓
Suggest Purchase
↓
Create Draft PO
Smart Purchase Suggestions

Instead of:

"Stock is low"

Show:

You sold 84 units this month.
Average daily sales: 2.8
Current stock lasts 6 days.
Recommended purchase: 120 units.

Dead Stock Detection

Automatically detect:

no sales in 90 days
overstock
aging inventory
seasonal products
Slow/Fast Moving Products

Rank inventory:

A (Fast)
B
C
Dead Stock
Seasonal Demand Prediction

Based on historical sales.

Example:

Ramadan
Eid
Winter
School Opening

Recommend stocking before demand spikes.

Phase 4 — Intelligent Alerts

Instead of generic notifications.

Examples:

Low stock
Negative stock attempt
Expiring items
Overstock
Dead stock
Slow moving
Margin dropped
High return rate
Suspicious adjustments
Phase 5 — Automation Rules

Allow users to create rules without coding.

Examples:

IF
Stock < Reorder Level

THEN

Create Purchase Draft
Notify Manager
Notify Supplier
Phase 6 — Industry-Specific Automation

Your Industry Pack architecture is perfect for this.

Each pack contributes automation rules instead of changing core inventory.

Examples:

Pharmacy
Batch tracking
Expiry alerts
FEFO
Recall handling
Restaurant
Recipe deduction
Ingredient consumption
Waste tracking
Grocery
Weight-based inventory
Freshness alerts
Electronics
IMEI/Serial tracking
Warranty stock
Fashion
Size/color variants
Seasonal inventory

No core module changes required.

Phase 7 — Inventory Dashboard

A manager should understand inventory in 10 seconds.

Include:

Inventory value
Today's movements
Fast movers
Slow movers
Dead stock
Reorder list
Overstock
Stock health score
Expiring products
Warehouse utilization
Purchase recommendations
Phase 8 — AI Assistant (Future)

Examples:

"What should I buy this week?"

"Why did inventory value drop?"

"Which products will run out next week?"

"Which supplier is causing delays?"

Recommended Module Structure
Inventory/
├── InventoryEngine
├── InventoryLedger
├── StockMovementEngine
├── ReservationEngine
├── AllocationEngine
├── CostingEngine
├── ForecastEngine
├── ReorderEngine
├── AlertEngine
├── AutomationEngine
├── AuditEngine
├── ReportingEngine
└── AIRecommendationEngine

Each engine should have:

Contracts
Services
Events
Listeners
Policies
Actions
DTOs
Value Objects
Development Roadmap (Priority Order)
Inventory Ledger (single source of truth)
Stock Movement Engine
Automatic Inventory Updates from Business Events
Costing Engine (Weighted Average first, FIFO later)
Reservation & Allocation
Reorder Engine
Alert Engine
Forecast & Purchase Suggestions
Automation Rules Engine
AI Inventory Assistant
One Architectural Principle

Never let any module (POS, Purchase, Sales, Manufacturing, Returns, etc.) update stock directly.

Every module should publish a domain event (e.g., SaleCompleted, PurchaseReceived, StockTransferred), and only the Inventory Engine should translate those events into stock movements and inventory balances. This keeps the system consistent, auditable, and extensible as Souda grows into an enterprise-grade multi-industry platform. 

---

## Assistant (Plan · DeepSeek V4 Flash Free · 8.3s)

_Thinking:_

The user wants me to read the docs/agents.md file and other markdown files from .cursor, agents, and docs folders to understand the current project state. They've also shared their architectural vision for automated inventory management. Let me start by reading the relevant files to understand the project structure and current state.

I'm in plan/read-only mode, so I can only read files and search. Let me start by reading the key files mentioned.

**Tool: read**

**Input:**
```json
{
  "filePath": "C:\\dev\\souda\\docs\\agents.md"
}
```

**Output:**
```
<path>C:\dev\souda\docs\agents.md</path>
<type>file</type>
<content>
1: # SOUDA — AI Agent Guide
2: 
3: This file is for AI coding agents. It defines exact conventions, patterns, guardrails, and templates for working on the entire SOUDA platform.
4: 
5: ---
6: 
7: ## Project Identity
8: 
9: - **App**: SOUDA — multi-tenant, multi-vertical business management platform
10: - **PHP**: 8.4.16
11: - **Laravel**: 12
12: - **React**: 19 + Inertia.js v2 + Tailwind CSS v4
13: - **Tenancy**: hybrid (shared `souda_shared` + per-tenant dedicated DBs), using stancl/tenancy v3
14: - **Auth**: Laravel Fortify v1 (headless) with Inertia React views
15: - **Packs**: 15 industry packs in `IndustryPack` interface
16: - **Templates**: 16 `TenantTemplate` implementations per business type
17: - **Config caching**: 24h TTL in Laravel cache + `tenant_configs` table
18: - **Database**: MySQL only (central + shared + dedicated per Enterprise)
19: 
20: ---
21: 
22: ## Global Agent Guardrails (MUST FOLLOW)
23: 
24: ### NEVER
25: - Add hardcoded business type checks (`if ($slug === 'pharmacy')`)
26: - Use `switch`/`case` on industry slugs
27: - Create per-industry tables or columns
28: - Use EAV patterns
29: - Modify core module code when adding a new industry
30: - Use `DB::` when an Eloquent relationship exists
31: - Add inline validation — use Form Requests
32: - Store per-industry logic in controllers, services, or views
33: - Add comments in code (use PHPDoc blocks for complex logic instead)
34: - Allow empty `__construct()` with zero params (unless private)
35: - Use `env()` outside config files — use `config()` always
36: - Commit secrets, .env files, or credentials
37: 
38: ### ALWAYS
39: - Implement `IndustryPack` interface for new industries
40: - Create `TenantTemplate` for new business types
41: - Register packs in `IndustryServiceProvider::boot()`
42: - Register templates in `OnboardingServiceProvider::boot()`
43: - Seed central DB when adding a new business type (BusinessTypeSeeder + BusinessTypeModuleSeeder)
44: - Run Pint after modifying PHP files: `vendor/bin/pint --format agent`
45: - Use `php artisan make:` commands (with `--no-interaction` + correct options)
46: - Use proper return type hints on all methods
47: - Use constructor property promotion
48: - Use curly braces for control structures (even single-line)
49: - Use Form Request classes for validation
50: - Prefer Eloquent relationships over raw queries
51: - Use PHP 8 typed properties throughout
52: - Add `->orderBy('id')` before `->each()` (Laravel 12 requirement)
53: 
54: ---
55: 
56: ## Architecture Overview
57: 
58: ```
59: ┌──────────────────────────────────────────────────────────────────────┐
60: │                         HTTP Request                                 │
61: └──────────────────────────────────────────────────────────────────────┘
62:                                │
63:                       ┌────────▼────────┐
64:                       │  Middleware:     │
65:                       │  web, auth,      │
66:                       │  tenancy, sub    │
67:                       └────────┬────────┘
68:                                │
69:                ┌───────────────┼───────────────┐
70:                │               │               │
71:                ▼               ▼               ▼
72:       ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
73:       │  Central DB  │ │  Shared DB   │ │  Dedicated   │
74:       │   (souda)    │ │(souda_shared)│ │  Tenant DBs  │
75:       │  users       │ │  tenant_     │ │  products    │
76:       │  tenants     │ │  settings    │ │  inventory   │
77:       │  billing     │ │  tasks       │ │  orders      │
78:       │  business_   │ │  tenant_     │ │  categories  │
79:       │  types       │ │  configs     │ │  ...         │
80:       └──────────────┘ └──────────────┘ └──────────────┘
81: ```
82: 
83: ### Database Connections
84: 
85: | Name | Database (prod) | Database (test) | Purpose | Models use |
86: |---|---|---|---|---|
87: | `central` | `souda` | `souda_testing` | Users, tenants, billing, business types, roles | `CentralConnection` trait |
88: | `shared` | `souda_shared` | `souda_shared` | Shared-mode tenant data (stores, settings, tasks, configs) | `HasTenantScope` trait |
89: | `mysql` | `souda` | `souda_testing` | Default / template for dedicated tenant DBs | Normal Eloquent |
90: 
91: In tests, `central` and `mysql` both point to `souda_testing`, so test processes must run sequentially — parallel migrations collide.
92: 
93: ### Tenancy Modes
94: 
95: | Mode | Storage | Isolation | Plans |
96: |---|---|---|---|
97: | `shared` | `souda_shared` table | `tenant_id` column | Free, Starter, Professional |
98: | `dedicated` | `souda_tenant_{uuid}` DB | Separate database | Enterprise |
99: 
100: ---
101: 
102: ## Authentication Patterns
103: 
104: ### Fortify Setup
105: 
106: Fortify is headless (no Jetstream). Custom Inertia views in `resources/js/pages/auth/`.
107: 
108: **Registration Includes Business Type:**
109: ```php
110: // Register view passes business types for dropdown
111: Fortify::registerView(fn () => Inertia::render('auth/register', [
112:     'businessTypes' => BusinessType::query()
113:         ->where('is_active', true)
114:         ->orderBy('name')
115:         ->get(['id', 'slug', 'name', 'description', 'icon']),
116: ]));
117: ```
118: 
119: **CreateNewUser** (`app/Actions/Fortify/CreateNewUser.php`):
120: 1. Validates `name`, `email`, `password`, optional `business_type_slug` (validated against `business_types` table)
121: 2. Creates Tenant → User → updates Tenant owner
122: 3. Stores `business_type_slug` in session: `session()->put('onboarding.business_type', $slug)`
123: 4. Redirects to `/onboarding` via RegisterResponse
124: 
125: **RegisterResponse** (`app/Http/Responses/RegisterResponse.php`):
126: ```php
127: return redirect()->intended('/onboarding');
128: ```
129: 
130: ### Social Auth
131: 
132: Providers: Google, GitHub. Guarded by `AppSetting::getBoolean('social_auth_enabled')`.
133: Routes in `routes/web.php`: `/auth/{provider}/redirect`, `/auth/{provider}/callback`.
134: 
135: ### Rate Limiting
136: 
137: Login: 6/min per email+IP. Two-factor challenge: 5/min.
138: 
139: ---
140: 
141: ## Tenant System Patterns
142: 
143: ### TenantManager (`app/Tenancy/TenantManager.php`)
144: 
145: Singleton central orchestrator. Key method: `initialize(Tenant $tenant)` resolves mode strategy.
146: 
147: ### Mode Strategies
148: 
149: **SharedMode** — sets `config('database.default')` to `'shared'`, does NOT call `tenancy()->initialize()`.
150: **DedicatedMode** — calls `tenancy()->initialize($tenant)` (stancl native multi-DB).
151: 
152: ### HasTenantScope Trait
153: 
154: Used by all shared-mode models (in `app/Tenancy/Models/Concerns/`). Uses `app()` helper with try-catch:
155: 
156: ```php
157: public static function bootHasTenantScope(): void
158: {
159:     try {
160:         static::addGlobalScope(app(TenantScope::class));
161:     } catch (\Throwable) {
162:         // No app context (unit tests)
163:     }
164:     static::creating(function ($model) {
165:         try {
166:             $manager = app(TenantManager::class);
167:             if ($manager->initialized() && $manager->isShared() && ! $model->tenant_id) {
168:                 $model->tenant_id = $manager->id();
169:             }
170:         } catch (\Throwable) {
171:             // No app context
172:         }
173:     });
174: }
175: ```
176: 
177: ### TenantScope (`app/Tenancy/Scopes/TenantScope.php`)
178: 
179: Adds `WHERE tenant_id = ?` in shared mode. Provides `withoutTenancy()` macro.
180: 
181: ### InitializeTenancyByUser Middleware
182: 
183: Resolves tenant via 3-tier fallback: (1) session `current_tenant_id` → check access, (2) `$user->tenants()` pivot query with try-catch, (3) `$user->tenant` legacy BelongsTo. Access check order: direct `$user->tenant_id` match first (no pivot query), then `tenants()` pivot. Runs before `SubstituteBindings`. Applied to all tenant routes and settings routes.
184: 
185: ---
186: 
187: ## Product Module Patterns
188: 
189: ### Models (17)
190: 
191: All in `app/Modules/Product/Models/`. Most have `HasTenantScope`, `CentralConnection`, or normal Eloquent.
192: 
193: **Product** — ULID PK, `ProductTypeEnum`, `ProductStatusEnum`, `Searchable` (Scout/Algolia), slugs, media.
194: **Variant** — ULID, belongsTo Product, own SKU/barcode/price/stock.
195: **Category** — hierarchical via `HasMaterializedPath`, BelongsToMany via `category_product` pivot.
196: **WarehouseStock** — HasOptimisticLocking (`lock_version`), tracks qty + reserved_qty.
197: **StockReservation** — statuses: active/consumed/expired/cancelled.
198: 
199: ### Controllers
200: 
201: ProductController (CRUD + archive/restore/publish/duplicate), CategoryController, BrandController, AttributeController, StockController.
202: 
203: ### Services (20)
204: 
205: ProductService, CategoryService, BrandService, VariantService, AttributeService, MediaService, WarehouseService, StockService, StockLockService, StockAuditService, StockReservationService, TaxService, PricingRuleService, ProductImportService + contract implementations.
206: 
207: ### Events (14)
208: 
209: ProductCreated/Updated/Deleted/Archived/Published, VariantCreated/Updated/Deleted, StockUpdated/Depleted, LowStockAlert, StockReservationCreated/Expired, StockTransferCompleted.
210: 
211: ### Observers
212: 
213: ProductObserver, VariantObserver, WarehouseStockObserver, StockReservationObserver.
214: 
215: ### Policies
216: 
217: ProductPolicy, CategoryPolicy, BrandPolicy, WarehousePolicy.
218: 
219: ---
220: 
221: ## Onboarding System Patterns
222: 
223: ### Flow
224: 
225: ```
226: Registration → /onboarding (business type picker)
227:   → POST /onboarding/select-type (stores in session)
228:   → GET /onboarding/provision (shows progress UI)
229:   → POST /onboarding/run (runs 10-step pipeline)
230:   → redirect /dashboard
231: ```
232: 
233: ### Routes (`routes/onboarding.php`)
234: 
235: ```
236: GET    /onboarding                    -> OnboardingController@start
237: POST   /onboarding/select-type        -> OnboardingController@selectType
238: GET    /onboarding/provision          -> OnboardingController@provision
239: POST   /onboarding/run                -> OnboardingController@run
240: GET    /onboarding/{tenant}/progress  -> OnboardingController@progress
241: GET    /api/business-types             -> BusinessTypeController@index
242: ```
243: 
244: ### TenantTemplate Contract (`app/Modules/Onboarding/Contracts/TenantTemplate.php`)
245: 
246: ```php
247: interface TenantTemplate
248: {
249:     public function businessType(): string;
250:     public function defaultCategories(): array;
251:     public function productSchema(): array;     // fields, sections, search_columns, list_columns
252:     public function dashboardLayout(): array;   // widgets with width/order
253:     public function posDefaults(): array;       // layout, tender_types, specialty settings
254:     public function defaultTeam(): array;       // roles array
255:     public function notificationDefaults(): array;
256:     public function initialData(): array;
257: }
258: ```
259: 
260: All 16 templates in `app/Modules/Onboarding/Templates/`, registered in `OnboardingServiceProvider::boot()`.
261: 
262: ### ProvisioningContext (`app/Modules/Onboarding/Data/ProvisioningContext.php`)
263: 
264: ```php
265: public function __construct(
266:     public Tenant $tenant,
267:     public string $businessTypeSlug,  // required — used to look up TenantTemplate
268:     public array $planData = [],
269: ) {}
270: ```
271: 
272: Class is NOT readonly (was changed from `readonly class` for PHP 8.4 compatibility). Has `setCurrentStep()` method.
273: 
274: ### ProvisioningStep Contract (`app/Modules/Onboarding/Contracts/ProvisioningStep.php`)
275: 
276: ```php
277: interface ProvisioningStep
278: {
279:     public function handle(ProvisioningContext $context): void;
280:     public function rollback(ProvisioningContext $context): void;
281:     public function label(): string;
282: }
283: ```
284: 
285: ### 11 Pipeline Steps (in `app/Modules/Onboarding/Services/`)
286: 
287: | Step | Label | What It Does |
288: |---|---|---|
289: | `CreateTenantStep` | Initializing workspace | Sets onboarding_status to 'provisioning' |
290: | `AssignBusinessTypeStep` | Assigning business type | Sets business_type_id on tenant |
291: | `ProvisionModulesStep` | Enabling modules | Enables modules per business type |
292: | `CreatePermissionsStep` | Creating roles | Creates role-permission mappings |
293: | `SeedDefaultDataStep` | Seeding data | Seeds initial categories, defaults |
294: | `ConfigureProductSchemaStep` | Configuring product fields | Sets product schema fields |
295: | `ConfigureDashboardStep` | Setting up dashboard | Adds industry-specific widget layout |
296: | `ConfigurePOSStep` | Configuring POS | Sets POS defaults per industry |
297: | `CreateDefaultTeamStep` | Setting up team | Creates default team roles |
298: | `CreateDefaultStoreStep` | Creating default store | Creates first store from `TenantTemplate::defaultStores()` via `StoreService::createStore()` |
299: | `BuildTenantConfigStep` | Building config | Builds + caches TenantConfig |
300: 
301: ### Onboarding Events (in `app/Modules/Onboarding/Events/`)
302: 
303: - `OnboardingStarted` — pipeline begins
304: - `OnboardingStepCompleted` — each step completes
305: - `OnboardingCompleted` — all 10 steps succeed, tenant `onboarding_status = 'completed'`
306: - `OnboardingFailed` — step throws, all completed steps rolled back in reverse
307: 
308: ### ProvisioningPipeline (`app/Modules/Onboarding/Services/ProvisioningPipeline.php`)
309: 
310: ```php
311: $pipeline->run($tenant, $businessTypeSlug, $planData = []);
312: // Or resume from a failed step:
313: $pipeline->resumeFrom($tenant, $stepClass, $planData = []);
314: ```
315: 
316: ---
317: 
318: ## Business Type Engine Patterns
319: 
320: ### IndustryPack Contract (`app/Modules/BusinessType/Contracts/IndustryPack.php`)
321: 
322: ```php
323: interface IndustryPack
324: {
325:     public function slug(): string;
326:     public function name(): string;
327:     public function description(): string;
328:     public function modules(): array;           // ['module_slug' => ['required' => bool]]
329:     public function menus(): array;             // navigation structure
330:     public function permissions(): array;       // role => permissions map
331:     public function posConfig(): array;         // layout, tender_types, features
332:     public function dashboardWidgets(): array;  // widgets with component/title/width
333:     public function reportDefinitions(): array; // reports with permission/filters
334:     public function defaultSettings(): array;   // currency, default markup, etc.
335:     public function featureFlags(): array;      // key => bool
336:     public function onTenantAssigned(Tenant $tenant): void;
337:     public function onTenantRemoved(Tenant $tenant): void;
338: }
339: ```
340: 
341: ### Config Resolution Chain
342: 
343: ```
344: IndustryPack defaults
345:   → BusinessTypeConfigBuilder::build()
346:     → applyPlanGating() (check plan features/limits)
347:     → applyTenantOverrides() (tenant_configs table overrides)
348:   → cached 24h in Laravel cache + tenant_configs table
349: ```
350: 
351: ### TenantConfig API
352: 
353: ```php
354: $config->businessType;        // string slug
355: $config->enabledModules;      // string[]
356: $config->menus;               // array
357: $config->permissions;         // array
358: $config->fieldDefinitions;    // array
359: $config->dashboardWidgets;    // array
360: $config->posConfig;           // array
361: $config->reportDefinitions;   // array
362: $config->settings;            // array (merged)
363: 
364: // Helpers
365: $config->hasModule('kitchen');         // bool
366: $config->hasFeature('batch_tracking'); // bool
367: ```
368: 
369: ---
370: 
371: ## Billing System Patterns
372: 
373: ### Subscription Status Lifecycle
374: 
375: ```
376: PendingPayment → (payment received) → Active
377: Trial          → (auto on create)  → Active
378: Active         → (cancelled)       → Grace
379: Grace          → (expired)         → Expired
380:                                      Cancelled (immediate)
381: ```
382: 
383: ### SubscriptionService::createSubscription() Logic
384: 
385: ```php
386: // 1. Calculate amount from plan price
387: $amount = $plan->monthly_price;
388: 
389: // 2. Check trial availability
390: $trialAvailable = $plan->trial_enabled && $tenant && !$tenant->trial_used;
391: 
392: // 3. Determine path:
393: if ($trialAvailable && $plan->trial_without_card) {
394:     // → Immediate activation (Trial→Active)
395: }
396: if ($amount === 0) {
397:     // → Immediate activation for Free plan
398: }
399: // Otherwise → Payment gateway flow
400: ```
401: 
402: ### Payment Gateways
403: 
404: All implement `BillingGatewayInterface`. Drivers: sslcommerz (✅), stripe (❌ stub), bkash (❌ stub), nagad (❌ stub), portwallet (❌ stub), manual (✅).
405: 
406: ---
407: 
408: ## Frontend Integration Patterns
409: 
410: ### Inertia Shared Data (`HandleInertiaRequests`)
411: 
412: ```php
413: 'currentTenant' => fn () => resolveCurrentTenant(),  // shared with logo, business_type_id
414: 'tenants'       => fn () => $user->tenants,           // for tenant switcher
415: 'currentStore'  => fn () => resolveCurrentStore(),    // shared with all store fields
416: 'stores'        => fn () => $user->stores,            // for store switcher
417: 'businessTypes' => fn () => BusinessType::all(),     // lazy prop for dropdowns
418: 'tenant_config' => fn () => [
419:     'business_type' => $config->businessType,
420:     'modules'       => $config->enabledModules,
421: ],
422: ```
423: 
424: `resolveCurrentTenant()` has 3-tier fallback: `TenantManager->initialized()` → `$user->tenants()` pivot (try-catch) → `$user->tenant` legacy BelongsTo.
425: 
426: ### Hooks (`resources/js/hooks/use-tenant-config.ts`)
427: 
428: ```typescript
429: const config = useTenantConfig();            // TenantConfig | null
430: const enabled = useModuleEnabled('kitchen');  // boolean
431: const modules = useEnabledModules();          // string[]
432: ```
433: 
434: ### Module Nav Map (`resources/js/components/module-nav-items.ts`)
435: 
436: 11 modules with icons, labels, sub-items. `buildModuleNavItems(enabledModules)` returns filtered NavItem[].
437: 
438: | Slug | Label | Routes |
439: |---|---|---|
440: | product | Products | /products, /products/categories, /products/brands, /products/attributes |
441: | inventory | Inventory | /products/inventory, /products/stock-transfers |
442: | order | Orders | /orders |
443: | pos | POS | /pos |
444: | crm | CRM | /crm/customers, /crm/segments |
445: | billing | Billing | /billing, /billing/invoices |
446: | team | Team | /team |
447: | supplier | Suppliers | /suppliers, /suppliers/purchase-orders |
448: | kitchen | Kitchen | /kitchen |
449: | appointment | Appointments | /appointments, /appointments/services |
450: | reporting | Reports | /reports |
451: 
452: ### Industry Widgets (`resources/js/modules/dashboard/components/industry-widgets.tsx`)
453: 
454: - `IndustryGreeting` — industry-specific welcome card with icon (16 industries)
455: - `ModuleQuickActions` — up to 6 action cards from enabled modules
456: 
457: ---
458: 
459: ## File Creation Patterns
460: 
461: ### New TenantTemplate
462: 
463: Create at `app/Modules/Onboarding/Templates/{Name}Template.php`:
464: 
465: ```php
466: <?php
467: 
468: declare(strict_types=1);
469: 
470: namespace App\Modules\Onboarding\Templates;
471: 
472: use App\Modules\Onboarding\Contracts\TenantTemplate;
473: 
474: class BakeryTemplate implements TenantTemplate
475: {
476:     public function businessType(): string { return 'bakery'; }
477: 
478:     public function defaultCategories(): array
479:     {
480:         return [
481:             ['name' => 'Breads', 'children' => [
482:                 ['name' => 'White Bread'],
483:                 ['name' => 'Whole Wheat'],
484:             ]],
485:             ['name' => 'Pastries', 'children' => [
486:                 ['name' => 'Croissants'],
487:                 ['name' => 'Muffins'],
488:             ]],
489:         ];
490:     }
491: 
492:     public function productSchema(): array
493:     {
494:         return [
495:             'fields' => [
496:                 ['slug' => 'weight_g', 'label' => 'Weight (g)', 'type' => 'decimal',
497:                  'required' => false, 'section' => 'details', 'order' => 1],
498:                 ['slug' => 'expiry_date', 'label' => 'Expiry Date', 'type' => 'date',
499:                  'required' => true, 'section' => 'production', 'order' => 2],
500:             ],
501:             'sections' => [
502:                 'details' => ['title' => 'Product Details', 'order' => 1],
503:                 'production' => ['title' => 'Production', 'order' => 2],
504:             ],
505:             'search_columns' => ['name', 'sku'],
506:             'list_columns' => ['name', 'price', 'stock', 'weight_g'],
507:         ];
508:     }
509: 
510:     public function dashboardLayout(): array
511:     {
512:         return [
513:             ['widget' => 'daily_production', 'width' => 'half', 'order' => 1],
514:             ['widget' => 'daily_revenue', 'width' => 'half', 'order' => 2],
515:         ];
516:     }
517: 
518:     public function posDefaults(): array
519:     {
520:         return [
521:             'layout' => 'grid',
522:             'has_weight_scale' => true,
523:             'supports_fractional_quantity' => true,
524:             'tender_types' => ['cash', 'card', 'mobile_banking'],
525:         ];
526:     }
527: 
528:     public function defaultTeam(): array
529:     {
530:         return ['roles' => ['admin', 'manager', 'baker', 'cashier']];
531:     }
532: 
533:     public function notificationDefaults(): array
534:     {
535:         return [
536:             'email_notifications' => true,
537:             'low_stock_alerts' => true,
538:             'expiry_alerts' => true,
539:         ];
540:     }
541: 
542:     public function initialData(): array { return []; }
543: }
544: ```
545: 
546: ### New Industry Pack
547: 
548: Create at `app/Modules/BusinessType/Packs/{Name}Pack.php`:
549: 
550: ```php
551: <?php
552: 
553: declare(strict_types=1);
554: 
555: namespace App\Modules\BusinessType\Packs;
556: 
557: use App\Models\Tenant;
558: use App\Modules\BusinessType\Contracts\IndustryPack;
559: 
560: class NewIndustryPack implements IndustryPack
561: {
562:     public function slug(): string { return 'new_industry'; }
563:     public function name(): string { return 'New Industry'; }
564:     public function description(): string { return 'Description'; }
565: 
566:     public function modules(): array
567:     {
568:         return [
569:             'product' => ['required' => true],
570:             'inventory' => ['required' => true],
571:             'order' => ['required' => true],
572:             'pos' => ['required' => true],
573:             'crm' => ['required' => true],
574:             'billing' => ['required' => true],
575:             'team' => ['required' => true],
576:             'reporting' => ['required' => true],
577:         ];
578:     }
579: 
580:     public function menus(): array
581:     {
582:         return [
583:             'main' => [
584:                 ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'LayoutDashboard'],
585:                 ['label' => 'Products', 'route' => 'products.index', 'icon' => 'Package'],
586:                 ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Package'],
587:                 ['label' => 'Sales', 'route' => 'pos.index', 'icon' => 'ShoppingCart'],
588:                 ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'Users'],
589:                 ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3'],
590:                 ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'Settings'],
591:             ],
592:         ];
593:     }
594: 
595:     public function permissions(): array
596:     {
597:         return [
598:             'admin' => ['products.*', 'inventory.*', 'pos.*', 'orders.*', 'customers.*', 'reports.*', 'settings.*'],
599:             'manager' => ['products.*', 'inventory.view', 'pos.*', 'orders.*', 'customers.view', 'reports.view'],
600:             'staff' => ['pos.create', 'orders.create', 'customers.create', 'inventory.view'],
601:         ];
602:     }
603: 
604:     public function posConfig(): array
605:     {
606:         return [
607:             'layout' => 'grid',
608:             'product_search_columns' => ['name', 'sku', 'barcode'],
609:             'quick_actions' => [],
610:             'checkout_fields' => [],
611:             'batch_picking' => false,
612:             'show_expiry_warning' => false,
613:             'tender_types' => ['cash', 'card', 'mobile_banking'],
614:         ];
615:     }
616: 
617:     public function dashboardWidgets(): array
618:     {
619:         return [
620:             'today_sales' => [
621:                 'component' => 'TodaySalesSummary',
622:                 'title' => "Today's Sales",
623:                 'width' => 'half',
624:                 'permission' => 'pos.view',
625:             ],
626:             'recent_orders' => [
627:                 'component' => 'RecentOrdersList',
628:                 'title' => 'Recent Orders',
629:                 'width' => 'half',
630:                 'permission' => 'orders.view',
631:             ],
632:         ];
633:     }
634: 
635:     public function reportDefinitions(): array
636:     {
637:         return [
638:             'sales-summary' => [
639:                 'name' => 'Sales Summary',
640:                 'description' => 'Sales breakdown by product and time period',
641:                 'permission' => 'reports.view',
642:                 'filters' => ['date_range'],
643:                 'export_formats' => ['pdf', 'csv', 'xlsx'],
644:             ],
645:         ];
646:     }
647: 
648:     public function defaultSettings(): array
649:     {
650:         return [
651:             'currency' => 'BDT',
652:             'default_markup_percentage' => 20,
653:         ];
654:     }
655: 
656:     public function featureFlags(): array { return []; }
657: 
658:     public function onTenantAssigned(Tenant $tenant): void {}
659:     public function onTenantRemoved(Tenant $tenant): void {}
660: }
661: ```
662: 
663: ### New ProvisioningStep
664: 
665: Place in `app/Modules/Onboarding/Services/`:
666: 
667: ```php
668: <?php
669: 
670: declare(strict_types=1);
671: 
672: namespace App\Modules\Onboarding\Services;
673: 
674: use App\Modules\Onboarding\Contracts\ProvisioningStep;
675: use App\Modules\Onboarding\Data\ProvisioningContext;
676: 
677: class MyStep implements ProvisioningStep
678: {
679:     public function handle(ProvisioningContext $context): void
680:     {
681:         // $context->tenant, $context->businessTypeSlug, $context->planData
682:     }
683: 
684:     public function rollback(ProvisioningContext $context): void
685:     {
686:         // Undo handle() effects
687:     }
688: 
689:     public function label(): string
690:     {
691:         return 'Human-readable step name';
692:     }
693: }
694: ```
695: 
696: Register in `ProvisioningPipeline` constructor step array.
697: 
698: ### New Service Using TenantConfig
699: 
700: ```php
701: use App\Modules\BusinessType\ValueObjects\TenantConfig;
702: 
703: class SomeController
704: {
705:     public function __invoke(TenantConfig $config)
706:     {
707:         if ($config->hasModule('kitchen')) {
708:             // ...
709:         }
710:     }
711: }
712: ```
713: 
714: ### New Migration (Central DB)
715: 
716: Use anonymous class, no typed properties:
717: 
718: ```php
719: <?php
720: 
721: declare(strict_types=1);
722: 
723: use Illuminate\Database\Migrations\Migration;
724: use Illuminate\Database\Schema\Blueprint;
725: use Illuminate\Support\Facades\Schema;
726: 
727: return new class extends Migration
728: {
729:     public function up(): void
730:     {
731:         Schema::create('my_table', function (Blueprint $table) {
732:             $table->id();
733:             $table->timestamps();
734:         });
735:     }
736: 
737:     public function down(): void
738:     {
739:         Schema::dropIfExists('my_table');
740:     }
741: };
742: ```
743: 
744: ### New Migration (Tenant DB)
745: 
746: Place in `app/Modules/BusinessType/Database/Migrations/Tenant/`:
747: 
748: ```php
749: <?php
750: 
751: declare(strict_types=1);
752: 
753: use Illuminate\Database\Migrations\Migration;
754: use Illuminate\Database\Schema\Blueprint;
755: use Illuminate\Support\Facades\Schema;
756: 
757: return new class extends Migration
758: {
759:     public function up(): void
760:     {
761:         Schema::create('my_table', function (Blueprint $table) {
762:             $table->id();
763:             $table->string('tenant_id', 36);
764:             $table->timestamps();
765:         });
766:     }
767: 
768:     public function down(): void
769:     {
770:         Schema::dropIfExists('my_table');
771:     }
772: };
773: ```
774: 
775: ### New Frontend Component in Industry Widgets
776: 
777: Add to `industryMap` in `industry-widgets.tsx`:
778: 
779: ```typescript
780: new_industry: {
781:     name: 'New Industry',
782:     icon: Package,
783:     greeting: 'Welcome to your New Industry',
784:     description: 'Description of the industry.',
785: },
786: ```
787: 
788: ---
789: 
790: ## Registration Steps
791: 
792: ### Adding a New Business Type (Industry Pack + Template)
793: 
794: #### 1. Create the Industry Pack
795: 
796: Create `app/Modules/BusinessType/Packs/{Name}Pack.php` implementing `IndustryPack` (see template above).
797: 
798: #### 2. Create the TenantTemplate
799: 
800: Create `app/Modules/Onboarding/Templates/{Name}Template.php` implementing `TenantTemplate` (see template above).
801: 
802: #### 3. Register in Providers
803: 
804: **IndustryServiceProvider::boot():**
805: ```php
806: $registry->register(new NewIndustryPack);
807: ```
808: 
809: **OnboardingServiceProvider::boot():**
810: ```php
811: $registry->register(new NewIndustryTemplate);
812: ```
813: 
814: #### 4. Add seed data
815: 
816: **`database/seeders/BusinessTypeSeeder.php`** — add entry:
817: ```php
818: [
819:     'slug' => 'new_industry',
820:     'name' => 'New Industry',
821:     'description' => 'Description',
822:     'icon' => 'SomeIcon',
823:     'is_active' => true,
824:     'config_template' => ['default_modules' => ['product', 'inventory', 'pos', 'crm']],
825: ],
826: ```
827: 
828: **`database/seeders/BusinessTypeModuleSeeder.php`** — add mapping:
829: ```php
830: 'new_industry' => [
831:     'product' => true,
832:     'inventory' => true,
833:     'order' => true,
834:     'pos' => true,
835:     'crm' => true,
836:     'billing' => true,
837:     'team' => true,
838:     'reporting' => true,
839: ],
840: ```
841: 
842: #### 5. Add frontend widgets
843: 
844: Add to `industryMap` in `industry-widgets.tsx` and `industryMap` in `business-type.tsx`.
845: 
846: #### 6. Run seeders
847: 
848: ```bash
849: php artisan db:seed --class=BusinessTypeSeeder
850: php artisan db:seed --class=BusinessTypeModuleSeeder
851: ```
852: 
853: ### Adding a New ProvisioningStep
854: 
855: 1. Create step class in `app/Modules/Onboarding/Services/` implementing `ProvisioningStep`
856: 2. Inject into `ProvisioningPipeline` constructor (Laravel auto-resolves)
857: 3. Add to `$this->steps` array in the constructor body
858: 
859: ---
860: 
861: ## Verification Checklists
862: 
863: ### After Business Type / Pack Change
864: 
865: ```bash
866: # 1. PHP syntax check
867: php -l app/Modules/BusinessType/Packs/{PackName}.php
868: php -l app/Modules/Onboarding/Templates/{TemplateName}.php
869: 
870: # 2. Code style
871: vendor/bin/pint --format agent
872: 
873: # 3. Run relevant tests
874: php artisan test --compact --filter="FeatureGatingTest"
875: 
876: # 4. Verify seeders run clean
877: php artisan db:seed --class=BusinessTypeSeeder
878: php artisan db:seed --class=BusinessTypeModuleSeeder
879: 
880: # 5. Verify templates register
881: php artisan tinker --execute="
882: app()->register(App\Providers\OnboardingServiceProvider::class);
883: echo count(app(App\Modules\Onboarding\Services\TenantTemplateRegistry::class)->all()) . ' templates\n';"
884: ```
885: 
886: ### After Provider/Config Changes
887: 
888: ```bash
889: # 1. PHP syntax
890: php -l app/Providers/{ProviderName}.php
891: 
892: # 2. Code style
893: vendor/bin/pint --format agent
894: 
895: # 3. Check provider boot
896: php artisan tinker --execute="app()->register(App\Providers\OnboardingServiceProvider::class); echo 'OK\n';"
897: 
898: # 4. Clear + warm caches
899: php artisan optimize:clear
900: ```
901: 
902: ### After Any PHP Changes
903: 
904: ```bash
905: vendor/bin/pint --format agent
906: php -l {changed_file}
907: php artisan test --compact --filter="{RelevantTest}"
908: ```
909: 
910: ---
911: 
912: ## Critical Gotchas
913: 
914: ### PHP 8.4 Typed Properties in Migrations
915: 
916: Anonymous migration classes **must not** have typed properties. The `Migration` base class declares `$connection` without a type. Using `public string $connection` will cause a fatal error. Always use `Schema::create()` directly.
917: 
918: ### `orderBy` Required for `each()` (Laravel 12)
919: 
920: Always add `->orderBy('id')` before `->each()` on query builders:
921: ```php
922: DB::table('tenant_settings')
923:     ->where('tenant_id', $tenant->id)
924:     ->orderBy('id')              // REQUIRED
925:     ->each(function ($row) { ... });
926: ```
927: 
928: ### Tenant vs Central Connection
929: 
930: - **Central models** (BusinessType, Module, User, Plan, Subscription, Payment) use `CentralConnection` trait — they query the central MySQL database
931: - **Shared models** (TenantConfig, TenantModuleOverride, Task, TenantSetting) use `HasTenantScope` trait — they query `souda_shared` with `tenant_id` isolation
932: - **Dedicated models** (Product, Variant, Category, Brand, Warehouse, etc.) use normal Eloquent — they query the dedicated tenant DB
933: 
934: ### Service Provider Boot Order (`bootstrap/providers.php`)
935: 
936: ```php
937: AppServiceProvider::class,         // loadMigrationsFrom('database/migrations/central')
938: FortifyServiceProvider::class,     // Auth actions, Inertia views
939: ProductServiceProvider::class,     // Products, observers, policies
940: TenancyServiceProvider::class,     // TenantManager, middleware
941: BillingServiceProvider::class,     // Billing services, gateways
942: IndustryServiceProvider::class,    // Industry packs, TenantConfig
943: OnboardingServiceProvider::class,  // TenantTemplates, ProvisioningPipeline
944: ```
945: 
946: `AppServiceProvider::boot()` calls `loadMigrationsFrom(database_path('migrations/central'))` so the `tenant_user` pivot table migration and other central migrations are available in all environments (including test).
947: 
948: IndustryServiceProvider runs last among core providers. OnboardingServiceProvider runs last overall.
949: 
950: ### Shared Database Must Exist
951: 
952: ```bash
953: mysql -u root -e "CREATE DATABASE IF NOT EXISTS souda_shared CHARACTER SET utf8mb4"
954: php artisan migrate --force --database=shared --path=database/migrations/shared
955: ```
956: 
957: ### Cache Invalidation
958: 
959: After changing a tenant's business type or module overrides:
960: ```php
961: app(BusinessTypeEngine::class)->invalidateConfig($tenant);
962: ```
963: 
964: ### Plan-to-Mode Mapping (`config/tenancy.php`)
965: 
966: ```php
967: 'plan_mode_map' => [
968:     'free'         => 'shared',
969:     'starter'      => 'shared',
970:     'professional' => 'shared',
971:     'enterprise'   => 'dedicated',
972: ],
973: ```
974: 
975: ### $0 Payment Auto-Activation
976: 
977: Free plan (`$amount === 0`) activates immediately in `SubscriptionService::createSubscription()` without going through payment gateway. This was added as a fix — the subscription goes directly to `Active` and dispatches `SubscriptionActivated`.
978: 
979: ### Test DB Is `souda_testing` — Central + Default Share It
980: 
981: In `phpunit.xml`, both `DB_DATABASE` and `CENTRAL_DB_DATABASE` are set to `souda_testing`. The `central` and `default` (`mysql`) connections both target the same MySQL database. **Do not run tests in parallel** — each `php artisan test` command must complete before the next starts, or `migrate:fresh` calls will collide and corrupt the DB.
982: 
983: ### HasTenantScope Must Be Test-Safe
984: 
985: The `HasTenantScope` trait wraps `app()` calls in try-catch blocks. This prevents unit test failures when no Laravel application is booted. A `TestCase::setUp()` also resets `Model::$booting` via reflection to prevent stale boot state across tests:
986: 
987: ```php
988: protected function setUp(): void
989: {
990:     parent::setUp();
991:     Model::clearBootedModels();
992:     $reflection = new ReflectionClass(Model::class);
993:     $bootingProperty = $reflection->getProperty('booting');
994:     $bootingProperty->setAccessible(true);
995:     $bootingProperty->setValue(null, []);
996: }
997: ```
998: 
999: Do NOT remove the try-catch guards or the setUp() reset — they fix 239 pre-existing test failures.
1000: 
1001: ---
1002: 
1003: ## Key Classes Reference
1004: 
1005: ### Debugging Quick Reference
1006: 
1007: | Problem | Inspect |
1008: |---|---|
1009: | Config not applying | `BusinessTypeConfigBuilder::build()`, `applyTenantOverrides()` |
1010: | Cache serving stale data | `BusinessTypeEngine::getEffectiveConfig()` |
1011: | Pack not found | `IndustryPackRegistry::get()`, `IndustryServiceProvider::boot()` |
1012: | Template not found | `TenantTemplateRegistry::get()`, `OnboardingServiceProvider::boot()` |
1013: | Module not showing | `resolveModules()` in `BusinessTypeConfigBuilder` |
1014: | Tenant assignment issues | `BusinessTypeEngine::assignBusinessType()` |
1015: | Provisioning step fails | `ProvisioningPipeline::handleFailure()`, `onboarding_progress` JSON |
1016: | Subscription stuck pending | `SubscriptionService::createSubscription()`, amount/status check |
1017: | Test fails in Feature suite | `HasTenantScope` guard, `Model::$booting` reset in `TestCase::setUp()` |
1018: | Route not found for tenant | `InitializeTenancyByUser` middleware not applied, tenant not set up |
1019: 
1020: ### Container Bindings
1021: 
1022: | Abstract | Concrete | Type |
1023: |---|---|---|
1024: | `IndustryPackRegistry` | `IndustryPackRegistry` | singleton |
1025: | `BusinessTypeEngine` | `BusinessTypeEngine` | singleton |
1026: | `BusinessTypeConfigBuilder` | `BusinessTypeConfigBuilder` | singleton |
1027: | `TenantConfig` | closure (per-request) | bind |
1028: | `TenantTemplateRegistry` | `TenantTemplateRegistry` | singleton |
1029: | `ProvisioningPipeline` | `ProvisioningPipeline` | singleton |
1030: | `TenantManager` | `TenantManager` | singleton |
1031: | `BillingManager` | `BillingManager` | singleton |
1032: | `SubscriptionService` | `SubscriptionService` | singleton |
1033: 
1034: ### All TenantTemplates (16)
1035: 
1036: | Class | Slug | File |
1037: |---|---|---|
1038: | `PharmacyTemplate` | pharmacy | `app/Modules/Onboarding/Templates/PharmacyTemplate.php` |
1039: | `RestaurantTemplate` | restaurant | `app/Modules/Onboarding/Templates/RestaurantTemplate.php` |
1040: | `GroceryTemplate` | grocery | `app/Modules/Onboarding/Templates/GroceryTemplate.php` |
1041: | `SalonTemplate` | salon | `app/Modules/Onboarding/Templates/SalonTemplate.php` |
1042: | `BakeryTemplate` | bakery | `app/Modules/Onboarding/Templates/BakeryTemplate.php` |
1043: | `CafeTemplate` | cafe | `app/Modules/Onboarding/Templates/CafeTemplate.php` |
1044: | `ElectronicsTemplate` | electronics | `app/Modules/Onboarding/Templates/ElectronicsTemplate.php` |
1045: | `FashionTemplate` | fashion | `app/Modules/Onboarding/Templates/FashionTemplate.php` |
1046: | `CosmeticsTemplate` | cosmetics | `app/Modules/Onboarding/Templates/CosmeticsTemplate.php` |
1047: | `HardwareTemplate` | hardware | `app/Modules/Onboarding/Templates/HardwareTemplate.php` |
1048: | `WholesaleTemplate` | wholesale | `app/Modules/Onboarding/Templates/WholesaleTemplate.php` |
1049: | `DistributionTemplate` | distribution | `app/Modules/Onboarding/Templates/DistributionTemplate.php` |
1050: | `AgroShopTemplate` | agro_shop | `app/Modules/Onboarding/Templates/AgroShopTemplate.php` |
1051: | `BookstoreTemplate` | bookstore | `app/Modules/Onboarding/Templates/BookstoreTemplate.php` |
1052: | `SpaTemplate` | spa | `app/Modules/Onboarding/Templates/SpaTemplate.php` |
1053: | `DefaultTemplate` | general | `app/Modules/Onboarding/Templates/DefaultTemplate.php` |
1054: 
1055: ### All Industry Packs (15)
1056: 
1057: | Pack Class | Slug | Specialty Modules | Key Feature Flags |
1058: |---|---|---|---|
1059: | `PharmacyPack` | pharmacy | supplier, reporting | batch_tracking, expiry_tracking, prescription_management, drug_schedule_management, insurance_billing |
1060: | `RestaurantPack` | restaurant | kitchen, supplier, reporting | table_management, menu_management, kitchen_display, recipe_management |
1061: | `SalonPack` | salon | appointment, reporting | service_booking, staff_scheduling, commission_tracking, membership |
1062: | `SpaPack` | spa | appointment, reporting | service_booking, staff_scheduling, membership, package_management |
1063: | `ElectronicsPack` | electronics | supplier, reporting | serial_number_tracking, warranty_management, repair_tracking |
1064: | `FashionPack` | fashion | reporting | variant_size_color, seasonal_collections |
1065: | `GroceryPack` | grocery | supplier, reporting | weight_based_pricing, perishable_tracking |
1066: | `CafePack` | cafe | reporting | quick_service, recipe_management |
1067: | `BakeryPack` | bakery | reporting | batch_baking, recipe_costing |
1068: | `CosmeticsPack` | cosmetics | reporting | shade_variants, ingredient_tracking |
1069: | `HardwarePack` | hardware | supplier, reporting | bulk_pricing, unit_conversion |
1070: | `WholesalePack` | wholesale | supplier, reporting | bulk_orders, tiered_pricing |
1071: | `DistributionPack` | distribution | supplier (no pos) | logistics, fleet_management, route_management |
1072: | `AgroShopPack` | agro_shop | supplier, reporting | seasonal_inventory, seed_feed_tracking |
1073: | `BookstorePack` | bookstore | reporting | isbn_management, author_publisher_management |
1074: 
1075: ---
1076: 
1077: ## Event & Listener Map
1078: 
1079: | Event | Listener(s) |
1080: |---|---|
1081: | `SubscriptionActivated` | `ProvisionTenantDatabase` (sync), `SendSubscriptionNotification` (sync) |
1082: | `PaymentReceived` | `SendSubscriptionNotification`, `GenerateInvoice` (queued) |
1083: | `PaymentFailed` | `SendSubscriptionNotification` |
1084: | `SubscriptionCancelled` | `SendSubscriptionNotification` |
1085: | `SubscriptionExpired` | `SendSubscriptionNotification` |
1086: | `ProductCreated` | `IndexProductForSearch`, `GenerateProductSKU` |
1087: | `ProductUpdated` | `UpdateProductSearchIndex` |
1088: | `ProductDeleted` | `RemoveProductFromSearchIndex` |
1089: | `StockUpdated` | `UpdateProductStockCache` |
1090: | `StockDepleted` | `MarkProductUnavailable`, `SendStockDepletedNotification` |
1091: | `LowStockAlert` | `SendLowStockNotification` |
1092: | `StockReservationCreated` | `TrackStockReservation` |
1093: | `StockReservationExpired` | `ReleaseExpiredStock` |
1094: | `OnboardingStarted` | (progress tracked on tenant) |
1095: | `OnboardingStepCompleted` | (progress tracked on tenant) |
1096: | `OnboardingCompleted` | (tenant marked complete) |
1097: | `OnboardingFailed` | (tenant rolled back) |
1098: 
1099: ---
1100: 
1101: ## Database Schema
1102: 
1103: ### Central (`souda`) — Key Tables
1104: 
1105: ```
1106: tenants                 — id (uuid), name, owner_id, tenancy_mode, business_type_id,
1107:                           onboarding_status, onboarding_progress, onboarded_at, ...
1108: users                   — id, name, email, password, tenant_id, email_verified_at, ...
1109: billing_plans           — id, name, slug, monthly_price, yearly_price, features, limits, trial_...
1110: billing_subscriptions   — id, tenant_id, plan_id, gateway, status, billing_cycle, amount, ...
1111: billing_payments        — id, subscription_id, tenant_id, gateway, transaction_id, amount, status
1112: billing_seat_allocations — id, tenant_id, subscription_id, user_id, seat_type, status
1113: business_types          — id, slug, name, description, icon, is_active, pack_class, config_template
1114: modules                 — id, slug, name, description, version, dependencies, is_core
1115: business_type_module    — business_type_id, module_id, is_required, config_defaults
1116: roles / permissions     — Spatie permission tables
1117: app_settings            — key, value
1118: social_accounts         — user_id, provider, provider_user_id
1119: ```
1120: 
1121: ### Shared (`souda_shared`) — Key Tables
1122: 
1123: ```
1124: stores                  — id (ULID), tenant_id, name, slug, code, timezone, currency, status, is_default, ...
1125: tenant_settings         — id, tenant_id, timezone, locale, currency, logo, ...
1126: tasks                   — id, tenant_id, title, description, is_completed
1127: tenant_configs          — id, tenant_id, business_type_slug, config (JSON), config_hash
1128: tenant_module_overrides — id, tenant_id, module_slug, is_enabled, settings (JSON)
1129: ```
1130: 
1131: Shared DB migration paths in `tests/Support/RefreshMultiDatabase.php`:
1132: - `database/migrations/shared/` — settings, product tables, business type tables
1133: - `app/Modules/Store/Database/Migrations/Tenant/` — stores table (module tenant migration run against shared DB)
1134: 
1135: ### Dedicated (per-tenant) — Key Tables
1136: 
1137: products, variants, categories, brands, warehouses, warehouse_stock, stock_movements, stock_reservations, product_media, attributes, attribute_values, product_attribute_values, pricing_rules, tax_categories, tax_rates, audit_logs + shared tables copied on upgrade.
1138: 
1139: ---
1140: 
1141: ## Onboarding Fields on Tenants Table
1142: 
1143: Migration `2026_06_20_000100_add_onboarding_fields_to_tenants_table`:
1144: ```php
1145: $table->string('onboarding_status')->default('pending');
1146: $table->json('onboarding_progress')->nullable();
1147: $table->timestamp('onboarded_at')->nullable();
1148: ```
1149: 
1150: Status values: `pending`, `provisioning`, `completed`, `failed`.
1151: Progress schema: array of `{step, status, index, timestamp}` objects.
1152: 
1153: ---
1154: 
1155: ## Route Summary
1156: 
1157: | File | Prefix | Middleware |
1158: |---|---|---|
1159: | `routes/web.php` | `/` | web |
1160: | `routes/admin.php` | `/admin` | web, auth, EnsureAdmin |
1161: | `routes/tenant.php` | (none) | web, auth, InitializeTenancyByUser, subscription |
1162: | `routes/onboarding.php` | (none) | web |
1163: | `routes/settings.php` | (included) | auth, InitializeTenancyByUser |
1164: | `routes/console.php` | -- | -- |
1165: 
1166: Settings routes include `InitializeTenancyByUser` so `currentTenant`/`currentStore` are available on profile pages.

(End of file - total 1166 lines)
</content>

<system-reminder>
Instructions from: C:\dev\souda\docs\AGENTS.md
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

| Name | Database (prod) | Database (test) | Purpose | Models use |
|---|---|---|---|---|
| `central` | `souda` | `souda_testing` | Users, tenants, billing, business types, roles | `CentralConnection` trait |
| `shared` | `souda_shared` | `souda_shared` | Shared-mode tenant data (stores, settings, tasks, configs) | `HasTenantScope` trait |
| `mysql` | `souda` | `souda_testing` | Default / template for dedicated tenant DBs | Normal Eloquent |

In tests, `central` and `mysql` both point to `souda_testing`, so test processes must run sequentially — parallel migrations collide.

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

Resolves tenant via 3-tier fallback: (1) session `current_tenant_id` → check access, (2) `$user->tenants()` pivot query with try-catch, (3) `$user->tenant` legacy BelongsTo. Access check order: direct `$user->tenant_id` match first (no pivot query), then `tenants()` pivot. Runs before `SubstituteBindings`. Applied to all tenant routes and settings routes.

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

### ProvisioningContext (`app/Modules/Onboarding/Data/ProvisioningContext.php`)

```php
public function __construct(
    public Tenant $tenant,
    public string $businessTypeSlug,  // required — used to look up TenantTemplate
    public array $planData = [],
) {}
```

Class is NOT readonly (was changed from `readonly class` for PHP 8.4 compatibility). Has `setCurrentStep()` method.

### ProvisioningStep Contract (`app/Modules/Onboarding/Contracts/ProvisioningStep.php`)

```php
interface ProvisioningStep
{
    public function handle(ProvisioningContext $context): void;
    public function rollback(ProvisioningContext $context): void;
    public function label(): string;
}
```

### 11 Pipeline Steps (in `app/Modules/Onboarding/Services/`)

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
| `CreateDefaultStoreStep` | Creating default store | Creates first store from `TenantTemplate::defaultStores()` via `StoreService::createStore()` |
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
'currentTenant' => fn () => resolveCurrentTenant(),  // shared with logo, business_type_id
'tenants'       => fn () => $user->tenants,           // for tenant switcher
'currentStore'  => fn () => resolveCurrentStore(),    // shared with all store fields
'stores'        => fn () => $user->stores,            // for store switcher
'businessTypes' => fn () => BusinessType::all(),     // lazy prop for dropdowns
'tenant_config' => fn () => [
    'business_type' => $config->businessType,
    'modules'       => $config->enabledModules,
],
```

`resolveCurrentTenant()` has 3-tier fallback: `TenantManager->initialized()` → `$user->tenants()` pivot (try-catch) → `$user->tenant` legacy BelongsTo.

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
AppServiceProvider::class,         // loadMigrationsFrom('database/migrations/central')
FortifyServiceProvider::class,     // Auth actions, Inertia views
ProductServiceProvider::class,     // Products, observers, policies
TenancyServiceProvider::class,     // TenantManager, middleware
BillingServiceProvider::class,     // Billing services, gateways
IndustryServiceProvider::class,    // Industry packs, TenantConfig
OnboardingServiceProvider::class,  // TenantTemplates, ProvisioningPipeline
```

`AppServiceProvider::boot()` calls `loadMigrationsFrom(database_path('migrations/central'))` so the `tenant_user` pivot table migration and other central migrations are available in all environments (including test).

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

### Test DB Is `souda_testing` — Central + Default Share It

In `phpunit.xml`, both `DB_DATABASE` and `CENTRAL_DB_DATABASE` are set to `souda_testing`. The `central` and `default` (`mysql`) connections both target the same MySQL database. **Do not run tests in parallel** — each `php artisan test` command must complete before the next starts, or `migrate:fresh` calls will collide and corrupt the DB.

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
stores                  — id (ULID), tenant_id, name, slug, code, timezone, currency, status, is_default, ...
tenant_settings         — id, tenant_id, timezone, locale, currency, logo, ...
tasks                   — id, tenant_id, title, description, is_completed
tenant_configs          — id, tenant_id, business_type_slug, config (JSON), config_hash
tenant_module_overrides — id, tenant_id, module_slug, is_enabled, settings (JSON)
```

Shared DB migration paths in `tests/Support/RefreshMultiDatabase.php`:
- `database/migrations/shared/` — settings, product tables, business type tables
- `app/Modules/Store/Database/Migrations/Tenant/` — stores table (module tenant migration run against shared DB)

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
| `routes/settings.php` | (included) | auth, InitializeTenancyByUser |
| `routes/console.php` | -- | -- |

Settings routes include `InitializeTenancyByUser` so `currentTenant`/`currentStore` are available on profile pages.

</system-reminder>
```

**Tool: glob**

**Input:**
```json
{
  "pattern": "**/*.md",
  "path": "C:\\dev\\souda\\docs"
}
```

**Output:**
```
C:\dev\souda\docs\user-guide.md
C:\dev\souda\docs\subscription-payment-flow.md
C:\dev\souda\docs\frontend\10-performance-optimization-strategy.md
C:\dev\souda\docs\frontend\09-product-page-breakdown.md
C:\dev\souda\docs\frontend\08-reusable-ui-patterns.md
C:\dev\souda\docs\frontend\07-api-integration-strategy.md
C:\dev\souda\docs\frontend\06-state-management-strategy.md
C:\dev\souda\docs\frontend\05-table-architecture.md
C:\dev\souda\docs\frontend\04-form-architecture.md
C:\dev\souda\docs\frontend\03-component-structure.md
C:\dev\souda\docs\frontend\02-folder-structure.md
C:\dev\souda\docs\frontend\01-recommended-frontend-architecture.md
C:\dev\souda\docs\dev.md
C:\dev\souda\docs\agents.md
C:\dev\souda\docs\architectures\product-module-design.md
C:\dev\souda\docs\architectures\multi-tenancy.md
C:\dev\souda\docs\architectures\12-multi-store-architecture.md
C:\dev\souda\docs\architectures\11-development-roadmap.md
C:\dev\souda\docs\architectures\10-package-ecosystem.md
C:\dev\souda\docs\architectures\09-event-listener-strategy.md
C:\dev\souda\docs\architectures\08-service-container-bindings.md
C:\dev\souda\docs\architectures\07-coding-standards.md
C:\dev\souda\docs\architectures\06-naming-conventions.md
C:\dev\souda\docs\architectures\05-queue-strategy.md
C:\dev\souda\docs\architectures\04-tenant-database-strategy.md
C:\dev\souda\docs\architectures\03-module-communication.md
C:\dev\souda\docs\architectures\02-folder-structure.md
C:\dev\souda\docs\architectures\01-recommended-architecture.md
```

**Tool: glob**

**Input:**
```json
{
  "pattern": "**/*.md",
  "path": "C:\\dev\\souda\\.cursor"
}
```

**Output:**
```
C:\dev\souda\.cursor\skills\wayfinder-development\SKILL.md
C:\dev\souda\.cursor\skills\fortify-development\SKILL.md
C:\dev\souda\.cursor\skills\pest-testing\SKILL.md
C:\dev\souda\.cursor\skills\cashier-stripe-development\SKILL.md
C:\dev\souda\.cursor\skills\scout-development\SKILL.md
C:\dev\souda\.cursor\skills\tailwindcss-development\SKILL.md
C:\dev\souda\.cursor\skills\laravel-best-practices\SKILL.md
C:\dev\souda\.cursor\skills\inertia-react-development\SKILL.md
C:\dev\souda\.cursor\skills\socialite-development\SKILL.md
C:\dev\souda\.cursor\skills\cashier-stripe-development\references\subscriptions.md
C:\dev\souda\.cursor\skills\cashier-stripe-development\references\testing.md
C:\dev\souda\.cursor\skills\cashier-stripe-development\references\webhooks.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\blade-views.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\architecture.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\advanced-queries.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\eloquent.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\db-performance.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\config.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\collections.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\caching.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\events-notifications.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\error-handling.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\mail.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\http-client.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\migrations.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\validation.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\testing.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\style.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\security.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\scheduling.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\routing.md
C:\dev\souda\.cursor\skills\laravel-best-practices\rules\queue-jobs.md
```

**Tool: glob**

**Input:**
```json
{
  "pattern": "**/*.md",
  "path": "C:\\dev\\souda"
}
```

**Output:**
```
C:\dev\souda\session-ses_11c8.md
C:\dev\souda\AGENTS.md
C:\dev\souda\session-ses_0fbc.md
C:\dev\souda\session-ses_0fa9.md
C:\dev\souda\session-ses_1a9c.md
C:\dev\souda\session-ses_1698.md
C:\dev\souda\agents\testing.md
C:\dev\souda\agents\tenants-admin.md
C:\dev\souda\agents\tasks-module.md
C:\dev\souda\agents\social-auth-providers.md
C:\dev\souda\agents\product.md
C:\dev\souda\agents\multi-tenancy.md
C:\dev\souda\agents\confirm-dialog.md
C:\dev\souda\agents\billing.md
C:\dev\souda\docs\user-guide.md
C:\dev\souda\docs\subscription-payment-flow.md
C:\dev\souda\docs\frontend\10-performance-optimization-strategy.md
C:\dev\souda\docs\frontend\09-product-page-breakdown.md
C:\dev\souda\docs\frontend\08-reusable-ui-patterns.md
C:\dev\souda\docs\frontend\07-api-integration-strategy.md
C:\dev\souda\docs\frontend\06-state-management-strategy.md
C:\dev\souda\docs\frontend\05-table-architecture.md
C:\dev\souda\docs\frontend\04-form-architecture.md
C:\dev\souda\docs\frontend\03-component-structure.md
C:\dev\souda\docs\frontend\02-folder-structure.md
C:\dev\souda\docs\frontend\01-recommended-frontend-architecture.md
C:\dev\souda\docs\dev.md
C:\dev\souda\docs\architectures\product-module-design.md
C:\dev\souda\docs\architectures\multi-tenancy.md
C:\dev\souda\docs\architectures\12-multi-store-architecture.md
C:\dev\souda\docs\architectures\11-development-roadmap.md
C:\dev\souda\docs\architectures\10-package-ecosystem.md
C:\dev\souda\docs\architectures\09-event-listener-strategy.md
C:\dev\souda\docs\architectures\08-service-container-bindings.md
C:\dev\souda\docs\architectures\07-coding-standards.md
C:\dev\souda\docs\architectures\06-naming-conventions.md
C:\dev\souda\docs\architectures\05-queue-strategy.md
C:\dev\souda\docs\architectures\04-tenant-database-strategy.md
C:\dev\souda\docs\architectures\03-module-communication.md
C:\dev\souda\docs\architectures\02-folder-structure.md
C:\dev\souda\docs\architectures\01-recommended-architecture.md
C:\dev\souda\docs\agents.md
C:\dev\souda\README.md
```

---

## Assistant (Plan · DeepSeek V4 Flash Free · 6.0s)

_Thinking:_

Let me now read the key architecture docs and session files to understand the current state of the project.

Let me read the key architecture documents and session files to understand the current state.

**Tool: read**

**Input:**
```json
{
  "filePath": "C:\\dev\\souda\\docs\\architectures\\11-development-roadmap.md"
}
```

**Output:**
```
<path>C:\dev\souda\docs\architectures\11-development-roadmap.md</path>
<type>file</type>
<content>
1: # Development Roadmap
2: 
3: ## Overview
4: 
5: Phased development plan for building the multi-tenant SaaS ERP platform. Each phase delivers working, testable functionality.
6: 
7: ## Phase 0: Foundation (Current State)
8: 
9: **Status:** Partially Complete
10: 
11: ### Completed
12: - [x] Laravel 12 setup with PHP 8.4
13: - [x] Multi-tenancy configuration (stancl/tenancy, hybrid mode)
14: - [x] Authentication (Fortify + Socialite)
15: - [x] Roles & permissions (custom Permission/Role models extending Spatie)
16: - [x] Billing module (custom + Cashier with strategy pattern)
17: - [x] Frontend scaffold (React + Inertia + Tailwind + shadcn/ui)
18: - [x] CI/CD basics (Pint, Pest, ESLint)
19: - [x] Product module (17 models, 19 tenant migrations, 10 controllers, 20 services)
20: - [x] Tenant config system (TenantConfig DTO, TenantConfigService, caching)
21: - [x] Onboarding pipeline (10-step provisioning, 16 TenantTemplates, 15 IndustryPacks)
22: - [x] Dashboard with industry-specific widgets
23: - [x] Module navigation registry (11 modules)
24: - [x] Social auth (Google, GitHub)
25: - [x] Admin tenant management (list, show, deactivate, restore, force-delete)
26: - [x] Subscription management (trial, grace, expired, cancelled states)
27: - [x] Seat-based pricing (SeatPricingStrategy, allocation lifecycle)
28: - [x] Two-phase migration pattern (Phase 1 skeleton → Phase 2 fill)
29: - [x] Wayfinder v0 + Vite plugin (auto-generated @/actions/, @/routes/)
30: - [x] Frontend architecture (React Query, react-hook-form + Zod, TanStack Table v8)
31: 
32: ### Remaining
33: 
34: - [ ] Complete Stripe/SSLCommerz/BKash/Nagad/PortWallet gateway drivers (stubs exist)
35: - [ ] Add comprehensive test coverage
36: - [ ] Set up Horizon for queue monitoring
37: - [ ] Configure Redis for queues and cache
38: - [ ] Set up staging environment
39: 
40: **Duration:** 1-2 weeks (completion)
41: 
42: ---
43: 
44: ## Phase 1: Product Management Module
45: 
46: **Goal:** Complete product catalog with categories, variants, and media.
47: 
48: ### Week 1-2: Core Product Structure ✅ (Complete)
49: 
50: - [x] Create `Product` module structure
51: - [x] Define `Product`, `Category`, `Brand`, `Variant`, `Warehouse`, `StockMovement`, `StockReservation`, `Attribute`, `TaxCategory`, `TaxRate`, `PricingRule`, `AuditLog`, `ProductMedia`, `AttributeValue`, `ProductAttributeValue`, `ProductAttributeTextValue`, `WarehouseStock` models (17)
52: - [x] Create 19 tenant migrations for product tables
53: - [x] Implement `ProductService` with CRUD operations
54: - [x] Create `ProductData`, `ProductSummaryDTO`, `ProductWithStockDTO`, `VariantDTO`
55: - [x] Create `StoreProductRequest`, `UpdateProductRequest`, `StoreCategoryRequest`, `StoreBrandRequest`, `StoreVariantRequest`, `StoreWarehouseRequest`, `StockAdjustmentRequest`, `StockTransferRequest`, `StoreAttributeRequest`, `StorePricingRuleRequest`
56: - [x] Write unit tests for services
57: 
58: ### Week 3: Variants & Inventory Base ✅ (Complete)
59: 
60: - [x] Implement variant management (size, color, custom options)
61: - [x] Create SKU generation service (`DefaultSKUGenerator`)
62: - [x] Implement bulk product import (`ImportProductsJob`)
63: - [x] Create `ProductCreated`, `ProductUpdated`, `ProductDeleted`, `ProductArchived`, `ProductPublished`, `VariantCreated`, `VariantUpdated`, `VariantDeleted`, `StockUpdated`, `StockDepleted`, `LowStockAlert`, `StockReservationCreated`, `StockReservationExpired`, `StockTransferCompleted` events (14)
64: - [x] Implement 13 listeners + 8 queued jobs
65: - [x] Write feature tests for product CRUD
66: 
67: ### Week 4: Media & Search ✅ (Complete)
68: 
69: - [x] `ProductMedia` model + `MediaService` for product images (in-house solution)
70: - [x] Implement Scout-based search indexing (`IndexProductForSearch`, `IndexProductJob`, `ReindexAllProductsJob`)
71: - [x] Frontend product listing page with filters via Inertia + React Query
72: - [x] Product create/edit forms with react-hook-form + Zod validation
73: - [x] Image upload with validation (MediaController)
74: 
75: ### Week 5: Frontend & Polish ✅ (Complete)
76: 
77: - [x] Product list page with TanStack Table (sorting, filtering, pagination)
78: - [x] Product detail page with variant selection
79: - [x] Category management UI
80: - [x] Brand management UI
81: - [x] Attribute management UI
82: - [x] Stock management pages
83: - [x] Warehouse management pages
84: - [x] Write integration tests
85: 
86: **Deliverables:**
87: - Full product catalog management
88: - Product search and filtering
89: - Image management
90: - Bulk import/export
91: - Stock tracking with reservations
92: - Multi-warehouse support
93: - Complete test coverage
94: 
95: **Duration:** Complete
96: 
97: ---
98: 
99: ## Phase 2: Order Management Module
100: 
101: **Goal:** Complete order processing from creation to fulfillment.
102: 
103: ### Week 6-7: Core Order Structure
104: 
105: - [ ] Create `Orders` module structure
106: - [ ] Define `Order`, `OrderItem` models
107: - [ ] Create tenant migrations for order tables
108: - [ ] Implement `OrderService` with CRUD operations
109: - [ ] Create `OrderDTO`, `OrderItemDTO`, `OrderCreatedDTO`
110: - [ ] Implement order number generation
111: - [ ] Create `StoreOrderRequest`, `UpdateOrderRequest`
112: - [ ] Implement order status workflow (Pending → Processing → Shipped → Delivered)
113: - [ ] Write unit tests
114: 
115: ### Week 8: Order Processing
116: 
117: - [ ] Implement `OrderCreated` event dispatching
118: - [ ] Create `DeductStock` listener (Inventory module integration)
119: - [ ] Create `UpdateCustomerActivity` listener (CRM module integration)
120: - [ ] Implement order cancellation flow
121: - [ ] Implement order refund flow
122: - [ ] Create order email notifications
123: - [ ] Write feature tests
124: 
125: ### Week 9: Frontend
126: 
127: - [ ] Order list page with status filters
128: - [ ] Order detail page with line items
129: - [ ] Order creation form (manual order entry)
130: - [ ] Order status update workflow
131: - [ ] Order printing (invoice/packing slip)
132: 
133: ### Week 10: Advanced Features
134: 
135: - [ ] Order export (CSV, PDF)
136: - [ ] Order search and filtering
137: - [ ] Bulk order actions
138: - [ ] Order notes and activity log
139: - [ ] Integration tests for cross-module communication
140: 
141: **Deliverables:**
142: - Complete order management
143: - Order status workflow
144: - Cross-module event integration
145: - Order printing and export
146: - Complete test coverage
147: 
148: **Duration:** 5 weeks
149: 
150: ---
151: 
152: ## Phase 3: Inventory Tracking Module
153: 
154: **Goal:** Real-time stock tracking with movement history and alerts.
155: 
156: ### Week 11-12: Core Inventory
157: 
158: - [ ] Create `Inventory` module structure
159: - [ ] Define `Stock`, `StockMovement`, `Warehouse` models
160: - [ ] Create tenant migrations for inventory tables
161: - [ ] Implement `InventoryService` with stock operations
162: - [ ] Create `StockDTO`, `StockMovementDTO`
163: - [ ] Implement stock deduction (from OrderCreated listener)
164: - [ ] Implement stock adjustment (manual)
165: - [ ] Implement stock transfer between warehouses
166: - [ ] Write unit tests
167: 
168: ### Week 13: Alerts & Reporting
169: 
170: - [ ] Implement low stock threshold per product
171: - [ ] Create `StockDepleted`, `LowStockAlert` events
172: - [ ] Implement `MarkProductUnavailable` listener
173: - [ ] Create stock alert notifications
174: - [ ] Implement stock valuation report
175: - [ ] Implement stock movement history
176: 
177: ### Week 14: Frontend
178: 
179: - [ ] Stock overview dashboard
180: - [ ] Stock movement history table
181: - [ ] Low stock alerts page
182: - [ ] Stock adjustment form
183: - [ ] Warehouse management UI
184: 
185: ### Week 15: Advanced Features
186: 
187: - [ ] Barcode/QR code support
188: - [ ] Batch/lot tracking
189: - [ ] Expiry date tracking
190: - [ ] Stock forecasting (basic)
191: - [ ] Integration tests
192: 
193: **Deliverables:**
194: - Real-time stock tracking
195: - Multi-warehouse support
196: - Low stock alerts
197: - Stock movement history
198: - Stock valuation reports
199: - Complete test coverage
200: 
201: **Duration:** 5 weeks
202: 
203: ---
204: 
205: ## Phase 4: CRM Module
206: 
207: **Goal:** Customer relationship management with contact tracking and deal pipeline.
208: 
209: ### Week 16-17: Core CRM
210: 
211: - [ ] Create `CRM` module structure
212: - [ ] Define `Contact`, `Interaction`, `Deal`, `Pipeline` models
213: - [ ] Create tenant migrations for CRM tables
214: - [ ] Implement `CRMService` with CRUD operations
215: - [ ] Create `ContactDTO`, `InteractionDTO`, `DealDTO`
216: - [ ] Implement contact import (CSV)
217: - [ ] Implement interaction logging
218: - [ ] Create `ContactCreated`, `InteractionLogged` events
219: - [ ] Write unit tests
220: 
221: ### Week 18: Deal Pipeline
222: 
223: - [ ] Implement deal pipeline stages
224: - [ ] Implement deal progression workflow
225: - [ ] Create `DealWon`, `DealLost` events
226: - [ ] Implement deal value tracking
227: - [ ] Implement pipeline analytics
228: - [ ] Write feature tests
229: 
230: ### Week 19: Frontend
231: 
232: - [ ] Contact list with search and filters
233: - [ ] Contact detail page with activity timeline
234: - [ ] Deal pipeline board (Kanban-style)
235: - [ ] Interaction logging form
236: - [ ] Contact import wizard
237: 
238: ### Week 20: Advanced Features
239: 
240: - [ ] Contact segmentation
241: - [ ] Deal forecasting
242: - [ ] Activity reports
243: - [ ] Email integration (send/receive)
244: - [ ] Integration tests
245: 
246: **Deliverables:**
247: - Contact management
248: - Interaction tracking
249: - Deal pipeline
250: - Pipeline analytics
251: - Contact import
252: - Complete test coverage
253: 
254: **Duration:** 5 weeks
255: 
256: ---
257: 
258: ## Phase 5: Billing Enhancements
259: 
260: **Goal:** Complete billing module with all gateway drivers and advanced features.
261: 
262: > **Note:** Seat-based pricing (strategy pattern, seat allocation, overage invoicing, team invitation routes, middleware gating) has been implemented ahead of schedule.
263: 
264: ### Completed
265: 
266: - [x] Seat pricing strategy pattern (`PricingStrategy` interface + `SeatPricingStrategy`/`FlatPricingStrategy`)
267: - [x] Seat allocation lifecycle (allocate, activate pending, release, resend invite)
268: - [x] Overage invoicing service (`OverageInvoiceService`)
269: - [x] `EnsureSeatAvailable` middleware (aliased `seat`)
270: - [x] Team invitation routes + controller + frontend page
271: - [x] Seat events + listeners (`SeatAllocated`, `SeatReleased`, `SeatOverageInvoiced`, `RecalculateSeatUsage`)
272: - [x] `SyncSeatAllocations` queued job for user→seat reconciliation
273: 
274: ### Remaining
275: 
276: ### Week 21-22: Gateway Completion
277: 
278: - [ ] Complete `StripeDriver` implementation
279: - [ ] Complete `BKashDriver` implementation
280: - [ ] Complete `NagadDriver` implementation
281: - [ ] Complete `PortWalletDriver` implementation
282: - [ ] Implement webhook signature verification for all gateways
283: - [ ] Write gateway integration tests
284: 
285: ### Week 23: Advanced Billing
286: 
287: - [ ] Implement subscription upgrades/downgrades
288: - [ ] Implement proration calculations
289: - [ ] Implement coupon/discount system
290: - [ ] Implement invoice PDF generation
291: - [ ] Implement payment history page
292: - [ ] Write feature tests
293: 
294: ### Week 24: Frontend
295: 
296: - [ ] Subscription management page
297: - [ ] Invoice history page (enhance existing)
298: - [ ] Payment method management
299: - [ ] Upgrade/downgrade flow
300: - [ ] Coupon redemption UI
301: 
302: **Deliverables:**
303: - All payment gateways functional
304: - Subscription management
305: - Invoice generation
306: - Payment history
307: - Complete test coverage
308: 
309: **Duration:** 4 weeks
310: 
311: ---
312: 
313: ## Phase 6: Platform Features
314: 
315: **Goal:** Cross-cutting platform features and infrastructure.
316: 
317: ### Week 25-26: Dashboard & Analytics
318: 
319: - [ ] Tenant dashboard with KPIs
320: - [ ] Revenue analytics
321: - [ ] Order analytics
322: - [ ] Product analytics
323: - [ ] Inventory analytics
324: - [ ] CRM analytics
325: - [ ] Custom report builder
326: 
327: ### Week 27-28: Notifications System
328: 
329: - [ ] Notification preferences per tenant
330: - [ ] Email notification templates
331: - [ ] In-app notification center
332: - [ ] Push notification support (optional)
333: - [ ] Notification scheduling
334: 
335: ### Week 29-30: Settings & Configuration
336: 
337: - [ ] Tenant settings page
338: - [ ] Feature toggle management
339: - [ ] Email template customization
340: - [ ] Branding customization (logo, colors)
341: - [ ] Timezone and locale settings
342: 
343: ### Week 31-32: API Layer
344: 
345: - [ ] API authentication (Sanctum)
346: - [ ] API versioning strategy
347: - [ ] Product API endpoints
348: - [ ] Order API endpoints
349: - [ ] Inventory API endpoints
350: - [ ] CRM API endpoints
351: - [ ] API documentation (OpenAPI/Swagger)
352: 
353: **Deliverables:**
354: - Analytics dashboard
355: - Notification system
356: - Tenant customization
357: - RESTful API
358: - API documentation
359: 
360: **Duration:** 8 weeks
361: 
362: ---
363: 
364: ## Phase 7: Production Readiness
365: 
366: **Goal:** Security, performance, monitoring, and deployment.
367: 
368: ### Week 33-34: Security Hardening
369: 
370: - [ ] Security audit
371: - [ ] Rate limiting configuration
372: - [ ] CSRF protection review
373: - [ ] XSS prevention review
374: - [ ] SQL injection prevention review
375: - [ ] File upload security
376: - [ ] API security review
377: - [ ] Penetration testing
378: 
379: ### Week 35-36: Performance Optimization
380: 
381: - [ ] Database query optimization
382: - [ ] Redis caching strategy
383: - [ ] Queue optimization
384: - [ ] Asset optimization (Vite)
385: - [ ] CDN configuration
386: - [ ] Database indexing review
387: - [ ] Load testing
388: 
389: ### Week 37-38: Monitoring & Observability
390: 
391: - [ ] Horizon dashboard configuration
392: - [ ] Error tracking (Sentry)
393: - [ ] Application metrics
394: - [ ] Log aggregation
395: - [ ] Health check endpoints
396: - [ ] Uptime monitoring
397: - [ ] Alert configuration
398: 
399: ### Week 39-40: Deployment & Documentation
400: 
401: - [ ] CI/CD pipeline
402: - [ ] Staging environment
403: - [ ] Production deployment
404: - [ ] Database backup strategy
405: - [ ] Disaster recovery plan
406: - [ ] Runbook documentation
407: - [ ] User documentation
408: - [ ] API documentation
409: 
410: **Deliverables:**
411: - Production-ready application
412: - Monitoring and alerting
413: - CI/CD pipeline
414: - Documentation
415: - Backup and recovery
416: 
417: **Duration:** 8 weeks
418: 
419: ---
420: 
421: ## Total Timeline
422: 
423: | Phase | Duration | Cumulative |
424: |-------|----------|------------|
425: | Phase 0: Foundation | 1-2 weeks | 2 weeks |
426: | Phase 1: Products | 5 weeks | 7 weeks |
427: | Phase 2: Orders | 5 weeks | 12 weeks |
428: | Phase 3: Inventory | 5 weeks | 17 weeks |
429: | Phase 4: CRM | 5 weeks | 22 weeks |
430: | Phase 5: Billing | 4 weeks | 26 weeks |
431: | Phase 6: Platform | 8 weeks | 34 weeks |
432: | Phase 7: Production | 8 weeks | 42 weeks |
433: 
434: **Total: ~42 weeks (10-11 months)**
435: 
436: ## Parallel Development Opportunities
437: 
438: | Phase | Can Run In Parallel With |
439: |-------|-------------------------|
440: | Phase 1 (Products) | Phase 5 (Billing enhancements) |
441: | Phase 2 (Orders) | Phase 3 (Inventory) - with coordination |
442: | Phase 4 (CRM) | Phase 6 (Platform features) |
443: | Phase 7 (Production) | All phases (ongoing) |
444: 
445: ## Milestone Checkpoints
446: 
447: | Milestone | Week | Deliverable |
448: |-----------|------|-------------|
449: | M1: Product Catalog | 7 | Full product management |
450: | M2: Order Processing | 12 | Complete order lifecycle |
451: | M3: Inventory Control | 17 | Stock tracking and alerts |
452: | M4: CRM System | 22 | Contact and deal management |
453: | M5: Complete Billing | 26 | All gateways + subscriptions |
454: | M6: Platform Complete | 34 | Dashboard, API, notifications |
455: | M7: Production Launch | 42 | Deployed and monitored |
456: 
457: ## Risk Mitigation
458: 
459: | Risk | Mitigation |
460: |------|------------|
461: | Scope creep | Strict phase boundaries, no feature additions mid-phase |
462: | Tenant isolation bugs | Comprehensive multi-DB testing in every phase |
463: | Performance issues | Load testing at each milestone |
464: | Integration failures | Contract tests between modules |
465: | Timeline slippage | Buffer weeks built into each phase |
466: | Package incompatibility | Test packages in isolation before integration |
467: 
468: ## Definition of Done (Per Phase)
469: 
470: - [ ] All features implemented and tested
471: - [ ] Unit tests written (>80% coverage)
472: - [ ] Feature tests written
473: - [ ] Integration tests for cross-module communication
474: - [ ] Code reviewed and formatted (Pint)
475: - [ ] TypeScript type-checked
476: - [ ] ESLint passes
477: - [ ] Documentation updated
478: - [ ] Demo/staging deployed
479: - [ ] Stakeholder sign-off

(End of file - total 479 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "C:\\dev\\souda\\docs\\architectures\\02-folder-structure.md"
}
```

**Output:**
```
<path>C:\dev\souda\docs\architectures\02-folder-structure.md</path>
<type>file</type>
<content>
1: # Folder Structure
2: 
3: ## Root Structure
4: 
5: ```
6: souda/
7: ├── .cursor/                          # AI agent skills configuration
8: ├── agents/                           # AI agent domain documentation
9: ├── app/                              # Application source code
10: ├── bootstrap/                        # Framework bootstrapping
11: ├── config/                           # Configuration files
12: ├── database/                         # Migrations, factories, seeders
13: ├── docs/                             # Project documentation
14: ├── public/                           # Web root (index.php, assets)
15: ├── resources/                        # Frontend source (JS, CSS, views)
16: ├── routes/                           # Route definitions
17: ├── storage/                          # Logs, cache, uploads
18: ├── tests/                            # Test suites
19: ├── AGENTS.md                         # AI agent guidelines
20: ├── composer.json                     # PHP dependencies
21: ├── package.json                      # Node dependencies
22: ├── pint.json                         # Code formatter config
23: ├── phpunit.xml                       # Test configuration
24: ├── tsconfig.json                     # TypeScript config
25: ├── vite.config.ts                    # Vite build config
26: └── eslint.config.js                  # ESLint config
27: ```
28: 
29: ## Application Structure (`app/`)
30: 
31: ```
32: app/
33: ├── Actions/                          # Application-level action classes
34: │   ├── Auth/
35: │   │   ├── CreateNewUser.php
36: │   │   └── CreateSocialUser.php
37: │   └── Fortify/
38: │       ├── CreateNewUser.php
39: │       ├── ResetUserPassword.php
40: │       └── ...
41: ├── Concerns/                         # Reusable PHP traits
42: │   ├── PasswordValidationRules.php
43: │   └── ProfileValidationRules.php
44: ├── Console/Commands/                 # Artisan commands
45: │   └── ImportStripeData.php
46: ├── Contracts/                        # Application-level interfaces
47: │   └── (cross-module contracts)
48: ├── DTOs/                             # Application-level DTOs
49: ├── Enums/                            # Application-level enums
50: ├── Events/                           # Application-level events
51: ├── Exceptions/                       # Application-level exceptions
52: ├── Http/
53: │   ├── Controllers/
54: │   │   ├── Admin/                    # Admin area controllers
55: │   │   ├── Auth/                     # Auth controllers
56: │   │   ├── Settings/                 # User settings controllers
57: │   │   └── (top-level controllers)
58: │   ├── Middleware/                   # HTTP middleware
59: │   ├── Requests/                     # Form request classes
60: │   │   ├── Admin/
61: │   │   ├── Settings/
62: │   │   ├── InviteTeamMemberRequest.php
63: │   │   └── (top-level requests)
64: │   ├── Resources/                    # API resource classes
65: │   └── Responses/                    # Response overrides
66: │       ├── LoginResponse.php
67: │       └── RegisterResponse.php
68: ├── Jobs/                             # Queueable jobs
69: ├── Listeners/                        # Application-level listeners
70: │   └── StripeEventListener.php
71: ├── Mail/                             # Mailable classes
72: ├── Models/                           # Central-database Eloquent models
73: │   ├── AppSetting.php
74: │   ├── Plan.php
75: │   ├── PlanPrice.php
76: │   ├── SocialAccount.php
77: │   ├── Task.php
78: │   ├── Tenant.php
79: │   └── User.php
80: ├── Modules/                          # Domain modules (modular monolith)
81: │   ├── Billing/                      # Billing bounded context
82: │   ├── Product/                      # Product catalog, inventory, stock management
83: │   ├── Orders/                       # Order management
84: │   ├── CRM/                          # Customer relationship management
85: │   ├── BusinessType/                 # Industry packs, tenant config engine
86: │   ├── Onboarding/                   # Registration pipeline, tenant provisioning
87: │   └── (future modules)
88: ├── Notifications/                    # Notification classes
89: ├── Observers/                        # Eloquent observers
90: ├── Policies/                         # Authorization policies
91: ├── Providers/                        # Service providers
92: │   ├── AppServiceProvider.php
93: │   ├── BillingServiceProvider.php
94: │   ├── FortifyServiceProvider.php
95: │   └── TenancyServiceProvider.php
96: ├── Rules/                            # Custom validation rules
97: ├── Services/                         # Application-level services
98: │   ├── BillingEmailService.php
99: │   └── SocialAuthService.php
100: └── Shared/                           # Shared kernel (cross-cutting)
101:     ├── Traits/
102:     ├── Helpers/
103:     └── Constants/
104: ```
105: 
106: ## Module Structure (`app/Modules/{Domain}/`)
107: 
108: Every module follows this consistent internal structure:
109: 
110: ```
111: app/Modules/{Domain}/
112: ├── Actions/                          # Single-responsibility action classes
113: ├── Contracts/                        # Module interfaces
114: ├── DTOs/                             # Data transfer objects
115: ├── Drivers/                          # External service drivers (strategy pattern)
116: ├── Enums/                            # PHP 8.1 backed enums
117: ├── Events/                           # Domain events
118: ├── Exceptions/                       # Module-specific exceptions
119: ├── Listeners/                        # Domain event listeners
120: ├── Models/                           # Eloquent models (tenant or central)
121: ├── Observers/                        # Model observers
122: ├── Policies/                         # Authorization policies
123: ├── Requests/                         # Form request validation
124: ├── Resources/                        # API resources
125: ├── Rules/                            # Custom validation rules
126: ├── Services/                         # Business logic services
127: ├── Tests/                            # Module-specific tests
128: │   ├── Feature/
129: │   └── Unit/
130: └── Webhooks/                         # Webhook handlers
131: ```
132: 
133: > **Note:** This project intentionally does NOT use the Repository pattern. Services operate directly on Eloquent models, which serve as the data access layer. This reduces boilerplate while maintaining testability through service-level abstraction.
134: 
135: ## Frontend Structure (`resources/js/`)
136: 
137: ```
138: resources/js/
139: ├── app.tsx                           # Inertia entry point
140: ├── ssr.tsx                           # SSR entry point
141: ├── css/
142: │   └── app.css                       # Tailwind imports
143: ├── actions/                          # Wayfinder-generated controller actions
144: │   └── (auto-generated by @laravel/vite-plugin-wayfinder on save, do not edit)
145: ├── routes/                           # Wayfinder-generated named routes
146: │   └── (auto-generated by @laravel/vite-plugin-wayfinder on save, do not edit)
147: ├── components/
148: │   ├── ui/                           # shadcn-style UI primitives
149: │   │   ├── alert-dialog.tsx
150: │   │   ├── avatar.tsx
151: │   │   ├── badge.tsx
152: │   │   ├── button.tsx
153: │   │   ├── card.tsx
154: │   │   ├── dialog.tsx
155: │   │   ├── dropdown-menu.tsx
156: │   │   ├── form.tsx
157: │   │   ├── input.tsx
158: │   │   ├── label.tsx
159: │   │   ├── select.tsx
160: │   │   ├── table.tsx
161: │   │   ├── tabs.tsx
162: │   │   └── ...
163: │   ├── app-*.tsx                     # App shell components
164: │   ├── nav-*.tsx                     # Navigation components
165: │   └── (shared components)
166: ├── modules/                          # Feature domain modules
167: │   ├── dashboard/                    # Dashboard widgets
168: │   │   └── components/
169: │   │       └── industry-widgets.tsx  # Industry-specific widgets
170: │   ├── product/                      # Product module frontend
171: │   ├── billing/                      # Billing module frontend
172: │   └── shared/                       # Cross-cutting shared components
173: │       ├── data-table.tsx            # TanStack Table wrapper
174: │       ├── page-header.tsx
175: │       ├── confirm-dialog.tsx
176: │       ├── filter-bar.tsx
177: │       ├── search-input.tsx
178: │       ├── status-badge.tsx
179: │       ├── empty-state.tsx
180: │       └── error-state.tsx
181: ├── hooks/                            # Custom React hooks
182: │   ├── use-appearance.tsx
183: │   ├── use-clipboard.ts
184: │   ├── use-current-url.ts
185: │   ├── use-initials.tsx
186: │   ├── use-mobile.tsx
187: │   ├── use-tenant-config.ts
188: │   ├── use-module-enabled.ts
189: │   ├── use-debounce.ts
190: │   ├── use-permissions.ts
191: │   └── use-pagination.ts
192: ├── layouts/                          # Page layout components
193: │   ├── app-layout.tsx
194: │   ├── auth-layout.tsx
195: │   ├── app/
196: │   ├── auth/
197: │   ├── settings/
198: │   └── admin/
199: ├── pages/                            # Inertia page components
200: │   ├── welcome.tsx
201: │   ├── dashboard.tsx
202: │   ├── auth/
203: │   ├── settings/
204: │   ├── admin/
205: │   ├── billing/
206: │   ├── team/
207: │   ├── product/
208: │   └── (module pages)
209: ├── lib/                              # Utility functions
210: │   ├── utils.ts
211: │   ├── format-currency.ts
212: │   ├── format-date.ts
213: │   ├── cn.ts
214: │   └── map-form-to-payload.ts
215: └── types/                            # TypeScript type definitions
216:     ├── auth.ts
217:     ├── navigation.ts
218:     ├── ui.ts
219:     ├── tenant-config.ts
220:     ├── index.ts
221:     └── ...
222: ```
223: 
224: ## Database Structure (`database/`)
225: 
226: ```
227: database/
228: ├── factories/                        # Model factories
229: │   ├── PlanFactory.php
230: │   ├── SocialAccountFactory.php
231: │   ├── TaskFactory.php
232: │   ├── TenantFactory.php
233: │   └── UserFactory.php
234: ├── migrations/
235: │   ├── 0001_01_01_000000_...         # Central: users, sessions
236: │   ├── 0001_01_01_000001_...         # Central: cache
237: │   ├── 0001_01_01_000002_...         # Central: jobs
238: │   ├── 2019_09_15_000010_...         # Central: tenants
239: │   ├── 2019_09_15_000020_...         # Central: domains
240: │   ├── (central migrations)          # Central: all shared tables
241: │   ├── shared/                       # Shared-tenant migrations (souda_shared)
242: │   │   └── 2026_02_22_094519_...     # Shared: tasks, tenant_configs, etc.
243: │   └── tenant/                       # Dedicated-tenant migrations
244: ├── seeders/
245: │   ├── DatabaseSeeder.php
246: │   ├── PlanSeeder.php
247: │   └── (module seeders)
248: └── (module seeders can live in app/Modules/{Domain}/Database/)
249: ```
250: 
251: ## Route Structure (`routes/`)
252: 
253: ```
254: routes/
255: ├── web.php                           # Public routes (landing, pricing)
256: ├── tenant.php                        # Tenant-scoped routes (behind tenancy)
257: ├── admin.php                         # Admin routes (/admin prefix)
258: ├── settings.php                      # User settings routes
259: ├── console.php                       # Artisan console routes
260: └── api.php                           # API routes (if enabled)
261: ```
262: 
263: ## Config Structure (`config/`)
264: 
265: ```
266: config/
267: ├── app.php                           # Application config
268: ├── auth.php                          # Authentication config
269: ├── billing.php                       # Billing gateway config
270: ├── cache.php                         # Cache config
271: ├── cashier.php                       # Stripe Cashier config
272: ├── database.php                      # Database connections
273: ├── filesystems.php                   # File storage config
274: ├── fortify.php                       # Fortify auth config
275: ├── inertia.php                       # Inertia.js config
276: ├── logging.php                       # Logging config
277: ├── mail.php                          # Email config
278: ├── queue.php                         # Queue config
279: ├── services.php                      # Third-party services
280: ├── session.php                       # Session config
281: ├── social-auth.php                   # Social auth providers
282: └── tenancy.php                       # Stancl Tenancy config
283: ```
284: 
285: ## Test Structure (`tests/`)
286: 
287: ```
288: tests/
289: ├── Feature/                          # Feature/integration tests
290: │   ├── Auth/
291: │   ├── Billing/
292: │   ├── Admin/
293: │   ├── Settings/
294: │   └── (module tests)
295: ├── Unit/                             # Unit tests
296: ├── Support/
297: │   └── RefreshMultiDatabase.php      # Custom trait for multi-DB testing
298: ├── Pest.php                          # Pest configuration
299: └── TestCase.php                      # Base test case
300: ```
301: 
302: ## Module Database Structure (Optional)
303: 
304: For larger modules, database files can live inside the module:
305: 
306: ```
307: app/Modules/{Domain}/Database/
308: ├── Migrations/
309: │   └── 2026_05_19_000001_...
310: ├── Seeders/
311: │   └── DomainSeeder.php
312: └── Factories/
313:     └── DomainModelFactory.php
314: ```
315: 
316: Register these in the module's service provider:
317: 
318: ```php
319: $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
320: ```
321: 
322: ## Key Conventions
323: 
324: 1. **Central models** live in `app/Models/` and use `CentralConnection` trait
325: 2. **Tenant models** live in `app/Modules/{Domain}/Models/` and use tenant connection
326: 3. **Module tests** can live in `tests/Feature/{Domain}/` or `app/Modules/{Domain}/Tests/`
327: 4. **Wayfinder-generated** files (`actions/`, `routes/`) are auto-generated, never edited
328: 5. **UI components** in `components/ui/` are shadcn primitives, do not modify directly
329: 6. **Shared components** go in `components/` at the appropriate level
330: 7. **Module-specific frontend components** go in `resources/js/components/{domain}/`

(End of file - total 330 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "C:\\dev\\souda\\docs\\architectures\\01-recommended-architecture.md"
}
```

**Output:**
```
<path>C:\dev\souda\docs\architectures\01-recommended-architecture.md</path>
<type>file</type>
<content>
1: # Recommended Complete Architecture
2: 
3: ## System Overview
4: 
5: Multi-tenant SaaS ERP platform for SME F-Commerce and E-Commerce businesses, built on a modular monolith with domain-driven design principles.
6: 
7: ```
8: ┌─────────────────────────────────────────────────────────────────┐
9: │                        Client Layer                              │
10: │  React SPA (Inertia.js v2) + Tailwind CSS v4 + shadcn/ui        │
11: └────────────────────────────┬────────────────────────────────────┘
12:                              │ HTTP / WebSocket
13: ┌────────────────────────────▼────────────────────────────────────┐
14: │                     Presentation Layer                           │
15: │  Controllers (Inertia + API)  │  Form Requests  │  Resources     │
16: └────────────────────────────┬────────────────────────────────────┘
17:                              │
18: ┌────────────────────────────▼────────────────────────────────────┐
19: │                     Application Layer                            │
20: │  Actions  │  Services  │  DTOs  │  Validators  │  Notifications │
21: └────────────────────────────┬────────────────────────────────────┘
22:                              │
23: ┌────────────────────────────▼────────────────────────────────────┐
24: │                      Domain Layer                                │
25: │  Modules (Billing, Products, Orders, Inventory, CRM, etc.)      │
26: │  ├── Models  │  Enums  │  Events  │  Listeners  │  Exceptions   │
27: │  ├── Contracts (Interfaces)  │  DTOs  │  Actions  │  Services   │
28: │  └── Repositories  │  Rules  │  Policies  │  Observers           │
29: └────────────────────────────┬────────────────────────────────────┘
30:                              │
31: ┌────────────────────────────▼────────────────────────────────────┐
32: │                   Infrastructure Layer                           │
33: │  Eloquent ORM  │  Queue (Redis)  │  Cache (Redis)  │  Storage   │
34: │  Payment Gateways  │  Email (Postmark/Resend)  │  Webhooks       │
35: └────────────────────────────┬────────────────────────────────────┘
36:                              │
37: ┌────────────────────────────▼────────────────────────────────────┐
38: │                      Data Layer                                  │
39: │  Central DB (MySQL)  │  Tenant DBs (MySQL)  │  Redis             │
40: └─────────────────────────────────────────────────────────────────┘
41: ```
42: 
43: ## Architectural Principles
44: 
45: ### 1. Modular Monolith
46: 
47: - Each business domain is a self-contained module under `app/Modules/{Domain}/`
48: - Modules communicate via events and service interfaces, never direct model access
49: - Modules can be extracted into packages later without rewriting
50: - Shared kernel (`app/Shared/`) for cross-cutting concerns (planned)
51: 
52: ### 2. Domain-Driven Design
53: 
54: - Modules represent bounded contexts
55: - Aggregates enforce invariants within modules
56: - Domain events for cross-module communication
57: - Ubiquitous language in code (class/method naming)
58: 
59: ### 3. Strict Tenant Isolation
60: 
61: - Hybrid tenancy: shared database (`souda_shared` with `tenant_id` scoping) for free/starter plans, dedicated MySQL databases for enterprise plans
62: - Central database holds shared data (plans, subscriptions, users, tenants)
63: - Shared database holds tenant-scoped data (configs, settings, tasks)
64: - Dedicated tenant databases hold business data (products, orders, inventory, CRM)
65: - `InitializeTenancyByUser` middleware enforces tenant context
66: - `HasTenantScope` trait and `TenantScope` global scope provide shared-mode isolation
67: - Models use `CentralConnection` trait to explicitly stay central
68: - Queue jobs are tenant-aware via `QueueTenancyBootstrapper`
69: 
70: ### 4. Layered Architecture
71: 
72: | Layer | Location | Responsibility |
73: |-------|----------|----------------|
74: | **Presentation** | `app/Http/Controllers/`, `resources/js/` | HTTP handling, UI rendering, request validation |
75: | **Application** | `app/Actions/`, `app/Services/`, `app/DTOs/` | Use cases, orchestration, data transformation |
76: | **Domain** | `app/Modules/{Domain}/` | Business rules, entities, domain events |
77: | **Infrastructure** | `app/Modules/{Domain}/Drivers/`, `app/Providers/` | External integrations, service providers |
78: 
79: ### 5. SOLID Principles
80: 
81: - **Single Responsibility**: One class, one reason to change
82: - **Open/Closed**: Extend via interfaces, not modification
83: - **Liskov Substitution**: Interface implementations are interchangeable
84: - **Interface Segregation**: Small, focused contracts
85: - **Dependency Inversion**: Depend on abstractions, resolved via container
86: 
87: ## Core Architecture Components
88: 
89: ### Multi-Tenancy Strategy
90: 
91: | Aspect | Decision |
92: |--------|----------|
93: | **Mode** | Hybrid (shared DB + dedicated DB per enterprise tenant) |
94: | **Identification** | User-based (`InitializeTenancyByUser`) |
95: | **Database Naming** | Shared: `souda_shared`, Dedicated: `souda_tenant_{uuid}` |
96: | **Bootstrappers** | Database, Cache, Filesystem, Queue |
97: | **Central Data** | Users, tenants, plans, subscriptions, billing, settings, roles/permissions |
98: | **Shared Data** | Tenant configs, tenant settings, tasks (scoped by `tenant_id`) |
99: | **Tenant Data** | Products, orders, inventory, CRM, business operations |
100: 
101: ### Module Communication
102: 
103: | Pattern | Use Case |
104: |---------|----------|
105: | **Domain Events** | Cross-module notifications (e.g., OrderCreated → Inventory deduction) |
106: | **Service Interfaces** | Module-to-module queries (e.g., Billing queries Plan features) |
107: | **Action Classes** | Single-purpose operations callable across modules |
108: | **DTOs** | Data transfer between modules without exposing models |
109: 
110: ### Data Flow
111: 
112: ```
113: User Request → Middleware (Tenancy + Auth + Subscription)
114:   → Controller (validates via Form Request)
115:   → Action/Service (orchestrates use case)
116:   → Repository/Model (data access)
117:   → Domain Event dispatched (if applicable)
118:   → Listener handles side effects (async via queue)
119:   → Response (Inertia redirect/props or API Resource)
120: ```
121: 
122: ## Security Architecture
123: 
124: | Layer | Mechanism |
125: |-------|-----------|
126: | **Authentication** | Laravel Fortify (sessions) + Socialite (OAuth) |
127: | **Authorization** | Spatie Permission (roles/policies) + Feature middleware |
128: | **Tenant Isolation** | Database separation + middleware enforcement |
129: | **Subscription Gating** | `EnsureSubscribed` middleware + `PlanFeatureService` |
130: | **Input Validation** | Form Request classes + DTOs |
131: | **CSRF Protection** | Laravel CSRF + exempted webhook routes |
132: | **Rate Limiting** | Fortify + custom limits |
133: 
134: ## Scalability Considerations
135: 
136: | Concern | Strategy |
137: |---------|----------|
138: | **Horizontal Scaling** | Stateless app servers, shared Redis, separate DBs |
139: | **Queue Processing** | Redis driver, worker pools per queue priority |
140: | **Caching** | Redis with tenant-tagged cache |
141: | **Database** | Per-tenant isolation allows independent scaling |
142: | **Asset Delivery** | Vite build + CDN for static assets |
143: | **Session Storage** | Database-backed sessions |

(End of file - total 143 lines)
</content>
```

---

## Assistant (Plan · DeepSeek V4 Flash Free · 4.6s)

_Thinking:_

Now let me read some of the session files and other key documents to understand recent changes and the current state of the project.

**Tool: read**

**Input:**
```json
{
  "filePath": "C:\\dev\\souda\\session-ses_11c8.md"
}
```

**Output:**
```
<path>C:\dev\souda\session-ses_11c8.md</path>
<type>file</type>
<content>
1: # New session - 2026-06-20T05:19:25.466Z
2: 
3: **Session ID:** ses_11c87cee5ffeG0wQoY77d9TYmR
4: **Created:** 6/20/2026, 11:19:25 AM
5: **Updated:** 6/20/2026, 1:44:45 PM
6: 
7: ---
8: 
9: ## Assistant (Compaction · DeepSeek V4 Flash Free · 20.1s)
10: 
11: _Thinking:_
12: 
13: Let me analyze the conversation history and the previous summary to create an updated version.
14: 
15: The conversation history shows:
16: 1. The user asked me to rewrite `docs/dev.md` comprehensively
17: 2. I produced a very detailed `docs/dev.md` file covering the entire system
18: 
19: The previous summary already covered a lot. The key updates would be:
20: - The `docs/dev.md` has now been created/updated with comprehensive documentation
21: - No other changes in the conversation
22: 
23: Let me carefully merge the new facts while preserving what's still true.
24: 
25: Key new facts from this turn:
26: - `docs/dev.md` has been rewritten comprehensively (but this was already in "Done" in the previous summary)
27: 
28: Actually, looking more carefully at the previous summary, it already says "Created `docs/dev.md`" in Done. The conversation just confirms that I've now rewritten it comprehensively. So the updates are minimal - the document is now more complete.
29: 
30: Let me also re-read the previous summary carefully to see if anything needs updating. It's quite thorough and accurate. The only thing I might adjust is adding more detail about what the dev.md covers, but since it's already in Done, I should keep it simple.
31: 
32: Let me structure the update:
33: 
34: 1. **Goal** - keep the same
35: 2. **Constraints & Preferences** - keep the same
36: 3. **Done** - add that dev.md was updated with comprehensive detail
37: 4. **In Progress** - keep as none
38: 5. **Blocked** - keep as none
39: 6. **Key Decisions** - keep the same
40: 7. **Next Steps** - keep the same
41: 8. **Critical Context** - keep the same, maybe add a note about the comprehensive docs
42: 9. **Relevant Files** - keep the same
43: 
44: Actually, I think the main thing is just updating the Done section to note that `docs/dev.md` was rewritten comprehensively. Everything else stays the same.
45: 
46: Let me craft the updated summary.
47: 
48: ## Goal
49: - Design and implement a Business Type Engine for SOUDA that supports 15+ industry verticals without core module modifications, with a fully working hybrid tenant subscription and provisioning flow.
50: 
51: ## Constraints & Preferences
52: - Multi-tenant safe, modular monolith compatible.
53: - No hardcoded business type checks, no switch/case, no if-pharmacy-if-restaurant logic.
54: - Future industries addable via IndustryPack interface only.
55: - Must support feature flags, subscription plans, seat-based pricing.
56: - Existing product schema must remain stable; no per-industry tables.
57: - Avoid EAV anti-patterns; use typed columns for dynamic values.
58: - Only Enterprise plan gets a dedicated database; all others use shared `souda_shared` DB with tenant_id isolation.
59: - All plans except Free have trial-without-card (immediate activation); Free requires payment callback.
60: 
61: ## Progress
62: ### Done
63: - Analyzed existing product module weaknesses (rigid schema, no industry concept, hardcoded validation and forms).
64: - Designed full Business Type Engine architecture covering 20 design points.
65: - Created Phase 1 foundation: directory structure, central migrations (business_types, modules, business_type_module, tenants.business_type_id), tenant migrations (tenant_configs, tenant_module_overrides).
66: - Created central models (BusinessType, Module with CentralConnection), tenant models (TenantConfig, TenantModuleOverride with HasTenantScope).
67: - Created Value Objects (TenantConfig, ModuleDefinition), IndustryPack contract interface.
68: - Created Services (BusinessTypeEngine, IndustryPackRegistry, BusinessTypeConfigBuilder).
69: - Created seeders: 15 business types, 11 modules, all business-type-to-module mappings seeded.
70: - Created IndustryServiceProvider with TenantConfig DI binding; registered in bootstrap/providers.php.
71: - Updated tenancy config to include tenant migration path.
72: - Updated Tenant model with business_type relationship and getCustomColumns().
73: - Added tenant_config to Inertia shared data via HandleInertiaRequests middleware.
74: - Ran migrations and seeders successfully (all 15 business types, 11 modules verified).
75: - Created 15 Industry Packs (PharmacyPack, RestaurantPack, SalonPack, etc.) and registered in IndustryServiceProvider::boot().
76: - Ran Pint, ran tests — zero regressions (same 239 pre-existing failures with or without changes).
77: - Created `docs/dev.md` — comprehensive developer documentation covering auth, tenant system, product domain, billing, business type engine, and full architecture.
78: - Created `docs/agents.md` — agent-focused documentation with guardrails, file templates, gotchas, and verification checklists.
79: - Created `souda_shared` database and ran shared migrations (22 tables).
80: - Fixed `ProvisionTenantDatabase` — added `->orderBy('id')` to all `->each()` calls (Laravel 12 requirement).
81: - Tested all 4 subscription plans end-to-end via tinker.
82: - Rewrote `docs/dev.md` comprehensively — covers tech stack, architecture overview, database architecture, tenancy modes (shared/dedicated), directory structure, authentication stack (Fortify + custom actions + social auth), full tenant system with TenantManager/strategies/scopes/provisioning flow, product module (17 models, 4 controllers, 20 services, 14 events, 13 listeners, 3 observers, 4 policies), billing system (4 models, 6 gateways, 4 services, 9 events, 5 listeners), Business Type Engine summary, route map (6 files), middleware stack (7 custom), 4 key flows (registration, subscription, tenant init, provisioning), development setup, database schema summary (central/shared/dedicated), known issues (239 test failures, StockCalculationTest, PHP 8.4 anonymous migrations, orderBy requirement), service provider registration order, and events-to-listeners map.
83: 
84: ### In Progress
85: - (none)
86: 
87: ### Blocked
88: - (none)
89: 
90: ## Key Decisions
91: - Business type config built as immutable TenantConfig value object, cached in Laravel cache with 24h TTL + explicit invalidation.
92: - Industry packs registered via IndustryPack interface in dedicated service provider — zero core module changes per new industry.
93: - Module enablement resolved from pack definition with tenant override support.
94: - `resolveModules()` returns all modules (not just required ones) so the enabled list is complete.
95: - Central migrations use `Schema::create()` without explicit connection (default connection is central MySQL).
96: - Plan-to-mode mapping: free/starter/professional → shared, enterprise → dedicated.
97: - `trial_without_card` enabled for all plans — no payment required to start trial.
98: - PostgreSQL removed from tenancy database manager config (MySQL only).
99: 
100: ## Next Steps
101: - Fix the 239 pre-existing test failures (WarehouseStock circular boot in ProductServiceProvider::boot()).
102: - Handle $0 payment auto-activation for Free plan (ManualDriver returns no checkoutUrl — subscription stuck in pending_payment).
103: - Add BusinessType seeding into the tenant provisioning flow (assign business_type_id when tenant subscribes).
104: - Begin Phase 2: Frontend integration — consume tenant_config from Inertia shared data, render industry-specific menus/widgets.
105: 
106: ## Critical Context
107: - PHP 8.4.16 — typed properties in anonymous migrations conflict with parent Migration::$connection (untyped); use `Schema::create()` directly.
108: - Laravel 12 requires `->orderBy('id')` before `->each()` on query builder — applies to all chunk/each usage.
109: - `souda_shared` database must exist before any shared-mode tenant operations.
110: - 239 pre-existing test failures (WarehouseStock boot issue) — unrelated to Business Type Engine, exists on clean git checkout.
111: - 3 pre-existing StockCalculationTest failures (MySQL-specific).
112: - `plan_mode_map` in `config/tenancy.php` and `guessModeFromPlan()` in `TenantManager` determine shared vs dedicated per subscription.
113: - `ProvisionTenantDatabase` listener handles all DB provisioning: shared (insert tenant_settings), dedicated (create DB + migrate + copy shared data), upgrade (shared→dedicated), downgrade (dedicated→shared).
114: 
115: ## Relevant Files
116: - `app/Modules/BusinessType/Contracts/IndustryPack.php`: contract all packs implement
117: - `app/Modules/BusinessType/Services/BusinessTypeEngine.php`: config resolution with caching
118: - `app/Modules/BusinessType/Services/BusinessTypeConfigBuilder.php`: config merge pipeline (pack → plan → tenant overrides)
119: - `app/Modules/BusinessType/Services/IndustryPackRegistry.php`: pack registration and lookup
120: - `app/Modules/BusinessType/ValueObjects/TenantConfig.php`: immutable config DTO injected app-wide
121: - `app/Modules/BusinessType/Models/BusinessType.php`: central model for business types
122: - `app/Modules/BusinessType/Models/Module.php`: central model for modules
123: - `app/Modules/BusinessType/Packs/*.php`: all 15 industry pack implementations
124: - `app/Providers/IndustryServiceProvider.php`: registers packs + TenantConfig binding
125: - `app/Models/Tenant.php`: now has business_type_id + businessType() relationship
126: - `app/Http/Middleware/HandleInertiaRequests.php`: shares tenant_config to frontend
127: - `app/Listeners/ProvisionTenantDatabase.php`: handles all shared/dedicated DB provisioning
128: - `app/Tenancy/TenantManager.php`: hybrid mode resolution, shared/dedicated strategy dispatch
129: - `app/Tenancy/Modes/SharedMode.php`: configures shared database, cache, storage for shared tenants
130: - `app/Http/Middleware/InitializeTenancyByUser.php`: per-request tenant initialization from auth user
131: - `database/migrations/2026_06_20_000001_create_business_types_table.php`: central migration
132: - `database/migrations/shared/`: shared database migrations (tenant_settings, tasks, products, tenant_configs, tenant_module_overrides)
133: - `database/seeders/BusinessTypeSeeder.php`, `ModuleSeeder.php`, `BusinessTypeModuleSeeder.php`, `PlanSeeder.php`
134: - `config/tenancy.php`: plan_mode_map, shared_connection, central_domains, bootstrappers
135: - `config/billing.php`: gateways (stripe, sslcommerz, bkash, nagad, portwallet, manual), default gateway
136: - `config/social-auth.php`: social provider config
137: - `docs/dev.md`: comprehensive developer documentation (rewritten)
138: - `docs/agents.md`: agent-focused documentation with guardrails and gotchas
139: 
140: ---
141: 
142: ## User
143: 
144: ---
145: 
146: ## Assistant (Build · DeepSeek V4 Flash Free · 4.7s)
147: 
148: _Thinking:_
149: 
150: The user is asking me to continue with the next steps from the previous session. Let me look at the next steps listed:
151: 
152: 1. Fix the 239 pre-existing test failures (WarehouseStock circular boot in ProductServiceProvider::boot()).
153: 2. Handle $0 payment auto-activation for Free plan (ManualDriver returns no checkoutUrl — subscription stuck in pending_payment).
154: 3. Add BusinessType seeding into the tenant provisioning flow (assign business_type_id when tenant subscribes).
155: 4. Begin Phase 2: Frontend integration — consume tenant_config from Inertia shared data, render industry-specific menus/widgets.
156: 
157: Let me first understand the current state of the codebase by checking recent changes and the status of things. Let me look at the current state of the project.
158: 
159: **Tool: todowrite**
160: 
161: **Input:**
162: ```json
163: {
164:   "todos": [
165:     {
166:       "content": "Fix 239 pre-existing test failures (WarehouseStock circular boot in ProductServiceProvider::boot())",
167:       "status": "pending",
168:       "priority": "high"
169:     },
170:     {
171:       "content": "Handle $0 payment auto-activation for Free plan (ManualDriver)",
172:       "status": "pending",
173:       "priority": "high"
174:     },
175:     {
176:       "content": "Add BusinessType seeding into tenant provisioning flow",
177:       "status": "pending",
178:       "priority": "high"
179:     },
180:     {
181:       "content": "Begin Phase 2: Frontend integration with Inertia",
182:       "status": "pending",
183:       "priority": "medium"
184:     }
185:   ]
186: }
187: ```
188: 
189: **Output:**
190: ```
191: [
192:   {
193:     "content": "Fix 239 pre-existing test failures (WarehouseStock circular boot in ProductServiceProvider::boot())",
194:     "status": "pending",
195:     "priority": "high"
196:   },
197:   {
198:     "content": "Handle $0 payment auto-activation for Free plan (ManualDriver)",
199:     "status": "pending",
200:     "priority": "high"
201:   },
202:   {
203:     "content": "Add BusinessType seeding into tenant provisioning flow",
204:     "status": "pending",
205:     "priority": "high"
206:   },
207:   {
208:     "content": "Begin Phase 2: Frontend integration with Inertia",
209:     "status": "pending",
210:     "priority": "medium"
211:   }
212: ]
213: ```
214: 
215: ---
216: 
217: ## Assistant (Build · DeepSeek V4 Flash Free · 538.8s)
218: 
219: _Thinking:_
220: 
221: Let me start by investigating the first next step - fixing the 239 pre-existing test failures. I need to understand the root cause first - the WarehouseStock circular boot in ProductServiceProvider::boot(). Let me look at the relevant files.
222: 
223: Let me start by investigating the pre-existing test failures.
224: 
225: **Tool: task**
226: 
227: **Input:**
228: ```json
229: {
230:   "description": "Investigate test failures",
231:   "subagent_type": "explore",
232:   "prompt": "Very thorough investigation needed. I need to understand the root cause of pre-existing test failures in this Laravel project. \n\nKey known issue: \"239 pre-existing test failures — WarehouseStock circular boot in ProductServiceProvider::boot()\"\n\nPlease investigate:\n1. Read `app/Providers/ProductServiceProvider.php` fully\n2. Read the WarehouseStock model at `app/Modules/Product/Models/WarehouseStock.php` fully\n3. Look at how ProductServiceProvider boot method works and why it might cause circular boot with WarehouseStock\n4. Check if there's any relationship or event that creates a circular reference\n5. Look at `bootstrap/providers.php` to see the provider registration order\n6. Check `app/Modules/Product/Providers/` for any sub-providers\n7. Read `tests/Feature/ProductServiceProviderTest.php` if it exists, or any test file mentioning WarehouseStock or ProductServiceProvider\n8. Run `php artisan test --compact --filter=ProductServiceProvider 2>&1 | tail -60` to see current failure output\n9. Run `php artisan test --compact --filter=WarehouseStock 2>&1 | tail -60`\n10. Run `php artisan test --compact --filter=Stock 2>&1 | tail -60`\n11. Check if there are listeners or events that trigger during boot that involve WarehouseStock loading\n\nReturn all findings in detail - full file contents where relevant, test error messages, and your analysis of the circular boot issue."
233: }
234: ```
235: 
236: **Output:**
237: ```
238: task_id: ses_11c3759c8ffeswytfu5xjg79Xu (for resuming to continue this task if needed)
239: 
240: <task_result>
241: I now have the complete picture. Let me compile the full investigation findings.
242: 
243: ---
244: 
245: ## Complete Root Cause Analysis — 239 Pre-existing Test Failures
246: 
247: ### Test Execution Order (Source: `phpunit.xml` lines 8-13)
248: 
249: The PHPUnit configuration defines two test suites in order:
250: 
251: ```xml
252: <testsuite name="Unit">    <!-- Runs FIRST  -->
253:     <directory>tests/Unit</directory>
254: </testsuite>
255: <testsuite name="Feature"> <!-- Runs SECOND -->
256:     <directory>tests/Feature</directory>
257: </testsuite>
258: ```
259: 
260: This means **all Unit tests execute before any Feature tests**.
261: 
262: ### The Primary Trigger File
263: 
264: **`/Users/salam/Herd/souda/tests/Unit/Product/StockCalculationTest.php`** (lines 7-36)
265: 
266: This file contains 3 unit tests that instantiate `new WarehouseStock` directly — but since this is a **Unit test** (not extending `Tests\TestCase`, controlled by `tests/Pest.php` line 13: `->in('Feature')`), the Laravel application is **not booted**. The `App` facade is not available.
267: 
268: ### The Failing Trait
269: 
270: **`/Users/salam/Herd/souda/app/Tenancy/Models/Concerns/HasTenantScope.php`** (line 15)
271: 
272: ```php
273: use Illuminate\Support\Facades\App;  // The FACADE, not the helper
274: 
275: public static function bootHasTenantScope(): void
276: {
277:     static::addGlobalScope(App::make(TenantScope::class));  // LINE 15 — FAILS
278:     ...
279: }
280: ```
281: 
282: `App::make()` uses the `App` **facade**. In unit tests without a booted application, `Facade::getFacadeRoot()` returns `null`, producing:
283: 
284: > `RuntimeException: A facade root has not been set.`
285: 
286: ### The Contamination Mechanism
287: 
288: **`/Users/salam/Herd/souda/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php`** (lines 331-357)
289: 
290: The `bootIfNotBooted()` method:
291: 
292: ```php
293: protected function bootIfNotBooted()
294: {
295:     if (! isset(static::$booted[static::class])) {
296:         if (isset(static::$booting[static::class])) {
297:             throw new LogicException('...');   // LINE 335
298:         }
299:         static::$booting[static::class] = true;  // LINE 338 — SET DURING BOOT
300: 
301:         $this->fireModelEvent('booting', false);
302:         static::booting();
303:         static::boot();                          // Calls bootHasTenantScope() → THROWS
304:         // ... cleanup never reached ...
305:         static::$booted[static::class] = true;
306:         unset(static::$booting[static::class]);  // LINE 346 — NEVER REACHED
307:     }
308: }
309: ```
310: 
311: When `test 1` of StockCalculationTest runs:
312: 1. `new WarehouseStock` → `bootIfNotBooted()` → sets `$booting[WarehouseStock] = true`
313: 2. `boot()` → `bootHasTenantScope()` → `App::make(TenantScope::class)` → **`RuntimeException` thrown**
314: 3. `$booting[WarehouseStock]` is **never cleared** — **it stays `true` for the rest of the PHP process**
315: 
316: When `test 2` and `test 3` of StockCalculationTest run:
317: 1. `new WarehouseStock` → `bootIfNotBooted()` → checks `$booting[WarehouseStock]` → **still `true`!**
318: 2. **`LogicException: bootIfNotBooted may not be called on model while it is being booted`**
319: 
320: ### The Cascade to All 239 Feature Tests
321: 
322: When the Feature suite begins (e.g., `StockManagementTest`), each Feature test boots a new Laravel Application. During boot:
323: 
324: **`/Users/salam/Herd/souda/app/Providers/ProductServiceProvider.php`** (line 105)
325: 
326: ```php
327: public function boot(): void
328: {
329:     ...
330:     WarehouseStock::observe(WarehouseStockObserver::class);  // LINE 105
331:     ...
332: }
333: ```
334: 
335: `Model::observe()` internally calls `new static()` (line 76 of `vendor/laravel/framework/src/Illuminate/Database/Eloquent/Concerns/HasEvents.php`):
336: 
337: ```php
338: public static function observe($classes)
339: {
340:     $instance = new static;  // Creates new WarehouseStock
341:     ...
342: }
343: ```
344: 
345: But `$booting[WarehouseStock::class]` is **still `true`** from the earlier Unit test. This throws:
346: 
347: > `LogicException: The [Illuminate\Database\Eloquent\Model::bootIfNotBooted] method may not be called on model [App\Modules\Product\Models\WarehouseStock] while it is being booted.`
348: 
349: **All 239 Feature tests fail** — every single one — because the app cannot complete its boot sequence.
350: 
351: ### Additional Observations
352: 
353: **1. `clearBootedModels()` doesn't fix `$booting`:**
354: 
355: ```php
356: // vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:494
357: public static function clearBootedModels()
358: {
359:     static::$booted = [];
360:     static::$bootedCallbacks = [];
361:     static::$classAttributes = [];
362:     static::$globalScopes = [];
363:     // NOTE: static::$booting is NOT reset
364: }
365: ```
366: 
367: Even if called, `$booting` would remain stuck. This is an oversight in the current `tests/TestCase.php` which is also completely empty (no `setUp` logic).
368: 
369: **2. 17 models in the Product module use `HasTenantScope`** — any of them instantiated in a Unit test would cause the same contamination:
370: - `WarehouseStock`, `Product`, `Variant`, `StockReservation`, `Brand`, `Category`, `Warehouse`, `PricingRule`, `TaxCategory`, `TaxRate`, `ProductMedia`, `StockMovement`, `Attribute`, `AttributeValue`, `ProductAttributeValue`, `ProductAttributeTextValue`, `AuditLog`
371: 
372: **3. Separate latent bug — missing `use` import in `ProductServiceProvider.php`** (line 101):
373: ```php
374: Gate::policy(Warehouse::class, WarehousePolicy::class);
375: ```
376: The class `Warehouse` resolves to `App\Providers\Warehouse` (current namespace), NOT `App\Modules\Product\Models\Warehouse`. The import `use App\Modules\Product\Models\Warehouse;` is missing. However, `Gate::policy()` only stores a string and doesn't validate the class, so this bug is latent — it will only surface when a Warehouse authorization check is performed.
377: 
378: ### Summary of Fixes Required
379: 
380: | # | Problem | Fix Location | Fix Description |
381: |---|---------|-------------|-----------------|
382: | 1 | `HasTenantScope` uses `App::make()` facade instead of the `app()` helper | `app/Tenancy/Models/Concerns/HasTenantScope.php:15` | Replace `App::make(...)` with `app(...)` or `Container::getInstance()->make(...)` |
383: | 2 | `hasTenantScope` doesn't gracefully handle missing app container | `app/Tenancy/Models/Concerns/HasTenantScope.php:15` | Guard against null container with a conditional check |
384: | 3 | No reset of `Model::$booting` static state between tests | `tests/TestCase.php` | Add `Model::clearBootedModels()` in `setUp()`, plus explicit `Model::$booting = []` reset using reflection |
385: | 4 | `Model::clearBootedModels()` doesn't clear `$booting` | (Laravel core, but worked around in fix #3) | Reset `static::$booting = []` in the test's `setUp()` |
386: | 5 | Missing `Warehouse` import in `ProductServiceProvider` | `app/Providers/ProductServiceProvider.php` | Add `use App\Modules\Product\Models\Warehouse;` |
387: </task_result>
388: ```
389: 
390: **Tool: task**
391: 
392: **Input:**
393: ```json
394: {
395:   "description": "Investigate Free plan payment",
396:   "subagent_type": "explore",
397:   "prompt": "Very thorough investigation needed. I need to understand how the Free plan payment/subscription activation works, specifically the issue where \"$0 payment auto-activation for Free plan (ManualDriver returns no checkoutUrl — subscription stuck in pending_payment)\".\n\nPlease investigate:\n1. Read `config/billing.php` fully\n2. Read the ManualDriver class - search for it with glob `**/Manual*Driver*` or `**/Manual/**`\n3. Read the subscription flow - look at how subscriptions are created and activated\n4. Read `app/Services/Billing/` directory structure\n5. Find and read the main billing service/manager that handles subscription creation\n6. Look at how checkoutUrl is generated and what happens with $0 payments\n7. Check if there are payment-related events/listeners (look in `app/Listeners/` or `app/Providers/`)\n8. Read any listener or handler that processes payment callbacks or subscription activation\n9. Read the ProvisionTenantDatabase listener fully\n\nReturn all findings in detail - full file contents where relevant, and your analysis of the $0 payment / Free plan activation issue."
398: }
399: ```
400: 
401: **Output:**
402: ```
403: task_id: ses_11c374d53ffezNSTTmKi54JQKI (for resuming to continue this task if needed)
404: 
405: <task_result>
406: Here is my complete, thorough investigation and analysis.
407: 
408: ---
409: 
410: ## Investigation Findings: Free Plan / $0 Payment Auto-Activation Issue
411: 
412: ### 1. Configuration: `config/billing.php`
413: 
414: **File:** `/Users/salam/Herd/souda/config/billing.php` (128 lines)
415: 
416: Key settings:
417: - Default gateway: `env('BILLING_DEFAULT_GATEWAY', 'stripe')`
418: - Currency: BDT
419: - Six gateways registered: `stripe`, `sslcommerz`, `bkash`, `nagad`, `portwallet`, `manual`
420: - The `manual` gateway has an empty config array `'config' => []`
421: - Grace period: 3 days
422: 
423: ---
424: 
425: ### 2. ManualDriver Class
426: 
427: **File:** `/Users/salam/Herd/souda/app/Modules/Billing/Drivers/ManualDriver.php` (73 lines)
428: 
429: This is a key file. The `ManualDriver::createPayment()` method:
430: - Generates a transaction ID (`manual_<uniqid>`)
431: - Returns `PaymentDTO` with `status = PaymentStatus::Pending`
432: - **Critically:** Sets `checkout_url` to `$options['checkout_url'] ?? null`
433: 
434: ```php
435: public function createPayment(SubscriptionDTO $subscription, array $options = []): PaymentDTO
436: {
437:     $transactionId = $options['transaction_id'] ?? 'manual_'.uniqid();
438:     return PaymentDTO::fromArray([
439:         'transaction_id' => $transactionId,
440:         'gateway' => 'manual',
441:         'status' => PaymentStatus::Pending->value,
442:         'amount' => $subscription->amount,
443:         'currency' => $subscription->currency,
444:         'checkout_url' => $options['checkout_url'] ?? null,  // <-- THIS IS THE PROBLEM
445:     ]);
446: }
447: ```
448: 
449: The `verifyPayment()` is a no-op (returns whatever status is in the payload, defaulting to Pending). There is no callback/webhook mechanism for manual payments -- they rely entirely on admin action.
450: 
451: ---
452: 
453: ### 3. The Full Subscription Flow (Trace)
454: 
455: #### Step A: User clicks "Get Started" on the Free plan
456: **File:** `/Users/salam/Herd/souda/resources/js/pages/billing/index.tsx` (lines 173-200)
457: 
458: ```typescript
459: async function redirectToCheckout(url, data) {
460:     const response = await axios.post(url, data);
461:     if (response.data.checkout_url) {
462:         window.location.href = response.data.checkout_url;
463:         return { success: true };
464:     }
465:     // If no checkout_url, assume auto-activation success
466:     window.location.href = `${window.location.pathname}?checkout=success`;
467:     return { success: true };
468: }
469: ```
470: 
471: When `checkout_url` is `null`/falsy, it falls through and redirects to `billing?checkout=success`. This is the frontend's implicit assumption that a missing checkout URL means the subscription was activated.
472: 
473: #### Step B: `BillingController::subscribe()`
474: **File:** `/Users/salam/Herd/souda/app/Http/Controllers/BillingController.php` (lines 92-152)
475: 
476: ```php
477: $result = $this->subscriptionService->createSubscription(
478:     tenantId: $tenant->id,
479:     planId: $validated['plan_id'],
480:     gateway: $validated['gateway'],
481:     ...
482: );
483: return response()->json([
484:     'checkout_url' => $result['checkoutUrl'],
485:     'subscription_id' => $result['subscription']->id,
486: ]);
487: ```
488: 
489: The `options` passed to `createSubscription` do NOT include a `checkout_url` key. The `checkoutUrl` from the result is blindly passed through.
490: 
491: #### Step C: `SubscriptionService::createSubscription()` -- THE CRITICAL METHOD
492: **File:** `/Users/salam/Herd/souda/app/Modules/Billing/Services/SubscriptionService.php` (lines 33-131)
493: 
494: Here is the complete logic flow:
495: 
496: ```
497: 1. Calculate amount from plan:
498:    $amount = $plan->monthly_price  // For Free plan: 0
499: 
500: 2. Determine trial availability:
501:    $trialAvailable = $plan->trial_enabled && $tenant && !$tenant->trial_used
502: 
503: 3. Determine initial status:
504:    if ($trialAvailable && $plan->trial_without_card) {
505:        $status = Trial;       // Immediate activation (works)
506:    } else {
507:        $status = PendingPayment;  // Goes to payment flow (BROKEN for $0)
508:    }
509: 
510: 4. Create subscription record in the DB with $amount=0, status=pending_payment
511: 
512: 5. If status === Trial:
513:    - Activate immediately -> dispatch SubscriptionActivated
514:    - Return checkoutUrl = null  <-- THIS WORKS FINE
515: 
516: 6. If status === PendingPayment:
517:    - Create DTO and call $gatewayDriver->createPayment($dto, $options)
518:    - For ManualDriver: returns PaymentDTO with checkoutUrl=null
519:    - Return checkoutUrl = paymentDTO->checkoutUrl  <-- null
520: ```
521: 
522: **There is NO check for `$amount === 0` anywhere in this method.** A $0 subscription that doesn't take the trial path always goes through the payment gateway initiation, even though no actual payment is needed.
523: 
524: ---
525: 
526: ### 4. All Files in `app/Modules/Billing/`
527: 
528: **Directory:** `/Users/salam/Herd/souda/app/Modules/Billing/`
529: 
530: ```
531: Actions/          - CreateSubscription, ActivateSubscription, CancelSubscription
532: Contracts/        - BillingGatewayInterface, PricingStrategy
533: Drivers/          - ManualDriver, StripeDriver, SSLCommerzDriver, BKashDriver, NagadDriver, PortWalletDriver
534: DTOs/             - PaymentDTO, SubscriptionDTO, PlanDTO, SeatAllocationDTO, OverageInvoiceDTO
535: Enums/            - SubscriptionStatus, PaymentStatus, Gateway, BillingCycle, PricingModel, SeatType, SeatStatus
536: Events/           - PaymentReceived, PaymentFailed, SubscriptionActivated, SubscriptionCancelled, SubscriptionExpired, etc.
537: Exceptions/       - SubscriptionException, PaymentFailedException, etc.
538: Http/Middleware/  - EnsureSeatAvailable
539: Jobs/             - SyncSeatAllocations
540: Listeners/        - GenerateInvoice, SendSubscriptionNotification, RecalculateSeatUsage
541: Models/           - Subscription, Payment, Plan, SeatAllocation
542: Services/         - BillingManager, SubscriptionService, PaymentService, PlanService, InvoiceService, etc.
543: Strategies/       - FlatPricingStrategy, SeatPricingStrategy
544: Webhooks/         - WebhookHandler, StripeWebhookHandler, SSLCommerzWebhookHandler
545: ```
546: 
547: ---
548: 
549: ### 5. The Event/Listener Chain (Event registration)
550: 
551: **File:** `/Users/salam/Herd/souda/app/Providers/BillingServiceProvider.php` (lines 70-112)
552: 
553: The event-to-listener wiring:
554: 
555: | Event | Listener | Sync/Async |
556: |---|---|---|
557: | `SubscriptionActivated` | `ProvisionTenantDatabase` | **SYNC** |
558: | `SubscriptionActivated` | `SendSubscriptionNotification::handleSubscriptionActivated` | Sync |
559: | `SubscriptionExpired` | `SendSubscriptionNotification::handleSubscriptionExpired` | Sync |
560: | `PaymentReceived` | `SendSubscriptionNotification::handlePaymentReceived` | Sync |
561: | `PaymentFailed` | `SendSubscriptionNotification::handlePaymentFailed` | Sync |
562: | `SubscriptionCancelled` | `SendSubscriptionNotification::handleSubscriptionCancelled` | Sync |
563: | `PaymentReceived` | `GenerateInvoice` | **Async** (ShouldQueue) |
564: 
565: ---
566: 
567: ### 6. ProvisionTenantDatabase Listener
568: 
569: **File:** `/Users/salam/Herd/souda/app/Listeners/ProvisionTenantDatabase.php` (237 lines)
570: 
571: This listener:
572: - Is triggered by `SubscriptionActivated` event
573: - Determines the tenant mode (`dedicated` or `shared`) based on plan slug
574: - For dedicated: creates the tenant database, runs migrations, seeds defaults
575: - For shared: inserts tenant settings into the shared database
576: - Handles upgrades (shared -> dedicated) and downgrades (dedicated -> shared)
577: 
578: **This listener only fires when `SubscriptionActivated` is dispatched.** If the subscription stays in `pending_payment`, this listener never fires and the tenant database is never provisioned.
579: 
580: ---
581: 
582: ### 7. How checkoutUrl is Generated vs How It's Used
583: 
584: **All six drivers** implement `generateCheckoutUrl()` identically:
585: ```php
586: public function generateCheckoutUrl(PaymentDTO $payment): string
587: {
588:     return $payment->checkoutUrl ?? '';
589: }
590: ```
591: 
592: This method is **never called anywhere in the codebase**. I confirmed that `generateCheckoutUrl` is only defined in the interface and all drivers, but is never invoked. Instead, the `checkoutUrl` is passed through from the `PaymentDTO` created in `createPayment()`:
593: 
594: ```php
595: // In SubscriptionService::createPayment (called from SubscriptionService::createSubscription):
596: return [
597:     'subscription' => $subscription,
598:     'checkoutUrl' => $paymentDTO->checkoutUrl,  // Direct from DTO
599: ];
600: ```
601: 
602: For **ManualDriver**, `checkout_url` comes from `$options['checkout_url'] ?? null` -- and since `BillingController::subscribe()` never sets `checkout_url` in the options array, it is always `null`.
603: 
604: For **SSLCommerzDriver**, it properly sets `checkout_url` from the gateway API response:
605: ```php
606: 'checkout_url' => $checkoutUrl,  // From SSLCommerz GatewayPageURL
607: ```
608: 
609: ---
610: 
611: ### 8. Root Cause Analysis
612: 
613: The bug is in **`SubscriptionService::createSubscription()`** at `/Users/salam/Herd/souda/app/Modules/Billing/Services/SubscriptionService.php`, lines 89-131.
614: 
615: The method has two paths for non-trial subscriptions:
616: - **Lines 89-97:** Trial path (immediate activation) -- works correctly
617: - **Lines 99-131:** Payment gateway path -- assumes a real payment is needed
618: 
619: **There is no third path for $0 (free) subscriptions that are not on trial.**
620: 
621: The specific scenario that breaks:
622: 
623: 1. Free plan has `monthly_price = 0` and `trial_enabled = false` (or `trial_without_card = false`)
624: 2. `$trialAvailable` is false
625: 3. `$status` is set to `PendingPayment`
626: 4. `ManualDriver::createPayment()` returns a payment DTO with `checkout_url = null` and `status = Pending`
627: 5. The frontend sees `checkout_url` is null and redirects to `?checkout=success`
628: 6. **But the subscription remains in `pending_payment` status** -- nothing ever fires `SubscriptionActivated`
629: 7. The `ProvisionTenantDatabase` listener never runs
630: 8. The tenant database is never created
631: 9. The tenant is stuck
632: 
633: ---
634: 
635: ### 9. Summary of All Relevant Files
636: 
637: | File | Purpose |
638: |---|---|
639: | `/Users/salam/Herd/souda/config/billing.php` | Billing config, gateway definitions |
640: | `/Users/salam/Herd/souda/app/Modules/Billing/Services/SubscriptionService.php` | **Core file with the bug** (line 89-97 checks trial but not $0) |
641: | `/Users/salam/Herd/souda/app/Modules/Billing/Drivers/ManualDriver.php` | Manual gateway driver -- returns null checkoutUrl |
642: | `/Users/salam/Herd/souda/app/Modules/Billing/Contracts/BillingGatewayInterface.php` | Interface defining createPayment, verifyPayment, etc. |
643: | `/Users/salam/Herd/souda/app/Modules/Billing/DTOs/PaymentDTO.php` | Data transfer object with `checkoutUrl` field |
644: | `/Users/salam/Herd/souda/app/Modules/Billing/DTOs/SubscriptionDTO.php` | DTO used to pass subscription data to drivers |
645: | `/Users/salam/Herd/souda/app/Modules/Billing/Services/BillingManager.php` | Gateway driver resolver |
646: | `/Users/salam/Herd/souda/app/Modules/Billing/Services/PaymentService.php` | Payment recording and completion |
647: | `/Users/salam/Herd/souda/app/Http/Controllers/BillingController.php` | HTTP controller -- initiates subscription, handles callbacks |
648: | `/Users/salam/Herd/souda/app/Providers/BillingServiceProvider.php` | Event-to-listener wiring |
649: | `/Users/salam/Herd/souda/app/Listeners/ProvisionTenantDatabase.php` | Provisions tenant DB on `SubscriptionActivated` |
650: | `/Users/salam/Herd/souda/app/Modules/Billing/Events/SubscriptionActivated.php` | Event dispatched when a subscription is activated |
651: | `/Users/salam/Herd/souda/app/Modules/Billing/Events/PaymentReceived.php` | Event dispatched when a payment is completed |
652: | `/Users/salam/Herd/souda/app/Modules/Billing/Models/Subscription.php` | Subscription model with `PendingPayment`, `Trial`, `Active` statuses |
653: | `/Users/salam/Herd/souda/app/Modules/Billing/Models/Plan.php` | Plan model with `monthly_price`, `yearly_price`, `trial_enabled`, `trial_without_card` |
654: | `/Users/salam/Herd/souda/app/Modules/Billing/Enums/SubscriptionStatus.php` | Enum with `PendingPayment`, `Trial`, `Active`, etc. |
655: | `/Users/salam/Herd/souda/resources/js/pages/billing/index.tsx` | Frontend billing page -- handles checkout URL redirect |
656: | `/Users/salam/Herd/souda/database/factories/PlanFactory.php` | Factory with a `free()` state setting `monthly_price => 0` |
657: | `/Users/salam/Herd/souda/tests/Feature/Billing/SubscriptionPaymentFlowTest.php` | Tests for SSLCommerz callback flow (no Free plan tests exist) |
658: 
659: ---
660: 
661: ### 10. Conclusion and Fix Recommendation
662: 
663: The fix should be added to `SubscriptionService::createSubscription()` in `/Users/salam/Herd/souda/app/Modules/Billing/Services/SubscriptionService.php`, after the trial check (line 97) and before the payment gateway initiation (line 99).
664: 
665: A check for `$amount === 0` should immediately activate the subscription, just like the trial path does:
666: 
667: ```php
668: // In SubscriptionService::createSubscription(), add after line 97:
669: // If amount is zero (free plan), activate immediately without payment.
670: if ($amount === 0) {
671:     $this->activateSubscription($subscription);
672:     return [
673:         'subscription' => $subscription,
674:         'checkoutUrl' => null,
675:     ];
676: }
677: ```
678: 
679: This ensures that:
680: - The subscription goes directly to `Active` status (skipping `PendingPayment`)
681: - `SubscriptionActivated` is dispatched
682: - `ProvisionTenantDatabase` fires and provisions the tenant database
683: - The frontend receives a null `checkout_url` and redirects to `?checkout=success`, which is consistent with its existing behavior
684: </task_result>
685: ```
686: 
687: **Tool: task**
688: 
689: **Input:**
690: ```json
691: {
692:   "description": "Investigate tenant provisioning",
693:   "subagent_type": "explore",
694:   "prompt": "Very thorough investigation needed. I need to understand how tenant provisioning works, specifically to add BusinessType seeding into the flow (assign business_type_id when tenant subscribes).\n\nPlease investigate:\n1. Read `app/Listeners/ProvisionTenantDatabase.php` fully\n2. Read the subscription creation flow - how tenants are created when a subscription is made\n3. Look at how business_type_id is currently assigned to tenants\n4. Read the Tenant model (`app/Models/Tenant.php`) fully\n5. Read any subscription-related models (search for `app/Models/Subscription.php`, `app/Modules/Subscription/`, etc.)\n6. Read the subscription seeder/listener that triggers tenant creation\n7. Look at `app/Tenancy/TenantManager.php` fully\n8. Read how plans are linked to business types\n9. Read `database/seeders/BusinessTypeSeeder.php` and `database/seeders/PlanSeeder.php`\n\nReturn all findings in detail - full file contents where relevant, and your analysis of how business_type_id should flow from plan selection to tenant creation/provisioning."
695: }
696: ```
697: 
698: **Output:**
699: ```
700: task_id: ses_11c37437cffeaCWGznBNEqul12 (for resuming to continue this task if needed)
701: 
702: <task_result>
703: Now I have a comprehensive picture. Here is my complete analysis:
704: 
705: ---
706: 
707: ## Complete Investigation: Tenant Provisioning & `business_type_id` Flow
708: 
709: ### 1. File Contents and Structure
710: 
711: #### `app/Listeners/ProvisionTenantDatabase.php` (237 lines)
712: This is the central listener for `SubscriptionActivated`. It handles tenant provisioning logic:
713: 
714: - **Line 18**: Listens for `SubscriptionActivated` event
715: - **Line 21**: Fetches `$subscription->tenant` (the tenant)
716: - **Line 27**: Gets plan slug from subscription (`$planSlug = $subscription->plan?->slug ?? 'free'`)
717: - **Line 31**: Calls `$manager->guessModeFromPlan($planSlug)` to determine shared vs. dedicated
718: - **Lines 33-49**: Routes to `upgradeToDedicated()`, `downgradeToShared()`, `provisionSharedTenant()`, or `provisionDedicatedTenant()`
719: - **Line 218-236**: `seedTenantDefaults()` runs migrations and seeds `tenant_settings` defaults
720: 
721: **CRITICAL**: This method handles provisioning of databases, but **never touches `business_type_id`** on the tenant. The business type is not passed through at any point.
722: 
723: ---
724: 
725: #### `app/Models/Tenant.php` (109 lines)
726: The `Tenant` model inherits from `Stancl\Tenancy\Database\Models\Tenant`:
727: 
728: - **Line 29**: `@property int|null $business_type_id` — nullable column exists
729: - **Line 46**: `'business_type_id'` — listed in `getCustomColumns()`
730: - **Line 92-95**: Has `businessType()` relationship: `return $this->belongsTo(BusinessType::class, 'business_type_id');`
731: - **Line 97-103**: has `activeSubscription()` helper to get the latest accessible subscription
732: 
733: The model is ready for `business_type_id` — the column, the relationship, and the cast are all in place.
734: 
735: ---
736: 
737: #### `app/Modules/Billing/Models/Subscription.php` (133 lines)
738: - Uses `CentralConnection` (lives in central DB)
739: - **Line 40-43**: `plan()` relationship to `Plan`
740: - **Line 48-51**: `tenant()` relationship to `Tenant`
741: - **Line 72-79**: `scopeAccessible()` — filters to Trial, Active, Grace statuses
742: 
743: No `business_type_id` or business type involvement at all.
744: 
745: ---
746: 
747: #### `app/Modules/Billing/Models/Plan.php` (63 lines)
748: - `CentralConnection`, lives in central DB
749: - Has `subscriptions()` HasMany relationship
750: - Has scopes: `active()`, `ordered()`, `bySlug()`
751: - **NO relationship to BusinessType** — no `business_type_id`, no pivot table, nothing linking plans to business types
752: - Columns: `name`, `slug`, `monthly_price`, `yearly_price`, `currency`, `features` (array), `limits` (array), trial fields, etc.
753: 
754: ---
755: 
756: #### `app/Modules/Billing/Services/SubscriptionService.php` (319 lines)
757: The core subscription orchestration class:
758: 
759: - **Lines 33-131**: `createSubscription()` — creates subscription record, optionally initiates payment. Parameters: `$tenantId`, `$planId`, `$gateway`, `$billingCycle`, `$options`. **No business type parameter**.
760: - **Lines 136-162**: `activateSubscription()` — transitions subscription to Active, sets `trial_used = true` on tenant, dispatches `SubscriptionActivated`. **No business type handling**.
761: - **Lines 167-216**: `verifyAndActivate()` — verifies payment via gateway, activates subscription. **No business type handling**.
762: 
763: ---
764: 
765: #### `app/Modules/Billing/Actions/CreateSubscription.php` (53 lines)
766: Thin action wrapper around `SubscriptionService::createSubscription()`. Same parameters. **No business type**.
767: 
768: ---
769: 
770: #### `app/Http/Controllers/BillingController.php` (364 lines)
771: - **Lines 92-152**: `subscribe()` method — validates `plan_id`, `gateway`, `billing_cycle` from request. Calls `SubscriptionService::createSubscription()`. **No `business_type_id` in validation rules, no business type sent.**
772: - **Lines 218-242**: `callback()` — payment verification callback. **No business type.**
773: - Lines 62-89: `index()` returns plans to frontend with `transformPlans()`. No business type data on plans.
774: 
775: ---
776: 
777: #### `app/Tenancy/TenantManager.php` (94 lines)
778: - **Lines 88-93**: `guessModeFromPlan(string $planSlug)` — looks up `config('tenancy.plan_mode_map')` to determine shared vs dedicated mode based on plan slug.
779: - The plan_mode_map in config/tenancy.php (line 51-56):
780: ```php
781: 'plan_mode_map' => [
782:     'free' => 'shared',
783:     'starter' => 'shared',
784:     'professional' => 'shared',
785:     'enterprise' => 'dedicated',
786: ],
787: ```
788: 
789: ---
790: 
791: #### `app/Modules/BusinessType/Models/BusinessType.php` (52 lines)
792: - Has `CentralConnection`, lives in central DB
793: - Fillable: `slug`, `name`, `description`, `icon`, `is_active`, `pack_class`, `config_template`, `metadata`
794: - **Line 49-51**: `tenants()` relationship: `return $this->hasMany(Tenant::class, 'business_type_id');`
795: - Has `modules()` and `requiredModules()` many-to-many relationships
796: 
797: ---
798: 
799: #### `app/Modules/BusinessType/Services/BusinessTypeEngine.php` (89 lines)
800: **This is the only place that sets `business_type_id`**:
801: 
802: - **Lines 64-83**: `assignBusinessType(Tenant $tenant, string $businessTypeSlug)` — looks up BusinessType by slug, assigns `$tenant->business_type_id`, saves, triggers `$pack->onTenantAssigned($tenant)`, and rebuilds config
803: 
804: **But this method is NEVER CALLED anywhere in the entire codebase** — it's dead code.
805: 
806: ---
807: 
808: #### `app/Modules/BusinessType/Services/BusinessTypeConfigBuilder.php` (120 lines)
809: - `build(Tenant $tenant)` — builds config from `$tenant->businessType` (the relationship). If null, falls back to `buildDefaultConfig()` which uses `'general'` as the business type.
810: - This is used for rendering the tenant config in Inertia (via `IndustryServiceProvider` → `TenantConfig` binding)
811: 
812: ---
813: 
814: ### 2. Database Schema
815: 
816: From migration `2026_06_20_000004_add_business_type_id_to_tenants_table.php`:
817: ```php
818: Schema::table('tenants', function (Blueprint $table) {
819:     $table->foreignId('business_type_id')
820:         ->nullable()
821:         ->constrained('business_types')
822:         ->nullOnDelete();
823: });
824: ```
825: The column exists, is nullable, and has a proper foreign key.
826: 
827: ---
828: 
829: ### 3. Complete Subscription → Provisioning Flow
830: 
831: Here is the full flow with all the relevant files:
832: 
833: ```
834: 1. User registers
835:    → CreateNewUser.php: Tenant::create(['name' => ...]) -- NO business_type_id
836:    → CreateSocialUser.php: Tenant::create(['name' => ...]) -- NO business_type_id
837: 
838: 2. User goes to /billing
839:    → BillingController::index() -- shows plans (no business type context)
840:    → TransformsPlansForFrontend.php -- transforms plans for frontend (no business type)
841:    → Plan model has NO business_type_id or business type relationship
842: 
843: 3. User selects a plan and clicks Subscribe
844:    → Frontend sends: { plan_id, gateway, billing_cycle }
845:    → NO business_type_id in the request
846: 
847: 4. BillingController::subscribe()
848:    → Validation: plan_id, gateway, billing_cycle (no business_type_id)
849:    → SubscriptionService::createSubscription(tenantId, planId, gateway, billingCycle, options)
850:    → Creates Subscription record -- NO business_type_id on subscription or tenant
851: 
852: 5. If trial without card:
853:    → SubscriptionService::activateSubscription()
854:    → Dispatches SubscriptionActivated
855: 
856: 6. ProvisionTenantDatabase::handle(SubscriptionActivated)
857:    → Gets $subscription->tenant
858:    → Gets $subscription->plan->slug
859:    → Determines shared vs dedicated mode
860:    → Provisions database (create, migrate, seed defaults)
861:    → NEVER touches business_type_id on the tenant
862: ```
863: 
864: ### 4. Gap Analysis: Where `business_type_id` Should Be Set
865: 
866: **Current state**: `business_type_id` is never assigned anywhere in the actual subscription flow. The `BusinessTypeEngine::assignBusinessType()` method exists but is dead code.
867: 
868: **Key observations**:
869: 
870: | Component | Has business_type awareness? | Notes |
871: |-----------|------------------------------|-------|
872: | `Tenant` model | Yes | Column, relationship, cast all exist |
873: | `BusinessType` model | Yes | Has `tenants()` relationship |
874: | `Plan` model | **No** | No relationship to BusinessType |
875: | `BusinessTypeEngine::assignBusinessType()` | Yes | But **never called** |
876: | `SubscriptionService` | **No** | No business type parameter |
877: | `BillingController::subscribe()` | **No** | No business type in validation/params |
878: | `ProvisionTenantDatabase` | **No** | Only provisions DB, no business type |
879: | `CreateNewUser` / `CreateSocialUser` | **No** | Tenant created without business type |
880: | `TenantFactory` | **No** | No business_type_id in factory |
881: | Frontend billing page | **No** | Only sends plan_id, gateway, billing_cycle |
882: | PlanSeeder | **No** | Plans have no business type link |
883: | Register page | **No** | No business type selection during signup |
884: 
885: ### 5. Recommended Flow for `business_type_id` Seeding
886: 
887: Based on the codebase architecture, `business_type_id` should flow like this:
888: 
889: ```
890: Option A: Set during subscription (on provisioning)
891: ====================================================
892: BillingController::subscribe(request)
893:   → validate plan_id, gateway, billing_cycle, BUSINESS_TYPE_ID
894:   → SubscriptionService::createSubscription(..., businessTypeSlug)
895:     → Creates subscription as today
896:   → ProvisionTenantDatabase::handle()
897:     → After provisioning, call:
898:       app(BusinessTypeEngine::class)->assignBusinessType($tenant, $businessTypeSlug)
899:     → This sets tenant->business_type_id, triggers pack, rebuilds config
900: 
901: Option B: Set during registration (before subscription)
902: =========================================================
903: Register page includes business type picker
904: CreateNewUser::create()
905:   → Tenant::create(['name' => ..., 'business_type_id' => $businessTypeId])
906: 
907: Option C: Plan-to-BusinessType mapping
908: =========================================
909: Add business_type_id or a pivot to plans:
910:   PlanSeeder adds business_type_id to each plan
911:   Plans with business types can auto-assign on subscription
912:   In ProvisionTenantDatabase:
913:     $businessType = $subscription->plan->businessType;
914:     if ($businessType) {
915:         app(BusinessTypeEngine::class)->assignBusinessType($tenant, $businessType->slug);
916:     }
917: ```
918: 
919: **The cleanest integration point** is in `ProvisionTenantDatabase::handle()`. After the database is provisioned (or during provisioning), you would add:
920: 
921: ```php
922: // In ProvisionTenantDatabase::handle(), after line 49:
923: $businessTypeSlug = $this->resolveBusinessTypeFromPlan($planSlug);
924: if ($businessTypeSlug) {
925:     app(\App\Modules\BusinessType\Services\BusinessTypeEngine::class)
926:         ->assignBusinessType($tenant, $businessTypeSlug);
927: }
928: ```
929: 
930: But this requires knowing which business type slug maps to which plan. Currently there is no such mapping anywhere — the `Plan` model has no relationship to `BusinessType`.
931: 
932: **To fully close the gap, you need to**:
933: 1. Add a `business_type_id` (or a pivot) to the `Plan` model so each plan can be associated with a business type
934: 2. Or add `business_type_id` to the subscription request payload so the user selects it during checkout
935: 3. In `ProvisionTenantDatabase::handle()`, after provisioning, call `BusinessTypeEngine::assignBusinessType()` with the resolved business type
936: </task_result>
937: ```
938: 
939: ---
940: 
941: ## Assistant (Build · DeepSeek V4 Flash Free · 5.7s)
942: 
943: _Thinking:_
944: 
945: Excellent! Now I have a comprehensive understanding of all three issues. Let me prioritize and start fixing them:
946: 
947: 1. **Fix 239 pre-existing test failures** - This is the highest priority because it blocks all future work. The root cause is:
948:    - `HasTenantScope` uses `App::make()` facade which fails in unit tests
949:    - `Model::$booting` static state leaks between tests (Laravel bug: `clearBootedModels()` doesn't clear `$booting`)
950:    - Missing `Warehouse` import in `ProductServiceProvider.php`
951: 
952: 2. **Handle $0 payment auto-activation for Free plan** - Add a check for `$amount === 0` in `SubscriptionService::createSubscription()` to immediately activate without going through payment gateway.
953: 
954: 3. **Add BusinessType seeding into tenant provisioning flow** - Add business_type_id assignment in `ProvisionTenantDatabase` listener.
955: 
956: Let me start with the test failures fix since it's blocking. I need to read the actual files to make the edits.
957: 
958: Now I have a clear picture. Let me start fixing the issues. First, the test failures (blocking everything else).
959: 
960: **Tool: todowrite**
961: 
962: **Input:**
963: ```json
964: {
965:   "todos": [
966:     {
967:       "priority": "high",
968:       "content": "Fix 239 pre-existing test failures (HasTenantScope App::make + Model::$booting leak)",
969:       "status": "in_progress"
970:     },
971:     {
972:       "priority": "high",
973:       "content": "Handle $0 payment auto-activation for Free plan (ManualDriver)",
974:       "status": "pending"
975:     },
976:     {
977:       "priority": "high",
978:       "content": "Add BusinessType seeding into tenant provisioning flow",
979:       "status": "pending"
980:     },
981:     {
982:       "priority": "medium",
983:       "content": "Begin Phase 2: Frontend integration with Inertia",
984:       "status": "pending"
985:     }
986:   ]
987: }
988: ```
989: 
990: **Output:**
991: ```
992: [
993:   {
994:     "content": "Fix 239 pre-existing test failures (HasTenantScope App::make + Model::$booting leak)",
995:     "status": "in_progress",
996:     "priority": "high"
997:   },
998:   {
999:     "content": "Handle $0 payment auto-activation for Free plan (ManualDriver)",
1000:     "status": "pending",
1001:     "priority": "high"
1002:   },
1003:   {
1004:     "content": "Add BusinessType seeding into tenant provisioning flow",
1005:     "status": "pending",
1006:     "priority": "high"
1007:   },
1008:   {
1009:     "content": "Begin Phase 2: Frontend integration with Inertia",
1010:     "status": "pending",
1011:     "priority": "medium"
1012:   }
1013: ]
1014: ```
1015: 
1016: ---
1017: 
1018: ## Assistant (Build · DeepSeek V4 Flash Free · 4.0s)
1019: 
1020: _Thinking:_
1021: 
1022: Let me read the files I need to edit.
1023: 
1024: **Tool: read**
1025: 
1026: **Input:**
1027: ```json
1028: {
1029:   "filePath": "/Users/salam/Herd/souda/app/Tenancy/Models/Concerns/HasTenantScope.php"
1030: }
1031: ```
1032: 
1033: **Output:**
1034: ```
1035: <path>/Users/salam/Herd/souda/app/Tenancy/Models/Concerns/HasTenantScope.php</path>
1036: <type>file</type>
1037: <content>
1038: 1: <?php
1039: 2: 
1040: 3: namespace App\Tenancy\Models\Concerns;
1041: 4: 
1042: 5: use App\Models\Tenant;
1043: 6: use App\Tenancy\Scopes\TenantScope;
1044: 7: use App\Tenancy\TenantManager;
1045: 8: use Illuminate\Database\Eloquent\Relations\BelongsTo;
1046: 9: use Illuminate\Support\Facades\App;
1047: 10: 
1048: 11: trait HasTenantScope
1049: 12: {
1050: 13:     public static function bootHasTenantScope(): void
1051: 14:     {
1052: 15:         static::addGlobalScope(App::make(TenantScope::class));
1053: 16: 
1054: 17:         static::creating(function ($model) {
1055: 18:             $manager = App::make(TenantManager::class);
1056: 19: 
1057: 20:             if ($manager->initialized() && $manager->isShared() && ! $model->tenant_id) {
1058: 21:                 $model->tenant_id = $manager->id();
1059: 22:             }
1060: 23:         });
1061: 24:     }
1062: 25: 
1063: 26:     public function tenant(): BelongsTo
1064: 27:     {
1065: 28:         return $this->belongsTo(Tenant::class, 'tenant_id');
1066: 29:     }
1067: 30: }
1068: 
1069: (End of file - total 30 lines)
1070: </content>
1071: ```
1072: 
1073: **Tool: read**
1074: 
1075: **Input:**
1076: ```json
1077: {
1078:   "filePath": "/Users/salam/Herd/souda/app/Providers/ProductServiceProvider.php"
1079: }
1080: ```
1081: 
1082: **Output:**
1083: ```

(Output capped at 50 KB. Showing lines 1-1083. Use offset=1084 to continue.)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "C:\\dev\\souda\\docs\\dev.md"
}
```

**Output:**
```
<path>C:\dev\souda\docs\dev.md</path>
<type>file</type>
<content>
1: # SOUDA — Developer Documentation
2: 
3: ## Overview
4: 
5: SOUDA is a multi-tenant, multi-vertical business management platform built with Laravel 12, Inertia.js v2 (React), and a hybrid tenancy architecture. It supports 16 industry verticals through a pluggable Business Type Engine with an onboarding wizard.
6: 
7: **Tech Stack:**
8: - PHP 8.4.16, Laravel 12
9: - React 19 + Inertia.js v2 + Tailwind CSS v4
10: - MySQL (central + shared + per-tenant databases)
11: - stancl/tenancy v3 (multi-database mode)
12: - Laravel Fortify v1 (authentication)
13: - Laravel Cashier (Stripe integration)
14: - Spatie Laravel Permission (roles)
15: - Laravel Scout + Algolia (search)
16: 
17: ---
18: 
19: ## Architecture Overview
20: 
21: ```
22: ┌─────────────────────────────────────────────────────────────────────┐
23: │                         HTTP Request                                │
24: └─────────────────────────────────────────────────────────────────────┘
25:                               │
26:                     ┌─────────▼─────────┐
27:                     │   Middleware Stack │
28:                     │  web, auth, tenancy│
29:                     │  subscription, ... │
30:                     └─────────┬─────────┘
31:                               │
32:               ┌───────────────┼───────────────┐
33:               │               │               │
34:               ▼               ▼               ▼
35:      ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
36:      │  Central DB  │ │  Shared DB   │ │  Dedicated   │
37:      │   (souda)    │ │ (souda_shared)│ │  Tenant DBs  │
38:      │              │ │              │ │ (souda_tenant)│
39:      │  users       │ │  tasks       │ │  products     │
40:      │  tenants     │ │  tenant_     │ │  inventory    │
41:      │  billing     │ │  settings    │ │  orders       │
42:      │  business_   │ │  tenant_     │ │  ...          │
43:      │  types       │ │  configs     │ │               │
44:      └──────────────┘ └──────────────┘ └──────────────┘
45: ```
46: 
47: ### Database Architecture
48: 
49: | Connection | Database | Purpose |
50: |---|---|---|
51: | `central` | `souda` | Users, tenants, billing, business types, roles |
52: | `shared` | `souda_shared` | Shared-mode tenant data (tenant_settings, tasks) |
53: | `mysql` | (template) | Template connection for dedicated tenant DBs |
54: 
55: ### Tenancy Modes
56: 
57: | Mode | Database | Isolation | Plans |
58: |---|---|---|---|
59: | `shared` | `souda_shared` | `tenant_id` column | Free, Starter, Professional |
60: | `dedicated` | `souda_tenant_{uuid}` | Separate database | Enterprise |
61: 
62: **Plan-to-Mode Mapping** (`config/tenancy.php`):
63: ```php
64: 'plan_mode_map' => [
65:     'free'         => 'shared',
66:     'starter'      => 'shared',
67:     'professional' => 'shared',
68:     'enterprise'   => 'dedicated',
69: ],
70: ```
71: 
72: ---
73: 
74: ## Directory Structure
75: 
76: ```
77: app/
78: ├── Actions/
79: │   ├── Auth/          (CreateSocialUser)
80: │   └── Fortify/       (CreateNewUser, ResetUserPassword)
81: ├── Console/Commands/  (ExpireSubscriptions, ImportStripeData, etc.)
82: ├── Events/            (TenantModeChanged)
83: ├── Http/
84: │   ├── Controllers/
85: │   │   ├── Admin/     (Users, Plans, Settings)
86: │   │   ├── Auth/      (SocialAuthController)
87: │   │   ├── BillingController
88: │   │   ├── Settings/  (Profile, Password, 2FA, ConnectedAccounts)
89: │   │   ├── TaskController
90: │   │   └── TeamController
91: │   ├── Middleware/    (7 custom middleware)
92: │   ├── Requests/     (Form requests)
93: │   └── Responses/    (RegisterResponse, LoginResponse)
94: ├── Jobs/              (MigrateTenantToDedicated/Shared)
95: ├── Listeners/         (ProvisionTenantDatabase, StripeEventListener)
96: ├── Models/            (User, Tenant, TenantSetting, Role, Permission, etc.)
97: ├── Modules/
98: │   ├── Billing/       (Plans, Subscriptions, Payments, Gateways)
99: │   ├── BusinessType/  (Industry packs, config engine)
100: │   ├── CRM/           (In progress)
101: │   ├── Inventory/     (In progress)
102: │   ├── Onboarding/    (TenantTemplates, ProvisioningPipeline, steps, events)
103: │   ├── Order/         (Events only)
104: │   ├── Product/       (Products, variants, stock, categories, brands)
105: │   └── Shared/        (DTOs, contracts, traits)
106: ├── Providers/         (8 service providers)
107: ├── Services/          (SocialAuthService, BillingEmailService)
108: └── Tenancy/
109:     ├── Contracts/     (TenantModeStrategy)
110:     ├── Modes/         (SharedMode, DedicatedMode)
111:     ├── Scopes/        (TenantScope)
112:     └── TenantManager.php
113: 
114: bootstrap/
115: ├── app.php           (Middleware, route groups)
116: └── providers.php     (Service provider registration)
117: 
118: config/
119: ├── tenancy.php       (Hybrid mode config, plan_mode_map)
120: ├── billing.php       (Gateways, currency)
121: ├── fortify.php       (Auth features)
122: ├── social-auth.php   (Social providers)
123: ├── permission.php    (Spatie roles)
124: └── database.php      (Central, shared, mysql connections)
125: 
126: database/
127: ├── migrations/           (~35 central migrations)
128: ├── migrations/tenant/    (2 tenant migrations)
129: ├── migrations/shared/    (2 shared + product + business type migrations)
130: └── seeders/              (All seeders, business types, plans, modules)
131: 
132: resources/js/
133: ├── pages/
134: │   ├── auth/             (login, register, forgot-password, etc.)
135: │   ├── billing/          (plan selection, checkout)
136: │   ├── onboarding/       (business-type picker, provisioning progress)
137: │   └── dashboard/
138: ├── components/
139: │   ├── app-sidebar.tsx   (dynamic module-based sidebar)
140: │   ├── module-nav-items.ts (11-module nav map)
141: │   └── ui/               (shadcn/ui components)
142: ├── hooks/
143: │   ├── use-tenant-config.ts (useTenantConfig, useModuleEnabled, useEnabledModules)
144: │   └── ...
145: ├── modules/
146: │   └── dashboard/
147: │       └── components/
148: │           └── industry-widgets.tsx (16-industry greeting + quick actions)
149: ├── layouts/
150: │   ├── auth-layout.tsx
151: │   └── app-layout.tsx
152: 
153: routes/
154: ├── web.php           (Home, social auth, billing)
155: ├── admin.php         (Admin panel)
156: ├── tenant.php        (Dashboard, products, tasks, team)
157: ├── onboarding.php    (Onboarding wizard)
158: ├── settings.php      (Profile, password, 2FA)
159: └── console.php       (Scheduled tasks)
160: ```
161: 
162: ---
163: 
164: ## Authentication
165: 
166: ### Stack
167: 
168: Laravel Fortify v1 (headless) with Inertia React views. Custom login/register response classes handle Inertia redirects.
169: 
170: ### Fortify Features
171: 
172: ```php
173: Features::registration(),
174: Features::resetPasswords(),
175: Features::emailVerification(),
176: Features::twoFactorAuthentication(['confirm' => true, 'confirmPassword' => true]),
177: ```
178: 
179: ### Custom Actions
180: 
181: **CreateNewUser** (`app/Actions/Fortify/CreateNewUser.php`):
182: 1. Validates name, email, password, optional `business_type_slug`
183: 2. Creates Tenant (defaults to `shared` mode)
184: 3. Creates User with `tenant_id`
185: 4. Updates Tenant `owner_id`
186: 5. Stores `business_type_slug` in session for post-registration onboarding
187: 6. Sends welcome email
188: 
189: **ResetUserPassword** (`app/Actions/Fortify/ResetUserPassword.php`):
190: - Validates and updates password
191: 
192: ### Register Response
193: 
194: **RegisterResponse** (`app/Http/Responses/RegisterResponse.php`):
195: - Redirects to `/onboarding` for the multi-step wizard (not directly to `/dashboard`)
196: 
197: ### Social Authentication
198: 
199: **Providers:** Google, GitHub (via `config/social-auth.php`)
200: 
201: **Enabled by:** `AppSetting::getBoolean('social_auth_enabled')`
202: 
203: **Routes:**
204: ```
205: GET /auth/{provider}/redirect   -> SocialAuthController@redirect
206: GET /auth/{provider}/callback   -> SocialAuthController@callback
207: ```
208: 
209: **Flow:**
210: 1. Socialite redirect -> callback
211: 2. Checks linking intent (session)
212: 3. Finds existing SocialAccount -> auto-login
213: 4. If new: creates via `CreateSocialUser` (auto-verifies email, random password)
214: 5. Creates SocialAccount record
215: 
216: ### Two-Factor Authentication
217: 
218: Handled by Fortify's `TwoFactorAuthenticatable` trait on User model. Routes at `/two-factor-challenge`, settings at `/settings/two-factor`.
219: 
220: ### Password Reset
221: 
222: Standard Fortify flow with email notifications. Rate limited to 6 attempts/minute.
223: 
224: ### Models
225: 
226: **User** (`app/Models/User.php`): `CentralConnection`, `HasRoles` (Spatie), `TwoFactorAuthenticatable`, `SoftDeletes`
227: - Relations: `tenant()` (BelongsTo Tenant), `socialAccounts()` (HasMany SocialAccount)
228: 
229: **SocialAccount** (`app/Models/SocialAccount.php`): Central connection, stores provider credentials.
230: 
231: ---
232: 
233: ## Onboarding System
234: 
235: ### Overview
236: 
237: After registration, users go through a multi-step onboarding wizard that:
238: 1. Selects a business type (16 industry cards)
239: 2. Runs a 10-step provisioning pipeline to configure the tenant
240: 3. Redirects to the dashboard with industry-specific configuration
241: 
242: ### Onboarding Routes (`routes/onboarding.php`)
243: 
244: ```
245: GET    /onboarding                          -> OnboardingController@start
246: POST   /onboarding/select-type              -> OnboardingController@selectType
247: GET    /onboarding/provision                -> OnboardingController@provision
248: POST   /onboarding/run                      -> OnboardingController@run
249: GET    /onboarding/{tenant}/progress         -> OnboardingController@progress
250: GET    /api/business-types                   -> BusinessTypeController@index
251: ```
252: 
253: ### Onboarding Flow
254: 
255: ```
256: Registration
257:   → CreateNewUser (stores business_type_slug in session)
258:   → RegisterResponse (redirects to /onboarding)
259:   → OnboardingController@start — shows business type picker
260:   → OnboardingController@selectType — validates + stores type
261:   → OnboardingController@provision — creates tenant record
262:   → OnboardingController@run — executes ProvisioningPipeline
263:     → 10 steps run sequentially with progress tracking
264:     → On failure: rolls back completed steps, marks tenant as failed
265:   → On success: marks tenant onboarded, redirects to dashboard
266: ```
267: 
268: ### ProvisioningPipeline (`app/Modules/Onboarding/Services/ProvisioningPipeline.php`)
269: 
270: 10-step pipeline that provisions a new tenant:
271: 
272: | # | Step Class | Purpose |
273: |---|-----------|---------|
274: | 1 | `CreateTenantStep` | Creates the tenant record in central DB |
275: | 2 | `AssignBusinessTypeStep` | Assigns `business_type_id` to tenant |
276: | 3 | `ProvisionModulesStep` | Enables modules based on business type |
277: | 4 | `CreatePermissionsStep` | Creates role-permission mappings |
278: | 5 | `SeedDefaultDataStep` | Seeds initial data (categories, etc.) |
279: | 6 | `ConfigureProductSchemaStep` | Sets product schema fields per industry |
280: | 7 | `ConfigureDashboardStep` | Sets dashboard widget layout |
281: | 8 | `ConfigurePOSStep` | Configures POS defaults |
282: | 9 | `CreateDefaultTeamStep` | Creates default team roles |
283: | 10 | `BuildTenantConfigStep` | Builds and caches the tenant config |
284: 
285: Each step implements `ProvisioningStep`:
286: ```php
287: interface ProvisioningStep
288: {
289:     public function handle(ProvisioningContext $context): void;
290:     public function rollback(ProvisioningContext $context): void;
291:     public function label(): string;
292: }
293: ```
294: 
295: **Rollback:** If any step fails, all completed steps are rolled back in reverse order. Tenant is marked as `failed` with error details.
296: 
297: ### Onboarding Events
298: 
299: | Event | Dispatched |
300: |---|---|
301: | `OnboardingStarted` | Pipeline begins |
302: | `OnboardingStepCompleted` | Each step completes |
303: | `OnboardingCompleted` | All 10 steps succeed |
304: | `OnboardingFailed` | Any step throws |
305: 
306: ### Tenant Model Onboarding Fields
307: 
308: On `tenants` table (migration `2026_06_20_000100`):
309: - `onboarding_status` — `pending` / `in_progress` / `completed` / `failed`
310: - `onboarding_progress` — JSON array of step results
311: - `onboarded_at` — timestamp of completion
312: 
313: ### TenantTemplate System
314: 
315: Each business type has a `TenantTemplate` that defines its default configuration:
316: 
317: ```php
318: interface TenantTemplate
319: {
320:     public function businessType(): string;
321:     public function defaultCategories(): array;
322:     public function productSchema(): array;
323:     public function dashboardLayout(): array;
324:     public function posDefaults(): array;
325:     public function defaultTeam(): array;
326:     public function notificationDefaults(): array;
327:     public function initialData(): array;
328: }
329: ```
330: 
331: **All 16 templates are registered in `OnboardingServiceProvider::boot()`:**
332: 
333: | Template Class | Slug | Specialty |
334: |---|---|---|
335: | `PharmacyTemplate` | pharmacy | Drug schedules, prescriptions, expiry tracking |
336: | `RestaurantTemplate` | restaurant | Dietary tags, allergens, kitchen display, table mgmt |
337: | `GroceryTemplate` | grocery | Perishables, weight/piece units |
338: | `SalonTemplate` | salon | Duration, booking requirement, gender |
339: | `BakeryTemplate` | bakery | Batch dates, ingredients, dietary tags |
340: | `CafeTemplate` | cafe | Temperature, size options, caffeine info |
341: | `ElectronicsTemplate` | electronics | Brand, model, warranty, serial numbers |
342: | `FashionTemplate` | fashion | Sizes, colors, material, season, gender |
343: | `CosmeticsTemplate` | cosmetics | Shade, volume, skin type, brand |
344: | `HardwareTemplate` | hardware | Weight, material, unit type |
345: | `WholesaleTemplate` | wholesale | Min order qty, bulk pricing, case qty |
346: | `DistributionTemplate` | distribution | Cargo weight/volume, hazmat, routing |
347: | `AgroShopTemplate` | agro_shop | Organic, crop type, season, unit type |
348: | `BookstoreTemplate` | bookstore | Author, ISBN, publisher, genre, language |
349: | `SpaTemplate` | spa | Duration, room type, gender, prep time |
350: | `DefaultTemplate` | general | Basic categories, core fields |
351: 
352: Registered via `TenantTemplateRegistry` singleton. Lookup by slug:
353: ```php
354: $template = $registry->getOrFail('pharmacy');
355: $categories = $template->defaultCategories();
356: ```
357: 
358: ---
359: 
360: ## Tenant System
361: 
362: ### TenantManager (`app/Tenancy/TenantManager.php`)
363: 
364: Singleton. Central orchestrator for all tenancy operations.
365: 
366: ```php
367: class TenantManager
368: {
369:     public function initialize(Tenant $tenant): void       // Resolves strategy, calls initialize
370:     public function end(): void                             // Tears down tenant context
371:     public function current(): ?Tenant                     // Current tenant or null
372:     public function isShared(): bool                        // Delegates to strategy
373:     public function isDedicated(): bool                     // Delegates to strategy
374:     public function databaseConnection(): string            // 'shared' or 'mysql'
375:     public function guessModeFromPlan(string $planSlug): string  // Maps plan slug to mode
376:     public function resolveStrategy(?Tenant $tenant): TenantModeStrategy
377: }
378: ```
379: 
380: ### Strategies
381: 
382: **SharedMode** (`app/Tenancy/Modes/SharedMode.php`):
383: - Sets `config('database.default')` to `'shared'`
384: - Registers tenant in container
385: - Configures cache prefix: `tenant_shared_{id}`
386: - Configures storage prefix: `shared/{id}`
387: - Does NOT call `tenancy()->initialize()` (stancl's DB bootstrapping is for dedicated only)
388: 
389: **DedicatedMode** (`app/Tenancy/Modes/DedicatedMode.php`):
390: - Calls `tenancy()->initialize($tenant)` (stancl native multi-DB init)
391: 
392: ### TenantScope (`app/Tenancy/Scopes/TenantScope.php`)
393: 
394: Global scope that applies `WHERE tenant_id = ?` on queries for shared-mode models. Only applies when TenantManager is initialized AND in shared mode. Provides `withoutTenancy()` macro.
395: 
396: ### HasTenantScope Trait (`app/Tenancy/Models/Concerns/HasTenantScope.php`)
397: 
398: Used by all shared-mode models. Auto-fills `tenant_id` on creation, adds global scope, provides `tenant()` relationship. Uses `app()` helper wrapped in try-catch to gracefully handle unit test environments without a booted application.
399: 
400: ```php
401: public static function bootHasTenantScope(): void
402: {
403:     try {
404:         static::addGlobalScope(app(TenantScope::class));
405:     } catch (\Throwable) {
406:         // No app context available (e.g., unit tests)
407:     }
408:     // ...
409: }
410: ```
411: 
412: ### InitializeTenancyByUser Middleware (`app/Http/Middleware/InitializeTenancyByUser.php`)
413: 
414: Applied to all tenant routes. Skips admin routes. Gets tenant from `$request->user()->tenant`. For dedicated: initializes via TenantManager. Catches `TenantDatabaseDoesNotExistException` and redirects to billing.
415: 
416: ### Tenant Provisioning Flow
417: 
418: ```
419: SubscriptionActivated
420:   → ProvisionTenantDatabase::handle()
421:     → guessModeFromPlan($planSlug)
422:     → assignBusinessType($tenant, $subscription)
423:       → assigns first active BusinessType if none set
424:     → if shared: provisionSharedTenant() (inserts tenant_settings)
425:     → if dedicated: upgradeToDedicated() (create DB, migrate, copy)
426:     → if upgrade: handles shared→dedicated migration
427:     → if downgrade: handles dedicated→shared migration
428: ```
429: 
430: ### Tenant Migration Paths (`config/tenancy.php`)
431: 
432: ```php
433: 'migration_parameters' => [
434:     '--path' => [
435:         database_path('migrations/tenant'),
436:         app_path('Modules/Product/Database/Migrations/Tenant'),
437:         app_path('Modules/BusinessType/Database/Migrations/Tenant'),
438:     ],
439: ],
440: ```
441: 
442: ---
443: 
444: ## Product Module
445: 
446: ### Directory: `app/Modules/Product/`
447: 
448: ### Models (17 models)
449: 
450: **Product** — ULID PK, `ProductTypeEnum` (simple/configurable/bundle/virtual), `ProductStatusEnum` (draft/active/archived). Has `Searchable` (Scout/Algolia), slugs, media, stock relationships. Relations: category, categories (pivot), brand, taxCategory, variants, media, attributeValues, warehouseStock, pricingRules.
451: 
452: **Variant** — ULID, belongs to Product. Has own SKU, barcode, price, stock tracking.
453: 
454: **Category** — Hierarchical via `HasMaterializedPath`. Parent/children tree, BelongsToMany products via `category_product` pivot.
455: 
456: **Brand** — Simple CRUD, scoped `active()`.
457: 
458: **Warehouse** — Physical locations. `is_default` flag.
459: 
460: **WarehouseStock** — Tracks quantity + reserved_quantity per warehouse+product+variant. Uses `HasOptimisticLocking` (lock_version for concurrency).
461: 
462: **StockReservation** — Statuses: active/consumed/expired/cancelled. Tracks reservations with expiration.
463: 
464: **Other Models:** Attribute, AttributeValue, ProductAttributeValue, ProductAttributeTextValue, ProductMedia, StockMovement, AuditLog, TaxCategory, TaxRate, PricingRule.
465: 
466: ### Enums (all in `app/Modules/Product/Enums/`)
467: - ProductStatusEnum, ProductTypeEnum, MovementTypeEnum, StockReservationStatusEnum, BarcodeTypeEnum, MediaTypeEnum, AuditActionEnum, PricingRuleScopeEnum, PricingRuleTypeEnum, PricingRuleConditionEnum, AttributeTypeEnum
468: 
469: ### Controllers
470: 
471: **ProductController**: index, create, store, show, edit, update, destroy, archive, restore, publish, duplicate
472: **CategoryController**: index, store, show, update, destroy, reorder
473: **BrandController**: index, store, update, destroy
474: **AttributeController**: index, store, update, destroy, storeValue, updateValue, destroyValue
475: **StockController**: lowStock, movements, transfer
476: 
477: ### Services (20 classes)
478: 
479: ProductService, CategoryService, BrandService, VariantService, AttributeService, MediaService, WarehouseService, StockService, StockLockService, StockAuditService, StockReservationService, TaxService, PricingRuleService, ProductImportService, plus contracts: DefaultSKUGenerator, DefaultStockAllocator, EloquentPricingCalculator, EloquentProductCatalogService, EloquentProductResolver, EloquentStockChecker.
480: 
481: ### Events (14 events)
482: 
483: ProductCreated, ProductUpdated, ProductDeleted, ProductArchived, ProductPublished, VariantCreated, VariantUpdated, VariantDeleted, StockUpdated, StockDepleted, LowStockAlert, StockReservationCreated, StockReservationExpired, StockTransferCompleted.
484: 
485: ### Listeners & Observers
486: 
487: Observers: ProductObserver, VariantObserver, WarehouseStockObserver, StockReservationObserver.
488: 13 listeners handle search indexing, stock notifications, reservation lifecycle.
489: 
490: ### Policies
491: 
492: ProductPolicy, CategoryPolicy, BrandPolicy, WarehousePolicy.
493: 
494: ---
495: 
496: ## Billing System
497: 
498: ### Directory: `app/Modules/Billing/`
499: 
500: ### Models
501: 
502: **Plan** (`billing_plans`, central):
503: | Column | Type |
504: |---|---|
505: | id, name, slug (unique) | |
506: | monthly_price, yearly_price | integer |
507: | currency (default BDT) | string(3) |
508: | features, limits | JSON |
509: | is_active, popular, display_order | boolean/integer |
510: | trial_enabled, trial_days, trial_without_card | boolean/integer |
511: | pricing_model, default_seats, seat_price, max_seats | |
512: 
513: **Subscription** (`billing_subscriptions`, central):
514: | Column | Type |
515: |---|---|
516: | id, tenant_id, plan_id, gateway | |
517: | status (SubscriptionStatus enum) | string |
518: | billing_cycle (BillingCycle enum) | string |
519: | amount, currency | integer/string |
520: | starts_at, expires_at, next_billing_at, trial_ends_at, cancelled_at | timestamps |
521: | metadata | JSON |
522: 
523: Statuses: Trial → Active → Grace → Expired | Cancelled | PendingPayment
524: 
525: **Payment** (`billing_payments`, central):
526: | Column | Type |
527: |---|---|
528: | id, subscription_id, tenant_id, gateway | |
529: | transaction_id (nullable) | string |
530: | amount, currency | integer/string |
531: | status (PaymentStatus enum) | string |
532: | payload (JSON), paid_at | |
533: 
534: Statuses: Pending → Completed | Failed | Refunded
535: 
536: **SeatAllocation** (`billing_seat_allocations`, central):
537: - Tracks user seats per subscription
538: - SeatType: owner, admin, staff
539: - Status: active, pending, released
540: 
541: ### Payment Gateways
542: 
543: All implement `BillingGatewayInterface` (`app/Modules/Billing/Contracts/`):
544: 
545: | Driver | Status |
546: |---|---|
547: | SSLCommerzDriver | ✅ Fully implemented |
548: | StripeDriver | ❌ Stub |
549: | BKashDriver | ❌ Stub |
550: | NagadDriver | ❌ Stub |
551: | PortWalletDriver | ❌ Stub |
552: | ManualDriver | ✅ Local/manual payments |
553: 
554: ### Services
555: 
556: **BillingManager** — Singleton gateway driver registry. `driver($gateway)` resolves gateway by name.
557: 
558: **SubscriptionService** — Core subscription lifecycle:
559: - `createSubscription()` — Creates subscription, initiates payment or trial
560:   - If trial available (trial_without_card): immediately activates
561:   - If `$amount === 0` (Free plan): immediately activates without payment
562:   - Otherwise: initiates payment via gateway
563: - `activateSubscription()` — Sets Active, dispatches `SubscriptionActivated`
564: - `verifyAndActivate()` — Verifies payment, activates
565: - `cancelSubscription()` — Gateway + local cancellation
566: - `tenantHasAccessibleSubscription()` — Boolean check
567: - `tenantHasFeature()` — Checks plan features
568: - `tenantHasReachedLimit()` — Checks plan limits
569: 
570: **PaymentService** — Records payments, marks complete/failed.
571: 
572: **PlanService** — CRUD for plans.
573: 
574: **SeatService** — Allocate, release, count seats.
575: 
576: ### Events (9 events)
577: 
578: | Event | When |
579: |---|---|
580: | `SubscriptionActivated` | Subscription becomes active (trial or paid) |
581: | `SubscriptionCancelled` | Subscription cancelled |
582: | `SubscriptionExpired` | Grace period ends |
583: | `PaymentReceived` | Payment completes |
584: | `PaymentFailed` | Payment fails |
585: | `InvoiceGenerated` | Invoice created |
586: | `SeatAllocated` | Seat assigned |
587: | `SeatReleased` | Seat released |
588: | `SeatOverageInvoiced` | Overage invoiced |
589: 
590: ### Stripe Integration
591: 
592: Laravel Cashier configured for Stripe. Separate `plans` and `plan_prices` tables (central) synced from Stripe via webhooks. `StripeEventListener` handles `WebhookReceived` from Cashier.
593: 
594: ---
595: 
596: ## Business Type Engine
597: 
598: ### Directory: `app/Modules/BusinessType/`
599: 
600: ### Architecture
601: 
602: 15 industry packs implementing `IndustryPack` interface. Each pack defines: modules, menus, permissions, POS config, dashboard widgets, reports, settings, feature flags. Packs are registered in `IndustryServiceProvider::boot()`.
603: 
604: ### Key Classes
605: 
606: | Class | Purpose |
607: |---|---|
608: | `IndustryPackRegistry` | In-memory registry of all 15 packs |
609: | `BusinessTypeConfigBuilder` | Assembles `TenantConfig` from pack + plan gating + tenant overrides |
610: | `BusinessTypeEngine` | Caching (24h TTL), config resolution, business type assignment |
611: | `TenantConfig` (readonly VO) | Injected via DI, available app-wide |
612: 
613: ### Container Bindings (`IndustryServiceProvider`)
614: 
615: | Abstract | Concrete | Type |
616: |---|---|---|
617: | `IndustryPackRegistry` | `IndustryPackRegistry` | singleton |
618: | `BusinessTypeEngine` | `BusinessTypeEngine` | singleton |
619: | `BusinessTypeConfigBuilder` | `BusinessTypeConfigBuilder` | singleton |
620: | `TenantConfig` | closure (per-request resolution) | bind |
621: 
622: ### TenantConfig API
623: 
624: ```php
625: $config->businessType;       // string — 'pharmacy', 'restaurant', etc.
626: $config->enabledModules;     // string[] — ['product', 'inventory', 'pos', ...]
627: $config->menus;              // array — navigation structure
628: $config->permissions;        // array — role → permissions map
629: $config->fieldDefinitions;   // array — custom field definitions
630: $config->dashboardWidgets;   // array — widget configs
631: $config->posConfig;          // array — POS layout/fields
632: $config->reportDefinitions;  // array — report configs
633: $config->settings;           // array — merged settings + features
634: 
635: // Helper methods
636: $config->hasModule('kitchen');        // bool
637: $config->hasFeature('batch_tracking'); // bool
638: $config->toArray();                   // serialized for storage/API
639: ```
640: 
641: ### All Industry Packs
642: 
643: | Pack Class | Slug | Specialty Modules | Key Feature Flags |
644: |---|---|---|---|
645: | `PharmacyPack` | pharmacy | supplier, reporting | batch_tracking, expiry_tracking, prescription_management, drug_schedule_management, insurance_billing |
646: | `RestaurantPack` | restaurant | kitchen, supplier, reporting | table_management, menu_management, kitchen_display, recipe_management |
647: | `SalonPack` | salon | appointment, reporting | service_booking, staff_scheduling, commission_tracking, membership |
648: | `SpaPack` | spa | appointment, reporting | service_booking, staff_scheduling, membership, package_management |
649: | `ElectronicsPack` | electronics | supplier, reporting | serial_number_tracking, warranty_management, repair_tracking |
650: | `FashionPack` | fashion | reporting | variant_size_color, seasonal_collections |
651: | `GroceryPack` | grocery | supplier, reporting | weight_based_pricing, perishable_tracking |
652: | `CafePack` | cafe | reporting | quick_service, recipe_management |
653: | `BakeryPack` | bakery | reporting | batch_baking, recipe_costing |
654: | `CosmeticsPack` | cosmetics | reporting | shade_variants, ingredient_tracking |
655: | `HardwarePack` | hardware | supplier, reporting | bulk_pricing, unit_conversion |
656: | `WholesalePack` | wholesale | supplier, reporting | bulk_orders, tiered_pricing |
657: | `DistributionPack` | distribution | supplier (no pos) | logistics, fleet_management, route_management |
658: | `AgroShopPack` | agro_shop | supplier, reporting | seasonal_inventory, seed_feed_tracking |
659: | `BookstorePack` | bookstore | reporting | isbn_management, author_publisher_management |
660: 
661: ### Frontend Integration
662: 
663: Tenant config is shared via Inertia in `HandleInertiaRequests`:
664: 
665: ```php
666: 'tenant_config' => fn () => [
667:     'business_type' => $config->businessType,
668:     'modules' => $config->enabledModules,
669: ],
670: ```
671: 
672: Consumed by:
673: - `use-tenant-config.ts` — `useTenantConfig()`, `useModuleEnabled()`, `useEnabledModules()`
674: - `module-nav-items.ts` — 11-module nav map, `buildModuleNavItems(enabledModules)`
675: - `app-sidebar.tsx` — renders dynamic nav items per enabled modules
676: - `industry-widgets.tsx` — `IndustryGreeting` (16 industries with icons) + `ModuleQuickActions`
677: 
678: ---
679: 
680: ## Frontend Architecture
681: 
682: ### Tech Stack
683: 
684: React 19 + Inertia.js v2 + Tailwind CSS v4 + shadcn/ui components.
685: 
686: ### Key Hooks
687: 
688: **`use-tenant-config.ts`** (`resources/js/hooks/use-tenant-config.ts`):
689: ```typescript
690: const config = useTenantConfig();            // TenantConfig | null
691: const enabled = useModuleEnabled('kitchen');  // boolean
692: const modules = useEnabledModules();          // string[]
693: ```
694: 
695: ### Module Navigation
696: 
697: **`module-nav-items.ts`** maps 11 module slugs to nav definitions:
698: - product, inventory, order, pos, crm, billing, team, supplier, kitchen, appointment, reporting
699: 
700: Used by `app-sidebar.tsx` to dynamically build sidebar navigation based on `tenant_config.modules`.
701: 
702: ### Industry Dashboard Widgets
703: 
704: **`industry-widgets.tsx`** provides:
705: - `IndustryGreeting` — industry-specific welcome card with icon (16 industries)
706: - `ModuleQuickActions` — up to 6 quick-action cards from enabled modules
707: 
708: ---
709: 
710: ## Routes
711: 
712: | Route File | Prefix | Middleware | Purpose |
713: |---|---|---|---|
714: | `routes/web.php` | `/` | `web` | Home, social auth, billing |
715: | `routes/admin.php` | `/admin` | `web, auth, EnsureAdmin` | Admin panel |
716: | `routes/tenant.php` | (none) | `web, auth, InitializeTenancyByUser, subscription` | Dashboard, products, tasks, team |
717: | `routes/onboarding.php` | (none) | `web` | Onboarding wizard |
718: | `routes/settings.php` | (included) | `auth` | Profile, password, 2FA |
719: | `routes/console.php` | -- | -- | Schedules |
720: 
721: ### Key Tenant Routes
722: 
723: ```
724: GET    /dashboard                  -> DashboardController
725: GET    /products                   -> ProductController@index
726: POST   /products                   -> ProductController@store
727: GET    /products/{product}         -> ProductController@show
728: PUT    /products/{product}         -> ProductController@update
729: DELETE /products/{product}         -> ProductController@destroy
730: GET    /categories                 -> CategoryController@index
731: GET    /brands                     -> BrandController@index
732: GET    /inventory/low-stock        -> StockController@lowStock
733: GET    /stock/movements            -> StockController@movements
734: POST   /stock/transfer             -> StockController@transfer
735: GET    /tasks                      -> TaskController@index
736: GET    /billing                    -> BillingController@index
737: POST   /billing/subscribe          -> BillingController@subscribe
738: ```
739: 
740: ### Onboarding Routes
741: 
742: ```
743: GET    /onboarding                     -> show business type picker
744: POST   /onboarding/select-type         -> select business type
745: GET    /onboarding/provision           -> show provisioning progress
746: POST   /onboarding/run                 -> start provisioning pipeline
747: GET    /onboarding/{tenant}/progress   -> poll provisioning progress
748: GET    /api/business-types             -> list active business types
749: ```
750: 
751: ### Key Admin Routes
752: 
753: ```
754: GET    /admin/users                -> Admin/UserController
755: GET    /admin/pricing              -> Admin/PlanController
756: GET    /admin/settings             -> Admin/SettingController
757: ```
758: 
759: ---
760: 
761: ## Middleware
762: 
763: | Middleware | Alias | Purpose |
764: |---|---|---|
765: | `InitializeTenancyByUser` | — | Initializes tenant context from auth user |
766: | `EnsureAdmin` | — | Checks `hasRole('admin')` |
767: | `EnsureTenantHasSubscription` | `subscription` | Redirects to billing if no subscription |
768: | `EnsureTenantHasFeature` | `feature` | Checks plan features |
769: | `EnsureSeatAvailable` | `seat` | Checks seat limit |
770: | `HandleInertiaRequests` | — | Inertia shared props incl. tenant_config |
771: | `HandleAppearance` | — | Appearance cookie |
772: 
773: **Priority:** `InitializeTenancyByUser` runs before `SubstituteBindings`.
774: 
775: ---
776: 
777: ## Key Flows
778: 
779: ### Registration Flow
780: 
781: ```
782: POST /register (name, email, password, business_type_slug)
783:   → Fortify -> CreateNewUser
784:     → Tenant::create(['name' => "{$name}'s Account"])
785:       → tenancy_mode defaults to 'shared'
786:       → business_type_id is null
787:     → User::create([..., 'tenant_id' => $tenant->id])
788:     → $tenant->update(['owner_id' => $user->id])
789:     → Stores business_type_slug in session
790:     → Send welcome email
791:   → RegisterResponse redirects to /onboarding
792:   → OnboardingController@start — business type picker page
793:   → User selects industry card
794:   → OnboardingController@selectType — stores type
795:   → OnboardingController@provision — creates tenant record
796:   → ProvisioningPipeline runs 10 steps
797:   → On completion: redirect to /dashboard with tenant_config
798: ```
799: 
800: ### Subscription Flow
801: 
802: ```
803: POST /billing/subscribe (plan_id, gateway)
804:   → SubscriptionService::createSubscription()
805: 
806:   Case A: Trial (no card required) — starter/professional/enterprise
807:     → status = Trial
808:     → activateSubscription() immediately
809:       → status = Active
810:       → dispatches SubscriptionActivated
811:       → ProvisionTenantDatabase::handle()
812:         → provisions shared or dedicated DB
813:         → assigns business type if none set
814:     → Returns checkoutUrl = null
815: 
816:   Case B: Free plan ($0)
817:     → status = PendingPayment → amount check → immediate activation
818:     → Same as Case A activation flow
819:     → Returns checkoutUrl = null
820: 
821:   Case C: Paid, no trial
822:     → status = PendingPayment
823:     → Gateway creates payment session
824:     → Returns checkoutUrl
825: 
826:   After payment callback:
827:     → verifyAndActivate() -> PaymentReceived -> activateSubscription()
828: ```
829: 
830: ### Tenant Initialization (Per-Request)
831: 
832: ```
833: InitializeTenancyByUser::handle()
834:   → $tenant = $request->user()->tenant
835:   → $manager->initialize($tenant)
836:     → resolveStrategy(): if $tenant->isDedicated() -> DedicatedMode else SharedMode
837:     → strategy->initialize():
838:       → SharedMode: sets DB default to 'shared', configures cache/storage
839:       → DedicatedMode: calls tenancy()->initialize($tenant)
840: ```
841: 
842: ---
843: 
844: ## Development Setup
845: 
846: ### Prerequisites
847: 
848: - PHP 8.4.16+
849: - MySQL 8.0+
850: - Node.js 20+
851: - Composer
852: - Laravel Herd (or Valet)
853: 
854: ### Installation
855: 
856: ```bash
857: git clone <repo> souda && cd souda
858: composer install
859: npm install
860: cp .env.example .env
861: php artisan key:generate
862: ```
863: 
864: ### Database Setup
865: 
866: ```bash
867: # Create central database
868: mysql -u root -e "CREATE DATABASE souda CHARACTER SET utf8mb4"
869: 
870: # Create shared database (REQUIRED for shared-mode tenants)
871: mysql -u root -e "CREATE DATABASE souda_shared CHARACTER SET utf8mb4"
872: 
873: # Run central migrations
874: php artisan migrate
875: 
876: # Run shared migrations
877: php artisan migrate --database=shared --path=database/migrations/shared
878: 
879: # Seed all data
880: php artisan db:seed
881: ```
882: 
883: ### Running
884: 
885: ```bash
886: npm run dev          # Vite dev server
887: php artisan serve    # Laravel dev server (or use Herd)
888: ```
889: 
890: ### Seeding
891: 
892: ```bash
893: # Full seed (all seeders run)
894: php artisan db:seed
895: 
896: # Individual seeders
897: php artisan db:seed --class=BusinessTypeSeeder
898: php artisan db:seed --class=ModuleSeeder
899: php artisan db:seed --class=BusinessTypeModuleSeeder
900: php artisan db:seed --class=PlanSeeder
901: php artisan db:seed --class=RolePermissionSeeder
902: php artisan db:seed --class=AdminRoleSeeder
903: ```
904: 
905: ### Testing
906: 
907: ```bash
908: # Run specific test file
909: php artisan test --compact --filter="FeatureGatingTest"
910: 
911: # Run all tests
912: php artisan test --compact
913: ```
914: 
915: ---
916: 
917: ## Database Schema Summary
918: 
919: ### Central (`souda`)
920: 
921: ```
922: users                   — id, name, email, password, tenant_id, email_verified_at, ...
923: tenants                 — id (uuid), name, owner_id, tenancy_mode, business_type_id,
924:                           onboarding_status, onboarding_progress, onboarded_at, ...
925: billing_plans           — id, name, slug, monthly_price, yearly_price, features, limits, trial_...
926: billing_subscriptions   — id, tenant_id, plan_id, gateway, status, billing_cycle, amount, ...
927: billing_payments        — id, subscription_id, tenant_id, gateway, transaction_id, amount, status
928: billing_seat_allocations — id, tenant_id, subscription_id, user_id, seat_type, status
929: business_types          — id, slug, name, description, icon, is_active, pack_class, config_template
930: modules                 — id, slug, name, description, version, dependencies, is_core
931: business_type_module    — business_type_id, module_id, is_required, config_defaults
932: roles / permissions     — Spatie permission tables
933: app_settings            — key, value
934: social_accounts         — user_id, provider, provider_user_id
935: domains                 — (stancl tenancy)
936: ```
937: 
938: ### Shared (`souda_shared`)
939: 
940: ```
941: tenant_settings         — id, tenant_id, timezone, locale, currency, logo, ...
942: tasks                   — id, tenant_id, title, description, is_completed
943: tenant_configs          — id, tenant_id, business_type_slug, config (JSON), config_hash
944: tenant_module_overrides — id, tenant_id, module_slug, is_enabled, settings (JSON)
945: ```
946: 
947: ### Dedicated (per-tenant)
948: 
949: All tables from `database/migrations/tenant/` + `Modules/Product/Database/Migrations/Tenant/` + `Modules/BusinessType/Database/Migrations/Tenant/`.
950: 
951: Includes: products, variants, categories, brands, warehouses, warehouse_stock, stock_movements, stock_reservations, product_media, attributes, attribute_values, product_attribute_values, pricing_rules, tax_categories, tax_rates, audit_logs, tenant_settings, tasks, tenant_configs, tenant_module_overrides.
952: 
953: ---
954: 
955: ## Service Providers
956: 
957: Registered in `bootstrap/providers.php` in this order:
958: 
959: | Provider | Purpose |
960: |---|---|
961: | `AppServiceProvider` | CarbonImmutable, DB commands, password rules |
962: | `FortifyServiceProvider` | Custom auth actions, Inertia views, rate limiting, business types in register view |
963: | `ProductServiceProvider` | Product services, events, observers, policies |
964: | `TenancyServiceProvider` | TenantManager binding, middleware, routes |
965: | `BillingServiceProvider` | Billing services, gateway drivers, events |
966: | `IndustryServiceProvider` | Industry packs, TenantConfig binding |
967: | `OnboardingServiceProvider` | TenantTemplateRegistry, ProvisioningPipeline, 16 templates, onboarding routes |
968: 
969: ---
970: 
971: ## Known Issues
972: 
973: ### 3 StockCalculationTest Failures
974: 
975: Require MySQL-specific features. Pre-existing.
976: 
977: ### PHP 8.4 Anonymous Migrations
978: 
979: Anonymous migration classes must not have typed properties. The base `Migration` class declares `$connection` without a type.
980: 
981: ### `orderBy` Required for `each()` (Laravel 12)
982: 
983: Always add `->orderBy('id')` before `->each()` on query builders. See `ProvisionTenantDatabase::migrateSharedDataToDedicated()` for the pattern.
984: 
985: ### Shared Database Must Exist
986: 
987: The `souda_shared` database must exist before any shared-mode tenant operations. Create it if missing:
988: 
989: ```bash
990: php artisan tinker --execute="DB::statement('CREATE DATABASE IF NOT EXISTS souda_shared CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');"
991: ```
992: 
993: ---
994: 
995: ## Events & Listeners Map
996: 
997: ```
998: SubscriptionActivated
999:   → ProvisionTenantDatabase (sync — provisions DB, assigns business type)
1000:   → SendSubscriptionNotification (email)
1001: 
1002: PaymentReceived
1003:   → SendSubscriptionNotification (email)
1004:   → GenerateInvoice (queued)
1005: 
1006: ProductCreated
1007:   → IndexProductForSearch (Algolia)
1008:   → GenerateProductSKU
1009: 
1010: StockUpdated
1011:   → UpdateProductStockCache
1012:   → SendLowStockNotification
1013:   → MarkProductUnavailable (if depleted)
1014: 
1015: TenantModeChanged
1016:   → (future: reindex, flush caches, etc.)
1017: 
1018: OnboardingStarted
1019:   → (logged, progress tracked)
1020: 
1021: OnboardingStepCompleted
1022:   → (progress tracked)
1023: 
1024: OnboardingCompleted
1025:   → (tenant marked complete, redirect triggered)
1026: 
1027: OnboardingFailed
1028:   → (tenant marked failed, rollback triggered)
1029: ```
1030: 
1031: ---
1032: 
1033: ## Key Design Principles
1034: 
1035: ### No Hardcoded Business Type Logic
1036: - All industry-specific behavior is encapsulated in `IndustryPack` and `TenantTemplate` interfaces
1037: - No `if ($slug === 'pharmacy')`, no `switch/case`, no per-industry tables
1038: - Adding a new industry = implement interfaces + register in providers
1039: 
1040: ### Module Registry Pattern
1041: - Modules self-register capabilities (menus, permissions, dashboard widgets)
1042: - Industry packs determine which modules are enabled per tenant
1043: - Future: `artisan module:cache`, `artisan module:list`
1044: 
1045: ### Immutable Value Objects
1046: - `TenantConfig` is a read-only DTO — all mutability happens through the builder
1047: - Config flows: IndustryPack default → BusinessTypeConfigBuilder → plan gating → tenant overrides
1048: 
1049: ### Service Provider Boot Order
1050: `IndustryServiceProvider` runs LAST among core providers. `OnboardingServiceProvider` runs last overall. This ensures all other providers (Product, Tenancy, Billing) have booted before industry packs and onboarding templates register.

(End of file - total 1050 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "C:\\dev\\souda\\docs\\architectures\\product-module-design.md"
}
```

**Output:**
```
<path>C:\dev\souda\docs\architectures\product-module-design.md</path>
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
19: // ProductServiceProvider::boot()
20: $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations/Tenant');
21: ```
22: 
23: > Note: The provider path resolution above uses `__DIR__.'/../Modules/...'` when the provider lived outside the module, or `__DIR__.'/../Database/...'` when it lives inside. The actual code uses the path relative to the service provider file location.
24: 
25: Models (e.g., `Product`, `Category`, `Brand`, `Variant`) extend `Model` directly — no `CentralConnection` trait — so they automatically use the tenant DB connection when tenancy is initialized.
26: 
27: ## Data Model
28: 
29: ```
30: ┌──────────────────────────────────────────────────────────────┐
31: │                    Tenant DB (souda_tenant_{uuid})            │
32: │                                                              │
33: │  ┌──────────┐    ┌──────────────┐    ┌──────────────────┐   │
34: │  │ Category │◄───│ category_    │───►│     Product      │   │
35: │  │          │    │ product      │    │                  │   │
36: │  │  • id    │    └──────────────┘    │  • ulid (PK)     │   │
37: │  │  • name  │                        │  • category_id   │   │
38: │  │  • slug  │    ┌──────────────┐    │  • brand_id      │   │
39: │  │  • depth │───►│    Brand     │    │  • name, slug    │   │
40: │  └──────────┘    │              │    │  • sku, barcode  │   │
41: │                  │  • id        │    │  • base_price    │   │
42: │  ┌──────────┐    │  • name      │    │  • status, type  │   │
43: │  │ Variant  │    │  • website   │    │  • total_{qty,   │   │
44: │  │          │    └──────────────┘    │    reserved,     │   │
45: │  │  • id    │                        │    available}    │   │
46: │  │  • sku   │    ┌──────────────┐    │  • dimensions    │   │
47: │  │  • price │───►│ ProductMedia │    │  • published_at  │   │
48: │  │  • stock │    │              │    └──────┬───────────┘   │
49: │  │  • image │    │  • id        │           │              │
50: │  └──────────┘    │  • url       │           │              │
51: │                  │  • sort_order│           │              │
52: │  ┌──────────┐    └──────────────┘           │              │
53: │  │Attribute │                               │              │
54: │  │  • id    │    ┌─────────────────┐        │              │
55: │  │  • name  │    │ WarehouseStock  │◄───────┘              │
56: │  │  • type  │    │                 │                       │
57: │  └────┬─────┘    │  • warehouse_id │    ┌──────────────┐   │
58: │       │          │  • product_id   │    │  Warehouse   │   │
59: │  ┌────▼─────┐    │  • quantity     │    │              │   │
60: │  │Attribute │    │  • reserved     │    │  • id        │   │
61: │  │ Value    │    │  • available    │    │  • name      │   │
62: │  │          │    └─────────────────┘    │  • location  │   │
63: │  │  • id    │                           └──────────────┘   │
64: │  │  • value │    ┌──────────────────┐                       │
65: │  └──────────┘    │  StockMovement   │                       │
66: │                  │                  │    ┌──────────────┐   │
67: │  ┌──────────┐    │  • product_id    │    │ TaxCategory  │   │
68: │  │ Pricing  │    │  • warehouse_id  │    │              │   │
69: │  │ Rule     │    │  • quantity      │    │  • id        │   │
70: │  │          │    │  • type (in/out) │    │  • name      │   │
71: │  │  • id    │    │  • reason        │    └──────┬───────┘   │
72: │  │  • scope │    │  • reference     │           │          │
73: │  └──────────┘    └──────────────────┘     ┌─────▼──────┐   │
74: │                                           │  TaxRate   │   │
75: │  ┌──────────┐    ┌──────────────────┐     │            │   │
76: │  │ AuditLog │    │ StockReservation │     │  • rate    │   │
77: │  │          │    │                  │     │  • type    │   │
78: │  │  • event │    │  • order_id      │     └────────────┘   │
79: │  │  • data  │    │  • expires_at    │                       │
80: │  └──────────┘    └──────────────────┘                       │
81: └──────────────────────────────────────────────────────────────┘
82: ```
83: 
84: ## Module Structure
85: 
86: ```
87: app/Modules/Product/
88: ├── Contracts/
89: │   ├── PricingCalculator.php       — Price calculation strategy
90: │   ├── ProductCatalogService.php   — Catalog query interface
91: │   ├── ProductResolver.php         — Product lookup (by id/sku/barcode)
92: │   ├── SKUGenerator.php            — SKU generation strategy
93: │   ├── StockAllocator.php          — Stock allocation strategy
94: │   └── StockChecker.php            — Stock availability queries
95: ├── Database/
96: │   ├── Factories/
97: │   │   ├── BrandFactory.php
98: │   │   ├── CategoryFactory.php
99: │   │   ├── ProductFactory.php
100: │   │   ├── StockReservationFactory.php
101: │   │   ├── VariantFactory.php
102: │   │   ├── WarehouseFactory.php
103: │   │   └── WarehouseStockFactory.php
104: │   └── Migrations/Tenant/
105: │       ├── 2026_05_21_000001_create_categories_table.php
106: │       ├── 2026_05_21_000002_create_brands_table.php
107: │       ├── 2026_05_21_000003_create_attributes_table.php
108: │       ├── 2026_05_21_000004_create_attribute_values_table.php
109: │       ├── 2026_05_21_000005_create_tax_categories_table.php
110: │       ├── 2026_05_21_000006_create_tax_rates_table.php
111: │       ├── 2026_05_21_000007_create_products_table.php
112: │       ├── 2026_05_21_000008_create_category_product_table.php
113: │       ├── 2026_05_21_000009_create_product_attribute_values_table.php
114: │       ├── 2026_05_21_000010_create_product_attribute_text_values_table.php
115: │       ├── 2026_05_21_000011_create_variants_table.php
116: │       ├── 2026_05_21_000012_create_variant_attribute_values_table.php
117: │       ├── 2026_05_21_000013_create_product_media_table.php
118: │       ├── 2026_05_21_000014_create_warehouses_table.php
119: │       ├── 2026_05_21_000015_create_warehouse_stock_table.php
120: │       ├── 2026_05_21_000016_create_stock_reservations_table.php
121: │       ├── 2026_05_21_000017_create_stock_movements_table.php
122: │       ├── 2026_05_21_000018_create_audit_logs_table.php
123: │       └── 2026_05_21_000019_create_pricing_rules_table.php
124: ├── DTOs/
125: │   ├── ProductDTO.php
126: │   ├── ProductSummaryDTO.php
127: │   ├── ProductWithStockDTO.php
128: │   └── VariantDTO.php
129: ├── Enums/
130: │   ├── ProductStatusEnum.php
131: │   ├── ProductTypeEnum.php
132: │   └── StockMovementType.php
133: ├── Events/
134: │   ├── LowStockAlert.php
135: │   ├── ProductArchived.php
136: │   ├── ProductCreated.php
137: │   ├── ProductDeleted.php
138: │   ├── ProductPublished.php
139: │   ├── ProductUpdated.php
140: │   ├── StockDepleted.php
141: │   ├── StockReservationCreated.php
142: │   ├── StockReservationExpired.php
143: │   ├── StockTransferCompleted.php
144: │   ├── StockUpdated.php
145: │   ├── VariantCreated.php
146: │   ├── VariantDeleted.php
147: │   └── VariantUpdated.php
148: ├── Http/
149: │   ├── Controllers/
150: │   │   ├── AttributeController.php
151: │   │   ├── BrandController.php
152: │   │   ├── CategoryController.php
153: │   │   ├── MediaController.php
154: │   │   ├── PricingRuleController.php
155: │   │   ├── ProductController.php
156: │   │   ├── StockController.php
157: │   │   ├── TaxController.php
158: │   │   ├── VariantController.php
159: │   │   └── WarehouseController.php
160: │   └── Requests/
161: │       ├── StockAdjustmentRequest.php
162: │       ├── StockTransferRequest.php
163: │       ├── StoreAttributeRequest.php
164: │       ├── StoreBrandRequest.php
165: │       ├── StoreCategoryRequest.php
166: │       ├── StorePricingRuleRequest.php
167: │       ├── StoreProductRequest.php
168: │       ├── StoreVariantRequest.php
169: │       ├── StoreWarehouseRequest.php
170: │       └── UpdateProductRequest.php
171: ├── Jobs/
172: │   ├── ExpireStockReservationsJob.php
173: │   ├── ExportProductsJob.php
174: │   ├── GenerateProductSKUsJob.php
175: │   ├── ImportProductsJob.php
176: │   ├── IndexProductJob.php
177: │   ├── ReindexAllProductsJob.php
178: │   ├── RemoveProductIndexJob.php
179: │   └── UpdateProductIndexJob.php
180: ├── Listeners/
181: │   ├── DeductProductStock.php         — Listens to OrderCreated
182: │   ├── ExpireStockReservations.php
183: │   ├── GenerateProductSKU.php
184: │   ├── IndexProductForSearch.php
185: │   ├── MarkProductUnavailable.php
186: │   ├── ReleaseExpiredStock.php
187: │   ├── RemoveProductFromSearchIndex.php
188: │   ├── RestoreProductStock.php        — Listens to OrderCancelled
189: │   ├── SendLowStockNotification.php
190: │   ├── SendStockDepletedNotification.php
191: │   ├── TrackStockReservation.php
192: │   ├── UpdateProductSearchIndex.php
193: │   └── UpdateProductStockCache.php
194: ├── Models/
195: │   ├── Attribute.php
196: │   ├── AttributeValue.php
197: │   ├── AuditLog.php
198: │   ├── Brand.php
199: │   ├── Category.php
200: │   ├── PricingRule.php
201: │   ├── Product.php
202: │   ├── ProductAttributeTextValue.php
203: │   ├── ProductAttributeValue.php
204: │   ├── ProductMedia.php
205: │   ├── StockMovement.php
206: │   ├── StockReservation.php
207: │   ├── TaxCategory.php
208: │   ├── TaxRate.php
209: │   ├── Variant.php
210: │   ├── Warehouse.php
211: │   └── WarehouseStock.php
212: ├── Observers/
213: │   ├── ProductObserver.php
214: │   ├── StockReservationObserver.php
215: │   ├── VariantObserver.php
216: │   └── WarehouseStockObserver.php
217: ├── Policies/
218: │   ├── BrandPolicy.php
219: │   ├── CategoryPolicy.php
220: │   ├── ProductPolicy.php
221: │   └── WarehousePolicy.php
222: ├── Rules/
223: │   ├── DifferentParent.php
224: │   ├── StockAvailable.php
225: │   ├── ValidBarcode.php
226: │   └── ValidSKU.php
227: ├── Services/
228: │   ├── AttributeService.php
229: │   ├── BrandService.php
230: │   ├── CategoryService.php
231: │   ├── DefaultSKUGenerator.php
232: │   ├── DefaultStockAllocator.php
233: │   ├── EloquentPricingCalculator.php
234: │   ├── EloquentProductCatalogService.php
235: │   ├── EloquentProductResolver.php
236: │   ├── EloquentStockChecker.php
237: │   ├── MediaService.php
238: │   ├── PricingRuleService.php
239: │   ├── ProductImportService.php
240: │   ├── ProductService.php
241: │   ├── StockAuditService.php
242: │   ├── StockLockService.php
243: │   ├── StockReservationService.php
244: │   ├── StockService.php
245: │   ├── TaxService.php
246: │   ├── VariantService.php
247: │   └── WarehouseService.php
248: ├── Traits/
249: │   ├── HasBarcode.php
250: │   ├── HasMaterializedPath.php
251: │   ├── HasOptimisticLocking.php
252: │   ├── HasProductMedia.php
253: │   ├── HasProductStock.php
254: │   └── Sluggable.php
255: └── ValueObjects/
256:     ├── PriceResult.php
257:     ├── ProductSearchCriteria.php
258:     └── TaxResult.php
259: ```
260: 
261: ## Tenant Isolation Verification
262: 
263: ### 1. Migrations are Tenant-Scoped
264: 
265: All 19 migration files live in `app/Modules/Product/Database/Migrations/Tenant/` and are registered via `loadMigrationsFrom()` in `ProductServiceProvider`:
266: 
267: ```php
268: // ProductServiceProvider::boot()
269: $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations/Tenant');
270: ```
271: 
272: > **Note:** `loadMigrationsFrom()` registers paths with the global migrator, so these migrations will appear in `php artisan migrate --pretend` output. However, the `database.connections.mysql` connection in the central context resolves to a different database than the tenant DBs, and the migrations are designed with `tenant_id` columns for shared mode or full isolation for dedicated mode. The `tenancy.migration_parameters` path in `config/tenancy.php` may also include module paths for `php artisan tenants:migrate` — both approaches coexist. The guard is the `InitializeTenancyByUser` middleware which ensures tenant context is active before product routes are reachable.
273: 
274: ### 2. Models Use Tenant Connection
275: 
276: Every model in `app/Modules/Product/Models/` extends `Illuminate\Database\Eloquent\Model` without the `CentralConnection` trait. This means they resolve to the `tenant` database connection when tenancy is active.
277: 
278: ```php
279: // Product.php — NO CentralConnection → tenant DB
280: class Product extends Model { ... }
281: 
282: // Category.php — NO CentralConnection → tenant DB
283: class Category extends Model { ... }
284: ```
285: 
286: ### 3. Routes Are Behind Tenancy Middleware
287: 
288: All product routes in `routes/tenant.php` are protected by `InitializeTenancyByUser` and `subscription` middleware:
289: 
290: ```php
291: Route::middleware(['web', 'auth', InitializeTenancyByUser::class])->group(function () {
292:     Route::middleware('subscription')->group(function () {
293:         Route::resource('products', ProductController::class);
294:         // category, brand, attribute, inventory routes...
295:     });
296: });
297: ```
298: 
299: ### 4. No Central Product Data
300: 
301: `database/migrations/` (central) contains no product-related tables. All product data exists exclusively in tenant DBs.
302: 
303: ## Cross-Module Communication
304: 
305: ```
306: OrderCreated
307:     ├──► DeductProductStock (Product/Listeners/)     — Deducts inventory
308:     └──► RestoreProductStock (Product/Listeners/)    — Restores on cancel
309: 
310: ProductCreated
311:     ├──► IndexProductForSearch                       — Meilisearch indexing
312:     └──► GenerateProductSKU                          — Auto SKU generation
313: ```
314: 
315: The Product module exposes contracts (`ProductResolver`, `StockChecker`, `PricingCalculator`, `ProductCatalogService`, `SKUGenerator`, `StockAllocator`) bound to implementations in `ProductServiceProvider` for other modules to consume.
316: 
317: ## Frontend Pages
318: 
319: | Page | Route | Path |
320: |------|-------|------|
321: | Product List | `products.index` | `resources/js/pages/Product/Index.tsx` |
322: | Product Create | `products.create` | `resources/js/pages/Product/Create.tsx` |
323: | Product Detail | `products.show` | `resources/js/pages/Product/Show.tsx` |
324: | Product Edit | `products.edit` | `resources/js/pages/Product/Edit.tsx` |
325: | Categories | `categories.index` | `resources/js/pages/Product/Category/Index.tsx` |
326: | Category Detail | `categories.show` | `resources/js/pages/Product/Category/Show.tsx` |
327: | Brands | `brands.index` | `resources/js/pages/Product/Brand/Index.tsx` |
328: | Attributes | `attributes.index` | `resources/js/pages/Product/Attribute/Index.tsx` |
329: | Low Stock | `inventory.index` | `resources/js/pages/Product/Stock/LowStock.tsx` |
330: | Movements | `stock.movements` | `resources/js/pages/Product/Stock/Movements.tsx` |
331: 
332: ## Tenant Database Provisioning Architecture
333: 
334: Tenant databases are **not** created on registration or first login. They are provisioned on **subscription activation**, following proper SaaS resource-gating.
335: 
336: ### Flow
337: 
338: ```
339: Registration → Tenant record in central DB (no tenant DB)
340:      │
341:      ▼
342: Login → InitializeTenancyByUser middleware
343:      │
344:      ├── Tenant has DB? → Normal tenancy init → proceed
345:      │
346:      └── No DB yet?
347:          ├── Route is /billing*? → Allow through (no tenancy init)
348:          └── Route is NOT billing? → Redirect to /billing
349:      │
350:      ▼
351: User selects plan + pays (billing uses only central models)
352:      │
353:      ▼
354: SubscriptionActivated event dispatched
355:      │
356:      ├──► [1] ProvisionTenantDatabase listener (sync)
357:      │       ├── Creates tenant database
358:      │       └── Runs all tenant migrations (products, categories, etc.)
359:      │
360:      └──► [2] SendSubscriptionNotification listener (queued)
361:               └── Sends confirmation email
362:      │
363:      ▼
364: User redirected → Next request → Middleware finds DB → Tenancy initialized → Dashboard
365: ```
366: 
367: ### Key Components
368: 
369: | Component | File | Role |
370: |-----------|------|------|
371: | `ProvisionTenantDatabase` listener | `app/Listeners/ProvisionTenantDatabase.php` | Creates DB + runs migrations on `SubscriptionActivated` |
372: | `InitializeTenancyByUser` middleware | `app/Http/Middleware/InitializeTenancyByUser.php` | Redirects non-billing routes when tenant has no DB |
373: | Billing routes | `routes/web.php` | No tenancy middleware; uses central models only |
374: | Tenant routes | `routes/tenant.php` | Protected by `InitializeTenancyByUser` + `subscription` |
375: 
376: ### Why This Pattern
377: 
378: | Aspect | Benefit |
379: |--------|---------|
380: | **Resource efficiency** | No wasted tenant DBs for unsubscribed users |
381: | **Security** | No tenant data exists until first payment |
382: | **Clean onboarding** | Registration → billing → provision → access (linear flow) |
383: | **Idempotent provisioning** | Listener checks if DB exists before creating |
384: | **Sync provisioning** | DB ready before user is redirected |
385: 
386: ## Authorization Enforcement
387: 
388: `ProductController` uses the `AuthorizesRequests` trait and explicitly calls `$this->authorize('create', Product::class)`, `$this->authorize('update', $product)`, and `$this->authorize('delete', $product)` in `store()`, `update()`, and `destroy()` respectively. This enforces the `ProductPolicy` gates defined in `app/Modules/Product/Policies/ProductPolicy.php`.
389: 
390: **Permissions are stored in the central database.** Custom `App\Models\Permission` and `App\Models\Role` models extend Spatie's stock models with the `CentralConnection` trait, ensuring all authorization lookups always target the central DB. Permissions are seeded via `database/seeders/RolePermissionSeeder.php`, which creates product permissions (`products.view`, `products.create`, `products.update`, `products.delete`, `products.archive`, `products.publish`, `products.duplicate`, `products.import`, `products.export`) and syncs them to the `admin` role (platform admins) and `tenant` role (tenant users).
391: 
392: Other controllers (`CategoryController`, `BrandController`, etc.) should follow the same pattern to enforce their respective policies via `$this->authorize()` calls.
393: 
394: ### Circular Category Reference
395: 
396: The `StoreCategoryRequest` validates `parent_id` via `withValidator()` — checking that a category cannot be set as its own parent. `CategoryService::validateParent()` throws `CircularCategoryException` as a safety net for circular chain detection.
397: 
398: ## Summary
399: 
400: The Product Management module is correctly designed for tenant database isolation:
401: 
402: - ✅ **19 tenant migrations** — all under tenant DB
403: - ✅ **17 tenant models** — no CentralConnection, auto-resolve to tenant DB
404: - ✅ **10 controllers** — behind tenancy + subscription middleware, `ProductController` enforces `AuthorizesRequests`
405: - ✅ **7 service contracts** — for cross-module consumption
406: - ✅ **14 domain events** — for decoupled cross-module communication
407: - ✅ **13 listeners** — including `DeductProductStock` (Order→Inventory bridge)
408: - ✅ **8 queued jobs** — for async operations (import, export, search indexing)
409: - ✅ **4 observers** — for model lifecycle hooks
410: - ✅ **4 authorization policies** — enforced via `$this->authorize()` in `ProductController`
411: - ✅ **10 frontend pages** — Inertia React SPA pages
412: - ✅ **No central product tables** — zero product data in central DB
413: - ✅ **Tenant DB provisioned on subscription** — not on registration or login

(End of file - total 413 lines)
</content>
```

---

## Assistant (Plan · DeepSeek V4 Flash Free · 5.2s)

_Thinking:_

Now I have a comprehensive understanding of the project. Let me also quickly check the remaining session files to get the latest status.

Let me read the latest session file to understand the most recent work done.

**Tool: read**

**Input:**
```json
{
  "filePath": "C:\\dev\\souda\\session-ses_1a9c.md",
  "limit": 200
}
```

**Output:**
```
<path>C:\dev\souda\session-ses_1a9c.md</path>
<type>file</type>
<content>
1: # New session - 2026-05-23T19:05:31.492Z
2: 
3: **Session ID:** ses_1a9c58d9cffeAAc78wxo0TreQL
4: **Created:** 5/24/2026, 1:05:31 AM
5: **Updated:** 5/24/2026, 1:39:28 AM
6: 
7: ---
8: 
9: ## User
10: 
11: in this project's create product form's input is not working... we can't input any data
12: 
13: ---
14: 
15: ## Assistant (Build · DeepSeek V4 Flash Free · 57.9s)
16: 
17: _Thinking:_
18: 
19: The user is reporting that the create product form's input fields are not working - they can't input any data. Let me explore the codebase to find the product-related forms and components.
20: 
21: **Tool: task**
22: 
23: **Input:**
24: ```json
25: {
26:   "description": "Find product form components",
27:   "prompt": "Search the codebase for product-related form components. Look for:\n1. Any files related to \"create product\" or \"product form\" in the resources/js directory\n2. Any Inertia pages related to products\n3. Any route definitions for products (web.php)\n\nReturn the file paths and their contents (or relevant portions) so I can diagnose why input fields aren't working.",
28:   "subagent_type": "explore"
29: }
30: ```
31: 
32: **Output:**
33: ```
34: task_id: ses_1a9c57a8cffe07U6kIvf8wuBkC (for resuming to continue this task if needed)
35: 
36: <task_result>
37: Here is a comprehensive report of all product-related form components, pages, routes, and validation logic in this codebase.
38: 
39: ---
40: 
41: ## 1. Inertia Pages (resources/js)
42: 
43: ### `/Users/salam/Herd/souda/resources/js/modules/product/pages/product-create.tsx`
44: Create page — renders `<ProductFormPage>` with `mode="create"`, a `post` method to `/products`, and passes `categories` and `brands` from server-side props.
45: 
46: ### `/Users/salam/Herd/souda/resources/js/modules/product/pages/product-edit.tsx`
47: Edit page — renders `<ProductFormPage>` with `mode="edit"`, a `put` method to `/products/{id}`, passes `product` mapped via `mapProductToFormData()`, plus `categories` and `brands`.
48: 
49: ### `/Users/salam/Herd/souda/resources/js/modules/product/pages/product-index.tsx`
50: Index/list page — uses `ProductTable`, `ProductFilterBar`, and `ProductBulkActions`. Links to `/products/create` via an Inertia `<Link>`.
51: 
52: ---
53: 
54: ## 2. Main Form Orchestrator
55: 
56: ### `/Users/salam/Herd/souda/resources/js/modules/product/components/product-form-page.tsx`
57: This is the **central form component**. It:
58: - Uses `useProductForm` hook for form state + submission
59: - Renders these sections in order (all within a single `<form>`):
60:   1. `ProductGeneralSection`
61:   2. `ProductPricingSection`
62:   3. `ProductInventorySection`
63:   4. `ProductImageUpload`
64:   5. `VariantGrid`
65:   6. `AttributeManager`
66:   7. `ProductShippingSection` (collapsible)
67:   8. `ProductSeoSection` (collapsible)
68:   9. `FormActions` (submit/cancel)
69: 
70: It constructs `nestedOnChange` callbacks (one per section) that all delegate to the single `onChange` from `useProductForm`.
71: 
72: ---
73: 
74: ## 3. Form Sections (individual input components)
75: 
76: ### `/Users/salam/Herd/souda/resources/js/modules/product/components/product-general-section.tsx`
77: Fields: `name` (text), `status` (native `<select>`), `description` (textarea), `categoryId` (native `<select>`), `brandId` (native `<select>`).
78: 
79: ### `/Users/salam/Herd/souda/resources/js/modules/product/components/product-pricing-section.tsx`
80: Fields: `price`, `comparePrice`, `costPrice` (all type="number"), `isTaxable` (Switch).
81: 
82: ### `/Users/salam/Herd/souda/resources/js/modules/product/components/product-inventory-section.tsx`
83: Fields: `sku` (text), `barcode` (text), `trackStock` (Switch), `quantity`, `lowStockThreshold` (type="number"), `allowBackorders` (Switch).
84: 
85: ### `/Users/salam/Herd/souda/resources/js/modules/product/components/product-shipping-section.tsx`
86: Fields: `weight`, `length`, `width`, `height` (all type="number"), `freeShipping` (Switch).
87: 
88: ### `/Users/salam/Herd/souda/resources/js/modules/product/components/product-seo-section.tsx`
89: Fields: `slug`, `metaTitle` (text), `metaDescription` (textarea).
90: 
91: ### `/Users/salam/Herd/souda/resources/js/modules/product/components/product-image-upload.tsx`
92: Uses a shared `ImageUploader` component for the `images` array field.
93: 
94: ---
95: 
96: ## 4. Form Hook & Types
97: 
98: ### `/Users/salam/Herd/souda/resources/js/modules/product/hooks/use-product-form.ts`
99: Key implementation details:
100: - Uses **`react-hook-form`** with `zodResolver` and `productFormSchema`
101: - The `onChange` function: calls `form.setValue(field, value, { shouldValidate: false, shouldDirty: true })` — note: **validation is skipped on change** (only fires on submit)
102: - `handleSubmit`: calls `form.handleSubmit()`, then uses **`router.put`** or **`router.post`** to send the serialized form data, NOT `form.handleSubmit()` to perform the actual submission via Inertia manually
103: - Server errors from Inertia are mapped via `mapServerErrors` (drops array indices from dot-notation keys)
104: 
105: ### `/Users/salam/Herd/souda/resources/js/modules/product/types/product-form.ts`
106: Zod schema (`productFormSchema`) with these key rules:
107: - `name`: `z.string().min(1).max(255)`
108: - `price`: `z.coerce.number().positive()`
109: - `sku`: `z.string().min(1).max(100)`
110: - `status`: `z.enum(['draft', 'active'])`
111: - `quantity`, `lowStockThreshold`: `z.coerce.number().int().min(0)`
112: - Various optionals with `.nullable()` for `comparePrice`, `costPrice`, `barcode`, etc.
113: - `images`, `variants`, `variantGroups`, `attributes` all default to `[]`
114: 
115: Default values (`defaultProductFormValues`) are also defined here.
116: 
117: ---
118: 
119: ## 5. Data Mapping
120: 
121: ### `/Users/salam/Herd/souda/resources/js/modules/product/lib/map-product.ts`
122: - `mapProductToFormData()`: transforms a server-side `product` object into `ProductFormData` shape (handles cents-to-dollars conversion for prices via `/100`)
123: - `mapFormToPayload()`: reverse mapping for submission (dollars-to-cents via `*100`)
124: 
125: ---
126: 
127: ## 6. Route Definitions
128: 
129: ### `/Users/salam/Herd/souda/routes/tenant.php` (lines 73-74)
130: ```php
131: Route::resource('products', ProductController::class)
132:     ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
133: ```
134: 
135: This generates these routes:
136: | Method | URI | Action | Route Name |
137: |--------|-----|--------|------------|
138: | GET | `/products` | `index` | `products.index` |
139: | GET | `/products/create` | `create` | `products.create` |
140: | POST | `/products` | `store` | `products.store` |
141: | GET | `/products/{product}/edit` | `edit` | `products.edit` |
142: | PUT/PATCH | `/products/{product}` | `update` | `products.update` |
143: | DELETE | `/products/{product}` | `destroy` | `products.destroy` |
144: 
145: Note: The **frontend create page** hardcodes `submitRoute="/products"` with `method="post"`, which maps to the `products.store` route. Similarly, the edit page uses `/products/${product.id}` with `method="put"`.
146: 
147: All routes are under `web`, `auth`, and `InitializeTenancyByUser` middleware, plus a `subscription` middleware.
148: 
149: ---
150: 
151: ## 7. Server-Side Controller & Request Validation
152: 
153: ### `/Users/salam/Herd/souda/app/Modules/Product/Http/Controllers/ProductController.php`
154: Renders Inertia pages at aliases `Product/Index`, `Product/Create`, `Product/Show`, `Product/Edit`. These likely map to Inertia page components registered elsewhere.
155: 
156: ### `/Users/salam/Herd/souda/app/Modules/Product/Http/Requests/StoreProductRequest.php`
157: Server-side validation expects fields like `base_price` (integer, cents), `type` (required), `status`, etc. The frontend sends `base_price` (via `mapFormToPayload`) as `Math.round(price * 100)`.
158: 
159: ### `/Users/salam/Herd/souda/app/Modules/Product/Http/Requests/UpdateProductRequest.php`
160: Same as store, but with `Rule::unique(...)->ignore($this->route('product'))` for `slug`, `sku`, and `barcode`.
161: 
162: ---
163: 
164: ## 8. Shared/UI Components
165: 
166: ### `/Users/salam/Herd/souda/resources/js/modules/shared/components/form-section.tsx`
167: Provides `FormSection` (always-open wrapper) and `FormSectionCollapsible` (toggleable wrapper).
168: 
169: ### `/Users/salam/Herd/souda/resources/js/modules/shared/components/form-actions.tsx`
170: Renders Cancel + Submit buttons in a sticky bottom bar.
171: 
172: ### `/Users/salam/Herd/souda/resources/js/components/ui/input.tsx`
173: Standard styled `<input>` with shadcn/ui classes. Uses `React.ComponentProps<"input">` for full native input passthrough.
174: 
175: ---
176: 
177: ## Potential Issues for Input Fields Not Working
178: 
179: Based on this code review, here are diagnostic leads:
180: 
181: 1. **`shouldValidate: false` in `useProductForm.onChange`** (line 45 of `use-product-form.ts`): `form.setValue(field, value, { shouldValidate: false, shouldDirty: true })` — validation is intentionally skipped on each keystroke. Errors only appear after submit. If you expect inline validation, that won't happen.
182: 
183: 2. **Manual Inertia submission bypassing `form.handleSubmit` validation**: The `handleSubmit` callback calls `form.handleSubmit(onsuccess, onError)(e)` but the `onError` callback is an empty `() => {}`. If Zod validation fails, the submission silently aborts — you must watch the browser console or check `form.formState.errors`.
184: 
185: 3. **`formatMargin` import not found**: In `product-pricing-section.tsx` line 79, `formatMargin` is used but not imported — it's defined locally in the same file at line 98, which is fine.
186: 
187: 4. **Server field name mismatch**: The server-side `StoreProductRequest` expects `base_price` (integer cents), `track_inventory` (boolean), `low_stock_threshold` (integer), etc. The frontend sends payload through `mapFormToPayload` which correctly maps frontend field names to these server names. But if the `ProductDTO` or `ProductService` expects different keys, data could be silently lost.
188: 
189: 5. **No `register()` call — using `setValue` directly**: The form hook doesn't call `form.register()` for each field. In react-hook-form, using `setValue` without registering the field can cause the field to not be included in `handleSubmit`'s output. The fields should be registered either via `<input {...register('name')} />` or via explicit `form.register('name')`. Currently the code uses controlled components with `form.watch()` + `form.setValue()` — this pattern works with react-hook-form v7 but requires the field to exist in `defaultValues` (which it does). However, if react-hook-form doesn't detect a registered field, `handleSubmit` may omit it. You can verify this by logging the data from the `handleSubmit` success callback.
190: 
191: 6. **Inertia page registration**: The controller returns `Inertia::render('Product/Create', ...)` and `Inertia::render('Product/Edit', ...)` — ensure these aliases resolve correctly to your page components (likely via a `PageResolution` callback or a `pages` directory in `resources/js/pages/Product/`).
192: </task_result>
193: ```
194: 
195: ---
196: 
197: ## Assistant (Build · DeepSeek V4 Flash Free · 5.2s)
198: 
199: _Thinking:_
200: 

(Showing lines 1-200 of 8268. Use offset=201 to continue.)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "C:\\dev\\souda\\session-ses_1698.md",
  "limit": 200
}
```

**Output:**
```
<path>C:\dev\souda\session-ses_1698.md</path>
<type>file</type>
<content>
1: # New session - 2026-06-05T06:31:25.952Z
2: 
3: **Session ID:** ses_1698545ffffe03Jlbmy4oIQfNy
4: **Created:** 6/5/2026, 12:31:25 PM
5: **Updated:** 6/5/2026, 1:13:30 PM
6: 
7: ---
8: 
9: ## Assistant (Build · DeepSeek V4 Flash Free · 2.9s)
10: 
11: **Tool: read**
12: 
13: **Input:**
14: ```json
15: {
16:   "filePath": "/Users/salam/Herd/souda/routes/tenant.php"
17: }
18: ```
19: 
20: **Output:**
21: ```
22: <path>/Users/salam/Herd/souda/routes/tenant.php</path>
23: <type>file</type>
24: <content>
25: 1: <?php
26: 2: 
27: 3: declare(strict_types=1);
28: 4: 
29: 5: use App\Http\Controllers\BillingController;
30: 6: use App\Http\Controllers\TaskController;
31: 7: use App\Http\Controllers\TeamController;
32: 8: use App\Http\Middleware\InitializeTenancyByUser;
33: 9: use App\Modules\Product\Http\Controllers\AttributeController;
34: 10: use App\Modules\Product\Http\Controllers\BrandController;
35: 11: use App\Modules\Product\Http\Controllers\CategoryController;
36: 12: use App\Modules\Product\Http\Controllers\ProductController;
37: 13: use App\Modules\Product\Http\Controllers\StockController;
38: 14: use Illuminate\Support\Facades\Route;
39: 15: use Inertia\Inertia;
40: 16: 
41: 17: /*
42: 18: |--------------------------------------------------------------------------
43: 19: | Tenant Routes
44: 20: |--------------------------------------------------------------------------
45: 21: |
46: 22: | Routes that require an authenticated user with an active tenant.
47: 23: | The InitializeTenancyByUser middleware sets the tenant context
48: 24: | based on the authenticated user's tenant_id.
49: 25: |
50: 26: */
51: 27: 
52: 28: Route::middleware(['web', 'auth', InitializeTenancyByUser::class])->group(function () {
53: 29:     Route::middleware('subscription')->group(function () {
54: 30:         Route::get('/dashboard', function () {
55: 31:             return Inertia::render('dashboard');
56: 32:         })->name('dashboard');
57: 33: 
58: 34:         Route::resource('tasks', TaskController::class)
59: 35:             ->only(['index', 'store', 'update', 'destroy']);
60: 36: 
61: 37:         // Product sub-resources — must be before products resource to prevent 405
62: 38:         Route::prefix('products')->group(function () {
63: 39:             // Categories
64: 40:             Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
65: 41:             Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
66: 42:             Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
67: 43:             Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
68: 44:             Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
69: 45:             Route::post('/categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
70: 46: 
71: 47:             // Brands
72: 48:             Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
73: 49:             Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
74: 50:             Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
75: 51:             Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
76: 52: 
77: 53:             // Attributes
78: 54:             Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
79: 55:             Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store');
80: 56:             Route::put('/attributes/{attribute}', [AttributeController::class, 'update'])->name('attributes.update');
81: 57:             Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy'])->name('attributes.destroy');
82: 58:             Route::post('/attributes/{attribute}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
83: 59:             Route::put('/attributes/values/{value}', [AttributeController::class, 'updateValue'])->name('attributes.values.update');
84: 60:             Route::delete('/attributes/values/{value}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');
85: 61: 
86: 62:             // Inventory
87: 63:             Route::get('/inventory', [StockController::class, 'lowStock'])->name('inventory.index');
88: 64: 
89: 65:             // Stock Transfers
90: 66:             Route::get('/stock-transfers', [StockController::class, 'movements'])->name('stock-transfers.index');
91: 67:             Route::post('/stock-transfers', [StockController::class, 'transfer'])->name('stock-transfers.transfer');
92: 68: 
93: 69:             // Stock movements (redirect target for receive/deduct/adjust)
94: 70:             Route::get('/movements', [StockController::class, 'movements'])->name('stock.movements');
95: 71:         });
96: 72: 
97: 73:         Route::resource('products', ProductController::class)
98: 74:             ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
99: 75: 
100: 76:         Route::get('/team', [TeamController::class, 'index'])->name('team.index');
101: 77:         Route::post('/team/invite', [TeamController::class, 'invite'])->name('team.invite')->middleware('seat');
102: 78:         Route::post('/team/accept/{token}', [TeamController::class, 'accept'])->name('team.accept');
103: 79:         Route::delete('/team/{allocation}', [TeamController::class, 'destroy'])->name('team.destroy');
104: 80:         Route::post('/team/{allocation}/resend', [TeamController::class, 'resend'])->name('team.resend');
105: 81:     });
106: 82: });
107: 83: 
108: 84: // SSLCommerz callback - no auth required (external POST from payment gateway)
109: 85: Route::post('/billing/success/sslcommerz', [BillingController::class, 'sslcommerzSuccess'])->name('billing.success.sslcommerz');
110: 86: Route::post('/billing/webhook/sslcommerz', [BillingController::class, 'sslcommerzWebhook'])->name('billing.webhook.sslcommerz');
111: 
112: (End of file - total 86 lines)
113: </content>
114: ```
115: 
116: ---
117: 
118: ## Assistant (Build · DeepSeek V4 Flash Free · 3.2s)
119: 
120: _Thinking:_
121: 
122: Now I have a very comprehensive understanding of the existing system. Let me read the remaining files I need.
123: 
124: **Tool: read**
125: 
126: **Input:**
127: ```json
128: {
129:   "filePath": "/Users/salam/Herd/souda/app/Models/TenantSetting.php"
130: }
131: ```
132: 
133: **Output:**
134: ```
135: <path>/Users/salam/Herd/souda/app/Models/TenantSetting.php</path>
136: <type>file</type>
137: <content>
138: 1: <?php
139: 2: 
140: 3: namespace App\Models;
141: 4: 
142: 5: use Illuminate\Database\Eloquent\Model;
143: 6: 
144: 7: class TenantSetting extends Model
145: 8: {
146: 9:     protected $guarded = [];
147: 10: 
148: 11:     protected function casts(): array
149: 12:     {
150: 13:         return [
151: 14:             'notification_preferences' => 'array',
152: 15:             'feature_toggles' => 'array',
153: 16:             'extra' => 'array',
154: 17:         ];
155: 18:     }
156: 19: 
157: 20:     public static function getDefaults(): array
158: 21:     {
159: 22:         return [
160: 23:             'timezone' => 'UTC',
161: 24:             'locale' => 'en',
162: 25:             'currency' => config('billing.currency', 'USD'),
163: 26:             'date_format' => 'Y-m-d',
164: 27:             'time_format' => 'H:i',
165: 28:             'company_name' => null,
166: 29:             'company_address' => null,
167: 30:             'company_email' => null,
168: 31:             'company_phone' => null,
169: 32:             'default_language' => 'en',
170: 33:             'notification_preferences' => [
171: 34:                 'email_notifications' => true,
172: 35:                 'order_confirmation' => true,
173: 36:                 'low_stock_alerts' => true,
174: 37:                 'new_customer_alerts' => false,
175: 38:             ],
176: 39:             'feature_toggles' => [],
177: 40:             'extra' => [],
178: 41:         ];
179: 42:     }
180: 43: }
181: 
182: (End of file - total 43 lines)
183: </content>
184: ```
185: 
186: ---
187: 
188: ## Assistant (Build · DeepSeek V4 Flash Free · 2.6s)
189: 
190: **Tool: read**
191: 
192: **Input:**
193: ```json
194: {
195:   "filePath": "/Users/salam/Herd/souda/app/Models/Task.php"
196: }
197: ```
198: 
199: **Output:**
200: ```

(Showing lines 1-200 of 7061. Use offset=201 to continue.)
</content>
```

---

## Assistant (Plan · DeepSeek V4 Flash Free · 9.1s)

_Thinking:_

I've now read the comprehensive project documentation. Let me summarize my understanding and acknowledge the user's request about the Automated Inventory Management vision.

The user wants me to:
1. Read docs/agents.md and other MD files to understand current project state
2. Be informed about the architectural plan for Automated Inventory Management
3. They shared a very detailed vision for this

I'm in READ-ONLY mode (plan mode), so I should just acknowledge what I've learned and confirm my understanding. The user wants me to absorb the architecture plan they shared.

我已查阅所有相关文档。以下是我的理解总结：

**当前项目状态：** Phase 0（基础）和 Phase 1（产品模块）已完成。该平台是一个基于 Laravel 12 + React 19 + Inertia v2 的多租户、多行业 SaaS，具有 15 个行业包、16 个租户模板、混合租户架构（共享/专用数据库）。产品模块拥有 17 个模型、20 个服务、14 个事件等所有功能。订阅流程（试用、免费计划、付费计划）已连接并工作。存在已知问题——239 个测试失败（`HasTenantScope`/`Model::$booting`），`StockCalculationTest` 的 3 次失败，以及一些 Payment Gateway 骨架。

**现有库存：** 通过 `WarehouseStock`（数量 + 保留数量）处理，位于产品模块内。使用 `StockService`、`StockReservationService`、`WarehouseService`，以及 `LowStockAlert`/`StockDepleted`/`StockTransferCompleted` 等事件。但它是一个子模块，而不是独立的库存引擎。

**你的自动化库存愿景**完全与现有架构一致——它遵循事件驱动模式（模块通过域事件通信，从不直接操作库存）。提议的 `Inventory/` 模块结构（带有 InventoryEngine、StockMovement、成本计算、预测等）自然适配于 `app/Modules/` 下，遵循与当前 Product 和 Billing 模块相同的模式。

我需要知道的是：你是想让我起草一份实施方案的计划（目录结构、对象、服务、事件、迁移），专注于 Phase 1——核心库存引擎？还是你还有其他问题？

---

## User

only apply and reply in english no chinise please

---

## Assistant (Plan · DeepSeek V4 Flash Free · 25.6s)

_Thinking:_

The user is reminding me to reply in English only. They also want me to plan the Automated Inventory Management implementation. Since I'm in plan mode, I should focus on laying out a comprehensive plan for Phase 1 - Core Inventory Engine.

Let me provide a clear plan of action based on their vision and the current codebase architecture.

Understood. Here's the implementation plan for **Phase 1 — Core Inventory Engine**.

---

## Phase 1 Plan: Core Inventory Engine

### Goal
Extract inventory from the Product sub-module into a standalone `Inventory` module that acts as the single source of truth for all stock operations. No module (POS, Purchase, Sales, etc.) ever writes to stock directly — they publish domain events, and only the Inventory Engine processes them into immutable stock movements.

### Proposed Module Structure

```
app/Modules/Inventory/
├── Contracts/
│   ├── InventoryEngineInterface.php
│   ├── StockMovementEngineInterface.php
│   ├── CostingEngineInterface.php
│   └── ReservationEngineInterface.php
├── DTOs/
│   ├── StockMovementDTO.php
│   ├── InventoryBalanceDTO.php
│   ├── CostLayerDTO.php
│   ├── ReservationDTO.php
│   └── BatchDTO.php
├── Enums/
│   ├── MovementTypeEnum.php      // in, out, transfer, adjustment, reservation
│   ├── CostingMethodEnum.php     // fifo, lifo, weighted_average
│   └── ReservationStatusEnum.php  // active, consumed, expired, cancelled
├── Events/
│   ├── StockMovementCreated.php
│   ├── InventoryBalanceUpdated.php
│   ├── StockDepleted.php
│   ├── LowStockAlert.php
│   ├── StockReserved.php
│   ├── StockReservationConsumed.php
│   ├── StockReservationExpired.php
│   ├── BatchExpiring.php
│   └── CostLayerCreated.php
├── Listeners/
│   ├── RecalculateInventoryBalance.php
│   ├── UpdateProductStockCache.php
│   ├── CheckReorderThreshold.php
│   ├── NotifyLowStock.php
│   └── ReleaseExpiredReservations.php
├── Models/
│   ├── InventoryLedger.php          // immutable — every row is a fact
│   ├── InventoryBalance.php         // current snapshot per warehouse+product+variant
│   ├── CostLayer.php                // FIFO/LIFO layers
│   ├── StockReservation.php         // extend existing
│   ├── Batch.php                    // batch/lot tracking
│   ├── SerialNumber.php             // serial tracking
│   └── Warehouse.php                // move from Product module
├── Database/Migrations/Tenant/
│   ├── create_inventory_ledger_table.php
│   ├── create_inventory_balances_table.php
│   ├── create_cost_layers_table.php
│   ├── create_batches_table.php
│   └── create_serial_numbers_table.php
├── Services/
│   ├── InventoryEngine.php          // orchestrator — single entry point
│   ├── StockMovementEngine.php      // write ledger entries
│   ├── InventoryBalanceService.php  // maintain balances from ledger
│   ├── CostingEngine.php            // weighted average (Phase 1), FIFO (Phase 2)
│   ├── ReservationEngine.php        // reserve, consume, expire
│   ├── BatchService.php
│   ├── SerialNumberService.php
│   ├── WarehouseService.php         // move from Product module
│   └── AuditService.php             // full audit trail
├── Actions/
│   ├── RecordStockMovement.php
│   ├── AdjustInventory.php
│   ├── TransferStock.php
│   ├── ReserveStock.php
│   ├── ConsumeReservation.php
│   └── CreateBatch.php
├── Policies/
│   ├── InventoryPolicy.php
│   ├── WarehousePolicy.php
│   └── BatchPolicy.php
├── ValueObjects/
│   ├── InventoryMovementResult.php
│   ├── BalanceSnapshot.php
│   └── CostCalculation.php
└── Providers/
    └── InventoryServiceProvider.php
```

### Phase 1 Inventory Engine — Data Flow

```
Business Event (e.g., PurchaseReceived)
    ↓
InventoryEngine::handle(event)
    ↓
StockMovementEngine::record(
    product_id, variant_id, warehouse_id,
    quantity, type, reference, cost_basis
)
    ↓
InventoryLedger::create([...])   ← immutable INSERT, never UPDATE/DELETE
    ↓
InventoryBalanceService::recalculate(product_id, warehouse_id)
    ↓
InventoryBalance UPSERT (current snapshot)
    ↓
CostingEngine::apply(product_id)  ← weighted average cost recalculated
    ↓
Domain Events dispatched:
  - StockMovementCreated
  - InventoryBalanceUpdated
  - LowStockAlert (if threshold breached)
  - StockDepleted (if balance = 0)
```

### Key Architectural Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| **Ledger immutability** | INSERT-only, no UPDATE/DELETE | Full audit trail, no reconciliation needed |
| **Balance derivation** | Computed from ledger on write + cached snapshot | Balance is always derivable; snapshot for performance |
| **Costing method** | Weighted Average first | Simpler, covers 80% of use cases; FIFO in Phase 2 |
| **Existing models** | Migrate `WarehouseStock` → `InventoryBalance`, `Warehouse` → new module | Don't break existing code — add adapter layer |
| **Event-driven** | Only domain events trigger stock changes | No module bypasses the engine |
| **Optimistic locking** | Retain on `InventoryBalance` | Prevents race conditions on concurrent writes |

### Migration Path from Existing Product Module

1. Create `app/Modules/Inventory/` with structure above
2. Create tenant migrations for new tables
3. Create `InventoryServiceProvider` and register in `bootstrap/providers.php`
4. Move `Warehouse` model and `WarehouseService` from Product to Inventory (with backward-compatible aliases)
5. Create `InventoryEngine` as the single entry point
6. Migrate existing `StockMovement` logic into `StockMovementEngine`
7. Transform `WarehouseStock` → `InventoryBalance` (new table + snapshot)
8. Add `CostingEngine` with weighted average
9. Wire existing Product events (`StockUpdated`, etc.) through the new engine
10. Keep old models as read-only facades initially, then deprecate

### What Stays in Product Module

- `Product`, `Variant` models (products are not inventory)
- Product-specific events (`ProductCreated`, `ProductUpdated`, etc.)
- Product-specific routes and controllers

### What Moves to Inventory Module

- `Warehouse`, `WarehouseStock`, `StockMovement`, `StockReservation` models
- `WarehouseService`, `StockService`, `StockReservationService`
- `StockAuditService`, `StockLockService`
- Stock events (`StockUpdated`, `StockDepleted`, `LowStockAlert`, etc.)
- Inventory routes (`/inventory`, `/stock-transfers`, `/movements`)

### Verification Strategy

1. Run existing Product tests — zero regressions
2. New unit tests for each engine service
3. Feature test: create purchase → assert ledger entry → assert balance updated
4. Feature test: POS sale → assert stock deducted → assert cost layer applied
5. Feature test: reservation → consume → assert balance changed
6. `php artisan test --compact --filter=Inventory`
7. `vendor/bin/pint --format agent`

---

### Questions for You

1. Should Phase 1 scope include **the full inventory ledger + costing + reservations**, or should we start with just **ledger + balance** and add costing/reservations incrementally?
2. Should the existing `WarehouseStock` model be immediately deprecated, or kept as a read-only facade during the transition?
3. Do you want frontend inventory pages rebuilt alongside the engine, or purely backend-first?

---

## User

we are on initial phase and our target is SME business owner... we need all essential features including the full inventory ledger + costing + reservations... include the full inventory ledger + costing + reservations and want frontend inventory pages rebuilt alongside the engine... and what goes with pure and standard architecuture... now like the agent.md create a dedicated docs/inventory-agent.md... it should be a professional architecture specification of roughly 300–600+ lines, not a short checklist.

It should include sections such as:

Project scope and inventory philosophy
Core architectural principles
Module boundaries and responsibilities
Inventory Engine architecture
Stock Movement Engine
Inventory Ledger design
Costing strategies (Weighted Average, FIFO, LIFO)
Reservation & Allocation engine
Warehouse, location, and transfer architecture
Batch, expiry, and serial number management
Manufacturing and recipe consumption
Automation and rule engine
Event-driven workflow
Domain events and listeners
Database conventions
Model relationships
Industry Pack extension rules
API design conventions
UI/UX principles for SME users
Performance and concurrency requirements
Multi-tenant considerations
Testing strategy
Coding standards and AI guardrails
Development roadmap and implementation phases

That document is essentially a mini software architecture handbook for the Inventory module. 

---

## Assistant (Build · DeepSeek V4 Flash Free · 8.9s)

_Thinking:_

The user wants me to create a comprehensive architecture document `docs/inventory-agent.md` for the Inventory module. This is a writing task - I need to produce a thorough, professional specification document following the style of `docs/agents.md` but focused on the Inventory module.

Let me first read the existing `docs/agents.md` to match its style and depth, then create the new document.

Key things the user wants:
1. ~300-600+ lines
2. Professional architecture specification
3. Sections covering: scope, principles, module boundaries, inventory engine, stock movement, ledger design, costing, reservation, warehouse/transfer, batch/expiry/serial, manufacturing, automation, events, DB conventions, models, industry packs, API design, UI/UX, performance, multi-tenant, testing, coding standards, roadmap
4. Follow the same style as docs/agents.md

I already read docs/agents.md fully. Let me create this document now.

**Tool: read**

**Input:**
```json
{
  "filePath": "C:\\dev\\souda\\docs\\agents.md",
  "limit": 100
}
```

**Output:**
```
<path>C:\dev\souda\docs\agents.md</path>
<type>file</type>
<content>
1: # SOUDA — AI Agent Guide
2: 
3: This file is for AI coding agents. It defines exact conventions, patterns, guardrails, and templates for working on the entire SOUDA platform.
4: 
5: ---
6: 
7: ## Project Identity
8: 
9: - **App**: SOUDA — multi-tenant, multi-vertical business management platform
10: - **PHP**: 8.4.16
11: - **Laravel**: 12
12: - **React**: 19 + Inertia.js v2 + Tailwind CSS v4
13: - **Tenancy**: hybrid (shared `souda_shared` + per-tenant dedicated DBs), using stancl/tenancy v3
14: - **Auth**: Laravel Fortify v1 (headless) with Inertia React views
15: - **Packs**: 15 industry packs in `IndustryPack` interface
16: - **Templates**: 16 `TenantTemplate` implementations per business type
17: - **Config caching**: 24h TTL in Laravel cache + `tenant_configs` table
18: - **Database**: MySQL only (central + shared + dedicated per Enterprise)
19: 
20: ---
21: 
22: ## Global Agent Guardrails (MUST FOLLOW)
23: 
24: ### NEVER
25: - Add hardcoded business type checks (`if ($slug === 'pharmacy')`)
26: - Use `switch`/`case` on industry slugs
27: - Create per-industry tables or columns
28: - Use EAV patterns
29: - Modify core module code when adding a new industry
30: - Use `DB::` when an Eloquent relationship exists
31: - Add inline validation — use Form Requests
32: - Store per-industry logic in controllers, services, or views
33: - Add comments in code (use PHPDoc blocks for complex logic instead)
34: - Allow empty `__construct()` with zero params (unless private)
35: - Use `env()` outside config files — use `config()` always
36: - Commit secrets, .env files, or credentials
37: 
38: ### ALWAYS
39: - Implement `IndustryPack` interface for new industries
40: - Create `TenantTemplate` for new business types
41: - Register packs in `IndustryServiceProvider::boot()`
42: - Register templates in `OnboardingServiceProvider::boot()`
43: - Seed central DB when adding a new business type (BusinessTypeSeeder + BusinessTypeModuleSeeder)
44: - Run Pint after modifying PHP files: `vendor/bin/pint --format agent`
45: - Use `php artisan make:` commands (with `--no-interaction` + correct options)
46: - Use proper return type hints on all methods
47: - Use constructor property promotion
48: - Use curly braces for control structures (even single-line)
49: - Use Form Request classes for validation
50: - Prefer Eloquent relationships over raw queries
51: - Use PHP 8 typed properties throughout
52: - Add `->orderBy('id')` before `->each()` (Laravel 12 requirement)
53: 
54: ---
55: 
56: ## Architecture Overview
57: 
58: ```
59: ┌──────────────────────────────────────────────────────────────────────┐
60: │                         HTTP Request                                 │
61: └──────────────────────────────────────────────────────────────────────┘
62:                                │
63:                       ┌────────▼────────┐
64:                       │  Middleware:     │
65:                       │  web, auth,      │
66:                       │  tenancy, sub    │
67:                       └────────┬────────┘
68:                                │
69:                ┌───────────────┼───────────────┐
70:                │               │               │
71:                ▼               ▼               ▼
72:       ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
73:       │  Central DB  │ │  Shared DB   │ │  Dedicated   │
74:       │   (souda)    │ │(souda_shared)│ │  Tenant DBs  │
75:       │  users       │ │  tenant_     │ │  products    │
76:       │  tenants     │ │  settings    │ │  inventory   │
77:       │  billing     │ │  tasks       │ │  orders      │
78:       │  business_   │ │  tenant_     │ │  categories  │
79:       │  types       │ │  configs     │ │  ...         │
80:       └──────────────┘ └──────────────┘ └──────────────┘
81: ```
82: 
83: ### Database Connections
84: 
85: | Name | Database (prod) | Database (test) | Purpose | Models use |
86: |---|---|---|---|---|
87: | `central` | `souda` | `souda_testing` | Users, tenants, billing, business types, roles | `CentralConnection` trait |
88: | `shared` | `souda_shared` | `souda_shared` | Shared-mode tenant data (stores, settings, tasks, configs) | `HasTenantScope` trait |
89: | `mysql` | `souda` | `souda_testing` | Default / template for dedicated tenant DBs | Normal Eloquent |
90: 
91: In tests, `central` and `mysql` both point to `souda_testing`, so test processes must run sequentially — parallel migrations collide.
92: 
93: ### Tenancy Modes
94: 
95: | Mode | Storage | Isolation | Plans |
96: |---|---|---|---|
97: | `shared` | `souda_shared` table | `tenant_id` column | Free, Starter, Professional |
98: | `dedicated` | `souda_tenant_{uuid}` DB | Separate database | Enterprise |
99: 
100: ---

(Showing lines 1-100 of 1166. Use offset=101 to continue.)
</content>
```

---

## Assistant (Build · DeepSeek V4 Flash Free · 52.9s)

_Thinking:_

I've read the full docs/agents.md already in the previous read. Now let me create the comprehensive docs/inventory-agent.md document. I'll follow the same professional tone, structure, and depth as docs/agents.md but focused entirely on the Inventory module architecture.

Let me write this carefully - 300-600+ lines covering all the sections the user requested.

**Tool: invalid**

**Input:**
```json
{
  "tool": "write",
  "error": "Invalid input for tool write: JSON parsing failed: Text: {\"filePath\": \"C:\\\\dev\\\\souda\\\\docs\\\\inventory-agent.md\", \"content\": \"# SOUDA — Inventory Module Architecture Guide\\n\\nThis file is for AI coding agents implementing the Inventory module. It defines the complete architecture, conventions, guardrails, patterns, interfaces, and implementation rules for the SOUDA Inventory Engine.\\n\\n---\\n\\n## 1. Project Scope & Inventory Philosophy\\n\\n### Vision\\n\\nSOUDA's Inventory module is not a stock-tracking CRUD — it is an **Automated Inventory Engine**. Every business operation (purchase, sale, return, transfer, manufacturing, adjustment) generates domain events. The Inventory Engine consumes those events and produces immutable stock movements, cost layers, balance snapshots, reservations, and alerts — all without manual stock entry.\\n\\n### Target Audience\\n\\nSME business owners in 15+ industries (retail, pharmacy, restaurant, grocery, electronics, fashion, etc.) who need real-time inventory visibility without operational overhead. The system must be intuitive enough that a shop manager can understand stock health in 10 seconds, yet powerful enough to handle batch tracking, expiry management, and multi-warehouse transfers.\\n\\n### Scope — Phase 1\\n\\n| Feature | Included |\\n|---------|----------|\\n| Immutable inventory ledger | ✅ |\\n| Stock movement recording | ✅ |\\n| Inventory balance (real-time snapshot) | ✅ |\\n| Weighted average costing | ✅ |\\n| FIFO costing layers | ✅ |\\n| Stock reservations & allocation | ✅ |\\n| Multi-warehouse support | ✅ |\\n| Warehouse bin/shelf locations | ✅ |\\n| Batch/lot tracking | ✅ |\\n| Serial number tracking | ✅ |\\n| Expiry date management | ✅ |\\n| Low stock & depletion alerts | ✅ |\\n| Stock transfer between warehouses | ✅ |\\n| Audit trail (who did what, when) | ✅ |\\n| Frontend management pages | ✅ |\\n| Industry Pack integration | ✅ |\\n| Automation rules engine | Phase 2 |\\n| Demand forecasting | Phase 2 |\\n| Reorder suggestions | Phase 2 |\\n\\n### One Golden Rule\\n\\n> **No module ever writes to stock directly.** Every module (POS, Purchase, Sales, Manufacturing, Returns) publishes domain events. Only the Inventory Engine translates those events into stock movements and inventory balances. This is non-negotiable.\\n\\n---\\n\\n## 2. Core Architectural Principles\\n\\n### Principle 1: Ledger Immutability\\n\\nThe `inventory_ledger` table is INSERT-only. Once a stock movement is recorded, it is never updated or deleted. Corrections are made via offsetting entries (reversal movements), not row mutations. This guarantees a complete audit trail and prevents data loss.\\n\\n```php\\n// WRONG — never update or delete ledger rows\\nInventoryLedger::where('id', $id)->update(['quantity' => 0]);\\n\\n// RIGHT — reversal\\nStockMovementEngine::record(\\n    productId: $productId,\\n    warehouseId: $warehouseId,\\n    quantity: -$originalQuantity,  // negative reversal\\n    type: MovementTypeEnum::Adjustment,\\n    reference: \\\"Reversal of ledger entry #{$originalId}\\\",\\n    metadata: ['reverses' => $originalId],\\n);\\n```\\n\\n### Principle 2: Event-Driven Decoupling\\n\\nEvery inventory operation is triggered by a domain event. The Inventory Engine never exposes public write methods that bypass events. Modules communicate through events, not service calls.\\n\\n```\\nSaleCompleted ──► InventoryEngine::onSaleCompleted()\\n                      └──► StockMovementEngine::record()\\n                            └──► InventoryBalanceService::recalculate()\\n                                  └──► CostingEngine::apply()\\n```\\n\\n### Principle 3: Balance = Derived State\\n\\n`InventoryBalance` is a computed snapshot derived from the ledger. It is updated synchronously on every write for read performance, but can always be rebuilt from scratch by replaying the ledger. This means:\\n\\n- `InventoryBalance` can be dropped and rebuilt without data loss\\n- The ledger is the source of truth, not the balance\\n- Reconciliation means replaying the ledger and comparing the computed balance\\n\\n### Principle 4: Costing Consistency\\n\\nEvery stock movement carries a `unit_cost` at the time of recording. The Costing Engine maintains cost layers (Weighted Average or FIFO) and recalculates the moving average cost on every inbound movement. Outbound movements consume cost from layers in FIFO order or at the current weighted average.\\n\\n### Principle 5: Industry-Neutral Core\\n\\nThe core inventory engine (ledger, balance, costing, reservations) is completely industry-agnostic. Industry-specific behaviors (FEFO for pharmacy, recipe deduction for restaurants, IMEI for electronics) are implemented via Industry Pack feature flags and plugin listeners — never via hardcoded checks in the engine.\\n\\n---\\n\\n## 3. Module Boundaries & Responsibilities\\n\\n```\\n┌─────────────────────────────────────────────────────────────────┐\\n│                     Inventory Module                              │\\n│                                                                   │\\n│  ┌─────────────────┐  ┌────────────────┐  ┌──────────────────┐  │\\n│  │ InventoryEngine  │  │ StockMovement  │  │ InventoryBalance  │  │\\n│  │ (Orchestrator)   │──│ Engine         │──│ Service           │  │\\n│  └─────────┬───────┘  └────────────────┘  └──────────────────┘  │\\n│            │                                                      │\\n│  ┌─────────▼───────┐  ┌────────────────┐  ┌──────────────────┐  │\\n│  │ CostingEngine    │  │ Reservation    │  │ BatchService      │  │\\n│  │ (FIFO/WAVG)      │──│ Engine         │──│ (Lot/Expiry)      │  │\\n│  └─────────────────┘  └────────────────┘  └──────────────────┘  │\\n│                                                                   │\\n│  ┌─────────────────┐  ┌────────────────┐  ┌──────────────────┐  │\\n│  │ SerialNumber     │  │ AuditService   │  │ AlertEngine       │  │\\n│  │ Service          │──│                │──│ (Thresholds)      │  │\\n│  └─────────────────┘  └────────────────┘  └──────────────────┘  │\\n│                                                                   │\\n│  ┌─────────────────┐  ┌────────────────┐                         │\\n│  │ WarehouseService │  │ TransferEngine │                         │\\n│  └─────────────────┘  └────────────────┘                         │\\n└─────────────────────────────────────────────────────────────────┘\\n```\\n\\n### Module Responsibilities\\n\\n| Component | Responsibility |\\n|-----------|---------------|\\n| `InventoryEngine` | Single public entry point. Subscribes to domain events, validates business rules, delegates to sub-engines |\\n| `StockMovementEngine` | Records immutable ledger entries. Generates movement reference numbers |\\n| `InventoryBalanceService` | Maintains real-time balance snapshots. Rebuilds from ledger on demand |\\n| `CostingEngine` | Manages cost layers. Weighted average recalculation. FIFO layer consumption |\\n| `ReservationEngine` | Creates, consumes, expires, and cancels stock reservations |\\n| `BatchService` | Batch/lot tracking. Expiry date management. FEFO picking order |\\n| `SerialNumberService` | Serial/IMEI tracking per unit. Status lifecycle: available → reserved → sold → returned |\\n| `WarehouseService` | Warehouse and bin/shelf location management |\\n| `TransferEngine` | Orchestrates inter-warehouse stock transfers with in-transit status |\\n| `AuditService` | Logs all inventory operations with before/after snapshots |\\n| `AlertEngine` | Evaluates thresholds (low stock, expiry, overstock, dead stock). Dispatches alerts |\\n\\n---\\n\\n## 4. Inventory Engine Architecture\\n\\n### InventoryEngine (Orchestrator)\\n\\nThe `InventoryEngine` is the single public facade for all inventory write operations. It receives domain events, validates them against business rules, and coordinates the sub-engines.\\n\\n```php\\nclass InventoryEngine\\n{\\n    public function __construct(\\n        private StockMovementEngine $movementEngine,\\n        private InventoryBalanceService $balanceService,\\n        private CostingEngine $costingEngine,\\n        private ReservationEngine $reservationEngine,\\n        private BatchService $batchService,\\n        private SerialNumberService $serialService,\\n        private TransferEngine $transferEngine,\\n        private AuditService $auditService,\\n        private AlertEngine $alertEngine,\\n        private TenantConfig $config,\\n    ) {}\\n\\n    // These are the ONLY public write methods on this class:\\n    public function handleSaleCompleted(SaleCompleted $event): void;\\n    public function handlePurchaseReceived(PurchaseReceived $event): void;\\n    public function handleReturnApproved(ReturnApproved $event): void;\\n    public function handleAdjustmentApproved(AdjustmentApproved $event): void;\\n    public function handleTransferInitiated(TransferInitiated $event): void;\\n    public function handleTransferCompleted(TransferCompleted $event): void;\\n    public function handleProductionCompleted(ProductionCompleted $event): void;\\n    public function handleRecipeConsumed(RecipeConsumed $event): void;\\n    public function handleReservationCreated(ReservationCreated $event): void;\\n    public function handleReservationCancelled(ReservationCancelled $event): void;\\n}\\n```\\n\\n### Engine Workflow (Example: Sale)\\n\\n```\\n1. SaleCompleted event received\\n2. InventoryEngine::handleSaleCompleted()\\n3. → Validate: product exists, warehouse exists, quantity is positive\\n4. → If batches tracked: resolve which batches to deduct (FIFO/FEFO)\\n5. → If serial tracked: validate each serial number is available\\n6. → ReserveEngine::consumeReservation(orderReference) if pre-reserved\\n7. → StockMovementEngine::record(sale, -qty, unitCost, reference)\\n8. → InventoryBalanceService::recalculate(productId, warehouseId)\\n9. → CostingEngine::consumeLayers(productId, qty, costingMethod)\\n10. → BatchService::deduct(productId, batchId, qty) if batch tracked\\n11. → SerialNumberService::markAsSold(serialNumbers) if serial tracked\\n12. → AuditService::log('sale', before, after, actor)\\n13. → AlertEngine::evaluate(productId, warehouseId)\\n14. → Dispatch InventoryBalanceUpdated, StockDepleted (if applicable)\\n```\\n\\n---\\n\\n## 5. Stock Movement Engine\\n\\n### Movement Types\\n\\n```php\\nenum MovementTypeEnum: string\\n{\\n    case PurchaseReceipt = 'purchase_receipt';      // inbound\\n    case SaleDeduction = 'sale_deduction';           // outbound\\n    case ReturnRestock = 'return_restock';           // inbound\\n    case TransferOut = 'transfer_out';               // outbound (sent)\\n    case TransferIn = 'transfer_in';                 // inbound (received)\\n    case AdjustmentIncrease = 'adjustment_increase'; // inbound\\n    case AdjustmentDecrease = 'adjustment_decrease'; // outbound\\n    case ProductionOutput = 'production_output';     // inbound\\n    case RecipeConsumption = 'recipe_consumption';   // outbound\\n    case ReservationDeduction = 'reservation_deduction'; // outbound\\n    case ReservationRelease = 'reservation_release';     // inbound\\n    case Reversal = 'reversal';                      // both (offsets previous)\\n    case InitialStock = 'initial_stock';             // inbound (first entry)\\n}\\n```\\n\\n### StockMovementEngine\\n\\n```php\\nclass StockMovementEngine\\n{\\n    public function record(\\n        string $productId,\\n        ?string $variantId,\\n        string $warehouseId,\\n        int $quantity,                    // positive for inbound, negative for outbound\\n        MovementTypeEnum $type,\\n        string $reference,                // order number, transfer ID, etc.\\n        ?int $unitCost = null,            // in cents\\n        ?string $batchId = null,\\n        ?array $serialNumbers = null,\\n        ?string $description = null,\\n        array $metadata = [],\\n    ): InventoryLedger;\\n\\n    public function findById(string $id): ?InventoryLedger;\\n    public function findByReference(string $reference): Collection;\\n    public function findByProduct(string $productId, array $filters = []): Collection;\\n}\\n```\\n\\n### Reference Number Format\\n\\nAuto-generated reference numbers follow this pattern:\\n\\n```\\nMOV-{YYYYMMDD}-{XXXX}\\n    Example: MOV-20260715-0042\\n\\nPrefixes by type:\\n  PUR-{date}-{seq}   → Purchase Receipt\\n  SAL-{date}-{seq}   → Sale Deduction\\n  RET-{date}-{seq}   → Return Restock\\n  TRF-{date}-{seq}   → Transfer\\n  ADJ-{date}-{seq}   → Adjustment\\n  PROD-{date}-{seq}  → Production\\n  REC-{date}-{seq}   → Recipe Consumption\\n```\\n\\n---\\n\\n## 6. Inventory Ledger Design\\n\\n### Table: `inventory_ledger`\\n\\n```php\\nSchema::create('inventory_ledger', function (Blueprint $table) {\\n    $table->id();\\n    $table->string('tenant_id', 36);            // shared mode scoping\\n    $table->ulid('product_id');                  // FK to products\\n    $table->ulid('variant_id')->nullable();      // FK to variants\\n    $table->unsignedBigInteger('warehouse_id');  // FK to warehouses\\n    $table->unsignedBigInteger('bin_id')->nullable(); // FK to bins\\n    $table->integer('quantity');                 // positive = in, negative = out\\n    $table->integer('quantity_before');          // snapshot before movement\\n    $table->integer('quantity_after');           // snapshot after movement\\n    $table->string('movement_type', 30);         // MovementTypeEnum value\\n    $table->string('reference', 100);            // order/invoice/transfer ref\\n    $table->string('reference_type', 30);        // class of reference (order, transfer, etc.)\\n    $table->unsignedBigInteger('batch_id')->nullable();\\n    $table->json('serial_numbers')->nullable();  // array of serials\\n    $table->bigInteger('unit_cost')->nullable(); // cost per unit in cents\\n    $table->bigInteger('total_cost')->nullable(); // unit_cost * |quantity|\\n    $table->text('description')->nullable();\\n    $table->json('metadata')->nullable();        // extensible payload\\n    $table->string('created_by', 36)->nullable(); // user ID\\n    $table->timestamp('created_at')->useCurrent();\\n\\n    $table->index(['product_id', 'warehouse_id', 'created_at']);\\n    $table->index(['reference', 'reference_type']);\\n    $table->index(['batch_id']);\\n    $table->index(['tenant_id', 'created_at']);\\n});\\n```\\n\\n### Table Rules\\n\\n- No `updated_at` — ledger entries are immutable\\n- No `deleted_at` — soft deletes defeat the audit purpose\\n- `quantity_before` and `quantity_after` as integrity checkpoints\\n- `unit_cost` is always in cents (integer) to avoid floating-point drift\\n- `total_cost = unit_cost * |quantity|` — pre-calculated for reporting performance\\n- `metadata` JSON column for extensibility without schema changes (source IP, reason, approval ID, etc.)\\n\\n---\\n\\n## 7. Inventory Balance\\n\\n### Table: `inventory_balances`\\n\\nThe real-time snapshot table. UPSERT-ed on every movement.\\n\\n```php\\nSchema::create('inventory_balances', function (Blueprint $table) {\\n    $table->id();\\n    $table->string('tenant_id', 36);\\n    $table->ulid('product_id');\\n    $table->ulid('variant_id')->nullable();\\n    $table->unsignedBigInteger('warehouse_id');\\n    $table->integer('quantity')->default(0);\\n    $table->integer('reserved_quantity')->default(0);\\n    $table->integer('available_quantity')->default(0); // quantity - reserved\\n    $table->bigInteger('average_unit_cost')->default(0); // weighted avg in cents\\n    $table->bigInteger('total_stock_value')->default(0); // unit_cost * quantity\\n    $table->unsignedBigInteger('lock_version')->default(0); // optimistic locking\\n    $table->timestamp('last_movement_at')->nullable();\\n    $table->timestamps();\\n\\n    $table->unique(['product_id', 'variant_id', 'warehouse_id']);\\n    $table->index(['warehouse_id', 'quantity']);\\n});\\n```\\n\\n### Balance Calculation\\n\\n```php\\nclass InventoryBalanceService\\n{\\n    // Called after every movement. UPSERTs the balance row.\\n    public function recalculate(\\n        string $productId,\\n        ?string $variantId,\\n        string $warehouseId,\\n    ): void;\\n\\n    // Full rebuild from ledger — for reconciliation or first-time setup\\n    public function rebuildFromLedger(\\n        ?string $productId = null,\\n        ?string $warehouseId = null,\\n    ): void;\\n\\n    // Bulk update after batch operations\\n    public function recalculateBatch(array $productWarehousePairs): void;\\n}\\n```\\n\\n### Optimistic Locking\\n\\n`lock_version` uses Laravel's `HasOptimisticLocking` trait. On concurrent writes, the first request succeeds and the second receives a `ModelNotFoundException` — the caller must retry. This prevents double-deduction during high-concurrency POS scenarios.\\n\\n```php\\ntry {\\n    $balance = InventoryBalance::where([\\n        'product_id' => $productId,\\n        'warehouse_id' => $warehouseId,\\n    ])->firstOrFail();\\n\\n    $balance->decrement('quantity', $quantity); // uses lock_version check\\n} catch (ModelNotFoundException) {\\n    // Concurrent update detected — retry the entire operation\\n    throw new ConcurrentInventoryUpdateException($productId, $warehouseId);\\n}\\n```\\n\\n---\\n\\n## 8. Costing Strategies\\n\\n### CostLayer Model\\n\\n```php\\nSchema::create('cost_layers', function (Blueprint $table) {\\n    $table->id();\\n    $table->string('tenant_id', 36);\\n    $table->ulid('product_id');\\n    $table->ulid('variant_id')->nullable();\\n    $table->unsignedBigInteger('warehouse_id');\\n    $table->bigInteger('unit_cost');             // cost per unit in cents\\n    $table->integer('quantity_remaining');        // units still in this layer\\n    $table->integer('quantity_original');         // original layer size\\n    $table->string('costing_method', 20);         // weighted_average or fifo\\n    $table->unsignedBigInteger('ledger_entry_id'); // FK to source ledger entry\\n    $table->timestamp('created_at')->useCurrent();\\n\\n    $table->index(['product_id', 'warehouse_id', 'costing_method']);\\n});\\n```\\n\\n### Weighted Average (Default)\\n\\nWeighted average is the default costing method. It is simple, fair, and covers 80% of SME use cases.\\n\\n```php\\nclass CostingEngine\\n{\\n    public function applyInbound(CostLayer $layer): void\\n    {\\n        // Recalculate weighted average for the product+warehouse\\n        $currentBalance = InventoryBalance::where([\\n            'product_id' => $layer->product_id,\\n            'warehouse_id' => $layer->warehouse_id,\\n        ])->first();\\n\\n        $totalCost = ($currentBalance->quantity * $currentBalance->average_unit_cost)\\n                   + ($layer->quantity_remaining * $layer->unit_cost);\\n        $totalQty = $currentBalance->quantity + $layer->quantity_remaining;\\n\\n        $newAvg = $totalQty > 0 ? (int) round($totalCost / $totalQty) : 0;\\n\\n        $currentBalance->update([\\n            'average_unit_cost' => $newAvg,\\n            'total_stock_value' => $totalQty * $newAvg,\\n        ]);\\n    }\\n\\n    public function consumeOutbound(\\n        string $productId,\\n        ?string $variantId,\\n        string $warehouseId,\\n        int $quantity,\\n    ): int {\\n        // Returns the cost to deduct (in cents)\\n        // Under weighted average: quantity * current average\\n        $balance = InventoryBalance::where([\\n            'product_id' => $productId,\\n            'warehouse_id' => $warehouseId,\\n        ])->firstOrFail();\\n\\n        return $quantity * $balance->average_unit_cost;\\n    }\\n}\\n```\\n\\n### FIFO\\n\\nFIFO consumes the oldest cost layers first. Enabled per-product via a `costing_method` field.\\n\\n```php\\nclass FifoCostingEngine\\n{\\n    public function consumeOutbound(\\n        string $productId,\\n        ?string $variantId,\\n        string $warehouseId,\\n        int $quantity,\\n    ): array {\\n        // Consume from oldest layers first\\n        $layers = CostLayer::where([\\n            'product_id' => $productId,\\n            'warehouse_id' => $warehouseId,\\n            'costing_method' => 'fifo',\\n        ])\\n        ->where('quantity_remaining', '>', 0)\\n        ->orderBy('created_at')\\n        ->get();\\n\\n        $totalCost = 0;\\n        $remaining = $quantity;\\n\\n        foreach ($layers as $layer) {\\n            $consume = min($remaining, $layer->quantity_remaining);\\n            $layer->decrement('quantity_remaining', $consume);\\n            $totalCost += $consume * $layer->unit_cost;\\n            $remaining -= $consume;\\n            if ($remaining <= 0) break;\\n        }\\n\\n        return [\\n            'total_cost' => $totalCost,\\n            'unit_cost' => $quantity > 0 ? (int) round($totalCost / $quantity) : 0,\\n            'layers_consumed' => $layers->count(),\\n        ];\\n    }\\n}\\n```\\n\\n### Costing Method Selection\\n\\nThe costing method is resolved per-product:\\n\\n```php\\n// Product model\\nclass Product extends Model\\n{\\n    public function costingMethod(): string\\n    {\\n        return $this->costing_method ?? config('inventory.default_costing_method', 'weighted_average');\\n    }\\n}\\n```\\n\\nThe `CostingEngine` delegates to the appropriate strategy:\\n\\n```php\\nclass CostingEngine\\n{\\n    public function __construct(\\n        private WeightedAverageCosting $weightedAverage,\\n        private FifoCosting $fifo,\\n    ) {}\\n\\n    public function consume(string $productId, ?string $variantId, string $warehouseId, int $quantity): CostResult\\n    {\\n        $product = Product::findOrFail($productId);\\n        $strategy = $product->costingMethod() === 'fifo' ? $this->fifo : $this->weightedAverage;\\n        return $strategy->consumeOutbound($productId, $variantId, $warehouseId, $quantity);\\n    }\\n}\\n```\\n\\n---\\n\\n## 9. Reservation & Allocation Engine\\n\\n### Reservation Lifecycle\\n\\n```\\nCreated (active) ──► Consumed (stock deducted)\\n    │                      │\\n    ▼                      ▼\\nExpired (auto-release)   Cancelled (manual)\\n```\\n\\n### Table: `stock_reservations`\\n\\n```php\\nSchema::create('stock_reservations', function (Blueprint $table) {\\n    $table->id();\\n    $table->string('tenant_id', 36);\\n    $table->ulid('product_id');\\n    $table->ulid('variant_id')->nullable();\\n    $table->unsignedBigInteger('warehouse_id');\\n    $table->integer('quantity');\\n    $table->string('status', 20); // active, consumed, expired, cancelled\\n    $table->string('reference', 100); // order ID, cart ID\\n    $table->string('reference_type', 30);\\n    $table->timestamp('expires_at')->nullable();\\n    $table->timestamp('consumed_at')->nullable();\\n    $table->string('created_by', 36)->nullable();\\n    $table->timestamps();\\n\\n    $table->index(['status', 'expires_at']);\\n    $table->index(['product_id', 'warehouse_id', 'status']);\\n    $table->index(['reference', 'reference_type']);\\n});\\n```\\n\\n### ReservationEngine\\n\\n```php\\nclass ReservationEngine\\n{\\n    public function reserve(\\n        string $productId,\\n        ?string $variantId,\\n        string $warehouseId,\\n        int $quantity,\\n        string $reference,\\n        ?int $ttlMinutes = 30,  // auto-expire after.\nError message: JSON Parse error: Unterminated string"
}
```

**Output:**
```
The arguments provided to the tool are invalid: Invalid input for tool write: JSON parsing failed: Text: {"filePath": "C:\\dev\\souda\\docs\\inventory-agent.md", "content": "# SOUDA — Inventory Module Architecture Guide\n\nThis file is for AI coding agents implementing the Inventory module. It defines the complete architecture, conventions, guardrails, patterns, interfaces, and implementation rules for the SOUDA Inventory Engine.\n\n---\n\n## 1. Project Scope & Inventory Philosophy\n\n### Vision\n\nSOUDA's Inventory module is not a stock-tracking CRUD — it is an **Automated Inventory Engine**. Every business operation (purchase, sale, return, transfer, manufacturing, adjustment) generates domain events. The Inventory Engine consumes those events and produces immutable stock movements, cost layers, balance snapshots, reservations, and alerts — all without manual stock entry.\n\n### Target Audience\n\nSME business owners in 15+ industries (retail, pharmacy, restaurant, grocery, electronics, fashion, etc.) who need real-time inventory visibility without operational overhead. The system must be intuitive enough that a shop manager can understand stock health in 10 seconds, yet powerful enough to handle batch tracking, expiry management, and multi-warehouse transfers.\n\n### Scope — Phase 1\n\n| Feature | Included |\n|---------|----------|\n| Immutable inventory ledger | ✅ |\n| Stock movement recording | ✅ |\n| Inventory balance (real-time snapshot) | ✅ |\n| Weighted average costing | ✅ |\n| FIFO costing layers | ✅ |\n| Stock reservations & allocation | ✅ |\n| Multi-warehouse support | ✅ |\n| Warehouse bin/shelf locations | ✅ |\n| Batch/lot tracking | ✅ |\n| Serial number tracking | ✅ |\n| Expiry date management | ✅ |\n| Low stock & depletion alerts | ✅ |\n| Stock transfer between warehouses | ✅ |\n| Audit trail (who did what, when) | ✅ |\n| Frontend management pages | ✅ |\n| Industry Pack integration | ✅ |\n| Automation rules engine | Phase 2 |\n| Demand forecasting | Phase 2 |\n| Reorder suggestions | Phase 2 |\n\n### One Golden Rule\n\n> **No module ever writes to stock directly.** Every module (POS, Purchase, Sales, Manufacturing, Returns) publishes domain events. Only the Inventory Engine translates those events into stock movements and inventory balances. This is non-negotiable.\n\n---\n\n## 2. Core Architectural Principles\n\n### Principle 1: Ledger Immutability\n\nThe `inventory_ledger` table is INSERT-only. Once a stock movement is recorded, it is never updated or deleted. Corrections are made via offsetting entries (reversal movements), not row mutations. This guarantees a complete audit trail and prevents data loss.\n\n```php\n// WRONG — never update or delete ledger rows\nInventoryLedger::where('id', $id)->update(['quantity' => 0]);\n\n// RIGHT — reversal\nStockMovementEngine::record(\n    productId: $productId,\n    warehouseId: $warehouseId,\n    quantity: -$originalQuantity,  // negative reversal\n    type: MovementTypeEnum::Adjustment,\n    reference: \"Reversal of ledger entry #{$originalId}\",\n    metadata: ['reverses' => $originalId],\n);\n```\n\n### Principle 2: Event-Driven Decoupling\n\nEvery inventory operation is triggered by a domain event. The Inventory Engine never exposes public write methods that bypass events. Modules communicate through events, not service calls.\n\n```\nSaleCompleted ──► InventoryEngine::onSaleCompleted()\n                      └──► StockMovementEngine::record()\n                            └──► InventoryBalanceService::recalculate()\n                                  └──► CostingEngine::apply()\n```\n\n### Principle 3: Balance = Derived State\n\n`InventoryBalance` is a computed snapshot derived from the ledger. It is updated synchronously on every write for read performance, but can always be rebuilt from scratch by replaying the ledger. This means:\n\n- `InventoryBalance` can be dropped and rebuilt without data loss\n- The ledger is the source of truth, not the balance\n- Reconciliation means replaying the ledger and comparing the computed balance\n\n### Principle 4: Costing Consistency\n\nEvery stock movement carries a `unit_cost` at the time of recording. The Costing Engine maintains cost layers (Weighted Average or FIFO) and recalculates the moving average cost on every inbound movement. Outbound movements consume cost from layers in FIFO order or at the current weighted average.\n\n### Principle 5: Industry-Neutral Core\n\nThe core inventory engine (ledger, balance, costing, reservations) is completely industry-agnostic. Industry-specific behaviors (FEFO for pharmacy, recipe deduction for restaurants, IMEI for electronics) are implemented via Industry Pack feature flags and plugin listeners — never via hardcoded checks in the engine.\n\n---\n\n## 3. Module Boundaries & Responsibilities\n\n```\n┌─────────────────────────────────────────────────────────────────┐\n│                     Inventory Module                              │\n│                                                                   │\n│  ┌─────────────────┐  ┌────────────────┐  ┌──────────────────┐  │\n│  │ InventoryEngine  │  │ StockMovement  │  │ InventoryBalance  │  │\n│  │ (Orchestrator)   │──│ Engine         │──│ Service           │  │\n│  └─────────┬───────┘  └────────────────┘  └──────────────────┘  │\n│            │                                                      │\n│  ┌─────────▼───────┐  ┌────────────────┐  ┌──────────────────┐  │\n│  │ CostingEngine    │  │ Reservation    │  │ BatchService      │  │\n│  │ (FIFO/WAVG)      │──│ Engine         │──│ (Lot/Expiry)      │  │\n│  └─────────────────┘  └────────────────┘  └──────────────────┘  │\n│                                                                   │\n│  ┌─────────────────┐  ┌────────────────┐  ┌──────────────────┐  │\n│  │ SerialNumber     │  │ AuditService   │  │ AlertEngine       │  │\n│  │ Service          │──│                │──│ (Thresholds)      │  │\n│  └─────────────────┘  └────────────────┘  └──────────────────┘  │\n│                                                                   │\n│  ┌─────────────────┐  ┌────────────────┐                         │\n│  │ WarehouseService │  │ TransferEngine │                         │\n│  └─────────────────┘  └────────────────┘                         │\n└─────────────────────────────────────────────────────────────────┘\n```\n\n### Module Responsibilities\n\n| Component | Responsibility |\n|-----------|---------------|\n| `InventoryEngine` | Single public entry point. Subscribes to domain events, validates business rules, delegates to sub-engines |\n| `StockMovementEngine` | Records immutable ledger entries. Generates movement reference numbers |\n| `InventoryBalanceService` | Maintains real-time balance snapshots. Rebuilds from ledger on demand |\n| `CostingEngine` | Manages cost layers. Weighted average recalculation. FIFO layer consumption |\n| `ReservationEngine` | Creates, consumes, expires, and cancels stock reservations |\n| `BatchService` | Batch/lot tracking. Expiry date management. FEFO picking order |\n| `SerialNumberService` | Serial/IMEI tracking per unit. Status lifecycle: available → reserved → sold → returned |\n| `WarehouseService` | Warehouse and bin/shelf location management |\n| `TransferEngine` | Orchestrates inter-warehouse stock transfers with in-transit status |\n| `AuditService` | Logs all inventory operations with before/after snapshots |\n| `AlertEngine` | Evaluates thresholds (low stock, expiry, overstock, dead stock). Dispatches alerts |\n\n---\n\n## 4. Inventory Engine Architecture\n\n### InventoryEngine (Orchestrator)\n\nThe `InventoryEngine` is the single public facade for all inventory write operations. It receives domain events, validates them against business rules, and coordinates the sub-engines.\n\n```php\nclass InventoryEngine\n{\n    public function __construct(\n        private StockMovementEngine $movementEngine,\n        private InventoryBalanceService $balanceService,\n        private CostingEngine $costingEngine,\n        private ReservationEngine $reservationEngine,\n        private BatchService $batchService,\n        private SerialNumberService $serialService,\n        private TransferEngine $transferEngine,\n        private AuditService $auditService,\n        private AlertEngine $alertEngine,\n        private TenantConfig $config,\n    ) {}\n\n    // These are the ONLY public write methods on this class:\n    public function handleSaleCompleted(SaleCompleted $event): void;\n    public function handlePurchaseReceived(PurchaseReceived $event): void;\n    public function handleReturnApproved(ReturnApproved $event): void;\n    public function handleAdjustmentApproved(AdjustmentApproved $event): void;\n    public function handleTransferInitiated(TransferInitiated $event): void;\n    public function handleTransferCompleted(TransferCompleted $event): void;\n    public function handleProductionCompleted(ProductionCompleted $event): void;\n    public function handleRecipeConsumed(RecipeConsumed $event): void;\n    public function handleReservationCreated(ReservationCreated $event): void;\n    public function handleReservationCancelled(ReservationCancelled $event): void;\n}\n```\n\n### Engine Workflow (Example: Sale)\n\n```\n1. SaleCompleted event received\n2. InventoryEngine::handleSaleCompleted()\n3. → Validate: product exists, warehouse exists, quantity is positive\n4. → If batches tracked: resolve which batches to deduct (FIFO/FEFO)\n5. → If serial tracked: validate each serial number is available\n6. → ReserveEngine::consumeReservation(orderReference) if pre-reserved\n7. → StockMovementEngine::record(sale, -qty, unitCost, reference)\n8. → InventoryBalanceService::recalculate(productId, warehouseId)\n9. → CostingEngine::consumeLayers(productId, qty, costingMethod)\n10. → BatchService::deduct(productId, batchId, qty) if batch tracked\n11. → SerialNumberService::markAsSold(serialNumbers) if serial tracked\n12. → AuditService::log('sale', before, after, actor)\n13. → AlertEngine::evaluate(productId, warehouseId)\n14. → Dispatch InventoryBalanceUpdated, StockDepleted (if applicable)\n```\n\n---\n\n## 5. Stock Movement Engine\n\n### Movement Types\n\n```php\nenum MovementTypeEnum: string\n{\n    case PurchaseReceipt = 'purchase_receipt';      // inbound\n    case SaleDeduction = 'sale_deduction';           // outbound\n    case ReturnRestock = 'return_restock';           // inbound\n    case TransferOut = 'transfer_out';               // outbound (sent)\n    case TransferIn = 'transfer_in';                 // inbound (received)\n    case AdjustmentIncrease = 'adjustment_increase'; // inbound\n    case AdjustmentDecrease = 'adjustment_decrease'; // outbound\n    case ProductionOutput = 'production_output';     // inbound\n    case RecipeConsumption = 'recipe_consumption';   // outbound\n    case ReservationDeduction = 'reservation_deduction'; // outbound\n    case ReservationRelease = 'reservation_release';     // inbound\n    case Reversal = 'reversal';                      // both (offsets previous)\n    case InitialStock = 'initial_stock';             // inbound (first entry)\n}\n```\n\n### StockMovementEngine\n\n```php\nclass StockMovementEngine\n{\n    public function record(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,                    // positive for inbound, negative for outbound\n        MovementTypeEnum $type,\n        string $reference,                // order number, transfer ID, etc.\n        ?int $unitCost = null,            // in cents\n        ?string $batchId = null,\n        ?array $serialNumbers = null,\n        ?string $description = null,\n        array $metadata = [],\n    ): InventoryLedger;\n\n    public function findById(string $id): ?InventoryLedger;\n    public function findByReference(string $reference): Collection;\n    public function findByProduct(string $productId, array $filters = []): Collection;\n}\n```\n\n### Reference Number Format\n\nAuto-generated reference numbers follow this pattern:\n\n```\nMOV-{YYYYMMDD}-{XXXX}\n    Example: MOV-20260715-0042\n\nPrefixes by type:\n  PUR-{date}-{seq}   → Purchase Receipt\n  SAL-{date}-{seq}   → Sale Deduction\n  RET-{date}-{seq}   → Return Restock\n  TRF-{date}-{seq}   → Transfer\n  ADJ-{date}-{seq}   → Adjustment\n  PROD-{date}-{seq}  → Production\n  REC-{date}-{seq}   → Recipe Consumption\n```\n\n---\n\n## 6. Inventory Ledger Design\n\n### Table: `inventory_ledger`\n\n```php\nSchema::create('inventory_ledger', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);            // shared mode scoping\n    $table->ulid('product_id');                  // FK to products\n    $table->ulid('variant_id')->nullable();      // FK to variants\n    $table->unsignedBigInteger('warehouse_id');  // FK to warehouses\n    $table->unsignedBigInteger('bin_id')->nullable(); // FK to bins\n    $table->integer('quantity');                 // positive = in, negative = out\n    $table->integer('quantity_before');          // snapshot before movement\n    $table->integer('quantity_after');           // snapshot after movement\n    $table->string('movement_type', 30);         // MovementTypeEnum value\n    $table->string('reference', 100);            // order/invoice/transfer ref\n    $table->string('reference_type', 30);        // class of reference (order, transfer, etc.)\n    $table->unsignedBigInteger('batch_id')->nullable();\n    $table->json('serial_numbers')->nullable();  // array of serials\n    $table->bigInteger('unit_cost')->nullable(); // cost per unit in cents\n    $table->bigInteger('total_cost')->nullable(); // unit_cost * |quantity|\n    $table->text('description')->nullable();\n    $table->json('metadata')->nullable();        // extensible payload\n    $table->string('created_by', 36)->nullable(); // user ID\n    $table->timestamp('created_at')->useCurrent();\n\n    $table->index(['product_id', 'warehouse_id', 'created_at']);\n    $table->index(['reference', 'reference_type']);\n    $table->index(['batch_id']);\n    $table->index(['tenant_id', 'created_at']);\n});\n```\n\n### Table Rules\n\n- No `updated_at` — ledger entries are immutable\n- No `deleted_at` — soft deletes defeat the audit purpose\n- `quantity_before` and `quantity_after` as integrity checkpoints\n- `unit_cost` is always in cents (integer) to avoid floating-point drift\n- `total_cost = unit_cost * |quantity|` — pre-calculated for reporting performance\n- `metadata` JSON column for extensibility without schema changes (source IP, reason, approval ID, etc.)\n\n---\n\n## 7. Inventory Balance\n\n### Table: `inventory_balances`\n\nThe real-time snapshot table. UPSERT-ed on every movement.\n\n```php\nSchema::create('inventory_balances', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->unsignedBigInteger('warehouse_id');\n    $table->integer('quantity')->default(0);\n    $table->integer('reserved_quantity')->default(0);\n    $table->integer('available_quantity')->default(0); // quantity - reserved\n    $table->bigInteger('average_unit_cost')->default(0); // weighted avg in cents\n    $table->bigInteger('total_stock_value')->default(0); // unit_cost * quantity\n    $table->unsignedBigInteger('lock_version')->default(0); // optimistic locking\n    $table->timestamp('last_movement_at')->nullable();\n    $table->timestamps();\n\n    $table->unique(['product_id', 'variant_id', 'warehouse_id']);\n    $table->index(['warehouse_id', 'quantity']);\n});\n```\n\n### Balance Calculation\n\n```php\nclass InventoryBalanceService\n{\n    // Called after every movement. UPSERTs the balance row.\n    public function recalculate(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n    ): void;\n\n    // Full rebuild from ledger — for reconciliation or first-time setup\n    public function rebuildFromLedger(\n        ?string $productId = null,\n        ?string $warehouseId = null,\n    ): void;\n\n    // Bulk update after batch operations\n    public function recalculateBatch(array $productWarehousePairs): void;\n}\n```\n\n### Optimistic Locking\n\n`lock_version` uses Laravel's `HasOptimisticLocking` trait. On concurrent writes, the first request succeeds and the second receives a `ModelNotFoundException` — the caller must retry. This prevents double-deduction during high-concurrency POS scenarios.\n\n```php\ntry {\n    $balance = InventoryBalance::where([\n        'product_id' => $productId,\n        'warehouse_id' => $warehouseId,\n    ])->firstOrFail();\n\n    $balance->decrement('quantity', $quantity); // uses lock_version check\n} catch (ModelNotFoundException) {\n    // Concurrent update detected — retry the entire operation\n    throw new ConcurrentInventoryUpdateException($productId, $warehouseId);\n}\n```\n\n---\n\n## 8. Costing Strategies\n\n### CostLayer Model\n\n```php\nSchema::create('cost_layers', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->unsignedBigInteger('warehouse_id');\n    $table->bigInteger('unit_cost');             // cost per unit in cents\n    $table->integer('quantity_remaining');        // units still in this layer\n    $table->integer('quantity_original');         // original layer size\n    $table->string('costing_method', 20);         // weighted_average or fifo\n    $table->unsignedBigInteger('ledger_entry_id'); // FK to source ledger entry\n    $table->timestamp('created_at')->useCurrent();\n\n    $table->index(['product_id', 'warehouse_id', 'costing_method']);\n});\n```\n\n### Weighted Average (Default)\n\nWeighted average is the default costing method. It is simple, fair, and covers 80% of SME use cases.\n\n```php\nclass CostingEngine\n{\n    public function applyInbound(CostLayer $layer): void\n    {\n        // Recalculate weighted average for the product+warehouse\n        $currentBalance = InventoryBalance::where([\n            'product_id' => $layer->product_id,\n            'warehouse_id' => $layer->warehouse_id,\n        ])->first();\n\n        $totalCost = ($currentBalance->quantity * $currentBalance->average_unit_cost)\n                   + ($layer->quantity_remaining * $layer->unit_cost);\n        $totalQty = $currentBalance->quantity + $layer->quantity_remaining;\n\n        $newAvg = $totalQty > 0 ? (int) round($totalCost / $totalQty) : 0;\n\n        $currentBalance->update([\n            'average_unit_cost' => $newAvg,\n            'total_stock_value' => $totalQty * $newAvg,\n        ]);\n    }\n\n    public function consumeOutbound(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n    ): int {\n        // Returns the cost to deduct (in cents)\n        // Under weighted average: quantity * current average\n        $balance = InventoryBalance::where([\n            'product_id' => $productId,\n            'warehouse_id' => $warehouseId,\n        ])->firstOrFail();\n\n        return $quantity * $balance->average_unit_cost;\n    }\n}\n```\n\n### FIFO\n\nFIFO consumes the oldest cost layers first. Enabled per-product via a `costing_method` field.\n\n```php\nclass FifoCostingEngine\n{\n    public function consumeOutbound(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n    ): array {\n        // Consume from oldest layers first\n        $layers = CostLayer::where([\n            'product_id' => $productId,\n            'warehouse_id' => $warehouseId,\n            'costing_method' => 'fifo',\n        ])\n        ->where('quantity_remaining', '>', 0)\n        ->orderBy('created_at')\n        ->get();\n\n        $totalCost = 0;\n        $remaining = $quantity;\n\n        foreach ($layers as $layer) {\n            $consume = min($remaining, $layer->quantity_remaining);\n            $layer->decrement('quantity_remaining', $consume);\n            $totalCost += $consume * $layer->unit_cost;\n            $remaining -= $consume;\n            if ($remaining <= 0) break;\n        }\n\n        return [\n            'total_cost' => $totalCost,\n            'unit_cost' => $quantity > 0 ? (int) round($totalCost / $quantity) : 0,\n            'layers_consumed' => $layers->count(),\n        ];\n    }\n}\n```\n\n### Costing Method Selection\n\nThe costing method is resolved per-product:\n\n```php\n// Product model\nclass Product extends Model\n{\n    public function costingMethod(): string\n    {\n        return $this->costing_method ?? config('inventory.default_costing_method', 'weighted_average');\n    }\n}\n```\n\nThe `CostingEngine` delegates to the appropriate strategy:\n\n```php\nclass CostingEngine\n{\n    public function __construct(\n        private WeightedAverageCosting $weightedAverage,\n        private FifoCosting $fifo,\n    ) {}\n\n    public function consume(string $productId, ?string $variantId, string $warehouseId, int $quantity): CostResult\n    {\n        $product = Product::findOrFail($productId);\n        $strategy = $product->costingMethod() === 'fifo' ? $this->fifo : $this->weightedAverage;\n        return $strategy->consumeOutbound($productId, $variantId, $warehouseId, $quantity);\n    }\n}\n```\n\n---\n\n## 9. Reservation & Allocation Engine\n\n### Reservation Lifecycle\n\n```\nCreated (active) ──► Consumed (stock deducted)\n    │                      │\n    ▼                      ▼\nExpired (auto-release)   Cancelled (manual)\n```\n\n### Table: `stock_reservations`\n\n```php\nSchema::create('stock_reservations', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->unsignedBigInteger('warehouse_id');\n    $table->integer('quantity');\n    $table->string('status', 20); // active, consumed, expired, cancelled\n    $table->string('reference', 100); // order ID, cart ID\n    $table->string('reference_type', 30);\n    $table->timestamp('expires_at')->nullable();\n    $table->timestamp('consumed_at')->nullable();\n    $table->string('created_by', 36)->nullable();\n    $table->timestamps();\n\n    $table->index(['status', 'expires_at']);\n    $table->index(['product_id', 'warehouse_id', 'status']);\n    $table->index(['reference', 'reference_type']);\n});\n```\n\n### ReservationEngine\n\n```php\nclass ReservationEngine\n{\n    public function reserve(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n        string $reference,\n        ?int $ttlMinutes = 30,  // auto-expire after.
Error message: JSON Parse error: Unterminated string
```

---

## Assistant (Build · DeepSeek V4 Flash Free · 20.7s)

_Thinking:_

The file write failed due to a JSON parsing error - likely because the content contains special characters that are not properly escaped. Let me write this file using bash instead, or split it into smaller pieces. Actually the issue is that the content parameter has unescaped characters. Let me use the bash tool to write the file instead.

Actually, looking more carefully, the issue is that the content string seems to contain characters that break JSON parsing. The Write tool requires valid JSON. Let me use a different approach - write the file in parts using bash, or use the Write tool with properly escaped content.

Let me try the bash approach - write the file using PowerShell's Set-Content or Out-File.

**Tool: bash**

**Input:**
```json
{
  "command": "@\"\n# SOUDA — Inventory Module Architecture Guide\n\nThis file is for AI coding agents implementing the Inventory module. It defines the complete architecture, conventions, guardrails, patterns, interfaces, and implementation rules for the SOUDA Inventory Engine.\n\n---\n\n## 1. Project Scope & Inventory Philosophy\n\n### Vision\n\nSOUDA's Inventory module is not a stock-tracking CRUD — it is an **Automated Inventory Engine**. Every business operation (purchase, sale, return, transfer, manufacturing, adjustment) generates domain events. The Inventory Engine consumes those events and produces immutable stock movements, cost layers, balance snapshots, reservations, and alerts — all without manual stock entry.\n\n### Target Audience\n\nSME business owners in 15+ industries (retail, pharmacy, restaurant, grocery, electronics, fashion, etc.) who need real-time inventory visibility without operational overhead. The system must be intuitive enough that a shop manager can understand stock health in 10 seconds, yet powerful enough to handle batch tracking, expiry management, and multi-warehouse transfers.\n\n### Scope — Phase 1\n\n| Feature | Included |\n|---------|----------|\n| Immutable inventory ledger | ✅ |\n| Stock movement recording | ✅ |\n| Inventory balance (real-time snapshot) | ✅ |\n| Weighted average costing | ✅ |\n| FIFO costing layers | ✅ |\n| Stock reservations & allocation | ✅ |\n| Multi-warehouse support | ✅ |\n| Warehouse bin/shelf locations | ✅ |\n| Batch/lot tracking | ✅ |\n| Serial number tracking | ✅ |\n| Expiry date management | ✅ |\n| Low stock & depletion alerts | ✅ |\n| Stock transfer between warehouses | ✅ |\n| Audit trail (who did what, when) | ✅ |\n| Frontend management pages | ✅ |\n| Industry Pack integration | ✅ |\n| Automation rules engine | Phase 2 |\n| Demand forecasting | Phase 2 |\n| Reorder suggestions | Phase 2 |\n\n### One Golden Rule\n\n> **No module ever writes to stock directly.** Every module (POS, Purchase, Sales, Manufacturing, Returns) publishes domain events. Only the Inventory Engine translates those events into stock movements and inventory balances. This is non-negotiable.\n\n---\n\n## 2. Core Architectural Principles\n\n### Principle 1: Ledger Immutability\n\nThe `inventory_ledger` table is INSERT-only. Once a stock movement is recorded, it is never updated or deleted. Corrections are made via offsetting entries (reversal movements), not row mutations. This guarantees a complete audit trail and prevents data loss.\n\n```php\n// WRONG — never update or delete ledger rows\nInventoryLedger::where('id', $id)->update(['quantity' => 0]);\n\n// RIGHT — reversal\nStockMovementEngine::record(\n    productId: $productId,\n    warehouseId: $warehouseId,\n    quantity: -$originalQuantity,  // negative reversal\n    type: MovementTypeEnum::Adjustment,\n    reference: \"Reversal of ledger entry #{$originalId}\",\n    metadata: ['reverses' => $originalId],\n);\n```\n\n### Principle 2: Event-Driven Decoupling\n\nEvery inventory operation is triggered by a domain event. The Inventory Engine never exposes public write methods that bypass events. Modules communicate through events, not service calls.\n\n```\nSaleCompleted ──► InventoryEngine::onSaleCompleted()\n                      └──► StockMovementEngine::record()\n                            └──► InventoryBalanceService::recalculate()\n                                  └──► CostingEngine::apply()\n```\n\n### Principle 3: Balance = Derived State\n\n`InventoryBalance` is a computed snapshot derived from the ledger. It is updated synchronously on every write for read performance, but can always be rebuilt from scratch by replaying the ledger. This means:\n\n- `InventoryBalance` can be dropped and rebuilt without data loss\n- The ledger is the source of truth, not the balance\n- Reconciliation means replaying the ledger and comparing the computed balance\n\n### Principle 4: Costing Consistency\n\nEvery stock movement carries a `unit_cost` at the time of recording. The Costing Engine maintains cost layers (Weighted Average or FIFO) and recalculates the moving average cost on every inbound movement. Outbound movements consume cost from layers in FIFO order or at the current weighted average.\n\n### Principle 5: Industry-Neutral Core\n\nThe core inventory engine (ledger, balance, costing, reservations) is completely industry-agnostic. Industry-specific behaviors (FEFO for pharmacy, recipe deduction for restaurants, IMEI for electronics) are implemented via Industry Pack feature flags and plugin listeners — never via hardcoded checks in the engine.\n\n---\n\n## 3. Module Boundaries & Responsibilities\n\n```\n┌─────────────────────────────────────────────────────────────────┐\n│                     Inventory Module                              │\n│                                                                   │\n│  ┌─────────────────┐  ┌────────────────┐  ┌──────────────────┐  │\n│  │ InventoryEngine  │  │ StockMovement  │  │ InventoryBalance  │  │\n│  │ (Orchestrator)   │──│ Engine         │──│ Service           │  │\n│  └─────────┬───────┘  └────────────────┘  └──────────────────┘  │\n│            │                                                      │\n│  ┌─────────▼───────┐  ┌────────────────┐  ┌──────────────────┐  │\n│  │ CostingEngine    │  │ Reservation    │  │ BatchService      │  │\n│  │ (FIFO/WAVG)      │──│ Engine         │──│ (Lot/Expiry)      │  │\n│  └─────────────────┘  └────────────────┘  └──────────────────┘  │\n│                                                                   │\n│  ┌─────────────────┐  ┌────────────────┐  ┌──────────────────┐  │\n│  │ SerialNumber     │  │ AuditService   │  │ AlertEngine       │  │\n│  │ Service          │──│                │──│ (Thresholds)      │  │\n│  └─────────────────┘  └────────────────┘  └──────────────────┘  │\n│                                                                   │\n│  ┌─────────────────┐  ┌────────────────┐                         │\n│  │ WarehouseService │  │ TransferEngine │                         │\n│  └─────────────────┘  └────────────────┘                         │\n└─────────────────────────────────────────────────────────────────┘\n```\n\n### Module Responsibilities\n\n| Component | Responsibility |\n|-----------|---------------|\n| `InventoryEngine` | Single public entry point. Subscribes to domain events, validates business rules, delegates to sub-engines |\n| `StockMovementEngine` | Records immutable ledger entries. Generates movement reference numbers |\n| `InventoryBalanceService` | Maintains real-time balance snapshots. Rebuilds from ledger on demand |\n| `CostingEngine` | Manages cost layers. Weighted average recalculation. FIFO layer consumption |\n| `ReservationEngine` | Creates, consumes, expires, and cancels stock reservations |\n| `BatchService` | Batch/lot tracking. Expiry date management. FEFO picking order |\n| `SerialNumberService` | Serial/IMEI tracking per unit. Status lifecycle: available → reserved → sold → returned |\n| `WarehouseService` | Warehouse and bin/shelf location management |\n| `TransferEngine` | Orchestrates inter-warehouse stock transfers with in-transit status |\n| `AuditService` | Logs all inventory operations with before/after snapshots |\n| `AlertEngine` | Evaluates thresholds (low stock, expiry, overstock, dead stock). Dispatches alerts |\n\n---\n\n## 4. Inventory Engine Architecture\n\n### InventoryEngine (Orchestrator)\n\nThe `InventoryEngine` is the single public facade for all inventory write operations. It receives domain events, validates them against business rules, and coordinates the sub-engines.\n\n```php\nclass InventoryEngine\n{\n    public function __construct(\n        private StockMovementEngine $movementEngine,\n        private InventoryBalanceService $balanceService,\n        private CostingEngine $costingEngine,\n        private ReservationEngine $reservationEngine,\n        private BatchService $batchService,\n        private SerialNumberService $serialService,\n        private TransferEngine $transferEngine,\n        private AuditService $auditService,\n        private AlertEngine $alertEngine,\n        private TenantConfig $config,\n    ) {}\n\n    // These are the ONLY public write methods on this class:\n    public function handleSaleCompleted(SaleCompleted $event): void;\n    public function handlePurchaseReceived(PurchaseReceived $event): void;\n    public function handleReturnApproved(ReturnApproved $event): void;\n    public function handleAdjustmentApproved(AdjustmentApproved $event): void;\n    public function handleTransferInitiated(TransferInitiated $event): void;\n    public function handleTransferCompleted(TransferCompleted $event): void;\n    public function handleProductionCompleted(ProductionCompleted $event): void;\n    public function handleRecipeConsumed(RecipeConsumed $event): void;\n    public function handleReservationCreated(ReservationCreated $event): void;\n    public function handleReservationCancelled(ReservationCancelled $event): void;\n}\n```\n\n### Engine Workflow (Example: Sale)\n\n```\n1. SaleCompleted event received\n2. InventoryEngine::handleSaleCompleted()\n3. → Validate: product exists, warehouse exists, quantity is positive\n4. → If batches tracked: resolve which batches to deduct (FIFO/FEFO)\n5. → If serial tracked: validate each serial number is available\n6. → ReserveEngine::consumeReservation(orderReference) if pre-reserved\n7. → StockMovementEngine::record(sale, -qty, unitCost, reference)\n8. → InventoryBalanceService::recalculate(productId, warehouseId)\n9. → CostingEngine::consumeLayers(productId, qty, costingMethod)\n10. → BatchService::deduct(productId, batchId, qty) if batch tracked\n11. → SerialNumberService::markAsSold(serialNumbers) if serial tracked\n12. → AuditService::log('sale', before, after, actor)\n13. → AlertEngine::evaluate(productId, warehouseId)\n14. → Dispatch InventoryBalanceUpdated, StockDepleted (if applicable)\n```\n\n---\n\"@ | Out-File -FilePath C:\\dev\\souda\\docs\\inventory-agent.md -Encoding utf8",
  "workdir": "C:\\dev\\souda"
}
```

**Output:**
```
(no output)
```

---

## Assistant (Build · DeepSeek V4 Flash Free · 22.8s)

_Thinking:_

The first part was written. Now I need to append the rest. Let me continue with the remaining sections.

**Tool: bash**

**Input:**
```json
{
  "command": "@\"\n## 5. Stock Movement Engine\n\n### Movement Types\n\n```php\nenum MovementTypeEnum: string\n{\n    case PurchaseReceipt = 'purchase_receipt';      // inbound\n    case SaleDeduction = 'sale_deduction';           // outbound\n    case ReturnRestock = 'return_restock';           // inbound\n    case TransferOut = 'transfer_out';               // outbound (sent)\n    case TransferIn = 'transfer_in';                 // inbound (received)\n    case AdjustmentIncrease = 'adjustment_increase'; // inbound\n    case AdjustmentDecrease = 'adjustment_decrease'; // outbound\n    case ProductionOutput = 'production_output';     // inbound\n    case RecipeConsumption = 'recipe_consumption';   // outbound\n    case ReservationDeduction = 'reservation_deduction'; // outbound\n    case ReservationRelease = 'reservation_release';     // inbound\n    case Reversal = 'reversal';                      // both (offsets previous)\n    case InitialStock = 'initial_stock';             // inbound (first entry)\n}\n```\n\n### StockMovementEngine\n\n```php\nclass StockMovementEngine\n{\n    public function record(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,                    // positive for inbound, negative for outbound\n        MovementTypeEnum $type,\n        string $reference,                // order number, transfer ID, etc.\n        ?int $unitCost = null,            // in cents\n        ?string $batchId = null,\n        ?array $serialNumbers = null,\n        ?string $description = null,\n        array $metadata = [],\n    ): InventoryLedger;\n\n    public function findById(string $id): ?InventoryLedger;\n    public function findByReference(string $reference): Collection;\n    public function findByProduct(string $productId, array $filters = []): Collection;\n}\n```\n\n### Reference Number Format\n\nAuto-generated reference numbers follow this pattern:\n\n```\nMOV-{YYYYMMDD}-{XXXX}\n    Example: MOV-20260715-0042\n\nPrefixes by type:\n  PUR-{date}-{seq}   → Purchase Receipt\n  SAL-{date}-{seq}   → Sale Deduction\n  RET-{date}-{seq}   → Return Restock\n  TRF-{date}-{seq}   → Transfer\n  ADJ-{date}-{seq}   → Adjustment\n  PROD-{date}-{seq}  → Production\n  REC-{date}-{seq}   → Recipe Consumption\n```\n\n---\n\n## 6. Inventory Ledger Design\n\n### Table: `inventory_ledger`\n\n```php\nSchema::create('inventory_ledger', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);            // shared mode scoping\n    $table->ulid('product_id');                  // FK to products\n    $table->ulid('variant_id')->nullable();      // FK to variants\n    $table->unsignedBigInteger('warehouse_id');  // FK to warehouses\n    $table->unsignedBigInteger('bin_id')->nullable(); // FK to bins\n    $table->integer('quantity');                 // positive = in, negative = out\n    $table->integer('quantity_before');          // snapshot before movement\n    $table->integer('quantity_after');           // snapshot after movement\n    $table->string('movement_type', 30);\n    $table->string('reference', 100);\n    $table->string('reference_type', 30);\n    $table->unsignedBigInteger('batch_id')->nullable();\n    $table->json('serial_numbers')->nullable();\n    $table->bigInteger('unit_cost')->nullable();\n    $table->bigInteger('total_cost')->nullable();\n    $table->text('description')->nullable();\n    $table->json('metadata')->nullable();\n    $table->string('created_by', 36)->nullable();\n    $table->timestamp('created_at')->useCurrent();\n\n    $table->index(['product_id', 'warehouse_id', 'created_at']);\n    $table->index(['reference', 'reference_type']);\n    $table->index(['batch_id']);\n    $table->index(['tenant_id', 'created_at']);\n});\n```\n\n### Table Rules\n\n- No `updated_at` — ledger entries are immutable\n- No `deleted_at` — soft deletes defeat the audit purpose\n- `quantity_before` and `quantity_after` as integrity checkpoints\n- `unit_cost` is always in cents (integer) to avoid floating-point drift\n- `total_cost = unit_cost * |quantity|` — pre-calculated for reporting performance\n- `metadata` JSON column for extensibility without schema changes\n\n---\n\n## 7. Inventory Balance\n\n### Table: `inventory_balances`\n\n```php\nSchema::create('inventory_balances', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->unsignedBigInteger('warehouse_id');\n    $table->integer('quantity')->default(0);\n    $table->integer('reserved_quantity')->default(0);\n    $table->integer('available_quantity')->default(0);\n    $table->bigInteger('average_unit_cost')->default(0);\n    $table->bigInteger('total_stock_value')->default(0);\n    $table->unsignedBigInteger('lock_version')->default(0); // optimistic locking\n    $table->timestamp('last_movement_at')->nullable();\n    $table->timestamps();\n\n    $table->unique(['product_id', 'variant_id', 'warehouse_id']);\n    $table->index(['warehouse_id', 'quantity']);\n});\n```\n\n### Balance Calculation\n\n```php\nclass InventoryBalanceService\n{\n    public function recalculate(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n    ): void;\n\n    public function rebuildFromLedger(\n        ?string $productId = null,\n        ?string $warehouseId = null,\n    ): void;\n\n    public function recalculateBatch(array $productWarehousePairs): void;\n}\n```\n\n### Optimistic Locking\n\n`lock_version` uses Laravel's `HasOptimisticLocking` trait. On concurrent writes, the first request succeeds and the second receives a `ModelNotFoundException` — the caller must retry.\n\n```php\ntry {\n    $balance = InventoryBalance::where([\n        'product_id' => $productId,\n        'warehouse_id' => $warehouseId,\n    ])->firstOrFail();\n\n    $balance->decrement('quantity', $quantity);\n} catch (ModelNotFoundException) {\n    throw new ConcurrentInventoryUpdateException($productId, $warehouseId);\n}\n```\n\n---\n\n## 8. Costing Strategies\n\n### CostLayer Model\n\n```php\nSchema::create('cost_layers', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->unsignedBigInteger('warehouse_id');\n    $table->bigInteger('unit_cost');\n    $table->integer('quantity_remaining');\n    $table->integer('quantity_original');\n    $table->string('costing_method', 20); // weighted_average or fifo\n    $table->unsignedBigInteger('ledger_entry_id');\n    $table->timestamp('created_at')->useCurrent();\n\n    $table->index(['product_id', 'warehouse_id', 'costing_method']);\n});\n```\n\n### Weighted Average (Default)\n\n```php\nclass WeightedAverageCosting\n{\n    public function applyInbound(CostLayer $layer): void\n    {\n        $currentBalance = InventoryBalance::where([\n            'product_id' => $layer->product_id,\n            'warehouse_id' => $layer->warehouse_id,\n        ])->first();\n\n        $totalCost = ($currentBalance->quantity * $currentBalance->average_unit_cost)\n                   + ($layer->quantity_remaining * $layer->unit_cost);\n        $totalQty = $currentBalance->quantity + $layer->quantity_remaining;\n        $newAvg = $totalQty > 0 ? (int) round($totalCost / $totalQty) : 0;\n\n        $currentBalance->update([\n            'average_unit_cost' => $newAvg,\n            'total_stock_value' => $totalQty * $newAvg,\n        ]);\n    }\n\n    public function consumeOutbound(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n    ): array {\n        $balance = InventoryBalance::where([\n            'product_id' => $productId,\n            'warehouse_id' => $warehouseId,\n        ])->firstOrFail();\n\n        $unitCost = $balance->average_unit_cost;\n        return [\n            'total_cost' => $quantity * $unitCost,\n            'unit_cost' => $unitCost,\n        ];\n    }\n}\n```\n\n### FIFO\n\n```php\nclass FifoCosting\n{\n    public function consumeOutbound(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n    ): array {\n        $layers = CostLayer::where([\n            'product_id' => $productId,\n            'warehouse_id' => $warehouseId,\n            'costing_method' => 'fifo',\n        ])\n        ->where('quantity_remaining', '>', 0)\n        ->orderBy('created_at')\n        ->get();\n\n        $totalCost = 0;\n        $remaining = $quantity;\n\n        foreach ($layers as $layer) {\n            $consume = min($remaining, $layer->quantity_remaining);\n            $layer->decrement('quantity_remaining', $consume);\n            $totalCost += $consume * $layer->unit_cost;\n            $remaining -= $consume;\n            if ($remaining <= 0) break;\n        }\n\n        return [\n            'total_cost' => $totalCost,\n            'unit_cost' => $quantity > 0 ? (int) round($totalCost / $quantity) : 0,\n            'layers_consumed' => $layers->count(),\n        ];\n    }\n}\n```\n\n### Costing Method Selection\n\n```php\nclass Product extends Model\n{\n    public function costingMethod(): string\n    {\n        return $this->costing_method ?? config('inventory.default_costing_method', 'weighted_average');\n    }\n}\n\nclass CostingEngine\n{\n    public function __construct(\n        private WeightedAverageCosting $weightedAverage,\n        private FifoCosting $fifo,\n    ) {}\n\n    public function consume(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n    ): CostResult {\n        $product = Product::findOrFail($productId);\n        $strategy = $product->costingMethod() === 'fifo' ? $this->fifo : $this->weightedAverage;\n        return $strategy->consumeOutbound($productId, $variantId, $warehouseId, $quantity);\n    }\n}\n```\n\n---\n\n## 9. Reservation & Allocation Engine\n\n### Reservation Lifecycle\n\n```\nCreated (active) ──► Consumed (stock deducted)\n    │                      │\n    ▼                      ▼\nExpired (auto-release)   Cancelled (manual)\n```\n\n### Table: `stock_reservations`\n\n```php\nSchema::create('stock_reservations', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->unsignedBigInteger('warehouse_id');\n    $table->integer('quantity');\n    $table->string('status', 20);\n    $table->string('reference', 100);\n    $table->string('reference_type', 30);\n    $table->timestamp('expires_at')->nullable();\n    $table->timestamp('consumed_at')->nullable();\n    $table->string('created_by', 36)->nullable();\n    $table->timestamps();\n\n    $table->index(['status', 'expires_at']);\n    $table->index(['product_id', 'warehouse_id', 'status']);\n    $table->index(['reference', 'reference_type']);\n});\n```\n\n### ReservationEngine\n\n```php\nclass ReservationEngine\n{\n    public function reserve(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n        string $reference,\n        ?int $ttlMinutes = 30,\n    ): StockReservation;\n\n    public function consume(string $reference): void;\n\n    public function release(string $reference): void;\n\n    public function cancel(string $reference): void;\n\n    public function expirePending(): int; // batch expiry job\n\n    public function available(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n    ): int; // qty available for reservation\n\n    public function isAvailable(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n    ): bool;\n}\n```\n\n### Allocation Rules\n\n| Scenario | Rule |\n|----------|------|\n| POS sale with cart | Reserve on add-to-cart, expire in 30 min if not checked out |\n| Online order | Reserve on creation, consume on payment |\n| Backorder | Reserve when stock arrives (requires reorder engine — Phase 2) |\n| Manual pick list | Reserve on pick list creation, consume on ship |\n| Multi-warehouse | Reserve from nearest/default warehouse, fallback to others |\n\n---\n\"@ | Out-File -FilePath C:\\dev\\souda\\docs\\inventory-agent.md -Encoding utf8 -Append",
  "workdir": "C:\\dev\\souda"
}
```

**Output:**
```
(no output)
```

---

## Assistant (Build · DeepSeek V4 Flash Free · 31.2s)

_Thinking:_

Good, let me continue appending the remaining sections.

**Tool: bash**

**Input:**
```json
{
  "command": "@\"\n## 10. Warehouse, Location & Transfer Architecture\n\n### Warehouse Hierarchy\n\n```\nWarehouse (physical location)\n  └── Zone (e.g., Dry Storage, Cold Storage)\n       └── Aisle (e.g., A, B, C)\n            └── Rack (e.g., R01, R02)\n                 └── Shelf (e.g., SH01, SH02)\n                      └── Bin (e.g., A-R01-SH01-BIN01)\n```\n\n### Warehouse Table\n\n```php\nSchema::create('warehouses', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->string('name');\n    $table->string('slug')->unique();\n    $table->string('code', 20)->nullable();\n    $table->string('type', 20)->default('physical'); // physical, virtual, transit\n    $table->boolean('is_default')->default(false);\n    $table->string('address_line1')->nullable();\n    $table->string('city')->nullable();\n    $table->string('country', 2)->nullable();\n    $table->string('contact_name')->nullable();\n    $table->string('contact_email')->nullable();\n    $table->string('contact_phone')->nullable();\n    $table->json('metadata')->nullable();\n    $table->boolean('is_active')->default(true);\n    $table->timestamps();\n    $table->softDeletes();\n\n    $table->unique(['tenant_id', 'slug']);\n});\n```\n\n### Bin Locations\n\n```php\nSchema::create('inventory_bins', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->unsignedBigInteger('warehouse_id');\n    $table->string('code', 50);         // A-R01-SH01-BIN01\n    $table->string('zone')->nullable(); // Dry, Cold, Hazardous\n    $table->string('aisle')->nullable();\n    $table->string('rack')->nullable();\n    $table->string('shelf')->nullable();\n    $table->boolean('is_pickable')->default(true);\n    $table->integer('max_weight_kg')->nullable();\n    $table->json('metadata')->nullable();\n    $table->timestamps();\n\n    $table->unique(['warehouse_id', 'code']);\n    $table->index(['warehouse_id', 'zone']);\n});\n```\n\n### Transfer Engine\n\n```php\nclass TransferEngine\n{\n    public function initiate(\n        string $fromWarehouseId,\n        string $toWarehouseId,\n        array $items, // [{product_id, variant_id?, quantity}]\n        ?string $reference = null,\n        ?string $description = null,\n    ): TransferOrder;\n\n    public function send(string $transferId): void;  // deducts source stock\n    public function receive(string $transferId): void; // adds destination stock\n    public function cancel(string $transferId): void; // reverts source deduction\n\n    public function partialReceive(\n        string $transferId,\n        array $receivedItems, // [{product_id, quantity}]\n    ): void;\n}\n```\n\n### Transfer Flow\n\n```\n1. Initiate → Source items reserved, TransferOrder status = draft\n2. Send → Source stock deducted (TransferOut movement), status = in_transit\n3. Receive → Destination stock added (TransferIn movement), status = completed\n4. Cancel → Source reservation released, status = cancelled\n\nFor partial receives:\n  - Only received quantities are transferred\n  - Unreceived quantities stay in_transit (or are returned to source on close)\n```\n\n### Transfer Table\n\n```php\nSchema::create('inventory_transfers', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->string('reference', 50)->unique();\n    $table->unsignedBigInteger('from_warehouse_id');\n    $table->unsignedBigInteger('to_warehouse_id');\n    $table->string('status', 20); // draft, in_transit, completed, cancelled, partially_received\n    $table->text('description')->nullable();\n    $table->string('created_by', 36)->nullable();\n    $table->timestamp('sent_at')->nullable();\n    $table->timestamp('received_at')->nullable();\n    $table->timestamps();\n});\n\nSchema::create('inventory_transfer_items', function (Blueprint $table) {\n    $table->id();\n    $table->unsignedBigInteger('transfer_id');\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->integer('quantity');\n    $table->integer('quantity_received')->default(0);\n    $table->timestamps();\n});\n```\n\n---\n\n## 11. Batch, Expiry & Serial Number Management\n\n### Batch Table\n\n```php\nSchema::create('inventory_batches', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->string('batch_number', 100);\n    $table->string('supplier_batch', 100)->nullable();\n    $table->date('manufacturing_date')->nullable();\n    $table->date('expiry_date')->nullable();\n    $table->date('best_before')->nullable();\n    $table->integer('initial_quantity');\n    $table->integer('remaining_quantity');\n    $table->bigInteger('unit_cost'); // cost at receipt\n    $table->string('status', 20);   // active, expired, depleted, quarantined\n    $table->timestamps();\n\n    $table->unique(['product_id', 'batch_number']);\n    $table->index(['expiry_date', 'status']);\n    $table->index(['warehouse_id']);\n});\n```\n\n### FEFO Picking (Pharmacy/Grocery)\n\n```php\nclass BatchService\n{\n    public function pickBatches(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n        string $method = 'fefo', // fefo, fifo, lifo\n    ): Collection {\n        $query = InventoryBatch::where([\n            'product_id' => $productId,\n            'warehouse_id' => $warehouseId,\n        ])->where('remaining_quantity', '>', 0)\n          ->whereIn('status', ['active']);\n\n        if ($method === 'fefo') {\n            $query->whereNotNull('expiry_date')\n                  ->where('expiry_date', '>=', now())\n                  ->orderBy('expiry_date');\n        } elseif ($method === 'fifo') {\n            $query->orderBy('manufacturing_date')->orderBy('id');\n        } else {\n            $query->orderByDesc('id'); // lifo: newest first\n        }\n\n        return $this->allocateFromBatches($query->get(), $quantity);\n    }\n\n    public function receive(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        string $batchNumber,\n        int $quantity,\n        ?string $supplierBatch = null,\n        ?Carbon $manufacturingDate = null,\n        ?Carbon $expiryDate = null,\n        ?Carbon $bestBefore = null,\n        int $unitCost = 0,\n    ): InventoryBatch;\n\n    public function deduct(string $batchId, int $quantity): void;\n\n    public function quarantine(string $batchId, ?string $reason = null): void;\n\n    public function findExpiring(Carbon $withinDays): Collection;\n}\n```\n\n### Serial Number Table\n\n```php\nSchema::create('serial_numbers', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->string('serial_number', 200);\n    $table->string('status', 20); // available, reserved, sold, returned, quarantined, expired\n    $table->unsignedBigInteger('warehouse_id')->nullable();\n    $table->unsignedBigInteger('batch_id')->nullable();\n    $table->string('order_reference')->nullable();\n    $table->timestamp('sold_at')->nullable();\n    $table->timestamp('warranty_expires_at')->nullable();\n    $table->text('notes')->nullable();\n    $table->timestamps();\n\n    $table->unique(['product_id', 'serial_number']);\n    $table->index(['status', 'warehouse_id']);\n    $table->index(['serial_number']);\n});\n```\n\n### SerialNumberService\n\n```php\nclass SerialNumberService\n{\n    public function register(\n        string $productId,\n        string $serialNumber,\n        ?string $warehouseId = null,\n        ?string $batchId = null,\n    ): SerialNumber;\n\n    public function validate(string $serialNumber, string $productId): bool;\n\n    public function markAsSold(\n        string $serialNumber,\n        string $orderReference,\n    ): void;\n\n    public function markAsReturned(string $serialNumber): void;\n\n    public function registerBatch(\n        string $productId,\n        array $serialNumbers, // ['SN001', 'SN002', ...]\n        ?string $warehouseId = null,\n        ?string $batchId = null,\n    ): Collection;\n\n    public function findByStatus(string $status, ?string $productId = null): Collection;\n\n    public function warrantyStatus(string $serialNumber): string;\n    // valid, expiring_soon, expired\n}\n```\n\n---\n\n## 12. Manufacturing & Recipe Consumption\n\n### Recipe/Bill of Materials\n\n```php\nSchema::create('recipes', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');           // finished good\n    $table->ulid('variant_id')->nullable();\n    $table->string('name');\n    $table->text('instructions')->nullable();\n    $table->integer('yield_quantity')->default(1); // how many finished units per batch\n    $table->string('waste_percentage')->default(0);\n    $table->boolean('is_active')->default(true);\n    $table->timestamps();\n    $table->softDeletes();\n});\n\nSchema::create('recipe_ingredients', function (Blueprint $table) {\n    $table->id();\n    $table->unsignedBigInteger('recipe_id');\n    $table->ulid('ingredient_product_id'); // raw material\n    $table->ulid('ingredient_variant_id')->nullable();\n    $table->decimal('quantity', 12, 4);    // qty per yield unit\n    $table->string('unit', 20);            // kg, pcs, l, etc.\n    $table->unsignedBigInteger('warehouse_id');\n    $table->integer('sort_order')->default(0);\n    $table->timestamps();\n});\n```\n\n### Production Flow\n\n```\nProductionCompleted event\n  → InventoryEngine::handleProductionCompleted()\n    → For each ingredient in recipe:\n      → StockMovementEngine::record(ingredient, -qty, RecipeConsumption)\n      → InventoryBalanceService::recalculate(ingredient)\n      → BatchService::deduct(ingredientBatch, qty)\n    → StockMovementEngine::record(finishedGood, +yield, ProductionOutput)\n    → InventoryBalanceService::recalculate(finishedGood)\n    → CostingEngine::applyInbound(finishedGood, totalIngredientCost)\n    → AuditService::log('production', recipe, ingredients, yield)\n```\n\n---\n\n## 13. Automation & Rule Engine (Phase 2)\n\n### Rules Engine Design\n\n```php\ninterface AutomationRule\n{\n    public function evaluate(AutomationContext $context): bool;\n    public function execute(AutomationContext $context): void;\n    public function name(): string;\n    public function description(): string;\n}\n\nclass ReorderRule implements AutomationRule\n{\n    public function evaluate(AutomationContext $context): bool\n    {\n        return $context->balance->quantity <= $context->product->reorder_point;\n    }\n\n    public function execute(AutomationContext $context): void\n    {\n        // Create draft purchase order\n        PurchaseOrder::create([\n            'product_id' => $context->product->id,\n            'quantity' => $context->product->reorder_quantity,\n            'status' => 'draft',\n        ]);\n    }\n}\n```\n\n### Available Rule Triggers\n\n| Trigger | Evaluation | Actions |\n|---------|-----------|---------|\n| On balance updated | Check: below reorder point | Create PO draft, notify manager |\n| On batch created | Check: expiry within threshold | Send expiry alert |\n| On stock depleted | Check: is backorder product | Notify supplier |\n| On transfer received | Check: auto-receive | Mark transfer completed |\n| On daily schedule | Check: dead stock (90d no movement) | Send dead stock report |\n\n---\n\n## 14. Event-Driven Workflow\n\n### Event Flow Diagram\n\n```\n┌─────────────┐     ┌──────────────────┐     ┌──────────────┐\n│ POS Module   │     │ Purchase Module  │     │ Manufacturing│\n│ SaleCompleted│     │ PurchaseReceived │     │ ProdComplete │\n└──────┬──────┘     └────────┬─────────┘     └──────┬───────┘\n       │                     │                      │\n       └──────────┬──────────┴──────────┬───────────┘\n                  │                     │\n         ┌────────▼────────┐   ┌────────▼────────┐\n         │ InventoryEngine  │   │ InventoryEngine  │\n         │ (Listener)       │   │ (Listener)       │\n         └────────┬────────┘   └────────┬────────┘\n                  │                     │\n         ┌────────▼─────────────────────▼─────────┐\n         │           StockMovementEngine           │\n         │           (record ledger entry)         │\n         └────────────────┬───────────────────────┘\n                          │\n         ┌────────────────▼───────────────────────┐\n         │      Domain Events Dispatched           │\n         │  ┌──────────────┐  ┌────────────────┐  │\n         │  │ StockMovement │  │ InventoryBalance│  │\n         │  │ Created       │  │ Updated         │  │\n         │  └──────────────┘  └────────────────┘  │\n         │  ┌──────────────┐  ┌────────────────┐  │\n         │  │ StockDepleted│  │ LowStockAlert   │  │\n         │  └──────────────┘  └────────────────┘  │\n         └────────────────┬───────────────────────┘\n                          │\n         ┌────────────────▼───────────────────────┐\n         │            External Listeners           │\n         │  Notifications, Search Indexing,        │\n         │  Reorder Engine, Audit Log, Dashboard   │\n         └─────────────────────────────────────────┘\n```\n\n---\n\"@ | Out-File -FilePath C:\\dev\\souda\\docs\\inventory-agent.md -Encoding utf8 -Append",
  "workdir": "C:\\dev\\souda"
}
```

**Output:**
```
(no output)
```

---

## Assistant (Build · DeepSeek V4 Flash Free · 49.7s)

**Tool: bash**

**Input:**
```json
{
  "command": "@\"\n## 15. Domain Events & Listeners\n\n### Inventory Events\n\n| Event | Payload | Dispatched By | Listeners |\n|-------|---------|--------------|-----------|\n| `StockMovementCreated` | product_id, warehouse_id, type, quantity, reference | StockMovementEngine | UpdateProductStockCache, NotifyInventoryUpdate (SSE) |\n| `InventoryBalanceUpdated` | product_id, warehouse_id, quantity_before, quantity_after | InventoryBalanceService | CheckReorderThreshold, UpdateProductSearchIndex |\n| `StockDepleted` | product_id, warehouse_id, variant_id | InventoryBalanceService | MarkProductUnavailable, SendStockDepletedNotification |\n| `LowStockAlert` | product_id, warehouse_id, current_qty, threshold | AlertEngine | SendLowStockNotification, DashboardAlert |\n| `StockReserved` | product_id, warehouse_id, quantity, reference | ReservationEngine | TrackReservation |\n| `ReservationConsumed` | reference, product_id, quantity | ReservationEngine | UpdateReservationStatus |\n| `ReservationExpired` | reservation_id, product_id, quantity | ReservationEngine (cron) | ReleaseReservedStock, NotifyUser |\n| `TransferInitiated` | transfer_id, from, to, items | TransferEngine | SendTransferNotification |\n| `TransferCompleted` | transfer_id, from, to, items, received_at | TransferEngine | UpdateInventoryDashboard |\n| `TransferCancelled` | transfer_id, reason | TransferEngine | ReleaseReservedStock |\n| `BatchExpiring` | batch_id, product_id, expiry_date, days_remaining | BatchService (cron) | SendExpiryAlert, DashboardAlert |\n| `BatchDepleted` | batch_id, product_id | StockMovementEngine | UpdateBatchStatus |\n| `BatchQuarantined` | batch_id, reason | BatchService | NotifyQualityControl |\n| `CostUpdated` | product_id, warehouse_id, old_avg, new_avg | CostingEngine | UpdateProductCostCache |\n| `ProductionCompleted` | product_id, recipe_id, yield, ingredient_costs | InventoryEngine | UpdateInventoryDashboard |\n| `SerialNumberSold` | serial_number, product_id, order_ref | SerialNumberService | UpdateWarrantyStatus |\n| `AdjustmentApproved` | product_id, warehouse_id, qty, reason, approved_by | InventoryEngine | AuditLog, NotifyManager |\n\n### Event Listener Registration\n\n```php\n// In InventoryServiceProvider::boot()\nEvent::listen(\n    SaleCompleted::class,\n    [InventoryEngine::class, 'handleSaleCompleted']\n);\n\nEvent::listen(\n    StockMovementCreated::class,\n    UpdateProductStockCache::class,\n);\n\nEvent::listen(\n    LowStockAlert::class,\n    SendLowStockNotification::class,\n);\n\nEvent::listen(\n    StockDepleted::class,\n    MarkProductUnavailable::class,\n);\n\nEvent::listen(\n    InventoryBalanceUpdated::class,\n    CheckReorderThreshold::class,\n);\n```\n\n### Cron Jobs\n\n| Frequency | Command | Purpose |\n|-----------|---------|---------|\n| Every minute | `inventory:expire-reservations` | Release expired stock reservations |\n| Every 6 hours | `inventory:expiry-alerts` | Find batches expiring within threshold, dispatch BatchExpiring |\n| Daily | `inventory:dead-stock-report` | Find products with no movement in 90 days |\n| Daily | `inventory:reconcile` | Compare ledger totals vs balance snapshots |\n\n---\n\n## 16. Database Conventions\n\n### Naming Conventions\n\n| Aspect | Convention | Example |\n|--------|-----------|---------|\n| Tables | snake_case, plural | `inventory_ledger`, `stock_reservations` |\n| Columns | snake_case | `product_id`, `average_unit_cost` |\n| Tenant FK | `tenant_id` (string, 36) | Shared-mode isolation |\n| Product FK | `product_id` (ulid) | References Product ULID |\n| Variant FK | `variant_id` (ulid, nullable) | References Variant ULID |\n| Warehouse FK | `warehouse_id` (unsignedBigInteger) | References warehouses.id |\n| Money | `bigInteger` in cents | `unit_cost`, `total_cost` |\n| Quantity | `integer` | Whole units; fractional → decimal(12,4) for weight |\n| Status | `string(20)` with enum backing | `MovementTypeEnum`, `ReservationStatusEnum` |\n| Timestamps | `created_at`, `updated_at` | Ledger has only `created_at` |\n| Soft deletes | `deleted_at` for reference data | warehouses, batches (not ledger) |\n\n### Migration Rules\n\n- All inventory tables run on tenant DB (dedicated) or shared DB (shared mode)\n- Migrations live in `app/Modules/Inventory/Database/Migrations/Tenant/`\n- Registered via `InventoryServiceProvider::boot()` `loadMigrationsFrom()`\n- All tables include `$table->string('tenant_id', 36)` for shared-mode scoping\n- Anonymous migration classes — no typed properties (PHP 8.4 compatibility)\n- Always add `->orderBy('id')` before `->each()` in migrations (Laravel 12 req)\n\n### Indexing Strategy\n\n| Table | Critical Indexes | Rationale |\n|-------|-----------------|-----------|\n| `inventory_ledger` | (product_id, warehouse_id, created_at), (reference, reference_type), (batch_id) | Movement history queries, reference lookup, batch audit |\n| `inventory_balances` | (product_id, variant_id, warehouse_id) UNIQUE | UPSERT lookup, fast single-row access |\n| `cost_layers` | (product_id, warehouse_id, costing_method), (created_at) | FIFO ordering, costing method queries |\n| `stock_reservations` | (status, expires_at), (product_id, warehouse_id, status) | Expiry cron, availability checks |\n| `inventory_batches` | (expiry_date, status), (product_id, warehouse_id) | FEFO picking, expiry alerts |\n| `serial_numbers` | (product_id, serial_number) UNIQUE, (status, warehouse_id) | Serial lookup, availability queries |\n| `inventory_transfers` | (from_warehouse_id, status), (reference) | Transfer tracking, reference lookup |\n\n---\n\n## 17. Model Relationships\n\n```php\nclass InventoryLedger extends Model\n{\n    public function product(): BelongsTo { ... }\n    public function variant(): BelongsTo { ... }\n    public function warehouse(): BelongsTo { ... }\n    public function batch(): BelongsTo { ... }\n    public function bin(): BelongsTo { ... }\n}\n\nclass InventoryBalance extends Model\n{\n    public function product(): BelongsTo { ... }\n    public function variant(): BelongsTo { ... }\n    public function warehouse(): BelongsTo { ... }\n}\n\nclass CostLayer extends Model\n{\n    public function product(): BelongsTo { ... }\n    public function warehouse(): BelongsTo { ... }\n    public function ledgerEntry(): BelongsTo { ... }\n}\n\nclass StockReservation extends Model\n{\n    public function product(): BelongsTo { ... }\n    public function variant(): BelongsTo { ... }\n    public function warehouse(): BelongsTo { ... }\n}\n\nclass InventoryBatch extends Model\n{\n    public function product(): BelongsTo { ... }\n    public function warehouse(): BelongsTo { ... }\n    public function ledgerEntries(): HasMany { ... }\n    public function serialNumbers(): HasMany { ... }\n}\n\nclass SerialNumber extends Model\n{\n    public function product(): BelongsTo { ... }\n    public function warehouse(): BelongsTo { ... }\n    public function batch(): BelongsTo { ... }\n}\n\nclass Warehouse extends Model\n{\n    public function bins(): HasMany { ... }\n    public function balances(): HasMany { ... }\n    public function outboundTransfers(): HasMany { ... }\n    public function inboundTransfers(): HasMany { ... }\n}\n\nclass InventoryBin extends Model\n{\n    public function warehouse(): BelongsTo { ... }\n    public function ledgerEntries(): HasMany { ... }\n}\n\nclass Recipe extends Model\n{\n    public function product(): BelongsTo { ... }\n    public function ingredients(): HasMany { ... }\n}\n\nclass RecipeIngredient extends Model\n{\n    public function recipe(): BelongsTo { ... }\n    public function ingredientProduct(): BelongsTo { ... }\n}\n```\n\n### Product Module Cross-References\n\nThe Inventory module references these models from the Product module (no circular dependency):\n\n- `App\\Modules\\Product\\Models\\Product` — by ULID only\n- `App\\Modules\\Product\\Models\\Variant` — by ULID only\n\nInventory never accesses Product properties or methods directly. When inventory needs product data (costing method, reorder point), it uses the Product module's published contracts via dependency injection.\n\n---\n\n## 18. Industry Pack Extension Rules\n\n### Feature Flags for Inventory\n\nEach Industry Pack defines feature flags in its `featureFlags()` method. The Inventory Engine checks these flags to activate industry-specific behaviors.\n\n```php\n// Example: PharmacyPack\npublic function featureFlags(): array\n{\n    return [\n        'batch_tracking' => true,\n        'expiry_tracking' => true,\n        'fefo_picking' => true,\n        'serial_number_tracking' => false,\n        'recipe_management' => false,\n        'weight_based_inventory' => false,\n        'bin_locations' => true,\n        'quarantine_management' => true,\n        'prescription_hold' => false,\n    ];\n}\n\n// Example: RestaurantPack\npublic function featureFlags(): array\n{\n    return [\n        'batch_tracking' => false,\n        'expiry_tracking' => true,\n        'recipe_management' => true,\n        'recipe_consumption' => true,\n        'waste_tracking' => true,\n        'batch_costing' => true,\n        'bin_locations' => false,\n        'serial_number_tracking' => false,\n    ];\n}\n\n// Example: ElectronicsPack\npublic function featureFlags(): array\n{\n    return [\n        'serial_number_tracking' => true,\n        'warranty_tracking' => true,\n        'imei_validation' => true,\n        'batch_tracking' => false,\n        'bin_locations' => false,\n        'recipe_management' => false,\n    ];\n}\n```\n\n### Flag Resolution\n\n```php\n// In InventoryEngine\nif ($this->config->hasFeature('batch_tracking')) {\n    $this->batchService->record($movement);\n}\n\nif ($this->config->hasFeature('serial_number_tracking')) {\n    $this->serialService->validate($serialNumbers);\n}\n\n// Picking strategy determined by feature flag\n$pickingMethod = $this->config->hasFeature('fefo_picking') ? 'fefo' : 'fifo';\n```\n\n### NEVER — Hardcoded Industry Checks\n\n```php\n// WRONG — never do this\nif ($config->businessType === 'pharmacy') { ... }\n\n// WRONG — never do this\nmatch ($config->businessType) {\n    'pharmacy' => $this->doPharmacyLogic(),\n    'restaurant' => $this->doRestaurantLogic(),\n};\n```\n\n### ALWAYS — Feature Flag Pattern\n\n```php\n// RIGHT — use feature flags\nif ($config->hasFeature('fefo_picking')) {\n    $this->batchService->pickBatches(..., method: 'fefo');\n}\n```\n\n---\n\n## 19. API Design Conventions\n\n### Controller Structure\n\nControllers return Inertia page responses for SSR pages. JSON API endpoints use `resources/js/modules/inventory/api/` with React Query.\n\n| Method | Path | Controller | Purpose |\n|--------|------|-----------|---------|\n| GET | `/inventory` | InventoryController@index | Inventory dashboard |\n| GET | `/inventory/ledger` | InventoryController@ledger | Movement history |\n| GET | `/inventory/balances` | InventoryController@balances | Current stock per product+warehouse |\n| GET | `/inventory/balances/{product}` | InventoryController@productBalance | Single product stock |\n| GET | `/inventory/transfers` | TransferController@index | Transfer list |\n| POST | `/inventory/transfers` | TransferController@store | Initiate transfer |\n| POST | `/inventory/transfers/{id}/send` | TransferController@send | Send transfer |\n| POST | `/inventory/transfers/{id}/receive` | TransferController@receive | Receive transfer |\n| GET | `/inventory/batches` | BatchController@index | Batch list |\n| POST | `/inventory/batches` | BatchController@store | Register batch |\n| GET | `/inventory/serials` | SerialController@index | Serial numbers |\n| POST | `/inventory/serials` | SerialController@store | Register serials |\n| GET | `/inventory/reservations` | ReservationController@index | Active reservations |\n| POST | `/inventory/reservations` | ReservationController@store | Create reservation |\n| DELETE | `/inventory/reservations/{id}` | ReservationController@destroy | Cancel reservation |\n| GET | `/inventory/adjustments` | AdjustmentController@index | Adjustment history |\n| POST | `/inventory/adjustments` | AdjustmentController@store | Submit adjustment |\n| GET | `/inventory/warehouses` | WarehouseController@index | Warehouse list |\n| POST | `/inventory/warehouses` | WarehouseController@store | Create warehouse |\n| GET | `/inventory/recipes` | RecipeController@index | BOM/recipe list |\n| POST | `/inventory/recipes` | RecipeController@store | Create recipe |\n| GET | `/inventory/alerts` | AlertController@index | Active alerts |\n\n### Form Request Validation\n\n```php\nclass StoreTransferRequest extends FormRequest\n{\n    public function rules(): array\n    {\n        return [\n            'from_warehouse_id' => ['required', 'exists:warehouses,id'],\n            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],\n            'items' => ['required', 'array', 'min:1'],\n            'items.*.product_id' => ['required', 'string', 'exists:products,ulid'],\n            'items.*.quantity' => ['required', 'integer', 'min:1'],\n            'description' => ['nullable', 'string', 'max:500'],\n        ];\n    }\n}\n\nclass StockAdjustmentRequest extends FormRequest\n{\n    public function rules(): array\n    {\n        return [\n            'product_id' => ['required', 'string', 'exists:products,ulid'],\n            'warehouse_id' => ['required', 'exists:warehouses,id'],\n            'quantity' => ['required', 'integer'], // negative = decrease, positive = increase\n            'reason' => ['required', 'string', 'in:damage,loss,theft,found,correction,other'],\n            'description' => ['nullable', 'string', 'max:500'],\n        ];\n    }\n}\n```\n\n---\n\n## 20. UI/UX Principles for SME Users\n\n### Design Tenets\n\n| Principle | Implementation |\n|-----------|---------------|\n| **10-second insight** | Dashboard shows stock value, today's movements, low stock count, and top alerts |\n| **One-click actions** | Most common actions (adjust stock, create transfer) are 2 clicks max |\n| **Visual stock health** | Color-coded badges: green (in stock), yellow (low), red (out), gray (discontinued) |\n| **Progressive disclosure** | Simple views for cashiers, detailed views for managers |\n| **Search-first** | Every inventory page has a global search bar — find by product name, SKU, barcode, batch, serial |\n| **Bulk operations** | Select multiple rows → adjust, transfer, print labels |\n| **Export** | Every table can be exported to CSV/PDF with one click |\n| **Mobile-friendly** | Core inventory operations work on tablets for warehouse staff |\n\n### Page Layouts\n\n**Inventory Dashboard (Manager View)**\n```\n┌──────────────────────────────────────────────────────────┐\n│ Inventory Dashboard                                       │\n├──────────┬──────────┬─────────────┬──────────────────────┤\n│ Stock    │ Today's  │ Low Stock   │ Expiring in 30 Days  │\n│ Value    │ Movements│ Items       │ Items                │\n│ $84,200  │ +12 in   │ 8 items     │ 3 batches            │\n│          │ -5 out   │ below min   │                      │\n├──────────┴──────────┴─────────────┴──────────────────────┤\n│ Fast Movers              │ Slow Movers                    │\n│ ┌────────────────────┐   │ ┌────────────────────┐        │\n│ │ 1. Milk (84 sold)  │   │ │ 1. Paint (0 in 90d)│        │\n│ │ 2. Bread (62 sold) │   │ │ 2. Oil (2 in 90d)  │        │\n│ │ 3. Eggs (58 sold)  │   │ │ 3. Spices (0 in 90d│        │\n│ └────────────────────┘   │ └────────────────────┘        │\n├──────────────────────────┴───────────────────────────────┤\n│ Recent Movements                                          │\n│ ┌──────┬──────────┬───────┬────────┬────────┬──────────┐│\n│ │ Time │ Product  │ Type  │ Qty    │ Ref    │ By       ││\n│ ├──────┼──────────┼───────┼────────┼────────┼──────────┤│\n│ │ 2:30 │ Paracet.│ Sale  │ -10    │ INV-23 │ Cashier  ││\n│ │ 2:15 │ Bandages│ Adj   │ +50    │ ADJ-04 │ Manager  ││\n│ │ 1:45 │ Syringes│ Pur   │ +200   │ PO-404 │ Supplier ││\n│ └──────┴──────────┴───────┴────────┴────────┴──────────┘│\n└──────────────────────────────────────────────────────────┘\n```\n\n**Stock Movement History**\n- Filterable by date range, type, product, warehouse\n- Paginated table with infinite scroll\n- Click row to see full details (batch, serials, cost, metadata)\n- Export to CSV\n\n**Transfer Management**\n- Two-panel view: source warehouse items → destination warehouse\n- Drag items from available stock into transfer list\n- Barcode scanner input for fast picking\n- Status timeline: Draft → Sent → In Transit → Received\n\n---\n\n## 21. Performance & Concurrency Requirements\n\n### Read Performance\n\n| Query Pattern | Strategy |\n|---------------|----------|\n| Dashboard load (aggregates) | Materialized dashboard cache, recompute every 5 min |\n| Product stock lookup (single) | Direct `InventoryBalance` read — single row by unique key |\n| Movement history (list) | Paginated query on `inventory_ledger` with composite indexes |\n| Batch/expiry queries | Indexed on `(expiry_date, status)` |\n| Search by SKU/barcode | MySQL index or Scout/Algolia |\n\n### Write Performance\n\n| Operation | Strategy |\n|-----------|----------|\n| POS sale (single deduction) | Synchronous — one ledger INSERT + one balance UPSERT + one costing call |\n| Bulk import (1000+ items) | Queue job — batches of 100 with chunked ledger inserts |\n| Stock transfer (full warehouse) | Queue job — async transfer processing |\n| End-of-day reconciliation | Queue job — ledger replay |\n\n### Concurrency Protection\n\n| Threat | Protection |\n|--------|-----------|\n| Two POS terminals deduct same item simultaneously | Optimistic locking on `inventory_balances.lock_version` |\n| Transfer and sale race on same stock | Lock version check fails second operation → retry |\n| Reservation expires while being consumed | Atomic check-then-act in DB transaction |\n| Bulk import conflicts with live sales | Row-level locking via `lockForUpdate()` on balance rows |\n\n### Transaction Strategy\n\n```php\n// Every inventory write operation uses a DB transaction\nDB::transaction(function () use ($movement) {\n    $ledger = $this->stockMovementEngine->record(...);\n    $this->inventoryBalanceService->recalculate(...);\n    $this->costingEngine->consume(...);\n\n    // Dispatch event after commit success\n    StockMovementCreated::dispatch($ledger);\n}, attempts: 3);\n```\n\n---\n\"@ | Out-File -FilePath C:\\dev\\souda\\docs\\inventory-agent.md -Encoding utf8 -Append",
  "workdir": "C:\\dev\\souda"
}
```

**Output:**
```
(no output)
```

---

## Assistant (Build · DeepSeek V4 Flash Free · 40.6s)

_Thinking:_

Let me continue with the remaining sections.

**Tool: bash**

**Input:**
```json
{
  "command": "@\"\n## 22. Multi-Tenant Considerations\n\n### Data Isolation\n\n| Tenant Mode | Inventory Tables Location | Isolation Mechanism |\n|-------------|--------------------------|---------------------|\n| Shared (Free, Starter, Professional) | `souda_shared` database | `tenant_id` column on every table, `HasTenantScope` trait |\n| Dedicated (Enterprise) | `souda_tenant_{uuid}` database | Separate MySQL database, no `tenant_id` needed |\n\n### HasTenantScope on Inventory Models\n\nAll shared-mode inventory models use the `HasTenantScope` trait to auto-scope queries by `tenant_id`:\n\n```php\nclass InventoryLedger extends Model\n{\n    use HasTenantScope;\n\n    protected $guarded = [];\n    public $timestamps = false; // ledger uses only created_at\n}\n\nclass InventoryBalance extends Model\n{\n    use HasTenantScope;\n\n    protected $guarded = [];\n}\n\nclass CostLayer extends Model\n{\n    use HasTenantScope;\n    ...\n}\n```\n\n### InventoryServiceProvider\n\n```php\nclass InventoryServiceProvider extends ServiceProvider\n{\n    public function register(): void\n    {\n        $this->app->singleton(InventoryEngine::class);\n        $this->app->singleton(StockMovementEngine::class);\n        $this->app->singleton(InventoryBalanceService::class);\n        $this->app->singleton(CostingEngine::class);\n        $this->app->singleton(ReservationEngine::class);\n        $this->app->singleton(BatchService::class);\n        $this->app->singleton(SerialNumberService::class);\n        $this->app->singleton(WarehouseService::class);\n        $this->app->singleton(TransferEngine::class);\n        $this->app->singleton(AuditService::class);\n        $this->app->singleton(AlertEngine::class);\n    }\n\n    public function boot(): void\n    {\n        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations/Tenant');\n\n        $this->registerRoutes();\n        $this->registerEvents();\n        $this->registerObservers();\n    }\n\n    private function registerRoutes(): void\n    {\n        Route::middleware(['web', 'auth', InitializeTenancyByUser::class])\n            ->group(function () {\n                Route::middleware('subscription')->group(function () {\n                    Route::prefix('inventory')->name('inventory.')->group(function () {\n                        // ... routes\n                    });\n                });\n            });\n    }\n\n    private function registerEvents(): void\n    {\n        Event::listen(SaleCompleted::class, [InventoryEngine::class, 'handleSaleCompleted']);\n        Event::listen(PurchaseReceived::class, [InventoryEngine::class, 'handlePurchaseReceived']);\n        // ... all inventory event wiring\n    }\n}\n```\n\n### Tenant Config Integration\n\nThe Inventory Engine checks the tenant's feature flags and preferences at runtime:\n\n```php\nclass InventoryEngine\n{\n    public function handlePurchaseReceived(PurchaseReceived $event): void\n    {\n        $movement = $this->movementEngine->record(/* ... */);\n\n        if ($this->config->hasFeature('batch_tracking')) {\n            $this->batchService->receive(/* ... */);\n        }\n\n        if ($this->config->hasFeature('serial_number_tracking')) {\n            $this->serialService->registerBatch(/* ... */);\n        }\n    }\n}\n```\n\n---\n\n## 23. Testing Strategy\n\n### Test Categories\n\n| Category | Scope | Framework | Location |\n|----------|-------|-----------|----------|\n| Unit tests | Engine services, cost calculations, validation rules | Pest | `tests/Unit/Inventory/` or `app/Modules/Inventory/Tests/Unit/` |\n| Feature tests | Full workflows (sale→ledger→balance→alert) | Pest | `tests/Feature/Inventory/` |\n| Integration | Cross-module (Order→Inventory, Purchase→Inventory) | Pest | `tests/Feature/` |\n| Reconciliation | Ledger replay matches expected balance | Pest | `tests/Feature/Inventory/ReconciliationTest.php` |\n\n### Required Test Coverage\n\n| Feature | Minimum Tests |\n|---------|--------------|\n| StockMovementEngine::record | 5+ (various types, with/without batch/serial) |\n| InventoryBalanceService::recalculate | 3+ (inbound, outbound, zero) |\n| CostingEngine (weighted average) | 5+ (multiple layers, zero balance, edge cases) |\n| CostingEngine (FIFO) | 5+ (layer consumption order, partial layers, mixed methods) |\n| ReservationEngine | 5+ (create, consume, expire, cancel, concurrent) |\n| TransferEngine | 4+ (initiate, send, receive, cancel, partial) |\n| BatchService | 4+ (receive, deduct, fifo/fefo picking, quarantine) |\n| SerialNumberService | 4+ (register, validate, mark sold, warranty check) |\n| AlertEngine | 3+ (low stock threshold, expiry alert, dead stock) |\n| Multi-warehouse | 2+ (transfer flow, balance isolation) |\n| Concurrent writes | 2+ (optimistic locking, retry) |\n| Reconciliation | 1+ (replay ledger → verify balance) |\n\n### Test Patterns\n\n```php\n// Unit test — StockMovementEngine\nit('records a purchase receipt movement', function () {\n    $engine = app(StockMovementEngine::class);\n\n    $ledger = $engine->record(\n        productId: $product->ulid,\n        warehouseId: $warehouse->id,\n        quantity: 100,\n        type: MovementTypeEnum::PurchaseReceipt,\n        reference: 'PO-001',\n        unitCost: 1500, // $15.00\n    );\n\n    expect($ledger)\n        ->quantity->toBe(100)\n        ->quantity_before->toBe(0)\n        ->quantity_after->toBe(100)\n        ->movement_type->toBe('purchase_receipt')\n        ->reference->toBe('PO-001')\n        ->unit_cost->toBe(1500);\n});\n\n// Feature test — Full sale workflow\nit('deducts stock and updates balance on sale', function () {\n    $product = Product::factory()->create([\n        'costing_method' => 'weighted_average',\n    ]);\n    $warehouse = Warehouse::factory()->create(['is_default' => true]);\n    InventoryBalance::factory()->create([\n        'product_id' => $product->ulid,\n        'warehouse_id' => $warehouse->id,\n        'quantity' => 50,\n        'average_unit_cost' => 1000,\n    ]);\n\n    $event = new SaleCompleted(\n        productId: $product->ulid,\n        warehouseId: $warehouse->id,\n        quantity: 5,\n        orderReference: 'ORD-001',\n    );\n\n    app(InventoryEngine::class)->handleSaleCompleted($event);\n\n    $balance = InventoryBalance::where([\n        'product_id' => $product->ulid,\n        'warehouse_id' => $warehouse->id,\n    ])->first();\n\n    expect($balance)\n        ->quantity->toBe(45)\n        ->available_quantity->toBe(45);\n})->shared(); // shared() mode for shared-database tests\n\n// Concurrent write test\nit('prevents double deduction with optimistic locking', function () {\n    $balance = InventoryBalance::factory()->create(['quantity' => 10]);\n\n    $first = InventoryBalance::find($balance->id);\n    $second = InventoryBalance::find($balance->id);\n\n    $first->decrement('quantity', 5); // succeeds\n    expect(fn () => $second->decrement('quantity', 5)) // fails\n        ->toThrow(ModelNotFoundException::class);\n});\n```\n\n### Test Database Considerations\n\n- Feature tests use `souda_testing` DB (shared between central and default connections)\n- Do NOT run tests in parallel — `migrate:fresh` calls will collide\n- Use `RefreshDatabase` trait or manual migration refresh in setUp\n- Factory state: `InventoryBalance::factory()->create()` with explicit `product_id`, `warehouse_id`\n\n---\n\n## 24. Coding Standards & AI Guardrails\n\n### MUST FOLLOW\n\n- All inventory write operations go through `InventoryEngine` — never call `StockMovementEngine` directly\n- `InventoryLedger` is INSERT-only — never UPDATE or DELETE ledger rows\n- Use `MovementTypeEnum` for movement types — never hardcoded strings\n- All monetary values in cents (integer) — `bigInteger` column type\n- All quantities are integers (whole units) — use `decimal(12,4)` only for weight-based products\n- Use constructor property promotion for all services\n- Use proper return type hints on all methods\n- Use Form Request classes for validation — no inline validation\n- Use PHPDoc for complex logic — no inline comments for simple code\n- Run `vendor/bin/pint --format agent` after all PHP changes\n- Add `->orderBy('id')` before `->each()` on query builders\n\n### NEVER\n\n- Write stock directly from another module (POS, Order, Purchase, etc.)\n- Use `env()` outside config files\n- Add hardcoded business type checks (`if ($slug === 'pharmacy')`)\n- Create per-industry tables or columns\n- Use `DB::` when an Eloquent relationship exists\n- Leave empty `__construct()` with zero params (unless private)\n- Delete inventory ledger rows\n- Commit secrets or credentials\n\n### ALWAYS\n\n- Create event listeners for cross-module communication\n- Wrap inventory writes in `DB::transaction()` with retry\n- Use `HasTenantScope` on shared-mode models\n- Register events in `InventoryServiceProvider::boot()`\n- Run tests before marking inventory tasks complete\n- Write feature tests for every new inventory workflow\n\n---\n\n## 25. Development Roadmap\n\n### Phase 1A: Foundation (Week 1-2)\n\n| Task | Deliverable |\n|------|-------------|\n| Create module structure | `app/Modules/Inventory/` with all directories |\n| Create tenant migrations | `inventory_ledger`, `inventory_balances`, `cost_layers`, `stock_reservations` |\n| Create shared migrations | Same tables with `tenant_id` for shared mode |\n| Implement Models | `InventoryLedger`, `InventoryBalance`, `CostLayer`, `StockReservation` |\n| Implement Enums | `MovementTypeEnum`, `CostingMethodEnum`, `ReservationStatusEnum` |\n| Implement StockMovementEngine | Core `record()` method with reference generation |\n| Implement InventoryBalanceService | `recalculate()`, `rebuildFromLedger()` |\n| Create InventoryServiceProvider | Singleton bindings, migration loading |\n| Create tests | StockMovementEngine unit tests, InventoryBalance feature tests |\n\n### Phase 1B: Costing & Reservations (Week 3-4)\n\n| Task | Deliverable |\n|------|-------------|\n| Implement CostingEngine | Weighted average + FIFO strategies |\n| Implement CostLayer model + migration | Layer tracking per product+warehouse |\n| Implement ReservationEngine | Reserve, consume, expire, cancel |\n| Implement reservation migration | `stock_reservations` table |\n| Create ExpireReservations command | Cron: every minute |\n| Create tests | CostingEngine unit tests, ReservationEngine feature tests |\n\n### Phase 1C: Warehouse & Transfer (Week 5)\n\n| Task | Deliverable |\n|------|-------------|\n| Move Warehouse model from Product | Backward-compatible alias |\n| Add bins/locations tables + models | `inventory_bins` |\n| Implement TransferEngine | Initiate, send, receive, cancel, partial receive |\n| Implement transfer tables + models | `inventory_transfers`, `inventory_transfer_items` |\n| Implement AuditService | Log all operations |\n| Implement AuditLog migration | `inventory_audit_logs` |\n| Create tests | TransferEngine feature tests, multi-warehouse tests |\n\n### Phase 1D: Batch, Serial & Alerts (Week 6)\n\n| Task | Deliverable |\n|------|-------------|\n| Implement BatchService | Receive, deduct, FEFO/FIFO picking, quarantine |\n| Implement batch migration + model | `inventory_batches` |\n| Implement SerialNumberService | Register, validate, lifecycle |\n| Implement serial migration + model | `serial_numbers` |\n| Implement AlertEngine | Low stock, depletion, expiry evaluation |\n| Create expiry alerts command | Cron: every 6 hours |\n| Create tests | BatchService, SerialNumberService, AlertEngine |\n\n### Phase 1E: Frontend (Week 7-8)\n\n| Task | Deliverable |\n|------|-------------|\n| Inventory Dashboard | Stock value, movements, alerts, fast/slow movers |\n| Stock Movement History | Filterable table with pagination |\n| Inventory Balance View | Per-product+warehouse stock snapshot |\n| Warehouse Management | CRUD for warehouses and bins |\n| Transfer Management | Create, send, receive transfers |\n| Batch Management | Create batches, view expiries |\n| Serial Number Management | Register serials, view lifecycle |\n| Stock Adjustments | Submit and approve adjustments |\n| Alerts Panel | Low stock, expiring, dead stock alerts |\n| Frontend API layer | React Query hooks for all inventory endpoints |\n\n### Phase 1F: Industry Pack Integration (Week 9)\n\n| Task | Deliverable |\n|------|-------------|\n| Update all 15 Industry Packs | Add inventory feature flags |\n| Integrate Pharmacy Pack | batch_tracking, expiry_tracking, fefo_picking |\n| Integrate Restaurant Pack | recipe_management, recipe_consumption, waste_tracking |\n| Integrate Electronics Pack | serial_number_tracking, warranty_tracking |\n| Integrate Grocery Pack | weight_based_inventory, perishable_tracking |\n| Integrate Fashion Pack | variant_size_color inventory views |\n| Test all pack configurations | Verify feature flag gating |\n\n### Phase 2: Automation & Smart Features (Future)\n\n| Feature | Complexity |\n|---------|-----------|\n| Auto Reorder Engine | Medium |\n| Purchase Suggestions | High |\n| Dead Stock Detection | Low |\n| Slow/Fast Moving Classification | Medium |\n| Seasonal Demand Prediction | High |\n| Automation Rules Engine (IF-THEN) | High |\n| Inventory Dashboard with Charts | Medium |\n| AI Assistant (chat-based) | Very High |\n\n---\n\n## 26. File Creation Templates\n\n### New Inventory Service\n\n```php\n<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Modules\\Inventory\\Services;\n\nuse App\\Modules\\BusinessType\\ValueObjects\\TenantConfig;\n\nclass MyInventoryService\n{\n    public function __construct(\n        private TenantConfig $config,\n    ) {}\n\n    public function execute(): void\n    {\n        if ($this->config->hasFeature('batch_tracking')) {\n            // industry-specific behavior\n        }\n    }\n}\n```\n\n### New Inventory Model (Shared Mode)\n\n```php\n<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Modules\\Inventory\\Models;\n\nuse App\\Tenancy\\Models\\Concerns\\HasTenantScope;\nuse Illuminate\\Database\\Eloquent\\Model;\n\nclass MyModel extends Model\n{\n    use HasTenantScope;\n\n    protected $guarded = [];\n\n    public function casts(): array\n    {\n        return [\n            'metadata' => 'array',\n        ];\n    }\n}\n```\n\n### New Inventory Event\n\n```php\n<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Modules\\Inventory\\Events;\n\nuse Illuminate\\Foundation\\Events\\Dispatchable;\n\nclass MyInventoryEvent\n{\n    use Dispatchable;\n\n    public function __construct(\n        public string $productId,\n        public string $warehouseId,\n        public int $quantity,\n    ) {}\n}\n```\n\n### New Inventory Listener\n\n```php\n<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Modules\\Inventory\\Listeners;\n\nuse App\\Modules\\Inventory\\Events\\MyInventoryEvent;\n\nclass MyInventoryListener\n{\n    public function handle(MyInventoryEvent $event): void\n    {\n        // Handle the event\n    }\n}\n```\n\n### New Tenant Migration\n\n```php\n<?php\n\ndeclare(strict_types=1);\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration\n{\n    public function up(): void\n    {\n        Schema::create('my_inventory_table', function (Blueprint $table) {\n            $table->id();\n            $table->string('tenant_id', 36);\n            $table->ulid('product_id');\n            $table->timestamps();\n        });\n    }\n\n    public function down(): void\n    {\n        Schema::dropIfExists('my_inventory_table');\n    }\n};\n```\n\n### New React Query Hook (Frontend)\n\n```typescript\n// resources/js/modules/inventory/hooks/use-inventory.ts\nimport { useQuery, useMutation } from '@tanstack/react-query';\nimport axios from 'axios';\nimport type { InventoryBalance, InventoryMovement } from '../types/inventory';\n\nexport function useInventoryBalances(warehouseId?: string) {\n    return useQuery({\n        queryKey: ['inventory', 'balances', warehouseId],\n        queryFn: async () => {\n            const { data } = await axios.get<InventoryBalance[]>(\n                route('inventory.balances'),\n                { params: { warehouse_id: warehouseId } }\n            );\n            return data;\n        },\n    });\n}\n\nexport function useMovementHistory(productId?: string) {\n    return useQuery({\n        queryKey: ['inventory', 'movements', productId],\n        queryFn: async () => {\n            const { data } = await axios.get<InventoryMovement[]>(\n                route('inventory.ledger'),\n                { params: { product_id: productId } }\n            );\n            return data;\n        },\n    });\n}\n\nexport function useCreateTransfer() {\n    return useMutation({\n        mutationFn: async (payload: TransferPayload) => {\n            const { data } = await axios.post(route('inventory.transfers.store'), payload);\n            return data;\n        },\n    });\n}\n```\n\n---\n\"@ | Out-File -FilePath C:\\dev\\souda\\docs\\inventory-agent.md -Encoding utf8 -Append",
  "workdir": "C:\\dev\\souda"
}
```

**Output:**
```
(no output)
```

---

## Assistant (Build · DeepSeek V4 Flash Free · 5.2s)

_Thinking:_

Let me verify the file was written correctly and check the line count.

**Tool: bash**

**Input:**
```json
{
  "command": "Get-Content C:\\dev\\souda\\docs\\inventory-agent.md | Measure-Object -Line",
  "workdir": "C:\\dev\\souda"
}
```

**Output:**
```

Lines Words Characters Property
----- ----- ---------- --------
 1622                          



```

---

## Assistant (Build · DeepSeek V4 Flash Free · 5.3s)

_Thinking:_

The file is 1622 lines, which is well over the 300-600+ range the user requested. That's actually great - it's a comprehensive document. Let me verify the file reads back properly by checking the last few lines to make sure the closing was correct.

**Tool: read**

**Input:**
```json
{
  "filePath": "C:\\dev\\souda\\docs\\inventory-agent.md",
  "offset": 1600
}
```

**Output:**
```
<path>C:\dev\souda\docs\inventory-agent.md</path>
<type>file</type>
<content>
1600:         warehouseId: ->id,
1601:         quantity: 100,
1602:         type: MovementTypeEnum::PurchaseReceipt,
1603:         reference: 'PO-001',
1604:         unitCost: 1500, // .00
1605:     );
1606: 
1607:     expect()
1608:         ->quantity->toBe(100)
1609:         ->quantity_before->toBe(0)
1610:         ->quantity_after->toBe(100)
1611:         ->movement_type->toBe('purchase_receipt')
1612:         ->reference->toBe('PO-001')
1613:         ->unit_cost->toBe(1500);
1614: });
1615: 
1616: // Feature test — Full sale workflow
1617: it('deducts stock and updates balance on sale', function () {
1618:      = Product::factory()->create([
1619:         'costing_method' => 'weighted_average',
1620:     ]);
1621:      = Warehouse::factory()->create(['is_default' => true]);
1622:     InventoryBalance::factory()->create([
1623:         'product_id' => ->ulid,
1624:         'warehouse_id' => ->id,
1625:         'quantity' => 50,
1626:         'average_unit_cost' => 1000,
1627:     ]);
1628: 
1629:      = new SaleCompleted(
1630:         productId: ->ulid,
1631:         warehouseId: ->id,
1632:         quantity: 5,
1633:         orderReference: 'ORD-001',
1634:     );
1635: 
1636:     app(InventoryEngine::class)->handleSaleCompleted();
1637: 
1638:      = InventoryBalance::where([
1639:         'product_id' => ->ulid,
1640:         'warehouse_id' => ->id,
1641:     ])->first();
1642: 
1643:     expect()
1644:         ->quantity->toBe(45)
1645:         ->available_quantity->toBe(45);
1646: })->shared(); // shared() mode for shared-database tests
1647: 
1648: // Concurrent write test
1649: it('prevents double deduction with optimistic locking', function () {
1650:      = InventoryBalance::factory()->create(['quantity' => 10]);
1651: 
1652:      = InventoryBalance::find(->id);
1653:      = InventoryBalance::find(->id);
1654: 
1655:     ->decrement('quantity', 5); // succeeds
1656:     expect(fn () => ->decrement('quantity', 5)) // fails
1657:         ->toThrow(ModelNotFoundException::class);
1658: });
1659: `
1660: 
1661: ### Test Database Considerations
1662: 
1663: - Feature tests use souda_testing DB (shared between central and default connections)
1664: - Do NOT run tests in parallel — migrate:fresh calls will collide
1665: - Use RefreshDatabase trait or manual migration refresh in setUp
1666: - Factory state: InventoryBalance::factory()->create() with explicit product_id, warehouse_id
1667: 
1668: ---
1669: 
1670: ## 24. Coding Standards & AI Guardrails
1671: 
1672: ### MUST FOLLOW
1673: 
1674: - All inventory write operations go through InventoryEngine — never call StockMovementEngine directly
1675: - InventoryLedger is INSERT-only — never UPDATE or DELETE ledger rows
1676: - Use MovementTypeEnum for movement types — never hardcoded strings
1677: - All monetary values in cents (integer) — igInteger column type
1678: - All quantities are integers (whole units) — use decimal(12,4) only for weight-based products
1679: - Use constructor property promotion for all services
1680: - Use proper return type hints on all methods
1681: - Use Form Request classes for validation — no inline validation
1682: - Use PHPDoc for complex logic — no inline comments for simple code
1683: - Run endor/bin/pint --format agent after all PHP changes
1684: - Add ->orderBy('id') before ->each() on query builders
1685: 
1686: ### NEVER
1687: 
1688: - Write stock directly from another module (POS, Order, Purchase, etc.)
1689: - Use env() outside config files
1690: - Add hardcoded business type checks (if ( === 'pharmacy'))
1691: - Create per-industry tables or columns
1692: - Use DB:: when an Eloquent relationship exists
1693: - Leave empty __construct() with zero params (unless private)
1694: - Delete inventory ledger rows
1695: - Commit secrets or credentials
1696: 
1697: ### ALWAYS
1698: 
1699: - Create event listeners for cross-module communication
1700: - Wrap inventory writes in DB::transaction() with retry
1701: - Use HasTenantScope on shared-mode models
1702: - Register events in InventoryServiceProvider::boot()
1703: - Run tests before marking inventory tasks complete
1704: - Write feature tests for every new inventory workflow
1705: 
1706: ---
1707: 
1708: ## 25. Development Roadmap
1709: 
1710: ### Phase 1A: Foundation (Week 1-2)
1711: 
1712: | Task | Deliverable |
1713: |------|-------------|
1714: | Create module structure | pp/Modules/Inventory/ with all directories |
1715: | Create tenant migrations | inventory_ledger, inventory_balances, cost_layers, stock_reservations |
1716: | Create shared migrations | Same tables with 	enant_id for shared mode |
1717: | Implement Models | InventoryLedger, InventoryBalance, CostLayer, StockReservation |
1718: | Implement Enums | MovementTypeEnum, CostingMethodEnum, ReservationStatusEnum |
1719: | Implement StockMovementEngine | Core 
1720: ecord() method with reference generation |
1721: | Implement InventoryBalanceService | 
1722: ecalculate(), 
1723: ebuildFromLedger() |
1724: | Create InventoryServiceProvider | Singleton bindings, migration loading |
1725: | Create tests | StockMovementEngine unit tests, InventoryBalance feature tests |
1726: 
1727: ### Phase 1B: Costing & Reservations (Week 3-4)
1728: 
1729: | Task | Deliverable |
1730: |------|-------------|
1731: | Implement CostingEngine | Weighted average + FIFO strategies |
1732: | Implement CostLayer model + migration | Layer tracking per product+warehouse |
1733: | Implement ReservationEngine | Reserve, consume, expire, cancel |
1734: | Implement reservation migration | stock_reservations table |
1735: | Create ExpireReservations command | Cron: every minute |
1736: | Create tests | CostingEngine unit tests, ReservationEngine feature tests |
1737: 
1738: ### Phase 1C: Warehouse & Transfer (Week 5)
1739: 
1740: | Task | Deliverable |
1741: |------|-------------|
1742: | Move Warehouse model from Product | Backward-compatible alias |
1743: | Add bins/locations tables + models | inventory_bins |
1744: | Implement TransferEngine | Initiate, send, receive, cancel, partial receive |
1745: | Implement transfer tables + models | inventory_transfers, inventory_transfer_items |
1746: | Implement AuditService | Log all operations |
1747: | Implement AuditLog migration | inventory_audit_logs |
1748: | Create tests | TransferEngine feature tests, multi-warehouse tests |
1749: 
1750: ### Phase 1D: Batch, Serial & Alerts (Week 6)
1751: 
1752: | Task | Deliverable |
1753: |------|-------------|
1754: | Implement BatchService | Receive, deduct, FEFO/FIFO picking, quarantine |
1755: | Implement batch migration + model | inventory_batches |
1756: | Implement SerialNumberService | Register, validate, lifecycle |
1757: | Implement serial migration + model | serial_numbers |
1758: | Implement AlertEngine | Low stock, depletion, expiry evaluation |
1759: | Create expiry alerts command | Cron: every 6 hours |
1760: | Create tests | BatchService, SerialNumberService, AlertEngine |
1761: 
1762: ### Phase 1E: Frontend (Week 7-8)
1763: 
1764: | Task | Deliverable |
1765: |------|-------------|
1766: | Inventory Dashboard | Stock value, movements, alerts, fast/slow movers |
1767: | Stock Movement History | Filterable table with pagination |
1768: | Inventory Balance View | Per-product+warehouse stock snapshot |
1769: | Warehouse Management | CRUD for warehouses and bins |
1770: | Transfer Management | Create, send, receive transfers |
1771: | Batch Management | Create batches, view expiries |
1772: | Serial Number Management | Register serials, view lifecycle |
1773: | Stock Adjustments | Submit and approve adjustments |
1774: | Alerts Panel | Low stock, expiring, dead stock alerts |
1775: | Frontend API layer | React Query hooks for all inventory endpoints |
1776: 
1777: ### Phase 1F: Industry Pack Integration (Week 9)
1778: 
1779: | Task | Deliverable |
1780: |------|-------------|
1781: | Update all 15 Industry Packs | Add inventory feature flags |
1782: | Integrate Pharmacy Pack | batch_tracking, expiry_tracking, fefo_picking |
1783: | Integrate Restaurant Pack | recipe_management, recipe_consumption, waste_tracking |
1784: | Integrate Electronics Pack | serial_number_tracking, warranty_tracking |
1785: | Integrate Grocery Pack | weight_based_inventory, perishable_tracking |
1786: | Integrate Fashion Pack | variant_size_color inventory views |
1787: | Test all pack configurations | Verify feature flag gating |
1788: 
1789: ### Phase 2: Automation & Smart Features (Future)
1790: 
1791: | Feature | Complexity |
1792: |---------|-----------|
1793: | Auto Reorder Engine | Medium |
1794: | Purchase Suggestions | High |
1795: | Dead Stock Detection | Low |
1796: | Slow/Fast Moving Classification | Medium |
1797: | Seasonal Demand Prediction | High |
1798: | Automation Rules Engine (IF-THEN) | High |
1799: | Inventory Dashboard with Charts | Medium |
1800: | AI Assistant (chat-based) | Very High |
1801: 
1802: ---
1803: 
1804: ## 26. File Creation Templates
1805: 
1806: ### New Inventory Service
1807: 
1808: `php
1809: <?php
1810: 
1811: declare(strict_types=1);
1812: 
1813: namespace App\Modules\Inventory\Services;
1814: 
1815: use App\Modules\BusinessType\ValueObjects\TenantConfig;
1816: 
1817: class MyInventoryService
1818: {
1819:     public function __construct(
1820:         private TenantConfig ,
1821:     ) {}
1822: 
1823:     public function execute(): void
1824:     {
1825:         if (->config->hasFeature('batch_tracking')) {
1826:             // industry-specific behavior
1827:         }
1828:     }
1829: }
1830: `
1831: 
1832: ### New Inventory Model (Shared Mode)
1833: 
1834: `php
1835: <?php
1836: 
1837: declare(strict_types=1);
1838: 
1839: namespace App\Modules\Inventory\Models;
1840: 
1841: use App\Tenancy\Models\Concerns\HasTenantScope;
1842: use Illuminate\Database\Eloquent\Model;
1843: 
1844: class MyModel extends Model
1845: {
1846:     use HasTenantScope;
1847: 
1848:     protected  = [];
1849: 
1850:     public function casts(): array
1851:     {
1852:         return [
1853:             'metadata' => 'array',
1854:         ];
1855:     }
1856: }
1857: `
1858: 
1859: ### New Inventory Event
1860: 
1861: `php
1862: <?php
1863: 
1864: declare(strict_types=1);
1865: 
1866: namespace App\Modules\Inventory\Events;
1867: 
1868: use Illuminate\Foundation\Events\Dispatchable;
1869: 
1870: class MyInventoryEvent
1871: {
1872:     use Dispatchable;
1873: 
1874:     public function __construct(
1875:         public string ,
1876:         public string ,
1877:         public int ,
1878:     ) {}
1879: }
1880: `
1881: 
1882: ### New Inventory Listener
1883: 
1884: `php
1885: <?php
1886: 
1887: declare(strict_types=1);
1888: 
1889: namespace App\Modules\Inventory\Listeners;
1890: 
1891: use App\Modules\Inventory\Events\MyInventoryEvent;
1892: 
1893: class MyInventoryListener
1894: {
1895:     public function handle(MyInventoryEvent ): void
1896:     {
1897:         // Handle the event
1898:     }
1899: }
1900: `
1901: 
1902: ### New Tenant Migration
1903: 
1904: `php
1905: <?php
1906: 
1907: declare(strict_types=1);
1908: 
1909: use Illuminate\Database\Migrations\Migration;
1910: use Illuminate\Database\Schema\Blueprint;
1911: use Illuminate\Support\Facades\Schema;
1912: 
1913: return new class extends Migration
1914: {
1915:     public function up(): void
1916:     {
1917:         Schema::create('my_inventory_table', function (Blueprint ) {
1918:             ->id();
1919:             ->string('tenant_id', 36);
1920:             ->ulid('product_id');
1921:             ->timestamps();
1922:         });
1923:     }
1924: 
1925:     public function down(): void
1926:     {
1927:         Schema::dropIfExists('my_inventory_table');
1928:     }
1929: };
1930: `
1931: 
1932: ### New React Query Hook (Frontend)
1933: 
1934: `	ypescript
1935: // resources/js/modules/inventory/hooks/use-inventory.ts
1936: import { useQuery, useMutation } from '@tanstack/react-query';
1937: import axios from 'axios';
1938: import type { InventoryBalance, InventoryMovement } from '../types/inventory';
1939: 
1940: export function useInventoryBalances(warehouseId?: string) {
1941:     return useQuery({
1942:         queryKey: ['inventory', 'balances', warehouseId],
1943:         queryFn: async () => {
1944:             const { data } = await axios.get<InventoryBalance[]>(
1945:                 route('inventory.balances'),
1946:                 { params: { warehouse_id: warehouseId } }
1947:             );
1948:             return data;
1949:         },
1950:     });
1951: }
1952: 
1953: export function useMovementHistory(productId?: string) {
1954:     return useQuery({
1955:         queryKey: ['inventory', 'movements', productId],
1956:         queryFn: async () => {
1957:             const { data } = await axios.get<InventoryMovement[]>(
1958:                 route('inventory.ledger'),
1959:                 { params: { product_id: productId } }
1960:             );
1961:             return data;
1962:         },
1963:     });
1964: }
1965: 
1966: export function useCreateTransfer() {
1967:     return useMutation({
1968:         mutationFn: async (payload: TransferPayload) => {
1969:             const { data } = await axios.post(route('inventory.transfers.store'), payload);
1970:             return data;
1971:         },
1972:     });
1973: }
1974: `
1975: 
1976: ---

(End of file - total 1976 lines)
</content>
```

**Tool: read**

**Input:**
```json
{
  "filePath": "C:\\dev\\souda\\docs\\inventory-agent.md",
  "limit": 30
}
```

**Output:**
```
<path>C:\dev\souda\docs\inventory-agent.md</path>
<type>file</type>
<content>
1: # SOUDA — Inventory Module Architecture Guide
2: 
3: This file is for AI coding agents implementing the Inventory module. It defines the complete architecture, conventions, guardrails, patterns, interfaces, and implementation rules for the SOUDA Inventory Engine.
4: 
5: ---
6: 
7: ## 1. Project Scope & Inventory Philosophy
8: 
9: ### Vision
10: 
11: SOUDA's Inventory module is not a stock-tracking CRUD — it is an **Automated Inventory Engine**. Every business operation (purchase, sale, return, transfer, manufacturing, adjustment) generates domain events. The Inventory Engine consumes those events and produces immutable stock movements, cost layers, balance snapshots, reservations, and alerts — all without manual stock entry.
12: 
13: ### Target Audience
14: 
15: SME business owners in 15+ industries (retail, pharmacy, restaurant, grocery, electronics, fashion, etc.) who need real-time inventory visibility without operational overhead. The system must be intuitive enough that a shop manager can understand stock health in 10 seconds, yet powerful enough to handle batch tracking, expiry management, and multi-warehouse transfers.
16: 
17: ### Scope — Phase 1
18: 
19: | Feature | Included |
20: |---------|----------|
21: | Immutable inventory ledger | ✅ |
22: | Stock movement recording | ✅ |
23: | Inventory balance (real-time snapshot) | ✅ |
24: | Weighted average costing | ✅ |
25: | FIFO costing layers | ✅ |
26: | Stock reservations & allocation | ✅ |
27: | Multi-warehouse support | ✅ |
28: | Warehouse bin/shelf locations | ✅ |
29: | Batch/lot tracking | ✅ |
30: | Serial number tracking | ✅ |

(Showing lines 1-30 of 1976. Use offset=31 to continue.)
</content>
```

---

## Assistant (Build · DeepSeek V4 Flash Free · 12.6s)

_Thinking:_

The file is 1976 lines. I notice some corruption in the output - PowerShell's here-string seems to have removed the `$` variable signs and replaced backticks with regular quotes in the code blocks. This is because PowerShell interprets `$` variables and backticks as escape characters even in here-strings. Let me look more closely at the damage:

The file has:
- Variable names with `$` stripped (e.g., `$product` became empty, `$config` became empty)
- Backtick code blocks (` ``` `) are corrupted - they lost their backticks
- The content has some garbled characters where `$` was used

This is significant corruption. I need to rewrite the file properly, ensuring the `$` signs and backticks are preserved. Let me use a different approach - I'll write the file using PHP or use a different encoding method.

Actually, the issue is PowerShell's string interpolation. Even though I used a here-string with `@"... "@`, PowerShell still interprets `$` for variable expansion. To prevent this, I should escape `$` as `` `$ `` in PowerShell.

But that's really tedious for a 1976-line document. Let me think of a better approach.

Options:
1. Use `Set-Content` with `-NoNewline` and raw strings
2. Write via PHP 
3. Use Write tool with careful escaping
4. Split into smaller files

Actually, let me try using the Write tool but with the content built differently. The Write tool accepts a string parameter. The issue before was JSON parsing - it was too long. Let me try breaking it into sections.

Actually wait - looking at the output more carefully, I see the corruption is massive. Almost every code block has missing variable names. This document is not usable in its current state.

Let me delete it and recreate it properly. The best approach for writing a large file with `$` signs and backticks via PowerShell is to use `Set-Content` with a here-string that has all `$` escaped as `` `$ ``, or write it via a PHP script that doesn't have this issue.

Let me use PHP to write the file since PHP doesn't have PowerShell's variable expansion issues.

The file has corruption — PowerShell stripped all `$` signs from code blocks. Let me rewrite it properly using PHP to avoid the escaping issue.

**Tool: bash**

**Input:**
```json
{
  "command": "Remove-Item -LiteralPath C:\\dev\\souda\\docs\\inventory-agent.md -Force",
  "workdir": "C:\\dev\\souda"
}
```

**Output:**
```
(no output)
```

---

## Assistant (Build · DeepSeek V4 Flash Free · 97.5s)

_Thinking:_

Now let me write the file using PHP's file_put_contents which won't have PowerShell's variable expansion issues. I'll create a PHP script that writes the content.

**Tool: write**

**Input:**
```json
{
  "filePath": "C:\\dev\\souda\\docs\\inventory-agent.md",
  "content": "# SOUDA — Inventory Module Architecture Guide\n\nThis file is for AI coding agents implementing the Inventory module. It defines the complete architecture, conventions, guardrails, patterns, interfaces, and implementation rules for the SOUDA Inventory Engine.\n\n---\n\n## 1. Project Scope & Inventory Philosophy\n\n### Vision\n\nSOUDA's Inventory module is not a stock-tracking CRUD — it is an **Automated Inventory Engine**. Every business operation (purchase, sale, return, transfer, manufacturing, adjustment) generates domain events. The Inventory Engine consumes those events and produces immutable stock movements, cost layers, balance snapshots, reservations, and alerts — all without manual stock entry.\n\n### Target Audience\n\nSME business owners in 15+ industries (retail, pharmacy, restaurant, grocery, electronics, fashion, etc.) who need real-time inventory visibility without operational overhead. The system must be intuitive enough that a shop manager can understand stock health in 10 seconds, yet powerful enough to handle batch tracking, expiry management, and multi-warehouse transfers.\n\n### Scope — Phase 1\n\n| Feature | Included |\n|---------|----------|\n| Immutable inventory ledger | ✅ |\n| Stock movement recording | ✅ |\n| Inventory balance (real-time snapshot) | ✅ |\n| Weighted average costing | ✅ |\n| FIFO costing layers | ✅ |\n| Stock reservations & allocation | ✅ |\n| Multi-warehouse support | ✅ |\n| Warehouse bin/shelf locations | ✅ |\n| Batch/lot tracking | ✅ |\n| Serial number tracking | ✅ |\n| Expiry date management | ✅ |\n| Low stock & depletion alerts | ✅ |\n| Stock transfer between warehouses | ✅ |\n| Audit trail (who did what, when) | ✅ |\n| Frontend management pages | ✅ |\n| Industry Pack integration | ✅ |\n| Automation rules engine | Phase 2 |\n| Demand forecasting | Phase 2 |\n| Reorder suggestions | Phase 2 |\n\n### One Golden Rule\n\n> **No module ever writes to stock directly.** Every module (POS, Purchase, Sales, Manufacturing, Returns) publishes domain events. Only the Inventory Engine translates those events into stock movements and inventory balances. This is non-negotiable.\n\n---\n\n## 2. Core Architectural Principles\n\n### Principle 1: Ledger Immutability\n\nThe `inventory_ledger` table is INSERT-only. Once a stock movement is recorded, it is never updated or deleted. Corrections are made via offsetting entries (reversal movements), not row mutations. This guarantees a complete audit trail and prevents data loss.\n\n```php\n// WRONG — never update or delete ledger rows\nInventoryLedger::where('id', $id)->update(['quantity' => 0]);\n\n// RIGHT — reversal\nStockMovementEngine::record(\n    productId: $productId,\n    warehouseId: $warehouseId,\n    quantity: -$originalQuantity,\n    type: MovementTypeEnum::Adjustment,\n    reference: \"Reversal of ledger entry #{$originalId}\",\n    metadata: ['reverses' => $originalId],\n);\n```\n\n### Principle 2: Event-Driven Decoupling\n\nEvery inventory operation is triggered by a domain event. The Inventory Engine never exposes public write methods that bypass events. Modules communicate through events, not service calls.\n\n```\nSaleCompleted ──► InventoryEngine::onSaleCompleted()\n                      └──► StockMovementEngine::record()\n                            └──► InventoryBalanceService::recalculate()\n                                  └──► CostingEngine::apply()\n```\n\n### Principle 3: Balance = Derived State\n\n`InventoryBalance` is a computed snapshot derived from the ledger. It is updated synchronously on every write for read performance, but can always be rebuilt from scratch by replaying the ledger. This means:\n\n- `InventoryBalance` can be dropped and rebuilt without data loss\n- The ledger is the source of truth, not the balance\n- Reconciliation means replaying the ledger and comparing the computed balance\n\n### Principle 4: Costing Consistency\n\nEvery stock movement carries a `unit_cost` at the time of recording. The Costing Engine maintains cost layers (Weighted Average or FIFO) and recalculates the moving average cost on every inbound movement. Outbound movements consume cost from layers in FIFO order or at the current weighted average.\n\n### Principle 5: Industry-Neutral Core\n\nThe core inventory engine (ledger, balance, costing, reservations) is completely industry-agnostic. Industry-specific behaviors (FEFO for pharmacy, recipe deduction for restaurants, IMEI for electronics) are implemented via Industry Pack feature flags and plugin listeners — never via hardcoded checks in the engine.\n\n---\n\n## 3. Module Boundaries & Responsibilities\n\n```\n┌─────────────────────────────────────────────────────────────────┐\n│                     Inventory Module                              │\n│                                                                   │\n│  ┌─────────────────┐  ┌────────────────┐  ┌──────────────────┐  │\n│  │ InventoryEngine  │  │ StockMovement  │  │ InventoryBalance  │  │\n│  │ (Orchestrator)   │──│ Engine         │──│ Service           │  │\n│  └─────────┬───────┘  └────────────────┘  └──────────────────┘  │\n│            │                                                      │\n│  ┌─────────▼───────┐  ┌────────────────┐  ┌──────────────────┐  │\n│  │ CostingEngine    │  │ Reservation    │  │ BatchService      │  │\n│  │ (FIFO/WAVG)      │──│ Engine         │──│ (Lot/Expiry)      │  │\n│  └─────────────────┘  └────────────────┘  └──────────────────┘  │\n│                                                                   │\n│  ┌─────────────────┐  ┌────────────────┐  ┌──────────────────┐  │\n│  │ SerialNumber     │  │ AuditService   │  │ AlertEngine       │  │\n│  │ Service          │──│                │──│ (Thresholds)      │  │\n│  └─────────────────┘  └────────────────┘  └──────────────────┘  │\n│                                                                   │\n│  ┌─────────────────┐  ┌────────────────┐                         │\n│  │ WarehouseService │  │ TransferEngine │                         │\n│  └─────────────────┘  └────────────────┘                         │\n└─────────────────────────────────────────────────────────────────┘\n```\n\n### Module Responsibilities\n\n| Component | Responsibility |\n|-----------|---------------|\n| `InventoryEngine` | Single public entry point. Subscribes to domain events, validates business rules, delegates to sub-engines |\n| `StockMovementEngine` | Records immutable ledger entries. Generates movement reference numbers |\n| `InventoryBalanceService` | Maintains real-time balance snapshots. Rebuilds from ledger on demand |\n| `CostingEngine` | Manages cost layers. Weighted average recalculation. FIFO layer consumption |\n| `ReservationEngine` | Creates, consumes, expires, and cancels stock reservations |\n| `BatchService` | Batch/lot tracking. Expiry date management. FEFO picking order |\n| `SerialNumberService` | Serial/IMEI tracking per unit. Status lifecycle: available to sold to returned |\n| `WarehouseService` | Warehouse and bin/shelf location management |\n| `TransferEngine` | Orchestrates inter-warehouse stock transfers with in-transit status |\n| `AuditService` | Logs all inventory operations with before/after snapshots |\n| `AlertEngine` | Evaluates thresholds (low stock, expiry, overstock, dead stock). Dispatches alerts |\n\n---\n\n## 4. Inventory Engine Architecture\n\n### InventoryEngine (Orchestrator)\n\nThe `InventoryEngine` is the single public facade for all inventory write operations. It receives domain events, validates them against business rules, and coordinates the sub-engines.\n\n```php\nclass InventoryEngine\n{\n    public function __construct(\n        private StockMovementEngine $movementEngine,\n        private InventoryBalanceService $balanceService,\n        private CostingEngine $costingEngine,\n        private ReservationEngine $reservationEngine,\n        private BatchService $batchService,\n        private SerialNumberService $serialService,\n        private TransferEngine $transferEngine,\n        private AuditService $auditService,\n        private AlertEngine $alertEngine,\n        private TenantConfig $config,\n    ) {}\n\n    // These are the ONLY public write methods on this class:\n    public function handleSaleCompleted(SaleCompleted $event): void;\n    public function handlePurchaseReceived(PurchaseReceived $event): void;\n    public function handleReturnApproved(ReturnApproved $event): void;\n    public function handleAdjustmentApproved(AdjustmentApproved $event): void;\n    public function handleTransferInitiated(TransferInitiated $event): void;\n    public function handleTransferCompleted(TransferCompleted $event): void;\n    public function handleProductionCompleted(ProductionCompleted $event): void;\n    public function handleRecipeConsumed(RecipeConsumed $event): void;\n    public function handleReservationCreated(ReservationCreated $event): void;\n    public function handleReservationCancelled(ReservationCancelled $event): void;\n}\n```\n\n### Engine Workflow (Example: Sale)\n\n```\n1. SaleCompleted event received\n2. InventoryEngine::handleSaleCompleted()\n3.   Validate: product exists, warehouse exists, quantity is positive\n4.   If batches tracked: resolve which batches to deduct (FIFO/FEFO)\n5.   If serial tracked: validate each serial number is available\n6.   ReserveEngine::consumeReservation(orderReference) if pre-reserved\n7.   StockMovementEngine::record(sale, -qty, unitCost, reference)\n8.   InventoryBalanceService::recalculate(productId, warehouseId)\n9.   CostingEngine::consumeLayers(productId, qty, costingMethod)\n10.  BatchService::deduct(productId, batchId, qty) if batch tracked\n11.  SerialNumberService::markAsSold(serialNumbers) if serial tracked\n12.  AuditService::log('sale', before, after, actor)\n13.  AlertEngine::evaluate(productId, warehouseId)\n14.  Dispatch InventoryBalanceUpdated, StockDepleted (if applicable)\n```\n\n---\n\n## 5. Stock Movement Engine\n\n### Movement Types\n\n```php\nenum MovementTypeEnum: string\n{\n    case PurchaseReceipt = 'purchase_receipt';\n    case SaleDeduction = 'sale_deduction';\n    case ReturnRestock = 'return_restock';\n    case TransferOut = 'transfer_out';\n    case TransferIn = 'transfer_in';\n    case AdjustmentIncrease = 'adjustment_increase';\n    case AdjustmentDecrease = 'adjustment_decrease';\n    case ProductionOutput = 'production_output';\n    case RecipeConsumption = 'recipe_consumption';\n    case ReservationDeduction = 'reservation_deduction';\n    case ReservationRelease = 'reservation_release';\n    case Reversal = 'reversal';\n    case InitialStock = 'initial_stock';\n}\n```\n\n### StockMovementEngine\n\n```php\nclass StockMovementEngine\n{\n    public function record(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n        MovementTypeEnum $type,\n        string $reference,\n        ?int $unitCost = null,\n        ?string $batchId = null,\n        ?array $serialNumbers = null,\n        ?string $description = null,\n        array $metadata = [],\n    ): InventoryLedger;\n\n    public function findById(string $id): ?InventoryLedger;\n    public function findByReference(string $reference): Collection;\n    public function findByProduct(string $productId, array $filters = []): Collection;\n}\n```\n\n### Reference Number Format\n\n```\nMOV-{YYYYMMDD}-{XXXX}\n    Example: MOV-20260715-0042\n\nPrefixes by type:\n  PUR-{date}-{seq}   Purchase Receipt\n  SAL-{date}-{seq}   Sale Deduction\n  RET-{date}-{seq}   Return Restock\n  TRF-{date}-{seq}   Transfer\n  ADJ-{date}-{seq}   Adjustment\n  PROD-{date}-{seq}  Production\n  REC-{date}-{seq}   Recipe Consumption\n```\n\n---\n\n## 6. Inventory Ledger Design\n\n### Table: `inventory_ledger`\n\n```php\nSchema::create('inventory_ledger', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->unsignedBigInteger('warehouse_id');\n    $table->unsignedBigInteger('bin_id')->nullable();\n    $table->integer('quantity');\n    $table->integer('quantity_before');\n    $table->integer('quantity_after');\n    $table->string('movement_type', 30);\n    $table->string('reference', 100);\n    $table->string('reference_type', 30);\n    $table->unsignedBigInteger('batch_id')->nullable();\n    $table->json('serial_numbers')->nullable();\n    $table->bigInteger('unit_cost')->nullable();\n    $table->bigInteger('total_cost')->nullable();\n    $table->text('description')->nullable();\n    $table->json('metadata')->nullable();\n    $table->string('created_by', 36)->nullable();\n    $table->timestamp('created_at')->useCurrent();\n\n    $table->index(['product_id', 'warehouse_id', 'created_at']);\n    $table->index(['reference', 'reference_type']);\n    $table->index(['batch_id']);\n    $table->index(['tenant_id', 'created_at']);\n});\n```\n\n### Table Rules\n\n- No `updated_at` — ledger entries are immutable\n- No `deleted_at` — soft deletes defeat the audit purpose\n- `quantity_before` and `quantity_after` as integrity checkpoints\n- `unit_cost` is always in cents (integer) to avoid floating-point drift\n- `total_cost = unit_cost * |quantity|` — pre-calculated for reporting\n- `metadata` JSON column for extensibility without schema changes\n\n---\n\n## 7. Inventory Balance\n\n### Table: `inventory_balances`\n\n```php\nSchema::create('inventory_balances', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->unsignedBigInteger('warehouse_id');\n    $table->integer('quantity')->default(0);\n    $table->integer('reserved_quantity')->default(0);\n    $table->integer('available_quantity')->default(0);\n    $table->bigInteger('average_unit_cost')->default(0);\n    $table->bigInteger('total_stock_value')->default(0);\n    $table->unsignedBigInteger('lock_version')->default(0);\n    $table->timestamp('last_movement_at')->nullable();\n    $table->timestamps();\n\n    $table->unique(['product_id', 'variant_id', 'warehouse_id']);\n    $table->index(['warehouse_id', 'quantity']);\n});\n```\n\n### Balance Calculation\n\n```php\nclass InventoryBalanceService\n{\n    public function recalculate(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n    ): void;\n\n    public function rebuildFromLedger(\n        ?string $productId = null,\n        ?string $warehouseId = null,\n    ): void;\n\n    public function recalculateBatch(array $productWarehousePairs): void;\n}\n```\n\n### Optimistic Locking\n\n`lock_version` uses Laravel's `HasOptimisticLocking` trait. On concurrent writes, the first request succeeds and the second receives a `ModelNotFoundException` — the caller must retry.\n\n```php\ntry {\n    $balance = InventoryBalance::where([\n        'product_id' => $productId,\n        'warehouse_id' => $warehouseId,\n    ])->firstOrFail();\n\n    $balance->decrement('quantity', $quantity);\n} catch (ModelNotFoundException) {\n    throw new ConcurrentInventoryUpdateException($productId, $warehouseId);\n}\n```\n\n---\n\n## 8. Costing Strategies\n\n### CostLayer Model\n\n```php\nSchema::create('cost_layers', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->unsignedBigInteger('warehouse_id');\n    $table->bigInteger('unit_cost');\n    $table->integer('quantity_remaining');\n    $table->integer('quantity_original');\n    $table->string('costing_method', 20);\n    $table->unsignedBigInteger('ledger_entry_id');\n    $table->timestamp('created_at')->useCurrent();\n\n    $table->index(['product_id', 'warehouse_id', 'costing_method']);\n});\n```\n\n### Weighted Average (Default)\n\n```php\nclass WeightedAverageCosting\n{\n    public function applyInbound(CostLayer $layer): void\n    {\n        $currentBalance = InventoryBalance::where([\n            'product_id' => $layer->product_id,\n            'warehouse_id' => $layer->warehouse_id,\n        ])->first();\n\n        $totalCost = ($currentBalance->quantity * $currentBalance->average_unit_cost)\n                   + ($layer->quantity_remaining * $layer->unit_cost);\n        $totalQty = $currentBalance->quantity + $layer->quantity_remaining;\n        $newAvg = $totalQty > 0 ? (int) round($totalCost / $totalQty) : 0;\n\n        $currentBalance->update([\n            'average_unit_cost' => $newAvg,\n            'total_stock_value' => $totalQty * $newAvg,\n        ]);\n    }\n\n    public function consumeOutbound(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n    ): array {\n        $balance = InventoryBalance::where([\n            'product_id' => $productId,\n            'warehouse_id' => $warehouseId,\n        ])->firstOrFail();\n\n        $unitCost = $balance->average_unit_cost;\n        return [\n            'total_cost' => $quantity * $unitCost,\n            'unit_cost' => $unitCost,\n        ];\n    }\n}\n```\n\n### FIFO\n\n```php\nclass FifoCosting\n{\n    public function consumeOutbound(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n    ): array {\n        $layers = CostLayer::where([\n            'product_id' => $productId,\n            'warehouse_id' => $warehouseId,\n            'costing_method' => 'fifo',\n        ])\n        ->where('quantity_remaining', '>', 0)\n        ->orderBy('created_at')\n        ->get();\n\n        $totalCost = 0;\n        $remaining = $quantity;\n\n        foreach ($layers as $layer) {\n            $consume = min($remaining, $layer->quantity_remaining);\n            $layer->decrement('quantity_remaining', $consume);\n            $totalCost += $consume * $layer->unit_cost;\n            $remaining -= $consume;\n            if ($remaining <= 0) break;\n        }\n\n        return [\n            'total_cost' => $totalCost,\n            'unit_cost' => $quantity > 0 ? (int) round($totalCost / $quantity) : 0,\n            'layers_consumed' => $layers->count(),\n        ];\n    }\n}\n```\n\n### Costing Method Selection\n\n```php\nclass Product extends Model\n{\n    public function costingMethod(): string\n    {\n        return $this->costing_method ?? config('inventory.default_costing_method', 'weighted_average');\n    }\n}\n\nclass CostingEngine\n{\n    public function __construct(\n        private WeightedAverageCosting $weightedAverage,\n        private FifoCosting $fifo,\n    ) {}\n\n    public function consume(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n    ): CostResult {\n        $product = Product::findOrFail($productId);\n        $strategy = $product->costingMethod() === 'fifo' ? $this->fifo : $this->weightedAverage;\n        return $strategy->consumeOutbound($productId, $variantId, $warehouseId, $quantity);\n    }\n}\n```\n\n---\n\n## 9. Reservation & Allocation Engine\n\n### Reservation Lifecycle\n\n```\nCreated (active) ──► Consumed (stock deducted)\n    │                      │\n    ▼                      ▼\nExpired (auto-release)   Cancelled (manual)\n```\n\n### Table: `stock_reservations`\n\n```php\nSchema::create('stock_reservations', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->unsignedBigInteger('warehouse_id');\n    $table->integer('quantity');\n    $table->string('status', 20);\n    $table->string('reference', 100);\n    $table->string('reference_type', 30);\n    $table->timestamp('expires_at')->nullable();\n    $table->timestamp('consumed_at')->nullable();\n    $table->string('created_by', 36)->nullable();\n    $table->timestamps();\n\n    $table->index(['status', 'expires_at']);\n    $table->index(['product_id', 'warehouse_id', 'status']);\n    $table->index(['reference', 'reference_type']);\n});\n```\n\n### ReservationEngine\n\n```php\nclass ReservationEngine\n{\n    public function reserve(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n        string $reference,\n        ?int $ttlMinutes = 30,\n    ): StockReservation;\n\n    public function consume(string $reference): void;\n\n    public function release(string $reference): void;\n\n    public function cancel(string $reference): void;\n\n    public function expirePending(): int;\n\n    public function available(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n    ): int;\n\n    public function isAvailable(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n    ): bool;\n}\n```\n\n### Allocation Rules\n\n| Scenario | Rule |\n|----------|------|\n| POS sale with cart | Reserve on add-to-cart, expire in 30 min if not checked out |\n| Online order | Reserve on creation, consume on payment |\n| Backorder | Reserve when stock arrives (requires reorder engine) |\n| Manual pick list | Reserve on pick list creation, consume on ship |\n| Multi-warehouse | Reserve from nearest/default warehouse, fallback to others |\n\n---\n\n## 10. Warehouse, Location & Transfer Architecture\n\n### Warehouse Hierarchy\n\n```\nWarehouse (physical location)\n  └── Zone (e.g., Dry Storage, Cold Storage)\n       └── Aisle (e.g., A, B, C)\n            └── Rack (e.g., R01, R02)\n                 └── Shelf (e.g., SH01, SH02)\n                      └── Bin (e.g., A-R01-SH01-BIN01)\n```\n\n### Warehouse Table\n\n```php\nSchema::create('warehouses', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->string('name');\n    $table->string('slug')->unique();\n    $table->string('code', 20)->nullable();\n    $table->string('type', 20)->default('physical');\n    $table->boolean('is_default')->default(false);\n    $table->string('address_line1')->nullable();\n    $table->string('city')->nullable();\n    $table->string('country', 2)->nullable();\n    $table->string('contact_name')->nullable();\n    $table->string('contact_email')->nullable();\n    $table->string('contact_phone')->nullable();\n    $table->json('metadata')->nullable();\n    $table->boolean('is_active')->default(true);\n    $table->timestamps();\n    $table->softDeletes();\n\n    $table->unique(['tenant_id', 'slug']);\n});\n```\n\n### Bin Locations\n\n```php\nSchema::create('inventory_bins', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->unsignedBigInteger('warehouse_id');\n    $table->string('code', 50);\n    $table->string('zone')->nullable();\n    $table->string('aisle')->nullable();\n    $table->string('rack')->nullable();\n    $table->string('shelf')->nullable();\n    $table->boolean('is_pickable')->default(true);\n    $table->integer('max_weight_kg')->nullable();\n    $table->json('metadata')->nullable();\n    $table->timestamps();\n\n    $table->unique(['warehouse_id', 'code']);\n    $table->index(['warehouse_id', 'zone']);\n});\n```\n\n### Transfer Engine\n\n```php\nclass TransferEngine\n{\n    public function initiate(\n        string $fromWarehouseId,\n        string $toWarehouseId,\n        array $items,\n        ?string $reference = null,\n        ?string $description = null,\n    ): TransferOrder;\n\n    public function send(string $transferId): void;\n    public function receive(string $transferId): void;\n    public function cancel(string $transferId): void;\n\n    public function partialReceive(\n        string $transferId,\n        array $receivedItems,\n    ): void;\n}\n```\n\n### Transfer Flow\n\n```\n1. Initiate  Source items reserved, TransferOrder status = draft\n2. Send  Source stock deducted (TransferOut movement), status = in_transit\n3. Receive  Destination stock added (TransferIn movement), status = completed\n4. Cancel  Source reservation released, status = cancelled\n\nFor partial receives:\n  - Only received quantities are transferred\n  - Unreceived quantities stay in_transit\n```\n\n### Transfer Tables\n\n```php\nSchema::create('inventory_transfers', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->string('reference', 50)->unique();\n    $table->unsignedBigInteger('from_warehouse_id');\n    $table->unsignedBigInteger('to_warehouse_id');\n    $table->string('status', 20);\n    $table->text('description')->nullable();\n    $table->string('created_by', 36)->nullable();\n    $table->timestamp('sent_at')->nullable();\n    $table->timestamp('received_at')->nullable();\n    $table->timestamps();\n});\n\nSchema::create('inventory_transfer_items', function (Blueprint $table) {\n    $table->id();\n    $table->unsignedBigInteger('transfer_id');\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->integer('quantity');\n    $table->integer('quantity_received')->default(0);\n    $table->timestamps();\n});\n```\n\n---\n\n## 11. Batch, Expiry & Serial Number Management\n\n### Batch Table\n\n```php\nSchema::create('inventory_batches', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->string('batch_number', 100);\n    $table->string('supplier_batch', 100)->nullable();\n    $table->date('manufacturing_date')->nullable();\n    $table->date('expiry_date')->nullable();\n    $table->date('best_before')->nullable();\n    $table->integer('initial_quantity');\n    $table->integer('remaining_quantity');\n    $table->bigInteger('unit_cost');\n    $table->string('status', 20);\n    $table->timestamps();\n\n    $table->unique(['product_id', 'batch_number']);\n    $table->index(['expiry_date', 'status']);\n    $table->index(['warehouse_id']);\n});\n```\n\n### FEFO Picking\n\n```php\nclass BatchService\n{\n    public function pickBatches(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        int $quantity,\n        string $method = 'fefo',\n    ): Collection {\n        $query = InventoryBatch::where([\n            'product_id' => $productId,\n            'warehouse_id' => $warehouseId,\n        ])->where('remaining_quantity', '>', 0)\n          ->whereIn('status', ['active']);\n\n        if ($method === 'fefo') {\n            $query->where('expiry_date', '>=', now())\n                  ->orderBy('expiry_date');\n        } elseif ($method === 'fifo') {\n            $query->orderBy('manufacturing_date')->orderBy('id');\n        } else {\n            $query->orderByDesc('id');\n        }\n\n        return $this->allocateFromBatches($query->get(), $quantity);\n    }\n\n    public function receive(\n        string $productId,\n        ?string $variantId,\n        string $warehouseId,\n        string $batchNumber,\n        int $quantity,\n        ?string $supplierBatch = null,\n        ?Carbon $manufacturingDate = null,\n        ?Carbon $expiryDate = null,\n        ?Carbon $bestBefore = null,\n        int $unitCost = 0,\n    ): InventoryBatch;\n\n    public function deduct(string $batchId, int $quantity): void;\n    public function quarantine(string $batchId, ?string $reason = null): void;\n    public function findExpiring(Carbon $withinDays): Collection;\n}\n```\n\n### Serial Number Table\n\n```php\nSchema::create('serial_numbers', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->string('serial_number', 200);\n    $table->string('status', 20);\n    $table->unsignedBigInteger('warehouse_id')->nullable();\n    $table->unsignedBigInteger('batch_id')->nullable();\n    $table->string('order_reference')->nullable();\n    $table->timestamp('sold_at')->nullable();\n    $table->timestamp('warranty_expires_at')->nullable();\n    $table->text('notes')->nullable();\n    $table->timestamps();\n\n    $table->unique(['product_id', 'serial_number']);\n    $table->index(['status', 'warehouse_id']);\n});\n```\n\n### SerialNumberService\n\n```php\nclass SerialNumberService\n{\n    public function register(\n        string $productId,\n        string $serialNumber,\n        ?string $warehouseId = null,\n        ?string $batchId = null,\n    ): SerialNumber;\n\n    public function validate(string $serialNumber, string $productId): bool;\n    public function markAsSold(string $serialNumber, string $orderReference): void;\n    public function markAsReturned(string $serialNumber): void;\n\n    public function registerBatch(\n        string $productId,\n        array $serialNumbers,\n        ?string $warehouseId = null,\n        ?string $batchId = null,\n    ): Collection;\n\n    public function findByStatus(string $status, ?string $productId = null): Collection;\n    public function warrantyStatus(string $serialNumber): string;\n}\n```\n\n---\n\n## 12. Manufacturing & Recipe Consumption\n\n### Recipe / Bill of Materials\n\n```php\nSchema::create('recipes', function (Blueprint $table) {\n    $table->id();\n    $table->string('tenant_id', 36);\n    $table->ulid('product_id');\n    $table->ulid('variant_id')->nullable();\n    $table->string('name');\n    $table->text('instructions')->nullable();\n    $table->integer('yield_quantity')->default(1);\n    $table->string('waste_percentage')->default(0);\n    $table->boolean('is_active')->default(true);\n    $table->timestamps();\n    $table->softDeletes();\n});\n\nSchema::create('recipe_ingredients', function (Blueprint $table) {\n    $table->id();\n    $table->unsignedBigInteger('recipe_id');\n    $table->ulid('ingredient_product_id');\n    $table->ulid('ingredient_variant_id')->nullable();\n    $table->decimal('quantity', 12, 4);\n    $table->string('unit', 20);\n    $table->unsignedBigInteger('warehouse_id');\n    $table->integer('sort_order')->default(0);\n    $table->timestamps();\n});\n```\n\n### Production Flow\n\n```\nProductionCompleted event\n  InventoryEngine::handleProductionCompleted()\n    For each ingredient in recipe:\n      StockMovementEngine::record(ingredient, -qty, RecipeConsumption)\n      InventoryBalanceService::recalculate(ingredient)\n      BatchService::deduct(ingredientBatch, qty)\n    StockMovementEngine::record(finishedGood, +yield, ProductionOutput)\n    InventoryBalanceService::recalculate(finishedGood)\n    CostingEngine::applyInbound(finishedGood, totalIngredientCost)\n    AuditService::log('production', recipe, ingredients, yield)\n```\n\n---\n\n## 13. Automation & Rule Engine (Phase 2)\n\n### Rule Engine Design\n\n```php\ninterface AutomationRule\n{\n    public function evaluate(AutomationContext $context): bool;\n    public function execute(AutomationContext $context): void;\n    public function name(): string;\n    public function description(): string;\n}\n\nclass ReorderRule implements AutomationRule\n{\n    public function evaluate(AutomationContext $context): bool\n    {\n        return $context->balance->quantity <= $context->product->reorder_point;\n    }\n\n    public function execute(AutomationContext $context): void\n    {\n        PurchaseOrder::create([\n            'product_id' => $context->product->id,\n            'quantity' => $context->product->reorder_quantity,\n            'status' => 'draft',\n        ]);\n    }\n}\n```\n\n### Available Rule Triggers\n\n| Trigger | Evaluation | Actions |\n|---------|-----------|---------|\n| On balance updated | Check: below reorder point | Create PO draft, notify manager |\n| On batch created | Check: expiry within threshold | Send expiry alert |\n| On stock depleted | Check: is backorder product | Notify supplier |\n| On transfer received | Check: auto-receive | Mark transfer completed |\n| On daily schedule | Check: dead stock (90d no movement) | Send dead stock report |\n\n---\n\n## 14. Event-Driven Workflow\n\n### Event Flow Diagram\n\n```\n┌─────────────┐     ┌──────────────────┐     ┌──────────────┐\n│ POS Module   │     │ Purchase Module  │     │ Manufacturing│\n│ SaleCompleted│     │ PurchaseReceived │     │ ProdComplete │\n└──────┬──────┘     └────────┬─────────┘     └──────┬───────┘\n       │                     │                      │\n       └──────────┬──────────┴──────────┬───────────┘\n                  │                     │\n         ┌────────▼────────┐   ┌────────▼────────┐\n         │ InventoryEngine  │   │ InventoryEngine  │\n         │ (Listener)       │   │ (Listener)       │\n         └────────┬────────┘   └────────┬────────┘\n                  │                     │\n         ┌────────▼─────────────────────▼─────────┐\n         │           StockMovementEngine           │\n         │           (record ledger entry)         │\n         └────────────────┬───────────────────────┘\n                          │\n         ┌────────────────▼───────────────────────┐\n         │      Domain Events Dispatched           │\n         │  ┌──────────────┐  ┌────────────────┐  │\n         │  │ StockMovement │  │ InventoryBalance│  │\n         │  │ Created       │  │ Updated         │  │\n         │  └──────────────┘  └────────────────┘  │\n         │  ┌──────────────┐  ┌────────────────┐  │\n         │  │ StockDepleted│  │ LowStockAlert   │  │\n         │  └──────────────┘  └────────────────┘  │\n         └────────────────┬───────────────────────┘\n                          │\n         ┌────────────────▼───────────────────────┐\n         │            External Listeners           │\n         │  Notifications, Search Indexing,        │\n         │  Reorder Engine, Audit Log, Dashboard   │\n         └─────────────────────────────────────────┘\n```\n\n---\n\n## 15. Domain Events & Listeners\n\n### Inventory Events\n\n| Event | Payload | Dispatched By | Listeners |\n|-------|---------|--------------|-----------|\n| `StockMovementCreated` | product_id, warehouse_id, type, quantity, reference | StockMovementEngine | UpdateProductStockCache, NotifyInventoryUpdate |\n| `InventoryBalanceUpdated` | product_id, warehouse_id, qty_before, qty_after | InventoryBalanceService | CheckReorderThreshold, UpdateProductSearchIndex |\n| `StockDepleted` | product_id, warehouse_id, variant_id | InventoryBalanceService | MarkProductUnavailable, SendStockDepletedNotification |\n| `LowStockAlert` | product_id, warehouse_id, current_qty, threshold | AlertEngine | SendLowStockNotification, DashboardAlert |\n| `StockReserved` | product_id, warehouse_id, quantity, reference | ReservationEngine | TrackReservation |\n| `ReservationConsumed` | reference, product_id, quantity | ReservationEngine | UpdateReservationStatus |\n| `ReservationExpired` | reservation_id, product_id, quantity | ReservationEngine (cron) | ReleaseReservedStock, NotifyUser |\n| `TransferInitiated` | transfer_id, from, to, items | TransferEngine | SendTransferNotification |\n| `TransferCompleted` | transfer_id, from, to, items, received_at | TransferEngine | UpdateInventoryDashboard |\n| `TransferCancelled` | transfer_id, reason | TransferEngine | ReleaseReservedStock |\n| `BatchExpiring` | batch_id, product_id, expiry_date, days_remaining | BatchService (cron) | SendExpiryAlert, DashboardAlert |\n| `BatchDepleted` | batch_id, product_id | StockMovementEngine | UpdateBatchStatus |\n| `BatchQuarantined` | batch_id, reason | BatchService | NotifyQualityControl |\n| `CostUpdated` | product_id, warehouse_id, old_avg, new_avg | CostingEngine | UpdateProductCostCache |\n| `ProductionCompleted` | product_id, recipe_id, yield, ingredient_costs | InventoryEngine | UpdateInventoryDashboard |\n| `SerialNumberSold` | serial_number, product_id, order_ref | SerialNumberService | UpdateWarrantyStatus |\n| `AdjustmentApproved` | product_id, warehouse_id, qty, reason, approved_by | InventoryEngine | AuditLog, NotifyManager |\n\n### Event Listener Registration\n\n```php\n// In InventoryServiceProvider::boot()\nEvent::listen(SaleCompleted::class, [InventoryEngine::class, 'handleSaleCompleted']);\nEvent::listen(PurchaseReceived::class, [InventoryEngine::class, 'handlePurchaseReceived']);\nEvent::listen(StockMovementCreated::class, UpdateProductStockCache::class);\nEvent::listen(LowStockAlert::class, SendLowStockNotification::class);\nEvent::listen(StockDepleted::class, MarkProductUnavailable::class);\nEvent::listen(InventoryBalanceUpdated::class, CheckReorderThreshold::class);\nEvent::listen(TransferInitiated::class, SendTransferNotification::class);\nEvent::listen(BatchExpiring::class, SendExpiryAlert::class);\n```\n\n### Cron Jobs\n\n| Frequency | Command | Purpose |\n|-----------|---------|---------|\n| Every minute | `inventory:expire-reservations` | Release expired stock reservations |\n| Every 6 hours | `inventory:expiry-alerts` | Find batches expiring within threshold, dispatch BatchExpiring |\n| Daily | `inventory:dead-stock-report` | Find products with no movement in 90 days |\n| Daily | `inventory:reconcile` | Compare ledger totals vs balance snapshots |\n\n---\n\n## 16. Database Conventions\n\n### Naming Conventions\n\n| Aspect | Convention | Example |\n|--------|-----------|---------|\n| Tables | snake_case, plural | `inventory_ledger`, `stock_reservations` |\n| Columns | snake_case | `product_id`, `average_unit_cost` |\n| Tenant FK | `tenant_id` (string, 36) | Shared-mode isolation |\n| Product FK | `product_id` (ulid) | References Product ULID |\n| Variant FK | `variant_id` (ulid, nullable) | References Variant ULID |\n| Warehouse FK | `warehouse_id` (unsignedBigInteger) | References warehouses.id |\n| Money | `bigInteger` in cents | `unit_cost`, `total_cost` |\n| Quantity | `integer` | Whole units; decimal(12,4) for weight |\n| Status | `string(20)` with enum backing | `MovementTypeEnum`, `ReservationStatusEnum` |\n| Timestamps | `created_at`, `updated_at` | Ledger has only `created_at` |\n| Soft deletes | `deleted_at` for reference data | warehouses, batches (not ledger) |\n\n### Migration Rules\n\n- All inventory tables run on tenant DB (dedicated) or shared DB (shared mode)\n- Migrations live in `app/Modules/Inventory/Database/Migrations/Tenant/`\n- Registered via `InventoryServiceProvider::boot()` `loadMigrationsFrom()`\n- All tables include `$table->string('tenant_id', 36)` for shared-mode scoping\n- Anonymous migration classes — no typed properties (PHP 8.4 compatibility)\n- Always add `->orderBy('id')` before `->each()` in migrations (Laravel 12 req)\n\n### Indexing Strategy\n\n| Table | Critical Indexes | Rationale |\n|-------|-----------------|-----------|\n| `inventory_ledger` | (product_id, warehouse_id, created_at), (reference, reference_type), (batch_id) | Movement history queries, reference lookup, batch audit |\n| `inventory_balances` | (product_id, variant_id, warehouse_id) UNIQUE | UPSERT lookup, fast single-row access |\n| `cost_layers` | (product_id, warehouse_id, costing_method), (created_at) | FIFO ordering, costing method queries |\n| `stock_reservations` | (status, expires_at), (product_id, warehouse_id, status) | Expiry cron, availability checks |\n| `inventory_batches` | (expiry_date, status), (product_id, warehouse_id) | FEFO picking, expiry alerts |\n| `serial_numbers` | (product_id, serial_number) UNIQUE, (status, warehouse_id) | Serial lookup, availability queries |\n| `inventory_transfers` | (from_warehouse_id, status), (reference) | Transfer tracking, reference lookup |\n\n---\n\n## 17. Model Relationships\n\n```php\nclass InventoryLedger extends Model\n{\n    public function product(): BelongsTo { ... }\n    public function variant(): BelongsTo { ... }\n    public function warehouse(): BelongsTo { ... }\n    public function batch(): BelongsTo { ... }\n    public function bin(): BelongsTo { ... }\n}\n\nclass InventoryBalance extends Model\n{\n    public function product(): BelongsTo { ... }\n    public function variant(): BelongsTo { ... }\n    public function warehouse(): BelongsTo { ... }\n}\n\nclass CostLayer extends Model\n{\n    public function product(): BelongsTo { ... }\n    public function warehouse(): BelongsTo { ... }\n    public function ledgerEntry(): BelongsTo { ... }\n}\n\nclass StockReservation extends Model\n{\n    public function product(): BelongsTo { ... }\n    public function variant(): BelongsTo { ... }\n    public function warehouse(): BelongsTo { ... }\n}\n\nclass InventoryBatch extends Model\n{\n    public function product(): BelongsTo { ... }\n    public function warehouse(): BelongsTo { ... }\n    public function ledgerEntries(): HasMany { ... }\n    public function serialNumbers(): HasMany { ... }\n}\n\nclass SerialNumber extends Model\n{\n    public function product(): BelongsTo { ... }\n    public function warehouse(): BelongsTo { ... }\n    public function batch(): BelongsTo { ... }\n}\n\nclass Warehouse extends Model\n{\n    public function bins(): HasMany { ... }\n    public function balances(): HasMany { ... }\n    public function outboundTransfers(): HasMany { ... }\n    public function inboundTransfers(): HasMany { ... }\n}\n\nclass InventoryBin extends Model\n{\n    public function warehouse(): BelongsTo { ... }\n    public function ledgerEntries(): HasMany { ... }\n}\n\nclass Recipe extends Model\n{\n    public function product(): BelongsTo { ... }\n    public function ingredients(): HasMany { ... }\n}\n\nclass RecipeIngredient extends Model\n{\n    public function recipe(): BelongsTo { ... }\n    public function ingredientProduct(): BelongsTo { ... }\n}\n```\n\n### Product Module Cross-References\n\nThe Inventory module references these models from the Product module by ULID only:\n\n- `App\\Modules\\Product\\Models\\Product`\n- `App\\Modules\\Product\\Models\\Variant`\n\nInventory never accesses Product properties or methods directly. When inventory needs product data (costing method, reorder point), it uses the Product module's published contracts via dependency injection.\n\n---\n\n## 18. Industry Pack Extension Rules\n\n### Feature Flags for Inventory\n\nEach Industry Pack defines feature flags in its `featureFlags()` method. The Inventory Engine checks these flags at runtime.\n\n```php\n// PharmacyPack\npublic function featureFlags(): array\n{\n    return [\n        'batch_tracking' => true,\n        'expiry_tracking' => true,\n        'fefo_picking' => true,\n        'serial_number_tracking' => false,\n        'recipe_management' => false,\n        'weight_based_inventory' => false,\n        'bin_locations' => true,\n        'quarantine_management' => true,\n    ];\n}\n\n// RestaurantPack\npublic function featureFlags(): array\n{\n    return [\n        'recipe_management' => true,\n        'recipe_consumption' => true,\n        'waste_tracking' => true,\n        'expiry_tracking' => true,\n        'batch_costing' => true,\n        'batch_tracking' => false,\n        'serial_number_tracking' => false,\n    ];\n}\n\n// ElectronicsPack\npublic function featureFlags(): array\n{\n    return [\n        'serial_number_tracking' => true,\n        'warranty_tracking' => true,\n        'imei_validation' => true,\n        'batch_tracking' => false,\n        'bin_locations' => false,\n    ];\n}\n```\n\n### Flag Resolution\n\n```php\nif ($this->config->hasFeature('batch_tracking')) {\n    $this->batchService->record($movement);\n}\n\nif ($this->config->hasFeature('serial_number_tracking')) {\n    $this->serialService->validate($serialNumbers);\n}\n\n$pickingMethod = $this->config->hasFeature('fefo_picking') ? 'fefo' : 'fifo';\n```\n\n### NEVER — Hardcoded Industry Checks\n\n```php\n// WRONG\nif ($config->businessType === 'pharmacy') { ... }\nmatch ($config->businessType) { 'pharmacy' => ..., 'restaurant' => ... };\n```\n\n### ALWAYS — Feature Flag Pattern\n\n```php\nif ($config->hasFeature('fefo_picking')) {\n    $this->batchService->pickBatches(..., method: 'fefo');\n}\n```\n\n---\n\n## 19. API Design Conventions\n\n### Controller Routes\n\n| Method | Path | Controller | Purpose |\n|--------|------|-----------|---------|\n| GET | `/inventory` | InventoryController@index | Inventory dashboard |\n| GET | `/inventory/ledger` | InventoryController@ledger | Movement history |\n| GET | `/inventory/balances` | InventoryController@balances | Current stock per product+warehouse |\n| GET | `/inventory/balances/{product}` | InventoryController@productBalance | Single product stock |\n| GET | `/inventory/transfers` | TransferController@index | Transfer list |\n| POST | `/inventory/transfers` | TransferController@store | Initiate transfer |\n| POST | `/inventory/transfers/{id}/send` | TransferController@send | Send transfer |\n| POST | `/inventory/transfers/{id}/receive` | TransferController@receive | Receive transfer |\n| GET | `/inventory/batches` | BatchController@index | Batch list |\n| POST | `/inventory/batches` | BatchController@store | Register batch |\n| GET | `/inventory/serials` | SerialController@index | Serial numbers |\n| POST | `/inventory/serials` | SerialController@store | Register serials |\n| GET | `/inventory/reservations` | ReservationController@index | Active reservations |\n| POST | `/inventory/reservations` | ReservationController@store | Create reservation |\n| DELETE | `/inventory/reservations/{id}` | ReservationController@destroy | Cancel reservation |\n| GET | `/inventory/adjustments` | AdjustmentController@index | Adjustment history |\n| POST | `/inventory/adjustments` | AdjustmentController@store | Submit adjustment |\n| GET | `/inventory/warehouses` | WarehouseController@index | Warehouse list |\n| POST | `/inventory/warehouses` | WarehouseController@store | Create warehouse |\n| GET | `/inventory/recipes` | RecipeController@index | BOM/recipe list |\n| POST | `/inventory/recipes` | RecipeController@store | Create recipe |\n| GET | `/inventory/alerts` | AlertController@index | Active alerts |\n\n### Form Request Validation\n\n```php\nclass StoreTransferRequest extends FormRequest\n{\n    public function rules(): array\n    {\n        return [\n            'from_warehouse_id' => ['required', 'exists:warehouses,id'],\n            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],\n            'items' => ['required', 'array', 'min:1'],\n            'items.*.product_id' => ['required', 'string', 'exists:products,ulid'],\n            'items.*.quantity' => ['required', 'integer', 'min:1'],\n            'description' => ['nullable', 'string', 'max:500'],\n        ];\n    }\n}\n\nclass StockAdjustmentRequest extends FormRequest\n{\n    public function rules(): array\n    {\n        return [\n            'product_id' => ['required', 'string', 'exists:products,ulid'],\n            'warehouse_id' => ['required', 'exists:warehouses,id'],\n            'quantity' => ['required', 'integer'],\n            'reason' => ['required', 'string', 'in:damage,loss,theft,found,correction,other'],\n            'description' => ['nullable', 'string', 'max:500'],\n        ];\n    }\n}\n```\n\n---\n\n## 20. UI/UX Principles for SME Users\n\n### Design Tenets\n\n| Principle | Implementation |\n|-----------|---------------|\n| **10-second insight** | Dashboard shows stock value, today's movements, low stock count, top alerts |\n| **One-click actions** | Most common actions (adjust stock, create transfer) are 2 clicks max |\n| **Visual stock health** | Color-coded badges: green (in stock), yellow (low), red (out), gray (discontinued) |\n| **Progressive disclosure** | Simple views for cashiers, detailed views for managers |\n| **Search-first** | Every inventory page has a global search bar |\n| **Bulk operations** | Select multiple rows to adjust, transfer, or print labels |\n| **Export** | Every table can be exported to CSV/PDF with one click |\n| **Mobile-friendly** | Core operations work on tablets for warehouse staff |\n\n### Page Layouts\n\n**Inventory Dashboard (Manager View)**\n```\n+----------------------------------------------------------+\n| Inventory Dashboard                                       |\n+----------+----------+-------------+----------------------+\n| Stock    | Today's  | Low Stock   | Expiring in 30 Days  |\n| Value    | Movements| Items       | Items                |\n| $84,200  | +12 in   | 8 items     | 3 batches            |\n|          | -5 out   | below min   |                      |\n+----------+----------+-------------+----------------------+\n| Fast Movers              | Slow Movers                    |\n| 1. Milk (84 sold)       | 1. Paint (0 in 90d)            |\n| 2. Bread (62 sold)      | 2. Oil (2 in 90d)              |\n| 3. Eggs (58 sold)       | 3. Spices (0 in 90d)           |\n+--------------------------+-------------------------------+\n| Recent Movements                                          |\n| Time    | Product   | Type  | Qty   | Ref    | By       |\n| 2:30    | Paracet.  | Sale  | -10   | INV-23 | Cashier  |\n| 2:15    | Bandages  | Adj   | +50   | ADJ-04 | Manager  |\n| 1:45    | Syringes  | Pur   | +200  | PO-404 | Supplier |\n+----------------------------------------------------------+\n```\n\n---\n\n## 21. Performance & Concurrency Requirements\n\n### Read Performance\n\n| Query Pattern | Strategy |\n|---------------|----------|\n| Dashboard (aggregates) | Materialized dashboard cache, recompute every 5 min |\n| Product stock lookup | Direct `InventoryBalance` read via unique key |\n| Movement history | Paginated query on `inventory_ledger` with composite indexes |\n| Batch/expiry queries | Indexed on `(expiry_date, status)` |\n| Search by SKU/barcode | MySQL index or Scout/Algolia |\n\n### Write Performance\n\n| Operation | Strategy |\n|-----------|----------|\n| POS sale (single deduction) | Synchronous — one ledger INSERT + one balance UPSERT + costing |\n| Bulk import (1000+ items) | Queue job — batches of 100 with chunked inserts |\n| Stock transfer (full warehouse) | Queue job — async processing |\n| End-of-day reconciliation | Queue job — ledger replay |\n\n### Concurrency Protection\n\n| Threat | Protection |\n|--------|-----------|\n| Two POS terminals deduct same item simultaneously | Optimistic locking on `lock_version` |\n| Transfer and sale race on same stock | Lock version check fails second operation, caller retries |\n| Reservation expires while being consumed | Atomic check-then-act in DB transaction |\n| Bulk import conflicts with live sales | Row-level locking via `lockForUpdate()` |\n\n### Transaction Strategy\n\n```php\nDB::transaction(function () use ($movement) {\n    $ledger = $this->stockMovementEngine->record(...);\n    $this->inventoryBalanceService->recalculate(...);\n    $this->costingEngine->consume(...);\n\n    StockMovementCreated::dispatch($ledger);\n}, attempts: 3);\n```\n\n---\n\n## 22. Multi-Tenant Considerations\n\n### Data Isolation\n\n| Tenant Mode | Inventory Tables Location | Isolation |\n|-------------|--------------------------|-----------|\n| Shared (Free, Starter, Professional) | `souda_shared` database | `tenant_id` column + `HasTenantScope` trait |\n| Dedicated (Enterprise) | `souda_tenant_{uuid}` database | Separate MySQL database |\n\n### HasTenantScope on Inventory Models\n\n```php\nclass InventoryLedger extends Model\n{\n    use HasTenantScope;\n    protected $guarded = [];\n    public $timestamps = false;\n}\n\nclass InventoryBalance extends Model\n{\n    use HasTenantScope;\n    protected $guarded = [];\n}\n\nclass CostLayer extends Model\n{\n    use HasTenantScope;\n    // ...\n}\n```\n\n### InventoryServiceProvider\n\n```php\nclass InventoryServiceProvider extends ServiceProvider\n{\n    public function register(): void\n    {\n        $this->app->singleton(InventoryEngine::class);\n        $this->app->singleton(StockMovementEngine::class);\n        $this->app->singleton(InventoryBalanceService::class);\n        $this->app->singleton(CostingEngine::class);\n        $this->app->singleton(ReservationEngine::class);\n        $this->app->singleton(BatchService::class);\n        $this->app->singleton(SerialNumberService::class);\n        $this->app->singleton(WarehouseService::class);\n        $this->app->singleton(TransferEngine::class);\n        $this->app->singleton(AuditService::class);\n        $this->app->singleton(AlertEngine::class);\n    }\n\n    public function boot(): void\n    {\n        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations/Tenant');\n        $this->registerRoutes();\n        $this->registerEvents();\n        $this->registerObservers();\n    }\n}\n```\n\n### Tenant Config Integration\n\n```php\nclass InventoryEngine\n{\n    public function handlePurchaseReceived(PurchaseReceived $event): void\n    {\n        $movement = $this->movementEngine->record(/* ... */);\n\n        if ($this->config->hasFeature('batch_tracking')) {\n            $this->batchService->receive(/* ... */);\n        }\n\n        if ($this->config->hasFeature('serial_number_tracking')) {\n            $this->serialService->registerBatch(/* ... */);\n        }\n    }\n}\n```\n\n---\n\n## 23. Testing Strategy\n\n### Test Categories\n\n| Category | Scope | Framework | Location |\n|----------|-------|-----------|----------|\n| Unit tests | Engine services, cost calculations, validation | Pest | `tests/Unit/Inventory/` |\n| Feature tests | Full workflows (sale, ledger, balance, alert) | Pest | `tests/Feature/Inventory/` |\n| Integration | Cross-module (Order, Inventory, Purchase) | Pest | `tests/Feature/` |\n| Reconciliation | Ledger replay matches expected balance | Pest | `tests/Feature/Inventory/ReconciliationTest.php` |\n\n### Required Coverage\n\n| Feature | Minimum Tests |\n|---------|--------------|\n| StockMovementEngine::record | 5+ (various types, with/without batch/serial) |\n| InventoryBalanceService::recalculate | 3+ (inbound, outbound, zero) |\n| CostingEngine (weighted average) | 5+ (multiple layers, zero balance, edge cases) |\n| CostingEngine (FIFO) | 5+ (layer consumption order, partial layers) |\n| ReservationEngine | 5+ (create, consume, expire, cancel, concurrent) |\n| TransferEngine | 4+ (initiate, send, receive, cancel, partial) |\n| BatchService | 4+ (receive, deduct, fifo/fefo, quarantine) |\n| SerialNumberService | 4+ (register, validate, mark sold, warranty) |\n| AlertEngine | 3+ (low stock, expiry, dead stock) |\n| Multi-warehouse | 2+ (transfer flow, balance isolation) |\n| Concurrent writes | 2+ (optimistic locking, retry) |\n| Reconciliation | 1+ (replay ledger to verify balance) |\n\n### Test Patterns\n\n```php\n// Unit test\nit('records a purchase receipt movement', function () {\n    $engine = app(StockMovementEngine::class);\n\n    $ledger = $engine->record(\n        productId: $product->ulid,\n        warehouseId: $warehouse->id,\n        quantity: 100,\n        type: MovementTypeEnum::PurchaseReceipt,\n        reference: 'PO-001',\n        unitCost: 1500,\n    );\n\n    expect($ledger)\n        ->quantity->toBe(100)\n        ->quantity_before->toBe(0)\n        ->quantity_after->toBe(100)\n        ->movement_type->toBe('purchase_receipt')\n        ->unit_cost->toBe(1500);\n});\n\n// Feature test\nit('deducts stock and updates balance on sale', function () {\n    $product = Product::factory()->create(['costing_method' => 'weighted_average']);\n    $warehouse = Warehouse::factory()->create(['is_default' => true]);\n    InventoryBalance::factory()->create([\n        'product_id' => $product->ulid,\n        'warehouse_id' => $warehouse->id,\n        'quantity' => 50,\n        'average_unit_cost' => 1000,\n    ]);\n\n    $event = new SaleCompleted(\n        productId: $product->ulid,\n        warehouseId: $warehouse->id,\n        quantity: 5,\n        orderReference: 'ORD-001',\n    );\n\n    app(InventoryEngine::class)->handleSaleCompleted($event);\n\n    $balance = InventoryBalance::where([\n        'product_id' => $product->ulid,\n        'warehouse_id' => $warehouse->id,\n    ])->first();\n\n    expect($balance)->quantity->toBe(45);\n})->shared();\n```\n\n### Test Database Considerations\n\n- Feature tests use `souda_testing` DB (shared between central and default connections)\n- Do NOT run tests in parallel — `migrate:fresh` calls will collide\n- Use `RefreshDatabase` trait or manual migration refresh in setUp\n- Factory state: explicit `product_id`, `warehouse_id` in creates\n\n---\n\n## 24. Coding Standards & AI Guardrails\n\n### MUST FOLLOW\n\n- All inventory write operations go through `InventoryEngine` — never call `StockMovementEngine` directly\n- `InventoryLedger` is INSERT-only — never UPDATE or DELETE ledger rows\n- Use `MovementTypeEnum` for movement types — never hardcoded strings\n- All monetary values in cents (integer) — `bigInteger` column type\n- All quantities are integers (whole units) — use `decimal(12,4)` only for weight-based\n- Use constructor property promotion for all services\n- Use proper return type hints on all methods\n- Use Form Request classes for validation — no inline validation\n- Use PHPDoc for complex logic — no inline comments for simple code\n- Run `vendor/bin/pint --format agent` after all PHP changes\n- Add `->orderBy('id')` before `->each()` on query builders\n\n### NEVER\n\n- Write stock directly from another module (POS, Order, Purchase, etc.)\n- Use `env()` outside config files\n- Add hardcoded business type checks (`if ($slug === 'pharmacy')`)\n- Create per-industry tables or columns\n- Use `DB::` when an Eloquent relationship exists\n- Leave empty `__construct()` with zero params (unless private)\n- Delete inventory ledger rows\n- Commit secrets or credentials\n\n### ALWAYS\n\n- Create event listeners for cross-module communication\n- Wrap inventory writes in `DB::transaction()` with retry\n- Use `HasTenantScope` on shared-mode models\n- Register events in `InventoryServiceProvider::boot()`\n- Run tests before marking inventory tasks complete\n- Write feature tests for every new inventory workflow\n\n---\n\n## 25. Development Roadmap\n\n### Phase 1A: Foundation (Week 1-2)\n\n| Task | Deliverable |\n|------|-------------|\n| Create module structure | `app/Modules/Inventory/` with all directories |\n| Create tenant migrations | `inventory_ledger`, `inventory_balances`, `cost_layers`, `stock_reservations` |\n| Create shared migrations | Same tables with `tenant_id` for shared mode |\n| Implement Models | `InventoryLedger`, `InventoryBalance`, `CostLayer`, `StockReservation` |\n| Implement Enums | `MovementTypeEnum`, `CostingMethodEnum`, `ReservationStatusEnum` |\n| Implement StockMovementEngine | Core `record()` method with reference generation |\n| Implement InventoryBalanceService | `recalculate()`, `rebuildFromLedger()` |\n| Create InventoryServiceProvider | Singleton bindings, migration loading |\n| Create tests | StockMovementEngine unit tests, InventoryBalance feature tests |\n\n### Phase 1B: Costing & Reservations (Week 3-4)\n\n| Task | Deliverable |\n|------|-------------|\n| Implement CostingEngine | Weighted average + FIFO strategies |\n| Implement CostLayer model + migration | Layer tracking per product+warehouse |\n| Implement ReservationEngine | Reserve, consume, expire, cancel |\n| Implement reservation migration | `stock_reservations` table |\n| Create ExpireReservations command | Cron: every minute |\n| Create tests | CostingEngine unit tests, ReservationEngine feature tests |\n\n### Phase 1C: Warehouse & Transfer (Week 5)\n\n| Task | Deliverable |\n|------|-------------|\n| Move Warehouse model from Product | Backward-compatible alias |\n| Add bins/locations tables + models | `inventory_bins` |\n| Implement TransferEngine | Initiate, send, receive, cancel, partial receive |\n| Implement transfer tables + models | `inventory_transfers`, `inventory_transfer_items` |\n| Implement AuditService | Log all operations |\n| Create tests | TransferEngine feature tests, multi-warehouse tests |\n\n### Phase 1D: Batch, Serial & Alerts (Week 6)\n\n| Task | Deliverable |\n|------|-------------|\n| Implement BatchService | Receive, deduct, FEFO/FIFO picking, quarantine |\n| Implement batch migration + model | `inventory_batches` |\n| Implement SerialNumberService | Register, validate, lifecycle |\n| Implement serial migration + model | `serial_numbers` |\n| Implement AlertEngine | Low stock, depletion, expiry evaluation |\n| Create expiry alerts command | Cron: every 6 hours |\n| Create tests | BatchService, SerialNumberService, AlertEngine |\n\n### Phase 1E: Frontend (Week 7-8)\n\n| Task | Deliverable |\n|------|-------------|\n| Inventory Dashboard | Stock value, movements, alerts, fast/slow movers |\n| Stock Movement History | Filterable table with pagination |\n| Inventory Balance View | Per-product+warehouse stock snapshot |\n| Warehouse Management | CRUD for warehouses and bins |\n| Transfer Management | Create, send, receive transfers |\n| Batch Management | Create batches, view expiries |\n| Serial Number Management | Register serials, view lifecycle |\n| Stock Adjustments | Submit and approve adjustments |\n| Alerts Panel | Low stock, expiring, dead stock alerts |\n| Frontend API layer | React Query hooks for all inventory endpoints |\n\n### Phase 1F: Industry Pack Integration (Week 9)\n\n| Task | Deliverable |\n|------|-------------|\n| Update all 15 Industry Packs | Add inventory feature flags |\n| Integrate Pharmacy Pack | `batch_tracking`, `expiry_tracking`, `fefo_picking` |\n| Integrate Restaurant Pack | `recipe_management`, `recipe_consumption`, `waste_tracking` |\n| Integrate Electronics Pack | `serial_number_tracking`, `warranty_tracking` |\n| Integrate Grocery Pack | `weight_based_inventory`, `perishable_tracking` |\n| Integrate Fashion Pack | `variant_size_color` inventory views |\n| Test all pack configurations | Verify feature flag gating |\n\n### Phase 2: Automation & Smart Features (Future)\n\n| Feature | Complexity |\n|---------|-----------|\n| Auto Reorder Engine | Medium |\n| Purchase Suggestions | High |\n| Dead Stock Detection | Low |\n| Slow/Fast Moving Classification | Medium |\n| Seasonal Demand Prediction | High |\n| Automation Rules Engine (IF-THEN) | High |\n| Inventory Dashboard with Charts | Medium |\n| AI Assistant (chat-based) | Very High |\n\n---\n\n## 26. File Creation Templates\n\n### New Inventory Service\n\n```php\n<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Modules\\Inventory\\Services;\n\nuse App\\Modules\\BusinessType\\ValueObjects\\TenantConfig;\n\nclass MyInventoryService\n{\n    public function __construct(\n        private TenantConfig $config,\n    ) {}\n\n    public function execute(): void\n    {\n        if ($this->config->hasFeature('batch_tracking')) {\n            // industry-specific behavior\n        }\n    }\n}\n```\n\n### New Inventory Model (Shared Mode)\n\n```php\n<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Modules\\Inventory\\Models;\n\nuse App\\Tenancy\\Models\\Concerns\\HasTenantScope;\nuse Illuminate\\Database\\Eloquent\\Model;\n\nclass MyModel extends Model\n{\n    use HasTenantScope;\n\n    protected $guarded = [];\n\n    public function casts(): array\n    {\n        return [\n            'metadata' => 'array',\n        ];\n    }\n}\n```\n\n### New Inventory Event\n\n```php\n<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Modules\\Inventory\\Events;\n\nuse Illuminate\\Foundation\\Events\\Dispatchable;\n\nclass MyInventoryEvent\n{\n    use Dispatchable;\n\n    public function __construct(\n        public string $productId,\n        public string $warehouseId,\n        public int $quantity,\n    ) {}\n}\n```\n\n### New Inventory Listener\n\n```php\n<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Modules\\Inventory\\Listeners;\n\nuse App\\Modules\\Inventory\\Events\\MyInventoryEvent;\n\nclass MyInventoryListener\n{\n    public function handle(MyInventoryEvent $event): void\n    {\n        // Handle the event\n    }\n}\n```\n\n### New Tenant Migration\n\n```php\n<?php\n\ndeclare(strict_types=1);\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration\n{\n    public function up(): void\n    {\n        Schema::create('my_inventory_table', function (Blueprint $table) {\n            $table->id();\n            $table->string('tenant_id', 36);\n            $table->ulid('product_id');\n            $table->timestamps();\n        });\n    }\n\n    public function down(): void\n    {\n        Schema::dropIfExists('my_inventory_table');\n    }\n};\n```\n\n### New React Query Hook (Frontend)\n\n```typescript\n// resources/js/modules/inventory/hooks/use-inventory.ts\nimport { useQuery, useMutation } from '@tanstack/react-query';\nimport axios from 'axios';\nimport type { InventoryBalance, InventoryMovement } from '../types/inventory';\n\nexport function useInventoryBalances(warehouseId?: string) {\n    return useQuery({\n        queryKey: ['inventory', 'balances', warehouseId],\n        queryFn: async () => {\n            const { data } = await axios.get<InventoryBalance[]>(\n                route('inventory.balances'),\n                { params: { warehouse_id: warehouseId } }\n            );\n            return data;\n        },\n    });\n}\n\nexport function useMovementHistory(productId?: string) {\n    return useQuery({\n        queryKey: ['inventory', 'movements', productId],\n        queryFn: async () => {\n            const { data } = await axios.get<InventoryMovement[]>(\n                route('inventory.ledger'),\n                { params: { product_id: productId } }\n            );\n            return data;\n        },\n    });\n}\n\nexport function useCreateTransfer() {\n    return useMutation({\n        mutationFn: async (payload: TransferPayload) => {\n            const { data } = await axios.post(route('inventory.transfers.store'), payload);\n            return data;\n        },\n    });\n}\n```\n\n---\n\n## 27. Final Checklist — Before Beginning Phase 1A\n\nBefore you write any code, verify:\n\n- [ ] All 10+ migrations are designed and ordered correctly\n- [ ] `InventoryServiceProvider` is registered in `bootstrap/providers.php` (after `ProductServiceProvider`)\n- [ ] All 15 Industry Packs have feature flags for their inventory needs\n- [ ] `HasTenantScope` is applied to every shared-mode model\n- [ ] `InventoryEngine` is the only class with public write methods\n- [ ] Events are defined for all 17 inventory domain events\n- [ ] Existing `WarehouseStock` model is deprecated with a read-only facade\n- [ ] Database indexes cover all critical query patterns\n- [ ] Optimistic locking is implemented on `inventory_balances`\n- [ ] The golden rule is followed: **no module writes stock directly**\n"
}
```

**Output:**
```
Wrote file successfully.
```

---

## Assistant (Build · DeepSeek V4 Flash Free · 5.7s)

_Thinking:_

Now let me verify the file is correct by checking it.

**Tool: bash**

**Input:**
```json
{
  "command": "Get-Content C:\\dev\\souda\\docs\\inventory-agent.md | Measure-Object -Line",
  "workdir": "C:\\dev\\souda"
}
```

**Output:**
```

Lines Words Characters Property
----- ----- ---------- --------
 1548                          



```

---

## Assistant (Build · DeepSeek V4 Flash Free · 6.9s)

_Thinking:_

Let me verify the file looks clean by checking a few spots for `$` signs and backticks to ensure there's no corruption.

**Tool: bash**

**Input:**
```json
{
  "command": "Select-String -Pattern 'public function record\\(' -Path C:\\dev\\souda\\docs\\inventory-agent.md -Context 0,5 | Select-Object -First 1",
  "workdir": "C:\\dev\\souda"
}
```

**Output:**
```

> docs\inventory-agent.md:227:    public function record(
  docs\inventory-agent.md:228:        string $productId,
  docs\inventory-agent.md:229:        ?string $variantId,
  docs\inventory-agent.md:230:        string $warehouseId,
  docs\inventory-agent.md:231:        int $quantity,
  docs\inventory-agent.md:232:        MovementTypeEnum $type,



```

**Tool: bash**

**Input:**
```json
{
  "command": "Select-String -Pattern 'Schema::create' -Path C:\\dev\\souda\\docs\\inventory-agent.md | Measure-Object -Line",
  "workdir": "C:\\dev\\souda"
}
```

**Output:**
```

Lines Words Characters Property
----- ----- ---------- --------
   13                          



```

---

## Assistant (Build · DeepSeek V4 Flash Free · 8.8s)

The document is clean — all `$` signs are preserved, all 13 schema migrations are present, and the file is 1,548 lines. Here's a summary of what was created:

**`docs/inventory-agent.md`** — 1,548 lines, 27 sections covering:

| Section | Content |
|---------|---------|
| 1-2 | Project scope, philosophy, 5 core architectural principles |
| 3-4 | Module boundaries diagram, InventoryEngine orchestrator with all handler methods |
| 5-6 | StockMovementEngine with 13 movement types, immutable ledger table design |
| 7 | InventoryBalance with optimistic locking (concurrency protection) |
| 8 | Costing: Weighted Average + FIFO with full PHP implementations |
| 9 | Reservation lifecycle (active, consumed, expired, cancelled) |
| 10 | Warehouse hierarchy (zone, aisle, rack, shelf, bin), TransferEngine |
| 11 | Batch/lot tracking, FEFO/FIFO picking, SerialNumber lifecycle |
| 12 | Manufacturing recipes, BOM, production flow |
| 13 | Automation rules engine design (Phase 2) |
| 14-15 | Event flow diagram, 17 domain events with listeners, cron jobs |
| 16-17 | DB naming conventions, indexing strategy, model relationships |
| 18 | Industry Pack extension rules with feature flag examples |
| 19 | 22 API routes, Form Request validation examples |
| 20 | UI/UX principles for SME users with dashboard wireframe |
| 21 | Performance strategy (read/write), concurrency protection |
| 22 | Multi-tenant isolation (shared + dedicated), service provider |
| 23 | Testing strategy: 12 test categories with Pest examples |
| 24 | Coding standards, AI guardrails (MUST/NEVER/ALWAYS) |
| 25 | Development roadmap: 6 sub-phases across 9 weeks |
| 26 | File creation templates (service, model, event, listener, migration, hook) |
| 27 | Pre-implementation checklist |

Ready to start Phase 1A whenever you are.

---

