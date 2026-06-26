# Billing Module

This application has **two billing systems**: a custom billing module (primary) and Laravel Cashier (Stripe mirror).

## Custom Billing Module (Primary)

The custom module is gateway-agnostic and handles all frontend billing flows. All billing data lives in the **central database** via `CentralConnection`.

### Plans & Subscriptions

- Plans (`billing_plans`) define pricing, features, limits, and seat configuration.
- Subscriptions (`billing_subscriptions`) track tenant status (trial/active/grace/expired/cancelled).
- Multiple gateways supported: Stripe, SSLCommerz, bKash, Nagad, PortWallet, Manual.

### Seat-Based Pricing

Plans support a `pricing_model` column (`flat`, `per_seat`, `tiered`, `usage_based`). The **strategy pattern** (`PricingStrategy` interface) allows extensible pricing calculations:

- `SeatPricingStrategy` — counts billable seats, calculates overage beyond `default_seats`
- `FlatPricingStrategy` — no seat tracking, zero overage

Seat allocations (`billing_seat_allocations`) track who consumes a seat:

| Status | Meaning |
|--------|---------|
| `Pending` | Invitation sent, not yet accepted (reserves seat) |
| `Active` | User accepted and occupies the seat |
| `Released` | Seat freed (user removed or invitation cancelled) |

Team management routes (`/team/*`) are gated by `subscription` middleware and the invite route is gated by `EnsureSeatAvailable` (aliased `seat`), which throws `SeatLimitExceededException` when `max_seats` is reached.

### Core Flow

1. Authenticated users open `/billing`.
2. The app reads plans from `billing_plans` and checks existing subscription.
3. Subscribe via `POST /billing/subscribe` with plan, gateway, billing cycle.
4. User completes payment via gateway redirect.
5. Webhook/callback verifies payment and activates subscription.

### Subscription Expiry

`php artisan subscription:expire-expired` (scheduled every 6 hours):

```
Active/Trial → Grace (3 day default) → Expired
```

## Laravel Cashier (Stripe Mirror)

Secondary system that syncs Stripe products/prices into local `plans` and `plan_prices` tables.

### Admin Pricing

Admins manage products/prices at `/admin/pricing`:

- Create/update/archive products (dual-written to Stripe + local DB)
- Create/update/deactivate prices
- Reorder products, manage metadata features/CTA/trial fields

### Webhook Sync

`StripeEventListener` handles product, price, and subscription webhook events.

## Useful Commands

```bash
php artisan stripe:import
php artisan subscription:expire-expired
php artisan subscription:expire-expired --dry-run
stripe listen --forward-to https://saasforgekit.test/stripe/webhook
```

## Key Middleware

| Alias | Middleware | Purpose |
|-------|-----------|---------|
| `subscription` | `EnsureTenantHasSubscription` | Gates routes requiring active subscription |
| `feature:{name}` | `EnsureTenantHasFeature` | Gates routes by plan feature |
| `seat` | `EnsureSeatAvailable` | Gates team invite routes by max_seats |
