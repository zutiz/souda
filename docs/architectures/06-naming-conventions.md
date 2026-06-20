# Naming Conventions

## Overview

Consistent, descriptive naming across all layers of the application following Laravel conventions and DDD principles.

## PHP Naming

### Classes

| Type | Convention | Example |
|------|------------|---------|
| **Models** | Singular, PascalCase | `Product`, `Order`, `Subscription` |
| **Controllers** | Singular + `Controller` | `ProductController`, `OrderController` |
| **Services** | Domain + `Service` | `ProductService`, `OrderService`, `SubscriptionService` |
| **Actions** | Verb + Noun | `CreateProduct`, `CancelSubscription`, `SendWelcomeEmail` |
| **DTOs** | Noun + `DTO` | `ProductDTO`, `OrderCreatedDTO`, `SubscriptionDTO` |
| **Events** | Noun + Past Tense | `ProductCreated`, `OrderShipped`, `SubscriptionActivated` |
| **Listeners** | Verb + Noun | `DeductStock`, `SendOrderConfirmation`, `UpdateCustomerActivity` |
| **Jobs** | Verb + Noun | `ProcessPayment`, `GenerateReport`, `SyncInventory` |
| **Requests** | Verb + Noun + `Request` | `StoreProductRequest`, `UpdateOrderRequest` |
| **Resources** | Singular + `Resource` | `ProductResource`, `OrderResource` |
| **Policies** | Model + `Policy` | `ProductPolicy`, `OrderPolicy` |
| **Exceptions** | Noun + `Exception` | `ProductNotFoundException`, `PaymentFailedException` |
| **Enums** | Singular, PascalCase | `OrderStatus`, `PaymentMethod`, `BillingCycle` |
| **Contracts** | Noun (role-based) | `PaymentGateway`, `StockChecker`, `NotificationSender` |
| **Drivers** | Provider + `Driver` | `StripeDriver`, `SSLCommerzDriver` |
| **Repositories** | Model + `Repository` | `ProductRepository`, `OrderRepository` |
| **Middleware** | `Ensure` + Noun | `EnsureSubscribed`, `EnsureTenantHasFeature` |
| **Rules** | Noun + `Rule` | `ValidSKU`, `UniqueEmail` |
| **Mailables** | Noun + descriptive | `OrderShipped`, `PaymentReceived` |
| **Notifications** | Noun + descriptive | `LowStockAlert`, `SubscriptionExpiring` |
| **Observers** | Model + `Observer` | `ProductObserver`, `OrderObserver` |
| **Providers** | Domain + `ServiceProvider` | `BillingServiceProvider`, `ProductServiceProvider` |
| **Traits** | `Has`/`Can` + Noun | `HasFeatures`, `CanExpire`, `LogsActivity` |
| **Factories** | Model + `Factory` | `ProductFactory`, `OrderFactory` |
| **Seeders** | Domain + `Seeder` | `ProductSeeder`, `PlanSeeder` |

### Methods

| Type | Convention | Example |
|------|------------|---------|
| **Query methods** | `get` + Noun | `getActiveProducts()`, `getTenantOrders()` |
| **Boolean methods** | `is`/`has`/`can` + Noun | `isActive()`, `hasSubscription()`, `canAccess()` |
| **Command methods** | Verb + Noun | `createProduct()`, `updateOrder()`, `cancelSubscription()` |
| **Factory methods** | `from` + Source | `fromModel()`, `fromRequest()`, `fromArray()` |
| **Builder scopes** | `scope` + Noun | `scopeActive()`, `scopeForTenant()` |
| **Relationships** | Noun (relation type) | `user()`, `orders()`, `subscription()` |

### Variables

| Type | Convention | Example |
|------|------------|---------|
| **Local variables** | camelCase | `$product`, `$orderTotal`, `$isActive` |
| **Boolean variables** | `is`/`has`/`can` + adjective | `$isAvailable`, `$hasPermission`, `$canEdit` |
| **Collections** | Plural camelCase | `$products`, `$orderItems`, `$subscribers` |
| **DTO instances** | camelCase of DTO type | `$productDTO`, `$orderData` |
| **Service instances** | camelCase of service type | `$productService`, `$billingManager` |

### Constants

| Type | Convention | Example |
|------|------------|---------|
| **Class constants** | UPPER_SNAKE_CASE | `MAX_ATTEMPTS`, `DEFAULT_CURRENCY` |
| **Enum values** | TitleCase | `Active`, `Pending`, `Monthly` |
| **Config keys** | snake_case | `billing.default_gateway`, `queue.retry_after` |

## Database Naming

### Tables

| Type | Convention | Example |
|------|------------|---------|
| **Models** | Plural, snake_case | `products`, `orders`, `order_items` |
| **Pivot tables** | Singular models, alphabetical | `product_category`, `order_product` |
| **Module tables** | Module prefix + plural | `billing_plans`, `billing_subscriptions` |

### Columns

| Type | Convention | Example |
|------|------------|---------|
| **Primary key** | `id` | `id` |
| **Foreign keys** | Singular + `_id` | `product_id`, `order_id`, `tenant_id` |
| **Timestamps** | `_at` suffix | `created_at`, `updated_at`, `deleted_at`, `paid_at` |
| **Boolean columns** | `is_`/`has_`/`can_` prefix | `is_active`, `has_trial`, `can_refund` |
| **JSON columns** | Plural noun | `features`, `limits`, `metadata`, `payload` |
| **Amount columns** | Noun + suffix | `total_amount`, `unit_price`, `discount_amount` |

### Migrations

```
YYYY_MM_DD_HHMMSS_description.php
```

Examples:
- `2026_05_19_000001_create_products_table.php`
- `2026_05_19_000002_add_sku_to_products_table.php`
- `2026_05_19_000003_create_order_items_table.php`

## Frontend Naming

### TypeScript/React

| Type | Convention | Example |
|------|------------|---------|
| **Components** | PascalCase | `ProductList`, `OrderForm`, `SubscriptionCard` |
| **Hooks** | `use` + camelCase | `useProducts()`, `useOrderStatus()`, `useSubscription()` |
| **Types** | PascalCase | `Product`, `Order`, `SubscriptionStatus` |
| **Interfaces** | PascalCase | `ProductProps`, `OrderFormValues` |
| **Utility functions** | camelCase | `formatCurrency()`, `calculateTotal()`, `cn()` |
| **Constants** | UPPER_SNAKE_CASE | `MAX_FILE_SIZE`, `DEFAULT_PAGE_SIZE` |
| **Variables** | camelCase | `productList`, `orderTotal`, `isActive` |

### File Naming

| Type | Convention | Example |
|------|------------|---------|
| **Components** | kebab-case | `product-list.tsx`, `order-form.tsx` |
| **Pages** | kebab-case | `product-list.tsx`, `create-order.tsx` |
| **Hooks** | camelCase | `use-products.ts`, `use-order-status.ts` |
| **Types** | camelCase | `product.ts`, `order.ts` |
| **Utils** | camelCase | `format-currency.ts`, `calculate-total.ts` |

### Inertia Pages

Mirror route structure:

```
resources/js/pages/
├── products/
│   ├── index.tsx
│   ├── create.tsx
│   ├── show.tsx
│   └── edit.tsx
├── orders/
│   ├── index.tsx
│   ├── create.tsx
│   └── show.tsx
└── billing/
    ├── index.tsx
    └── invoices.tsx
```

## Route Naming

### Named Routes

| Pattern | Convention | Example |
|---------|------------|---------|
| **Index** | `{resource}.index` | `products.index`, `orders.index` |
| **Create** | `{resource}.create` | `products.create`, `orders.create` |
| **Store** | `{resource}.store` | `products.store`, `orders.store` |
| **Show** | `{resource}.show` | `products.show`, `orders.show` |
| **Edit** | `{resource}.edit` | `products.edit`, `orders.edit` |
| **Update** | `{resource}.update` | `products.update`, `orders.update` |
| **Destroy** | `{resource}.destroy` | `products.destroy`, `orders.destroy` |
| **Nested** | `{parent}.{child}.{action}` | `products.variants.index`, `orders.items.store` |

### API Routes

| Method | Route | Name |
|--------|-------|------|
| GET | `/api/products` | `api.products.index` |
| POST | `/api/products` | `api.products.store` |
| GET | `/api/products/{product}` | `api.products.show` |
| PUT | `/api/products/{product}` | `api.products.update` |
| DELETE | `/api/products/{product}` | `api.products.destroy` |

## Event Naming

### Domain Events

| Pattern | Example |
|---------|---------|
| `{Entity}{PastAction}` | `ProductCreated`, `OrderShipped`, `SubscriptionCancelled` |
| `{Entity}{StateChange}` | `StockDepleted`, `PaymentCompleted`, `TrialExpired` |

### Event Payloads

Events should carry DTOs:

```php
class OrderCreated
{
    public function __construct(
        public OrderCreatedDTO $order
    ) {}
}
```

## Module Naming

### Module Directories

| Module | Directory | Description |
|--------|-----------|-------------|
| Billing | `app/Modules/Billing/` | Subscription and payment processing |
| Product | `app/Modules/Product/` | Product catalog management (incl. inventory, stock, categories) |
| Orders | `app/Modules/Orders/` | Order processing and management |
| CRM | `app/Modules/CRM/` | Customer relationship management |

### Module Internal Structure

All modules follow the same directory structure (see `02-folder-structure.md`).

## Anti-Patterns (Prohibited)

```php
// ✗ WRONG: Abbreviated names
class Prod {}
class OrdCtrl {}
function getCust() {}

// ✗ WRONG: Hungarian notation
string $strName;
array $arrProducts;
bool $boolIsActive;

// ✗ WRONG: Inconsistent casing
class productController {}
function create_order() {}

// ✗ WRONG: Vague names
class Handler {}
function process() {}
$data = [...];

// ✗ WRONG: Model names that don't match table
class ProductOrder {} // Table: product_orders (should be ProductOrder for pivot model)

// ✗ WRONG: Event names that aren't past tense
class CreateProduct {} // Should be ProductCreated
class OrderShip {}     // Should be OrderShipped
```

## Quick Reference

| Context | Convention | Example |
|---------|------------|---------|
| PHP Class | PascalCase | `ProductService` |
| PHP Method | camelCase | `createProduct()` |
| PHP Variable | camelCase | `$productList` |
| PHP Constant | UPPER_SNAKE_CASE | `MAX_PRODUCTS` |
| PHP Enum Value | TitleCase | `Active` |
| Database Table | plural_snake_case | `products` |
| Database Column | snake_case | `product_id` |
| Database Pivot | singular_singular | `product_category` |
| Migration | timestamp_description | `2026_05_19_000001_create_products_table.php` |
| React Component | PascalCase | `ProductList` |
| React Hook | camelCase | `useProducts()` |
| TypeScript Type | PascalCase | `Product` |
| TypeScript File | kebab-case | `product-list.tsx` |
| Route Name | dot.case | `products.index` |
| Config Key | dot.case | `billing.default_gateway` |
| Environment Var | UPPER_SNAKE_CASE | `BILLING_DEFAULT_GATEWAY` |
