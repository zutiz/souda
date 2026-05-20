# Event/Listener Strategy

## Overview

Event-driven architecture for decoupled module communication, side effects, and asynchronous processing. Events represent domain facts; listeners react to those facts.

## Event Categories

### 1. Domain Events

Represent significant business occurrences within a module.

| Module | Events | Purpose |
|--------|--------|---------|
| **Billing** | `SubscriptionActivated`, `SubscriptionCancelled`, `SubscriptionExpired`, `PaymentReceived`, `PaymentFailed`, `SeatAllocated`, `SeatReleased`, `SeatOverageInvoiced` | Subscription lifecycle + seat management |
| **Products** | `ProductCreated`, `ProductUpdated`, `ProductDeleted`, `ProductArchived` | Product catalog changes |
| **Orders** | `OrderCreated`, `OrderPaid`, `OrderShipped`, `OrderDelivered`, `OrderCancelled`, `OrderRefunded` | Order lifecycle |
| **Inventory** | `StockUpdated`, `StockDepleted`, `StockAdjusted`, `LowStockAlert` | Inventory changes |
| **CRM** | `ContactCreated`, `ContactUpdated`, `InteractionLogged`, `DealWon`, `DealLost` | Customer activity |

### 2. System Events

Platform-level events from Laravel or packages.

| Source | Events | Purpose |
|--------|--------|---------|
| **Tenancy** | `TenantCreated`, `TenantDeleted`, `TenancyInitialized`, `TenancyEnded` | Tenant lifecycle |
| **Auth** | `Login`, `Logout`, `Registered`, `PasswordReset` | Authentication events |
| **Queue** | `JobProcessed`, `JobFailed`, `JobPopped` | Queue lifecycle |

### 3. Integration Events

Events that bridge external systems.

| Source | Events | Purpose |
|--------|--------|---------|
| **Stripe** | `WebhookReceived` (Cashier) | Stripe webhook events |
| **SSLCommerz** | `PaymentVerified`, `PaymentFailed` | Gateway callbacks |

## Event Structure

### Event Class

```php
readonly class OrderCreated
{
    public function __construct(
        public OrderCreatedDTO $order,
        public CarbonImmutable $occurredAt = new CarbonImmutable(),
    ) {}

    public static function fromModel(Order $order): self
    {
        return new self(
            order: OrderCreatedDTO::fromModel($order),
        );
    }
}
```

### Rules

1. **Events are readonly** - Immutable facts, cannot be modified
2. **Events carry DTOs** - Never pass Eloquent models
3. **Events include timestamp** - `occurredAt` for audit trails
4. **Events have `fromModel()` factory** - Easy creation from models
5. **Events are past tense** - `OrderCreated`, not `CreateOrder`

## Listener Structure

### Listener Class

```php
class DeductStock implements ShouldQueue
{
    public $queue = 'high';
    public $tries = 3;
    public $timeout = 60;

    public function __construct(
        protected InventoryService $inventory,
    ) {}

    public function handle(OrderCreated $event): void
    {
        foreach ($event->order->lineItems as $item) {
            $this->inventory->deduct(
                productId: $item->productId,
                quantity: $item->quantity,
                referenceId: $event->order->orderId,
            );
        }
    }

    public function failed(OrderCreated $event, Throwable $exception): void
    {
        Log::error('Stock deduction failed', [
            'order_id' => $event->order->orderId,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

### Rules

1. **Listeners are single-responsibility** - One listener, one action
2. **Listeners should be queued** - Unless immediate execution is critical
3. **Listeners are idempotent** - Safe to retry on failure
4. **Listeners implement `failed()`** - Handle failures gracefully
5. **Listeners don't dispatch events** - Avoid event cascades

## Event Registration

### EventServiceProvider (Traditional)

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    // Billing events
    \App\Modules\Billing\Events\SubscriptionActivated::class => [
        \App\Modules\Billing\Listeners\SendSubscriptionNotification::class,
    ],
    \App\Modules\Billing\Events\SubscriptionCancelled::class => [
        \App\Modules\Billing\Listeners\SendSubscriptionNotification::class,
    ],
    \App\Modules\Billing\Events\PaymentReceived::class => [
        \App\Modules\Billing\Listeners\SendSubscriptionNotification::class,
    ],
    \App\Modules\Billing\Events\PaymentFailed::class => [
        \App\Modules\Billing\Listeners\SendSubscriptionNotification::class,
    ],

    // Product events
    \App\Modules\Products\Events\ProductCreated::class => [
        \App\Modules\Products\Listeners\IndexProductForSearch::class,
        \App\Modules\Products\Listeners\GenerateProductSKU::class,
    ],

    // Order events
    \App\Modules\Orders\Events\OrderCreated::class => [
        \App\Modules\Inventory\Listeners\DeductStock::class,
        \App\Modules\CRM\Listeners\UpdateCustomerActivity::class,
        \App\Modules\Notifications\Listeners\SendOrderConfirmation::class,
    ],
    \App\Modules\Orders\Events\OrderPaid::class => [
        \App\Modules\Billing\Listeners\RecordRevenue::class,
        \App\Modules\Notifications\Listeners\SendPaymentReceipt::class,
    ],

    // Inventory events
    \App\Modules\Inventory\Events\StockDepleted::class => [
        \App\Modules\Products\Listeners\MarkProductUnavailable::class,
        \App\Modules\Notifications\Listeners\SendLowStockAlert::class,
    ],

    // Tenant events
    \Stancl\Tenancy\Events\TenantCreated::class => [
        \App\Listeners\SetupTenantDefaults::class,
    ],
];
```

### Attribute-Based Registration (PHP 8+)

```php
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Events\Attributes\AsEventListener;

#[AsEventListener(event: OrderCreated::class)]
class DeductStock implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        // ...
    }
}
```

### Module Service Provider Registration

```php
// app/Providers/BillingServiceProvider.php
public function boot(): void
{
    Event::listen(SubscriptionActivated::class, SendSubscriptionNotification::class);
    Event::listen(SubscriptionCancelled::class, SendSubscriptionNotification::class);
    Event::listen(PaymentReceived::class, SendSubscriptionNotification::class);
    Event::listen(PaymentFailed::class, SendSubscriptionNotification::class);

    // Seat events
    Event::listen(SeatAllocated::class, RecalculateSeatUsage::class);
    Event::listen(SeatReleased::class, RecalculateSeatUsage::class);
}
```

> **Project Convention:** This project uses method-based event registration in service providers rather than the `$listen` array pattern.
> - `TenancyServiceProvider::events()` returns an event map for tenancy events
> - `BillingServiceProvider::registerEventListeners()` uses `$events->listen()` with method array syntax: `[$listener, 'handleMethodName']`
> - Both approaches are valid; the project convention prefers method-based registration for explicit listener method resolution.

## Event Dispatching

### From Services

```php
class OrderService
{
    public function __construct(
        protected EventDispatcher $events,
    ) {}

    public function createOrder(CreateOrderDTO $dto): Order
    {
        $order = Order::create($dto->toArray());

        $this->events->dispatch(OrderCreated::fromModel($order));

        return $order;
    }
}
```

### From Models (Observers)

```php
class OrderObserver
{
    public function created(Order $order): void
    {
        OrderCreated::dispatch($order);
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            OrderStatusChanged::fromModel($order);
        }
    }
}
```

### From Actions

```php
class CreateOrder
{
    public function handle(CreateOrderDTO $dto): OrderDTO
    {
        $order = Order::create($dto->toArray());

        OrderCreated::fromModel($order);

        return OrderDTO::fromModel($order);
    }
}
```

## Queue Priority for Listeners

| Priority | Queue | Listeners |
|----------|-------|-----------|
| **Critical** | `critical` | Payment processing, subscription changes |
| **High** | `high` | Inventory updates, stock deductions |
| **Default** | `default` | Email notifications, CRM updates |
| **Low** | `low` | Analytics, logging, cleanup |

```php
class SendOrderConfirmation implements ShouldQueue
{
    public $queue = 'default';
    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function handle(OrderCreated $event): void
    {
        Mail::to($event->order->customerEmail)
            ->send(new OrderConfirmationMail($event->order));
    }
}
```

## Event Flow Examples

### Order Creation Flow

```
User submits order
    │
    ▼
OrderController::store()
    │
    ▼
CreateOrder action
    │
    ├── Creates Order model
    │
    ▼
OrderCreated event dispatched
    │
    ├──► DeductStock listener (high queue)
    │       └── Deducts inventory for each line item
    │
    ├──► UpdateCustomerActivity listener (default queue)
    │       └── Logs order in CRM timeline
    │
    └──► SendOrderConfirmation listener (default queue)
            └── Sends confirmation email to customer
```

### Payment Processing Flow

```
SSLCommerz callback
    │
    ▼
BillingController::sslcommerzWebhook()
    │
    ▼
SubscriptionService::verifyAndActivate()
    │
    ├── Verifies payment via gateway API
    ├── Marks payment as Completed
    │
    ▼
PaymentReceived event dispatched
    │
    └──► SendSubscriptionNotification listener (default queue)
            └── Sends payment receipt email
    │
    ▼
SubscriptionActivated event dispatched
    │
    ├──► SendSubscriptionNotification listener (default queue)
    │       └── Sends subscription activation email
    │
    └──► RecordRevenue listener (high queue)
            └── Updates revenue tracking
```

### Seat Allocation Flow

```
Admin invites team member
    │
    ▼
TeamController::invite() with EnsureSeatAvailable middleware
    │
    ▼
SeatService::allocateSeat()
    │
    ├── Creates SeatAllocation with status Pending
    │
    ▼
SeatAllocated event dispatched
    │
    └──► RecalculateSeatUsage listener (sync)
            └── Logs seat count, triggers overage invoice if over limit

User accepts invitation
    │
    ▼
SeatService::activatePendingSeat() → status becomes Active
```

## Event Discovery & Debugging

### List Registered Events

```bash
php artisan event:list
```

### Test Event Dispatch

```php
Event::fake();

// Trigger action that should dispatch event
$this->post(route('orders.store'), [...]);

Event::assertDispatched(OrderCreated::class);
Event::assertDispatched(OrderCreated::class, function ($event) use ($order) {
    return $event->order->orderId === $order->id;
});
```

### Event Subscriber Pattern

For modules with many related listeners:

```php
class ProductEventSubscriber
{
    public function handleProductCreated(ProductCreated $event): void
    {
        // Handle product created
    }

    public function handleProductUpdated(ProductUpdated $event): void
    {
        // Handle product updated
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            ProductCreated::class,
            [ProductEventSubscriber::class, 'handleProductCreated']
        );
        $events->listen(
            ProductUpdated::class,
            [ProductEventSubscriber::class, 'handleProductUpdated']
        );
    }
}

// Register in service provider
Event::subscribe(ProductEventSubscriber::class);
```

## Best Practices

1. **Events are facts, not commands** - `OrderCreated`, not `CreateOrder`
2. **Listeners should be independent** - Order doesn't matter between listeners
3. **Use DTOs in events** - Never pass Eloquent models
4. **Queue non-critical listeners** - Keep request response fast
5. **Implement `failed()` on listeners** - Handle failures gracefully
6. **Use event locking for idempotency** - Prevent duplicate processing
7. **Document event payloads** - Clear DTO definitions
8. **Test event dispatching** - Verify events are fired correctly
9. **Avoid event cascades** - Listeners shouldn't dispatch more events
10. **Use `afterCommit` for DB-dependent events** - Prevent race conditions

```php
// In config/queue.php
'after_commit' => true, // Dispatch events after DB transaction commits
```
