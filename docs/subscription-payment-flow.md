# Subscription & Payment Flow

## Overview

This application has **two billing systems** operating in parallel:

1. **Laravel Cashier (Stripe)** - Syncs Stripe products/prices and handles Stripe webhooks
2. **Custom Billing Module** (`App\Modules\Billing`) - Gateway-agnostic system supporting multiple payment providers

The **Custom Billing Module** is the actively used system for the frontend billing flow.

> **Multi-Tenant Architecture:** This application uses `stancl/tenancy` v3 in **multi-database mode**. Each tenant gets its own isolated database. All billing data (plans, subscriptions, payments) stays in the **central database** using the `CentralConnection` trait on billing models.

---

## Architecture

### Database Tables

All billing tables reside in the **central database** (not per-tenant databases):

#### Custom Billing Module (Central Database)

| Table | Purpose |
|-------|---------|
| `billing_plans` | Plan definitions with pricing, features, limits |
| `billing_subscriptions` | Tenant subscriptions with status, billing cycle, gateway |
| `billing_payments` | Individual payment records with transaction details |

#### Laravel Cashier (Central Database)

| Table | Purpose |
|-------|---------|
| `plans` | Mirrors Stripe products |
| `plan_prices` | Mirrors Stripe prices |
| `subscriptions` | Cashier subscription records |
| `subscription_items` | Subscription line items |

### Central Connection (Tenancy Integration)

All billing models use `Stancl\Tenancy\Database\Concerns\CentralConnection` which ensures queries always target the central database, regardless of the current tenant context. This is critical because:

- Billing operations often run during tenant initialization or via Artisan commands outside tenant context
- `SubscriptionService` methods accept `$tenantId` explicitly rather than relying on `tenant()->id`
- `ExpireSubscriptions` command queries all subscriptions across all tenants from the central DB

```php
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Subscription extends Model
{
    use CentralConnection;
    // ...
}
```

### Tenant Lifecycle & Billing

| Lifecycle Event | Billing Impact |
|-----------------|----------------|
| **TenantCreated** | `SetupTenantDefaults` seeds tenant_settings; tenant is on `generic trial` (tracked via `trial_ends_at` and `trial_used` on tenants table) |
| **TenantDeleted** | No automatic billing cleanup (deleted tenant's subscription remains in central DB for audit) |
| **SubscriptionExpired** | Dispatched by `ExpireSubscriptions` command when grace period passes |
| **SubscriptionActivated** | Marks `tenant.trial_used = true` |

---

## Models & Relationships

### Custom Billing Module

#### Plan (`App\Modules\Billing\Models\Plan`)

```
billing_plans
├── name, slug, description
├── monthly_price, yearly_price, currency
├── features (JSON), limits (JSON)
├── is_active, display_order, popular, cta
├── trial_enabled, trial_days, trial_without_card
└── hasMany(Subscription)
```

#### Subscription (`App\Modules\Billing\Models\Subscription`)

```
billing_subscriptions
├── tenant_id, plan_id, gateway
├── status (enum), billing_cycle (enum)
├── amount, currency
├── gateway_subscription_id
├── starts_at, expires_at, next_billing_at
├── trial_ends_at, cancelled_at
├── metadata (JSON)
├── belongsTo(Plan)
└── hasMany(Payment)
```

#### Payment (`App\Modules\Billing\Models\Payment`)

```
billing_payments
├── subscription_id, tenant_id, gateway
├── transaction_id, amount, currency
├── status (enum), payload (JSON)
└── paid_at
```

---

## Enums & States

### SubscriptionStatus

| Status | Description | Accessible |
|--------|-------------|------------|
| `Trial` | Free trial period | Yes |
| `Active` | Paid and active | Yes |
| `Grace` | Expired but within grace period (3 days default) | Yes |
| `Expired` | Fully expired | No |
| `Cancelled` | User cancelled | No |
| `PendingPayment` | Awaiting payment completion | No |

### PaymentStatus

`Pending` → `Completed` → `Failed` → `Refunded` → `PartialRefunded`

### BillingCycle

`Daily`, `Weekly`, `Monthly`, `Quarterly`, `Yearly`, `Custom`

### Gateway

`Stripe`, `SSLCommerz`, `BKash`, `Nagad`, `PortWallet`, `Manual`

---

## Payment Gateway Integration

### Configuration (`config/billing.php`)

```php
'default_gateway' => env('BILLING_DEFAULT_GATEWAY', 'stripe'),
'currency' => env('BILLING_CURRENCY', 'BDT'),
'grace_period_days' => env('BILLING_GRACE_PERIOD_DAYS', 3),
```

### Gateway Drivers

| Gateway | Driver | Status |
|---------|--------|--------|
| Stripe | `StripeDriver` | Stub (uses Cashier for actual Stripe flow) |
| SSLCommerz | `SSLCommerzDriver` | Fully implemented |
| bKash | `BKashDriver` | Stub |
| Nagad | `NagadDriver` | Stub |
| PortWallet | `PortWalletDriver` | Stub |
| Manual | `ManualDriver` | Implemented (invoice-based, admin-verified) |

### Gateway Interface

All drivers implement `BillingGatewayInterface`:

```php
interface BillingGatewayInterface
{
    public function createPayment(SubscriptionDTO $dto, array $options = []): PaymentDTO;
    public function verifyPayment(string $transactionId, array $payload = []): PaymentDTO;
    public function cancelSubscription(string $gatewaySubscriptionId): bool;
    public function refundPayment(string $transactionId, ?int $amount = null): PaymentDTO;
    public function generateCheckoutUrl(PaymentDTO $paymentDTO): string;
}
```

### BillingManager

Factory pattern for resolving gateway drivers from config (`app/Modules/Billing/Services/BillingManager.php`).

---

## Complete Flow: User Signup to Payment

### Step 1: User Registration

- User created via `CreateNewUser` or `CreateSocialUser` actions
- A Tenant is created for the user
- `BillingEmailService::sendWelcomeRegistered()` sends welcome email

### Step 2: Access Billing Page

```
GET /billing → BillingController::index()
```

- Loads active plans via `PlanService::getActivePlans()`
- Checks for existing subscription via `SubscriptionService::getTenantSubscription()`
- Returns Inertia page with plans, subscription status, available gateways, trial info

### Step 3: Subscribe to a Plan

```
POST /billing/subscribe → BillingController::subscribe()
```

**Validation:** `plan_id`, `gateway`, `billing_cycle`

**SubscriptionService::createSubscription():**

1. Finds the plan
2. Calculates amount (yearly vs monthly)
3. Checks if trial available (`trial_enabled && !trial_used`)
4. If trial without card: creates subscription with `Trial` status, activates immediately
5. Otherwise: creates subscription with `PendingPayment` status
6. Initiates payment via gateway driver
7. Records payment in `billing_payments` table
8. Returns checkout URL to frontend

### Step 4: Payment Processing (SSLCommerz Example)

Frontend redirects to SSLCommerz checkout URL. User completes payment on SSLCommerz.

**Callback Routes:**

| Route | Method | Purpose |
|-------|--------|---------|
| `POST /billing/callback/{gateway}` | BillingController::callback() | Gateway callback |
| `POST /billing/success/sslcommerz` | BillingController::sslcommerzSuccess() | Success redirect |
| `POST /billing/webhook/sslcommerz` | BillingController::sslcommerzWebhook() | Webhook notification |

**SubscriptionService::verifyAndActivate():**

1. Gateway driver verifies payment via API
2. Finds payment record by `transaction_id`
3. Marks payment as `Completed`
4. Dispatches `PaymentReceived` event
5. Calls `activateSubscription()`:
   - Sets status to `Active`
   - Calculates `expires_at` based on billing cycle
   - Sets `next_billing_at`
   - Marks `trial_used = true` if applicable
   - Dispatches `SubscriptionActivated` event
6. Redirects to `/billing?checkout=success`

### Step 5: Subscription Protection

Routes protected by `subscription` middleware (`EnsureSubscribed`):

- Checks `SubscriptionService::tenantHasAccessibleSubscription()`
- If no accessible subscription, redirects to `/billing`
- Admins bypass this check

---

## Webhook & Event Handling

### Custom Module Webhooks

#### SSLCommerz

- Events: `success`, `fail`, `cancel`
- Verifies payment via SSLCommerz validation API
- Handlers: `BillingController` methods + `SSLCommerzWebhookHandler`

#### Stripe (Custom Module)

- Events: `checkout.session.completed`, `customer.subscription.updated`, `invoice.paid`, `invoice.payment_failed`
- Handler: `StripeWebhookHandler` (stub - signature verification not implemented)

### Cashier Webhooks (StripeEventListener)

Listens to `Laravel\Cashier\Events\WebhookReceived`:

| Stripe Event | Action |
|--------------|--------|
| `product.created/updated/deleted` | Syncs to `plans` table |
| `price.created/updated/deleted` | Syncs to `plan_prices` table |
| `customer.subscription.created/updated/deleted` | Updates Cashier subscription records |
| `invoice.paid` | Sends `invoice.paid` email |
| `invoice.payment_failed` | Sends `payment.failed` email |

Uses cache-based event locking to prevent duplicate processing.

### CSRF Exemptions (`bootstrap/app.php`)

- `stripe/*`
- `billing/webhook/*`
- `billing/success/sslcommerz`

---

## Events & Listeners

### Events

| Event | Triggered When |
|-------|----------------|
| `SubscriptionActivated` | Subscription becomes active |
| `SubscriptionCancelled` | Subscription is cancelled |
| `SubscriptionExpired` | Subscription expires |
| `PaymentReceived` | Payment is verified and completed |
| `PaymentFailed` | Payment verification fails |

### Listeners

`SendSubscriptionNotification` - Sends email notifications for subscription lifecycle events.

---

## Subscription Expiry Flow

### Command: `php artisan subscription:expire-expired`

Defined in `app/Console/Commands/ExpireSubscriptions.php` and scheduled via `routes/console.php` to run every six hours:

```php
Schedule::command('subscription:expire-expired')->everySixHours();
```

### Lifecycle

```
Active/Trial ──(expires_at ≤ now)──→ Grace ──(expires_at + grace ≥ now)──→ Expired
```

| Step | Transition | Condition | Action |
|------|-----------|-----------|--------|
| 1 | `Active` → `Grace` | `expires_at <= now` | Sets status to `Grace` |
| 2 | `Trial` → `Grace` | `trial_ends_at <= now` (via `expires_at`) | Sets status to `Grace` |
| 3 | `Grace` → `Expired` | `expires_at + grace_period_days <= now` | Sets status to `Expired`, dispatches `SubscriptionExpired` |

### Grace Period

Configurable via `BILLING_GRACE_PERIOD_DAYS` (default: 3 days). During grace, `EnsureSubscribed` / `EnsureTenantHasSubscription` still considers the subscription accessible.

### Dry-Run Mode

```bash
php artisan subscription:expire-expired --dry-run
```

Previews which subscriptions would transition without making changes. Shows a table with counts.

### Tenancy Considerations

- The command queries the **central database** directly via `Subscription` model (uses `CentralConnection`).
- No tenant context is initialized — the command iterates subscriptions by `tenant_id` from central DB.
- `SubscriptionExpired` event is dispatched per-subscription (central DB event, not tenant-scoped).

---

## Feature Gating

### Middleware: `EnsureTenantHasFeature`

Aliased as `feature` in `BillingServiceProvider::boot()`. Used in route definitions to gate access to plan-specific features:

```php
Route::middleware('feature:inventory_management')->group(function () {
    // Routes only accessible if tenant's plan includes 'inventory_management'
});
```

**How it works:**

1. Resolves `$tenant` from current tenant context
2. Calls `PlanFeatureService::requireFeature($tenant->id, $feature)`
3. Throws `FeatureNotAccessibleException` if the tenant's plan doesn't include the feature
4. Returns `403 JSON` for API requests or redirects to billing page with flash error

### Feature Definition in Plans

Features are stored as a JSON array on `billing_plans.features`:

```json
["inventory_management", "reports", "api_access", "team_members"]
```

Usage limits are stored on `billing_plans.limits`:

```json
{"team_members": 5, "storage_mb": 1000}
```

### PlanFeatureService

Located at `app/Modules/Billing/Services/PlanFeatureService.php`:

| Method | Purpose |
|--------|---------|
| `tenantHasFeature($tenant, $feature)` | Check if tenant's plan includes a feature |
| `requireFeature($tenant, $feature)` | Check + throw exception |
| `getTenantFeatures($tenant)` | Get all features for tenant's plan |
| `getFeatureLimit($tenant, $feature)` | Get numeric limit for a feature |
| `hasReachedLimit($tenant, $feature, $currentUsage)` | Check if usage limit is hit |

All methods accept `Tenant|string` (model or ID) and query from central DB.

### Check in Business Logic

For in-code checks (not route gating), `SubscriptionService` provides:

- `tenantHasFeature($tenantId, $feature)` — wraps same logic
- `getTenantFeatureLimits($tenantId, $feature)` — returns limit value
- `tenantHasReachedLimit($tenantId, $feature, $currentUsage)` — checks usage against limit

---

## Middleware Architecture

The billing/tenancy middleware stack has **four layers** with distinct responsibilities:

| Priority | Middleware | Alias | Purpose | Admin Bypass |
|----------|-----------|-------|---------|-------------|
| 1 | `InitializeTenancyByUser` | — | Sets tenant context from `auth()->user()->tenant_id` | N/A (admin uses separate context) |
| 2 | `EnsureSubscribed` | — | Admin + subscription gate (legacy) | Yes |
| 3 | `EnsureTenantHasSubscription` | `subscription` | Subscription existence + accessibility check | No (redirects to billing) |
| 4 | `EnsureTenantHasFeature` | `feature:{name}` | Plan feature gate | No |

### Route Registration in `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->prependToPriorityList(
        SubstituteBindings::class,
        InitializeTenancyByUser::class,
    );
})
```

### Route Usage in `routes/tenant.php`

```php
// Tenant context + auth
Route::middleware(['web', 'auth', InitializeTenancyByUser::class])->group(function () {

    // Billing pages (no subscription required)
    Route::get('/billing', ...)->name('billing');

    // Subscription-gated routes
    Route::middleware('subscription')->group(function () {
        Route::get('/dashboard', ...);
        Route::resource('tasks', TaskController::class);
    });

    // Feature-gated routes (anywhere in tenant routes)
    Route::middleware('feature:reports')->group(function () {
        Route::get('/reports', ...);
    });
});

// Admin routes bypass tenant context entirely
Route::middleware(['web', 'auth', 'admin'])->prefix('admin')->group(function () {
    // ...
});
```

### Billing Route Exemptions

SSLCommerz callbacks are outside the tenant middleware group (payment gateways POST directly):

```php
Route::post('/billing/success/sslcommerz', ...)->name('billing.success.sslcommerz');
Route::post('/billing/webhook/sslcommerz', ...)->name('billing.webhook.sslcommerz');
```

These are also CSRF-exempt in `bootstrap/app.php`:

```php
$middleware->validateCsrfTokens(except: [
    'stripe/*',
    'billing/webhook/*',
    'billing/success/sslcommerz',
]);
```

---

## Domain Support

### Domain Model (Future Use)

The `Domain` model is configured in `config/tenancy.php` but **not yet activated** for tenant resolution:

```php
'domain' => [
    'model' => Stancl\Tenancy\Database\Models\Domain::class,
],
```

Each `Tenant` can have related domains via the `domains()` relationship (provided by `stancl/tenancy`):

```php
$tenant->domains()->create(['domain' => 'example.com']);
```

### Current Resolution Strategy

Tenant identification is **user-based** via `InitializeTenancyByUser` middleware which reads `auth()->user()->tenant_id`. Domain-based resolution is not used.

### Future Domain Activation

To switch to domain-based identification:

1. Change middleware from `InitializeTenancyByUser` to `InitializeTenancyByDomain` in `routes/tenant.php`
2. Set up SSL certificates for each tenant domain
3. Update `config/tenancy.php` to make domains required
4. Add domain management UI for tenants

### Domain Readiness Checklist

| Item | Status |
|------|--------|
| Domain model in `config/tenancy.php` | Configured |
| Tenant→Domain relationship | Provided by stancl/tenancy |
| Domain-based resolution middleware | Not yet applied |
| Domain management UI | Not built |
| SSL/HTTPS for tenant domains | Not configured |
| DNS routing | Not configured |

---

| Template | Trigger |
|----------|---------|
| `subscription-activated.blade.php` | Subscription activated |
| `subscription-canceled.blade.php` | Subscription cancelled |
| `payment-failed.blade.php` | Payment fails |
| `invoice-paid.blade.php` | Invoice payment successful |

---

## Frontend

### Pages

| File | Purpose |
|------|---------|
| `resources/js/pages/billing/index.tsx` | Main billing/subscription page |
| `resources/js/pages/billing/invoices.tsx` | Invoice history |

### Key Features

- Plan selection with monthly/yearly toggle
- Gateway selection
- Trial indication
- Subscription status display
- Checkout redirect for payment gateways

---

## Key Files

### Models

- `app/Modules/Billing/Models/Subscription.php`
- `app/Modules/Billing/Models/Payment.php`
- `app/Modules/Billing/Models/Plan.php`
- `app/Models/Tenant.php`
- `app/Models/Plan.php` (Cashier mirror)
- `app/Models/PlanPrice.php` (Cashier mirror)

### Services

- `app/Modules/Billing/Services/SubscriptionService.php`
- `app/Modules/Billing/Services/PaymentService.php`
- `app/Modules/Billing/Services/BillingManager.php`
- `app/Modules/Billing/Services/PlanService.php`
- `app/Modules/Billing/Services/InvoiceService.php`

### Controllers

- `app/Http/Controllers/BillingController.php`

### Gateway Drivers

- `app/Modules/Billing/Drivers/StripeDriver.php`
- `app/Modules/Billing/Drivers/SSLCommerzDriver.php`
- `app/Modules/Billing/Drivers/BKashDriver.php`
- `app/Modules/Billing/Drivers/NagadDriver.php`
- `app/Modules/Billing/Drivers/PortWalletDriver.php`
- `app/Modules/Billing/Drivers/ManualDriver.php`

### Webhooks

- `app/Modules/Billing/Webhooks/StripeWebhookHandler.php`
- `app/Modules/Billing/Webhooks/SSLCommerzWebhookHandler.php`
- `app/Modules/Billing/Webhooks/WebhookHandler.php` (base)
- `app/Listeners/StripeEventListener.php` (Cashier webhooks)

### Middleware

- `app/Http/Middleware/EnsureSubscribed.php` - Admin-bypass subscription gate
- `app/Http/Middleware/EnsureTenantHasSubscription.php` - Strict subscription gate (aliased as `subscription`)
- `app/Http/Middleware/EnsureTenantHasFeature.php` - Plan feature gate (aliased as `feature`)
- `app/Http/Middleware/InitializeTenancyByUser.php` - User-based tenant resolver

### Config

- `config/billing.php`
- `config/cashier.php`
- `config/tenancy.php`

### Routes

- `routes/tenant.php` - All tenant-scoped routes
- `routes/console.php` - Schedules `ExpireSubscriptions`
- `bootstrap/app.php` - Middleware + CSRF exemption registration

### Commands

- `app/Console/Commands/ExpireSubscriptions.php` - Scheduled subscription expiry
- `app/Console/Commands/Tenant/TenantCommand.php` - Base class for tenant-scoped commands

### Jobs

- `app/Jobs/TenantJob.php` - Abstract base for tenant-scoped queued jobs

### Events

- `app/Modules/Billing/Events/SubscriptionActivated.php`
- `app/Modules/Billing/Events/SubscriptionCancelled.php`
- `app/Modules/Billing/Events/SubscriptionExpired.php`
- `app/Modules/Billing/Events/PaymentReceived.php`
- `app/Modules/Billing/Events/PaymentFailed.php`

### Listeners

- `app/Modules/Billing/Listeners/SendSubscriptionNotification.php`

### Service Provider

- `app/Providers/BillingServiceProvider.php` - Middleware aliases, event listeners, service singletons
- `app/Providers/TenancyServiceProvider.php` - Tenant lifecycle hooks (CreateDatabase, MigrateDatabase, SetupTenantDefaults)

---

## Important Notes

1. **StripeDriver is a stub** - Throws `PaymentFailedException`. The actual Stripe flow relies on Laravel Cashier.

2. **SSLCommerz is the only fully implemented custom gateway** - Complete API integration for session creation, payment verification, and refunds.

3. **Dual billing system** - The `plans`/`plan_prices` tables (Cashier) and `billing_plans`/`billing_subscriptions`/`billing_payments` tables (custom module) coexist. The frontend billing page uses the custom module.

4. **Trial system** - Supports both plan-level trials (with/without card) and tenant-level generic trials (`trial_ends_at` on tenants table). Plan trial sets `trial_used = true` on tenant activation; generic trial uses `TenantSetting` timezone/locale etc.

5. **Grace period** - Configurable via `BILLING_GRACE_PERIOD_DAYS` (default: 3 days).

6. **Central connection** - All billing models use `CentralConnection` trait. Billing data lives in the central database, not per-tenant databases. This allows cross-tenant billing queries (e.g., subscription expiry reports).

7. **Two subscription middleware layers** - `EnsureSubscribed` allows admin bypass and is used for page-level access; `EnsureTenantHasSubscription` (aliased as `subscription`) strictly gates routes and redirects non-subscribed tenants to billing. Routes in `routes/tenant.php` use `subscription` middleware.

8. **Domain support is configured but inactive** - Domain model registered in `config/tenancy.php`, tenant→domains relationship exists, but tenant resolution is user-based via `InitializeTenancyByUser`. Domain-based resolution requires route changes and SSL configuration.

9. **Subscription expiry is cron-driven** - `ExpireSubscriptions` runs every 6 hours via Laravel scheduler. Trial → Grace → Expired transitions are batch-processed. Uses `--dry-run` for preview. No real-time expiry listeners.

10. **Feature gating is plan-based** - Features are JSON arrays on `billing_plans`. `EnsureTenantHasFeature` middleware gates routes by feature name (e.g., `feature:reports`). `PlanFeatureService` provides programmatic feature checks with limit enforcement.

11. **`EnsureSubscribed` is dead code** - The class exists at `app/Http/Middleware/EnsureSubscribed.php` but is not aliased or referenced in any route definition. The active subscription middleware is `EnsureTenantHasSubscription` (aliased as `subscription`). Keep `EnsureSubscribed` as reference or remove when confident no other code depends on it.
