# Module Communication Strategy

## Overview

Modules are bounded contexts that should remain loosely coupled. Direct model access between modules is prohibited. All cross-module communication follows defined patterns.

## Communication Patterns

### 1. Domain Events (Primary)

**Use for:** Cross-module notifications where the sender doesn't need a response.

```
Module A (Publisher)          Module B (Subscriber)
├── Dispatches Event          ├── Listens to Event
├── Doesn't know about B      ├── Handles side effects
└── Continues immediately     └── Can be sync or async (queued)
```

**Rules:**
- Events live in the publishing module's `Events/` directory
- Listeners live in the subscribing module's `Listeners/` directory
- Events carry DTOs, never models
- Listeners should be queued for non-critical operations
- Use `ShouldBroadcast` only for real-time UI updates

**Example Flow:**
```
OrderModule                          InventoryModule
    │                                    │
    ├── OrderCreated (event) ───────────►│
    │                                    ├── DeductStock (listener)
    │                                    ├── LowStockAlert (event)
    │                                    │
CRMModule                          NotificationModule
    │                                    │
    ├── OrderCreated (same event) ──────►│
    │                                    ├── SendOrderConfirmation (listener)
```

### 2. Service Contracts (Query Pattern)

**Use for:** When Module A needs data from Module B synchronously.

```php
// Module B defines contract
// app/Modules/Billing/Contracts/SubscriptionChecker.php
interface SubscriptionChecker
{
    public function hasAccessibleSubscription(int $tenantId): bool;
    public function getActivePlanFeatures(int $tenantId): array;
}

// Module B implements
// app/Modules/Billing/Services/BillingSubscriptionChecker.php
class BillingSubscriptionChecker implements SubscriptionChecker { }

// Container binding in BillingServiceProvider
$this->app->bind(SubscriptionChecker::class, BillingSubscriptionChecker::class);

// Module A consumes via DI
class FeatureGateMiddleware
{
    public function __construct(
        protected SubscriptionChecker $subscriptionChecker
    ) {}
}
```

**Rules:**
- Contracts live in the providing module's `Contracts/` directory
- Implementations live in the providing module's `Services/` directory
- Bind in the providing module's service provider
- Consumers depend on the contract, never the implementation
- Keep contracts small and focused (Interface Segregation)

### 3. Action Classes (Command Pattern)

**Use for:** Cross-module operations that need to be triggered explicitly.

```php
// Action defined in target module
// app/Modules/Orders/Actions/CreateOrder.php
class CreateOrder
{
    public function handle(CreateOrderDTO $dto): OrderDTO
    {
        // Creates order, dispatches OrderCreated event
    }
}

// Called from another module
class CheckoutService
{
    public function __construct(
        protected CreateOrder $createOrder
    ) {}

    public function process(CheckoutDTO $dto): void
    {
        $order = $this->createOrder->handle($dto->toCreateOrderDTO());
    }
}
```

**Rules:**
- Actions are single-responsibility, callable classes
- Accept DTOs, return DTOs
- Actions can dispatch domain events
- Actions are resolved via container for DI

### 4. DTOs (Data Transfer)

**Use for:** All data crossing module boundaries.

```php
// app/Modules/Orders/DTOs/OrderCreatedDTO.php
readonly class OrderCreatedDTO
{
    public function __construct(
        public int $orderId,
        public int $tenantId,
        public string $status,
        public int $totalAmount,
        public array $lineItems,
        public CarbonImmutable $createdAt,
    ) {}

    public static function fromModel(Order $order): self
    {
        return new self(
            orderId: $order->id,
            tenantId: $order->tenant_id,
            status: $order->status->value,
            totalAmount: $order->total_amount,
            lineItems: $order->items->map(fn($i) => [...])->toArray(),
            createdAt: $order->created_at,
        );
    }
}
```

**Rules:**
- DTOs are `readonly` classes (PHP 8.2+)
- Have `fromModel()` factory methods
- Never expose Eloquent models across modules
- Include only data the consumer needs

## Communication Matrix

| From → To | Pattern | Example |
|-----------|---------|---------|
| Orders → Inventory | Domain Event | `OrderCreated` → `DeductStock` |
| Orders → Billing | Domain Event | `OrderPaid` → `RecordRevenue` |
| Orders → CRM | Domain Event | `OrderCreated` → `UpdateCustomerActivity` |
| Inventory → Orders | Domain Event | `StockDepleted` → `MarkProductUnavailable` |
| Inventory → Notifications | Domain Event | `LowStockAlert` → `SendLowStockEmail` |
| Billing → All | Domain Event | `SubscriptionCancelled` → `RestrictAccess` |
| Any → Billing | Service Contract | `SubscriptionChecker::hasAccessible()` |
| Any → Products | Service Contract | `ProductResolver::getProduct()` |
| Any → Orders | Action Class | `CreateOrder::handle()` |

## Anti-Patterns (Prohibited)

```php
// ✗ WRONG: Direct model access across modules
use App\Modules\Orders\Models\Order;

class InventoryService
{
    public function checkStock(int $orderId): void
    {
        $order = Order::find($orderId); // Direct cross-module model access
    }
}

// ✗ WRONG: Tight coupling to implementation
class FeatureGate
{
    public function __construct(
        protected BillingSubscriptionChecker $checker // Concrete class
    ) {}
}

// ✗ WRONG: Passing models across module boundaries
class OrderCreated
{
    public function __construct(
        public Order $order // Eloquent model in event
    ) {}
}

// ✓ CORRECT: Use DTOs
class OrderCreated
{
    public function __construct(
        public OrderCreatedDTO $order // DTO in event
    ) {}
}
```

## Event Discovery & Registration

### Registration in Service Providers

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    // Orders module events
    \App\Modules\Orders\Events\OrderCreated::class => [
        \App\Modules\Inventory\Listeners\DeductStock::class,
        \App\Modules\CRM\Listeners\UpdateCustomerActivity::class,
        \App\Modules\Notifications\Listeners\SendOrderConfirmation::class,
    ],

    // Inventory module events
    \App\Modules\Inventory\Events\StockDepleted::class => [
        \App\Modules\Orders\Listeners\MarkProductUnavailable::class,
        \App\Modules\Notifications\Listeners\SendLowStockAlert::class,
    ],

    // Billing module events
    \App\Modules\Billing\Events\SubscriptionActivated::class => [
        \App\Modules\Billing\Listeners\SendSubscriptionNotification::class,
    ],
];
```

### Auto-Discovery via `#[AsEventListener]` Attribute

```php
#[AsEventListener(event: OrderCreated::class)]
class DeductStock
{
    public function handle(OrderCreated $event): void
    {
        // ...
    }
}
```

## Synchronous vs Asynchronous

| Pattern | Default | Override When |
|---------|---------|---------------|
| Domain Events | Synchronous | Listener implements `ShouldQueue` |
| Service Contracts | Synchronous | Always sync (query pattern) |
| Action Classes | Synchronous | Action dispatches queued job internally |

### Queue Priority for Listeners

| Priority | Queue | Use Case |
|----------|-------|----------|
| `critical` | `critical` | Subscription changes, payment processing |
| `high` | `high` | Order creation, inventory updates |
| `default` | `default` | Notifications, emails |
| `low` | `low` | Analytics, logging, cleanup |

```php
class SendOrderConfirmation implements ShouldQueue
{
    public $queue = 'default';

    public function handle(OrderCreated $event): void
    {
        // Send email
    }
}
```

## Module Dependency Graph

```
                    ┌──────────┐
                    │  Billing │
                    └────┬─────┘
                         │ (provides subscription/feature checks)
                         ▼
┌──────────┐      ┌──────────┐      ┌──────────────┐
│ Products │─────►│  Orders  │─────►│  Inventory   │
└──────────┘      └────┬─────┘      └──────┬───────┘
                       │                   │
                       ▼                   ▼
                 ┌──────────┐      ┌──────────────┐
                 │   CRM    │◄─────│ Notifications│
                 └──────────┘      └──────────────┘

Legend:
────►  Depends on (service contract)
◄────  Listens to (domain event)
```

## Best Practices

1. **Never import models from another module** - Use events or contracts
2. **Events are fire-and-forget** - Sender should not depend on listener success
3. **Listeners should be idempotent** - Safe to retry on failure
4. **Use DTOs for all cross-module data** - Never pass Eloquent models
5. **Contracts define the boundary** - Implementation can change without affecting consumers
6. **Prefer events over contracts** - Events create looser coupling
7. **Document module dependencies** - Keep the dependency graph updated
8. **Test module boundaries** - Write integration tests that verify contracts
