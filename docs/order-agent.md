# SOUDA — Order Module Architecture Guide

This file is for AI coding agents implementing the Order module. It defines the complete architecture, conventions, guardrails, patterns, interfaces, and implementation rules for the SOUDA Order Engine.

---

## 1. Project Scope & Order Philosophy

### Vision

SOUDA's Order module is not a sales tracker — it is an **Order Processing Engine** that manages the full order lifecycle from creation through fulfillment. Every order (whether from POS, online, wholesale, or manual entry) flows through a unified status workflow, generates domain events consumed by Inventory and CRM, and provides complete audit trails for financial reporting.

### Golden Rules

> **1. No module ever writes stock directly.** The Order module publishes `OrderCreated`, `OrderCancelled`, and `OrderRefunded` events. Only the Inventory Engine translates those into stock movements.

> **2. Orders are store-scoped.** Every order belongs to exactly one Store. The store context is resolved by `InitializeStoreContext` middleware before the order controller is reached.

> **3. Monetary values are in cents.** All prices, totals, discounts, and taxes use integer `bigInteger` columns — never floats or decimals for money.

### Target Audience

SME business owners in 15+ industries who need to create, manage, and fulfill orders across physical storefronts (POS), online channels, and wholesale accounts.

### Scope — Phase 2

| Feature | Included |
|---------|----------|
| Order CRUD (create, view, update, cancel) | ✅ |
| Order status workflow (6 states) | ✅ |
| Order line items with product/variant resolution | ✅ |
| Order numbering (configurable format) | ✅ |
| Order notes and activity log | ✅ |
| Order search and filtering | ✅ |
| Bulk order actions | ✅ |
| Order addresses (shipping + billing) | ✅ |
| Payment tracking (multi-payment per order) | ✅ |
| Partial and full refunds | ✅ |
| Invoice generation (PDF) | ✅ |
| Packing slip / receipt printing | ✅ |
| Order export (CSV, PDF) | ✅ |
| Cross-module events (Inventory, CRM) | ✅ |
| Multi-store scoping | ✅ |
| Industry Pack integration | ✅ |
| Order status history / audit trail | ✅ |
| Courier integration (Pathao, Steadfast, RedX, Sendo, Paperfly) | ✅ |
| Shipment tracking with delivery status | ✅ |
| Multiple shipments per order (partial fulfillment) | ✅ |
| Shipping rules & rate calculation | ✅ |
| Delivery attempt tracking | ✅ |
| Courier label generation | ✅ |
| Customer-facing tracking page | ✅ |
| Advanced pricing (discounts, tiered) | Phase 3 |
| Recurring orders | Phase 3 |
| Order approval workflows | Phase 3 |

---

## 2. Core Architectural Principles

### Principle 1: Event-Driven Cross-Module Communication

```
Order Module                Inventory Module              CRM Module
    │                            │                           │
    ├── OrderCreated ───────────►│                           │
    │                            ├── DeductInventoryStock    │
    │                            │                           │
    ├── OrderCreated ────────────┼──────────►                │
    │                            │          ├── UpdateCustomerActivity
    │                            │                           │
    ├── OrderCancelled ─────────►│                           │
    │                   RestoreInventoryStock                │
    │                            │                           │
    ├── OrderRefunded ──────────►│                           │
    │                RestoreInventoryOnRefund                │
```

### Principle 2: Immutable Audit Trail

Once an order status transitions to a terminal state (Delivered, Cancelled, Refunded), the record is read-only. Corrections use refunds and credit notes — never direct edits.

### Principle 3: Store Isolation

All order queries are scoped to the current store via `StoreContextManager`. The `store_id` column is set automatically on order creation from the middleware context.

### Principle 4: Idempotent Event Handling

Order events can be replayed without duplicating stock deductions. Each listener uses idempotency keys from `EventEnvelope::idempotencyKey()` to guard against duplicate processing.

---

## 3. Module Structure

```
app/Modules/Order/
├── Actions/
│   └── FulfillOrder.php
├── Console/
│   └── Commands/
│       ├── ExpirePendingOrders.php
│       ├── SyncShipmentTracking.php
│       └── RetryFailedShipments.php
├── Contracts/
│   └── Courier/
│       ├── CourierProvider.php
│       ├── CourierManager.php
│       ├── ShipmentData.php
│       └── TrackingResult.php
├── Database/
│   ├── Factories/
│   │   ├── OrderFactory.php
│   │   ├── OrderItemFactory.php
│   │   ├── OrderAddressFactory.php
│   │   ├── OrderPaymentFactory.php
│   │   ├── OrderRefundFactory.php
│   │   ├── ShipmentFactory.php
│   │   └── DeliveryAttemptFactory.php
│   └── Migrations/
│       └── Tenant/
│           ├── 2026_07_20_000001_create_orders_table.php
│           ├── 2026_07_20_000002_create_order_items_table.php
│           ├── 2026_07_20_000003_create_order_addresses_table.php
│           ├── 2026_07_20_000004_create_order_payments_table.php
│           ├── 2026_07_20_000005_create_order_refunds_table.php
│           ├── 2026_07_20_000006_create_order_status_history_table.php
│           ├── 2026_07_20_000007_create_shipments_table.php
│           ├── 2026_07_20_000008_create_shipment_items_table.php
│           ├── 2026_07_20_000009_create_delivery_attempts_table.php
│           └── 2026_07_20_000010_create_shipping_rates_table.php
├── DTOs/
│   ├── OrderDTO.php              (existing)
│   ├── LineItemDTO.php           (existing)
│   ├── OrderAddressDTO.php       (existing)
│   ├── OrderPaymentDTO.php       (new)
│   ├── OrderRefundDTO.php        (new)
│   ├── CreateOrderDTO.php        (new)
│   ├── OrderSummaryDTO.php       (new)
│   ├── ShipmentDTO.php           (new)
│   └── ShippingRateDTO.php       (new)
├── Enums/
│   ├── OrderStatusEnum.php
│   ├── PaymentStatusEnum.php
│   ├── FulfillmentStatusEnum.php
│   ├── OrderTypeEnum.php
│   └── ShipmentStatusEnum.php
├── Events/
│   ├── OrderCreated.php          (existing)
│   ├── OrderCancelled.php        (existing)
│   ├── OrderRefunded.php         (existing)
│   ├── OrderConfirmed.php
│   ├── OrderShipped.php
│   ├── OrderDelivered.php
│   ├── OrderPaymentReceived.php
│   ├── OrderPaymentRefunded.php
│   ├── OrderStatusChanged.php
│   ├── ShipmentCreated.php
│   ├── ShipmentPickedUp.php
│   ├── ShipmentInTransit.php
│   ├── ShipmentOutForDelivery.php
│   ├── ShipmentDelivered.php
│   ├── ShipmentDeliveryFailed.php
│   └── ShipmentReturnedToSender.php
├── Exceptions/
│   ├── InvalidOrderStatusTransitionException.php
│   ├── OrderNotFoundException.php
│   ├── OrderItemNotFoundException.php
│   ├── OrderRefundExceedsTotalException.php
│   ├── CourierException.php
│   ├── CourierNotAvailableException.php
│   └── ShipmentCreationFailedException.php
├── Http/
│   ├── Controllers/
│   │   ├── OrderController.php
│   │   ├── ShipmentController.php
│   │   └── TrackingController.php
│   └── Requests/
│       ├── StoreOrderRequest.php
│       ├── UpdateOrderRequest.php
│       ├── CancelOrderRequest.php
│       ├── RefundOrderRequest.php
│       ├── CreateShipmentRequest.php
│       └── UpdateShippingRateRequest.php
├── Integration/
│   └── Courier/
│       ├── PathaoCourier.php
│       ├── SteadfastCourier.php
│       ├── RedXCourier.php
│       ├── SendoCourier.php
│       ├── PaperflyCourier.php
│       └── WebhookCourier.php
├── Listeners/
│   ├── MarkProductUnavailable.php
│   ├── SyncOrderStatusWithInventory.php
│   └── NotifyCustomerOnShipmentUpdate.php
├── Models/
│   ├── Order.php
│   ├── OrderItem.php
│   ├── OrderAddress.php
│   ├── OrderPayment.php
│   ├── OrderRefund.php
│   ├── OrderStatusHistory.php
│   ├── Shipment.php
│   ├── ShipmentItem.php
│   ├── DeliveryAttempt.php
│   └── ShippingRate.php
├── Observers/
│   ├── OrderObserver.php
│   └── ShipmentObserver.php
├── Policies/
│   ├── OrderPolicy.php
│   └── ShipmentPolicy.php
├── Providers/
│   ├── CourierServiceProvider.php
│   └── OrderServiceProvider.php
└── Services/
    ├── OrderService.php
    ├── OrderPaymentService.php
    ├── OrderNumberGenerator.php
    ├── FulfillmentService.php
    ├── ShipmentService.php
    ├── CourierManager.php
    └── ShippingRateCalculator.php
```

---

## 4. Models

### Order

```php
// app/Modules/Order/Models/Order.php

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'store_id', 'customer_id', 'order_number',
        'status', 'payment_status', 'fulfillment_status', 'order_type',
        'subtotal', 'tax_total', 'discount_total', 'grand_total',
        'currency', 'exchange_rate',
        'payment_method',
        'customer_name', 'customer_email', 'customer_phone',
        'notes', 'internal_notes',
        'metadata', 'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'subtotal' => 'integer',
            'tax_total' => 'integer',
            'discount_total' => 'integer',
            'grand_total' => 'integer',
            'exchange_rate' => 'decimal:6',
            'metadata' => 'array',
            'placed_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(OrderRefund::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeForStore(Builder $query, string $storeId): void
    {
        $query->where('store_id', $storeId);
    }

    public function scopeByStatus(Builder $query, OrderStatusEnum $status): void
    {
        $query->where('status', $status->value);
    }

    public function scopeRecent(Builder $query, int $days = 30): void
    {
        $query->where('placed_at', '>=', now()->subDays($days));
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (! $order->id) {
                $order->id = (string) Str::ulid();
            }
            if (! $order->order_number) {
                $order->order_number = app(OrderNumberGenerator::class)->generate();
            }
            if (! $order->placed_at) {
                $order->placed_at = now();
            }
        });
    }
}
```

### OrderItem

```php
class OrderItem extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_id', 'product_id', 'variant_id',
        'name', 'sku', 'barcode',
        'quantity', 'unit_price', 'total_price',
        'tax_amount', 'discount_amount',
        'warehouse_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'total_price' => 'integer',
            'tax_amount' => 'integer',
            'discount_amount' => 'integer',
            'metadata' => 'array',
        ];
    }
}
```

### OrderAddress

```php
class OrderAddress extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_id', 'type',
        'name', 'phone', 'email',
        'address_line_1', 'address_line_2',
        'city', 'state', 'postal_code', 'country',
    ];

    protected function casts(): array
    {
        return ['id' => 'string'];
    }
}
```

### OrderPayment

```php
class OrderPayment extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'reference',
        'gateway', 'gateway_transaction_id',
        'status',
        'paid_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'amount' => 'integer',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
```

### OrderRefund

```php
class OrderRefund extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'reason',
        'amount',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'amount' => 'integer',
            'metadata' => 'array',
        ];
    }
}
```

### OrderStatusHistory

```php
class OrderStatusHistory extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'changed_by',
        'reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'metadata' => 'array',
        ];
    }

    public $timestamps = false;
}
```

### Shipment

```php
// app/Modules/Order/Models/Shipment.php

class Shipment extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'shipment_number',
        'carrier',
        'tracking_number',
        'status',
        'shipping_method',
        'weight', 'weight_unit',
        'length', 'width', 'height', 'dimension_unit',
        'shipping_cost',
        'estimated_delivery_at',
        'shipped_at',
        'delivered_at',
        'pickup_address',
        'delivery_address',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'shipping_cost' => 'integer',
            'weight' => 'decimal:4',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'estimated_delivery_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'pickup_address' => 'array',
            'delivery_address' => 'array',
            'metadata' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function deliveryAttempts(): HasMany
    {
        return $this->hasMany(DeliveryAttempt::class);
    }

    public function scopeByCarrier(Builder $query, string $carrier): void
    {
        $query->where('carrier', $carrier);
    }

    public function scopeByStatus(Builder $query, ShipmentStatusEnum $status): void
    {
        $query->where('status', $status->value);
    }

    public function scopePending(Builder $query): void
    {
        $query->whereIn('status', [
            ShipmentStatusEnum::Pending->value,
            ShipmentStatusEnum::PickedUp->value,
            ShipmentStatusEnum::InTransit->value,
            ShipmentStatusEnum::OutForDelivery->value,
        ]);
    }

    protected static function booted(): void
    {
        static::creating(function (Shipment $shipment) {
            if (! $shipment->id) {
                $shipment->id = (string) Str::ulid();
            }
            if (! $shipment->shipment_number) {
                $shipment->shipment_number = 'SHP-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }
}
```

### ShipmentItem

```php
// app/Modules/Order/Models/ShipmentItem.php

class ShipmentItem extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'shipment_id',
        'order_item_id',
        'product_id',
        'name', 'sku',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'quantity' => 'integer',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
```

### DeliveryAttempt

```php
// app/Modules/Order/Models/DeliveryAttempt.php

class DeliveryAttempt extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'shipment_id',
        'status',           // success, failed
        'reason',
        'notes',
        'attempted_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'metadata' => 'array',
        ];
    }

    public $timestamps = false;

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
```

### ShippingRate

```php
// app/Modules/Order/Models/ShippingRate.php

class ShippingRate extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'store_id',
        'carrier',
        'name',
        'zone',              // inside_dhaka, outside_dhaka, all_bd
        'weight_min', 'weight_max',
        'base_rate',
        'per_kg_rate',
        'cod_fee_percent',
        'estimated_delivery_days_min',
        'estimated_delivery_days_max',
        'is_active',
        'conditions',       // JSON: min_order_amount for free shipping
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'weight_min' => 'decimal:4',
            'weight_max' => 'decimal:4',
            'base_rate' => 'integer',
            'per_kg_rate' => 'integer',
            'cod_fee_percent' => 'decimal:2',
            'is_active' => 'boolean',
            'conditions' => 'array',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeForZone(Builder $query, string $zone): void
    {
        $query->where('zone', $zone);
    }
}
```

---

## 5. Enums

### OrderStatusEnum

```php
enum OrderStatusEnum: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Confirmed => __('Confirmed'),
            self::Processing => __('Processing'),
            self::Shipped => __('Shipped'),
            self::Delivered => __('Delivered'),
            self::Cancelled => __('Cancelled'),
            self::Refunded => __('Refunded'),
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Processing, self::Cancelled],
            self::Processing => [self::Shipped, self::Cancelled],
            self::Shipped => [self::Delivered, self::Cancelled],
            self::Delivered => [self::Refunded],
            self::Cancelled => [],
            self::Refunded => [],
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled, self::Refunded], true);
    }

    public function isActive(): bool
    {
        return ! $this->isTerminal();
    }
}
```

### PaymentStatusEnum

```php
enum PaymentStatusEnum: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case PartiallyPaid = 'partially_paid';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';
    case Failed = 'failed';

    public function label(): string { /* ... */ }
    public function isSettled(): bool
    {
        return in_array($this, [self::Paid, self::Refunded], true);
    }
}
```

### FulfillmentStatusEnum

```php
enum FulfillmentStatusEnum: string
{
    case Unfulfilled = 'unfulfilled';
    case PartiallyFulfilled = 'partially_fulfilled';
    case Fulfilled = 'fulfilled';

    public function label(): string { /* ... */ }
}
```

### OrderTypeEnum

```php
enum OrderTypeEnum: string
{
    case InStore = 'in_store';       // POS sale
    case Online = 'online';          // E-commerce
    case Takeaway = 'takeaway';      // Restaurant/Cafe
    case Delivery = 'delivery';      // Restaurant/Cafe
    case DineIn = 'dine_in';         // Restaurant
    case Wholesale = 'wholesale';    // Bulk order

    public function label(): string { /* ... */ }
}
```

### ShipmentStatusEnum

```php
enum ShipmentStatusEnum: string
{
    case Pending = 'pending';
    case PickedUp = 'picked_up';
    case InTransit = 'in_transit';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case ReturnedToSender = 'returned_to_sender';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::PickedUp => __('Picked Up'),
            self::InTransit => __('In Transit'),
            self::OutForDelivery => __('Out for Delivery'),
            self::Delivered => __('Delivered'),
            self::Failed => __('Delivery Failed'),
            self::ReturnedToSender => __('Returned to Sender'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::PickedUp, self::Cancelled],
            self::PickedUp => [self::InTransit, self::Failed, self::ReturnedToSender],
            self::InTransit => [self::OutForDelivery, self::Failed, self::ReturnedToSender],
            self::OutForDelivery => [self::Delivered, self::Failed],
            self::Delivered => [],
            self::Failed => [self::OutForDelivery, self::ReturnedToSender],
            self::ReturnedToSender => [self::Cancelled],
            self::Cancelled => [],
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Delivered, self::ReturnedToSender, self::Cancelled], true);
    }

    public function isPending(): bool
    {
        return ! $this->isTerminal() && $this !== self::Cancelled;
    }
}
```

---

## 6. DTOs

### OrderDTO (existing, keep)

```php
readonly class OrderDTO
{
    public function __construct(
        public string $orderId,
        public string $orderNumber,
        public string $tenantId,
        public ?string $customerId,
        public string $status,
        public int $subtotal,
        public int $taxTotal,
        public int $discountTotal,
        public int $grandTotal,
        public string $currency,
        public OrderAddressDTO $shippingAddress,
        public ?OrderAddressDTO $billingAddress,
        public array $lineItems,
        public ?string $couponCode,
        public ?string $notes,
        public ?string $paymentMethod,
        public CarbonImmutable $placedAt,
        public ?array $metadata,
    ) {}

    public static function fromArray(array $data): self { /* ... */ }
    public function toArray(): array { /* ... */ }
}
```

### CreateOrderDTO (new)

```php
readonly class CreateOrderDTO
{
    public function __construct(
        public string $storeId,
        public ?string $customerId,
        public ?string $customerName,
        public ?string $customerEmail,
        public ?string $customerPhone,
        public OrderTypeEnum $orderType,
        public array $items,             // array of LineItemDTO
        public ?OrderAddressDTO $shippingAddress,
        public ?OrderAddressDTO $billingAddress,
        public ?int $discountTotal,
        public ?string $couponCode,
        public ?string $notes,
        public ?string $paymentMethod,
        public ?array $metadata,
    ) {}

    public static function fromArray(array $data): self { /* ... */ }
    public function toArray(): array { /* ... */ }
}
```

### OrderSummaryDTO (new — for listings)

```php
readonly class OrderSummaryDTO
{
    public function __construct(
        public string $orderId,
        public string $orderNumber,
        public string $status,
        public string $paymentStatus,
        public string $fulfillmentStatus,
        public int $grandTotal,
        public string $currency,
        public string $customerName,
        public int $itemCount,
        public CarbonImmutable $placedAt,
    ) {}

    public static function fromModel(Order $order): self { /* ... */ }
}
```

### OrderPaymentDTO (new)

```php
readonly class OrderPaymentDTO
{
    public function __construct(
        public string $paymentId,
        public string $orderId,
        public string $paymentMethod,
        public int $amount,
        public ?string $reference,
        public ?string $gateway,
        public ?string $gatewayTransactionId,
        public string $status,
        public ?CarbonImmutable $paidAt,
    ) {}

    public static function fromArray(array $data): self { /* ... */ }
    public static function fromModel(OrderPayment $payment): self { /* ... */ }
}
```

### OrderRefundDTO (new)

```php
readonly class OrderRefundDTO
{
    public function __construct(
        public string $refundId,
        public string $orderId,
        public int $amount,
        public string $reason,
        public string $status,
        public ?array $metadata,
    ) {}

    public static function fromArray(array $data): self { /* ... */ }
    public static function fromModel(OrderRefund $refund): self { /* ... */ }
}
```

### ShipmentDTO (new)

```php
readonly class ShipmentDTO
{
    public function __construct(
        public string $shipmentId,
        public string $orderId,
        public string $shipmentNumber,
        public string $carrier,
        public ?string $trackingNumber,
        public string $status,
        public ?string $shippingMethod,
        public ?float $weight,
        public ?string $weightUnit,
        public int $shippingCost,
        public ?CarbonImmutable $estimatedDeliveryAt,
        public ?CarbonImmutable $shippedAt,
        public ?CarbonImmutable $deliveredAt,
        public ?array $pickupAddress,
        public ?array $deliveryAddress,
        public array $items,
        public ?array $metadata,
    ) {}

    public static function fromArray(array $data): self { /* ... */ }
    public static function fromModel(Shipment $shipment): self { /* ... */ }
    public function toArray(): array { /* ... */ }
}
```

### ShippingRateDTO (new)

```php
readonly class ShippingRateDTO
{
    public function __construct(
        public string $rateId,
        public string $storeId,
        public string $carrier,
        public string $name,
        public string $zone,
        public ?float $weightMin,
        public ?float $weightMax,
        public int $baseRate,
        public int $perKgRate,
        public ?float $codFeePercent,
        public int $estimatedDaysMin,
        public int $estimatedDaysMax,
        public bool $isActive,
        public ?array $conditions,
    ) {}

    public static function fromArray(array $data): self { /* ... */ }
    public static function fromModel(ShippingRate $rate): self { /* ... */ }
}
```

---

## 7. Events

All order events follow the `DomainEvent` interface with `EventDispatchable` trait, matching the existing pattern in `app/Modules/Shared/`.

### Existing Events (keep as-is)

```php
readonly class OrderCreated implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public OrderDTO $order,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}

readonly class OrderCancelled implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public OrderDTO $order,
        public string $reason,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}

readonly class OrderRefunded implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public OrderDTO $order,
        public int $refundAmount,
        public string $reason,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}
```

### New Events

```php
readonly class OrderConfirmed implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public OrderDTO $order,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}

readonly class OrderShipped implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public OrderDTO $order,
        public ?string $trackingNumber = null,
        public ?string $carrier = null,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}

readonly class OrderDelivered implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public OrderDTO $order,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}

readonly class OrderPaymentReceived implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public OrderPaymentDTO $payment,
        public OrderDTO $order,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}

readonly class OrderPaymentRefunded implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public OrderRefundDTO $refund,
        public OrderDTO $order,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}

readonly class OrderStatusChanged implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public OrderDTO $order,
        public string $fromStatus,
        public string $toStatus,
        public ?string $reason = null,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}
```

### Shipment Events

```php
readonly class ShipmentCreated implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public ShipmentDTO $shipment,
        public OrderDTO $order,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}

readonly class ShipmentPickedUp implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public ShipmentDTO $shipment,
        public OrderDTO $order,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}

readonly class ShipmentInTransit implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public ShipmentDTO $shipment,
        public OrderDTO $order,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}

readonly class ShipmentOutForDelivery implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public ShipmentDTO $shipment,
        public OrderDTO $order,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}

readonly class ShipmentDelivered implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public ShipmentDTO $shipment,
        public OrderDTO $order,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}

readonly class ShipmentDeliveryFailed implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public ShipmentDTO $shipment,
        public OrderDTO $order,
        public string $reason,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}

readonly class ShipmentReturnedToSender implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public ShipmentDTO $shipment,
        public OrderDTO $order,
        public string $reason,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}
```

---

## 8. Listeners

### Within Order Module

| Listener | Handles | Action |
|----------|---------|--------|
| `MarkProductUnavailable` | `StockDepleted` (from Inventory) | Marks order items as unavailable if stock remains depleted |
| `SendOrderConfirmation` | `OrderCreated` | Sends `OrderConfirmation` mail notification to customer |
| `NotifyCustomerOnShipment` | `ShipmentCreated` | Looks up customer email from order, sends `ShipmentNotification` with tracking details |
| `AutoCompleteOrder` | `ShipmentDelivered` | Sets order status to Completed when all shipments are delivered |
| `SyncOrderStatusWithInventory` | Various | Syncs order status changes with inventory system |
| `NotifyCustomerOnShipmentUpdate` | `ShipmentPickedUp`, `ShipmentInTransit`, `ShipmentOutForDelivery`, `ShipmentDelivered`, `ShipmentDeliveryFailed` | Sends tracking update notification to customer |

### Cross-Module (in `OrderServiceProvider::boot()` via `Event::listen()`)

| Event | Listener | Module | Exists? |
|-------|----------|--------|---------|
| `OrderCreated` | `DeductInventoryStock` | Inventory | ✅ |
| `OrderCreated` | `SendOrderConfirmation` | Order (self) | ✅ |
| `OrderCancelled` | `RestoreInventoryStock` | Inventory | ✅ |
| `OrderRefunded` | `RestoreInventoryOnRefund` | Inventory | ✅ |
| `ShipmentCreated` | `NotifyCustomerOnShipment` | Order (self) | ✅ |
| `ShipmentDelivered` | `AutoCompleteOrder` | Order (self) | ✅ |
| `OrderCreated` | `UpdateCustomerActivity` | CRM | ❌ Create |

### Removed Listeners (legacy)

| Listener | Event | Reason for Removal |
|----------|-------|--------------------|
| `DeductProductStock` | `OrderCreated` | Legacy Product module — caused double deduction alongside `DeductInventoryStock` |
| `RestoreProductStock` | `OrderCancelled` | Legacy Product module — caused double restoration alongside `RestoreInventoryStock` |
| `UpdateInventoryOnShipment` | `ShipmentPickedUp` | Redundant — `DeductInventoryStock` already deducts stock at order placement; this listener deducted a second time at courier pickup |

**Registration in `OrderServiceProvider::boot()`:**

```php
Event::listen(OrderCreated::class, SendOrderConfirmation::class);
Event::listen(ShipmentCreated::class, NotifyCustomerOnShipment::class);
Event::listen(ShipmentDelivered::class, AutoCompleteOrder::class);
Event::listen(OrderCreated::class, DeductInventoryStock::class);
Event::listen(OrderCancelled::class, RestoreInventoryStock::class);
Event::listen(OrderRefunded::class, RestoreInventoryOnRefund::class);
```

---

## 9. Services

### OrderService

Central orchestrator for all order operations:

```php
class OrderService
{
    public function __construct(
        private OrderNumberGenerator $numberGenerator,
        private OrderPaymentService $paymentService,
        private TenantConfig $config,
    ) {}

    public function createOrder(CreateOrderDTO $dto): OrderDTO
    {
        // 1. Generate order number
        // 2. Create Order model with calculated totals
        // 3. Create OrderItems
        // 4. Create OrderAddresses (if provided)
        // 5. Dispatch OrderCreated event
        // 6. Return OrderDTO
    }

    public function updateStatus(Order $order, OrderStatusEnum $newStatus, ?string $reason = null): OrderDTO
    {
        // 1. Validate transition via OrderStatusEnum::canTransitionTo()
        // 2. Throw InvalidOrderStatusTransitionException if invalid
        // 3. Update order status
        // 4. Record in OrderStatusHistory
        // 5. Dispatch appropriate event (OrderShipped, OrderDelivered, etc.)
        // 6. Return OrderDTO
    }

    public function cancelOrder(Order $order, string $reason): OrderDTO
    {
        // 1. Validate cancellation is allowed (not terminal)
        // 2. Update status to Cancelled
        // 3. Record in OrderStatusHistory
        // 4. Dispatch OrderCancelled
        // 5. Return OrderDTO
    }

    public function refundOrder(Order $order, int $amount, string $reason): OrderDTO
    {
        // 1. Validate refund amount <= (grandTotal - alreadyRefunded)
        // 2. Throw OrderRefundExceedsTotalException if invalid
        // 3. Create OrderRefund
        // 4. Update order payment_status
        // 5. Dispatch OrderRefunded
        // 6. Return OrderDTO
    }

    public function search(?string $query, array $filters = [], array $sorts = []): LengthAwarePaginator
    {
        // Search by: order_number, customer_name, customer_email, status, date range
    }
}
```

### OrderPaymentService

```php
class OrderPaymentService
{
    public function recordPayment(Order $order, string $method, int $amount, ?array $gatewayData = null): OrderPayment
    {
        // 1. Create OrderPayment
        // 2. Update order.payment_status based on total paid vs grandTotal
        // 3. Dispatch OrderPaymentReceived
        // 4. Return OrderPayment
    }

    public function processRefund(Order $order, int $amount, string $reason): OrderRefund
    {
        // 1. Validate refund amount
        // 2. Create OrderRefund
        // 3. Update payment_status
        // 4. Dispatch OrderPaymentRefunded
        // 5. Return OrderRefund
    }

    public function getTotalPaid(Order $order): int
    {
        return (int) $order->payments()
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function getTotalRefunded(Order $order): int
    {
        return (int) $order->refunds()
            ->where('status', 'completed')
            ->sum('amount');
    }
}
```

### OrderNumberGenerator

```php
class OrderNumberGenerator
{
    public function generate(?string $storeCode = null, ?OrderTypeEnum $type = null): string
    {
        // Format: {prefix}-{date}-{sequence}
        // Example: ORD-20260720-0001
        // Example: POS-20260720-0001 (with store code)
        // Example: WHS-20260720-0001 (wholesale)
        // Sequence stored in tenant_configs or a dedicated counter table
    }
}
```

### FulfillmentService

Orchestrates the picking → packing → shipping flow for e-commerce orders:

```php
class FulfillmentService
{
    public function __construct(
        private ShipmentService $shipmentService,
        private OrderService $orderService,
        private TenantConfig $config,
    ) {}

    public function createShipment(Order $order, array $itemIds, array $data): ShipmentDTO
    {
        // 1. Validate all items belong to the order and are not already shipped
        // 2. Create Shipment model
        // 3. Create ShipmentItem records for selected items
        // 4. If carrier configured, dispatch to courier API via CourierManager
        // 5. Update tracking number from courier response
        // 6. Update order.fulfillment_status (PartiallyFulfilled / Fulfilled)
        // 7. Update order status to Shipped if first shipment
        // 8. Dispatch ShipmentCreated
        // 9. Return ShipmentDTO
    }

    public function updateShipmentStatus(Shipment $shipment, ShipmentStatusEnum $newStatus, ?array $data = null): ShipmentDTO
    {
        // 1. Validate transition via ShipmentStatusEnum::canTransitionTo()
        // 2. Update shipment status
        // 3. Log DeliveryAttempt if status is Failed
        // 4. If Delivered, update order status to Delivered
        // 5. If ReturnedToSender, update order fulfillment
        // 6. Dispatch appropriate event
        // 7. Return ShipmentDTO
    }

    public function getFulfillmentProgress(Order $order): array
    {
        return [
            'total_items' => $order->items->sum('quantity'),
            'shipped_items' => ShipmentItem::whereIn('shipment_id',
                $order->shipments()->pluck('id')
            )->sum('quantity'),
            'fulfillment_status' => $order->fulfillment_status,
            'shipments' => $order->shipments->map(fn ($s) => ShipmentDTO::fromModel($s)),
        ];
    }
}
```

### ShipmentService

```php
class ShipmentService
{
    public function __construct(
        private CourierManager $courierManager,
    ) {}

    public function dispatchToCourier(Shipment $shipment): Shipment
    {
        // 1. Resolve courier provider from CourierManager
        $courier = $this->courierManager->driver($shipment->carrier);

        // 2. Build ShipmentData DTO
        $data = new ShipmentData(
            orderId: $shipment->order_id,
            recipientName: $shipment->delivery_address['name'] ?? '',
            recipientPhone: $shipment->delivery_address['phone'] ?? '',
            recipientAddress: $shipment->delivery_address['address_line_1'] ?? '',
            city: $shipment->delivery_address['city'] ?? '',
            postalCode: $shipment->delivery_address['postal_code'] ?? '',
            weight: $shipment->weight,
            codAmount: $this->calculateCodAmount($shipment),
        );

        // 3. Call courier API
        $result = $courier->createShipment($data);

        // 4. Update tracking info
        $shipment->update([
            'tracking_number' => $result->trackingNumber,
            'status' => ShipmentStatusEnum::Pending->value,
        ]);

        // 5. Log courier response
        return $shipment->fresh();
    }

    public function trackShipment(Shipment $shipment): TrackingResult
    {
        if (! $shipment->tracking_number) {
            throw new CourierException('Shipment has no tracking number.');
        }

        $courier = $this->courierManager->driver($shipment->carrier);

        return $courier->trackShipment($shipment->tracking_number);
    }

    public function generateLabel(Shipment $shipment): string
    {
        if (! $shipment->tracking_number) {
            throw new CourierException('Shipment has no tracking number.');
        }

        $courier = $this->courierManager->driver($shipment->carrier);

        return $courier->generateLabel($shipment->tracking_number);
    }

    public function cancelShipment(Shipment $shipment): bool
    {
        if (! $shipment->tracking_number) {
            throw new CourierException('Shipment has no tracking number.');
        }

        $courier = $this->courierManager->driver($shipment->carrier);

        return $courier->cancelShipment($shipment->tracking_number);
    }

    private function calculateCodAmount(Shipment $shipment): int
    {
        $order = $shipment->order;
        $totalPaid = $order->payments()->where('status', 'completed')->sum('amount');

        return max(0, $order->grand_total - $totalPaid);
    }
}
```

### CourierManager

```php
// app/Modules/Order/Contracts/Courier/CourierProvider.php

interface CourierProvider
{
    public function createShipment(ShipmentData $data): ShipmentResult;
    public function trackShipment(string $trackingNumber): TrackingResult;
    public function cancelShipment(string $trackingNumber): bool;
    public function generateLabel(string $trackingNumber): string;
}
```

```php
// app/Modules/Order/Services/CourierManager.php

class CourierManager
{
    private array $drivers = [];

    public function __construct(
        private TenantConfig $config,
    ) {
        $this->registerDefaults();
    }

    public function register(string $name, CourierProvider $provider): void
    {
        $this->drivers[$name] = $provider;
    }

    public function driver(?string $name = null): CourierProvider
    {
        $name ??= $this->config->getSetting('default_courier', 'pathao');

        if (! isset($this->drivers[$name])) {
            throw new CourierNotAvailableException("Courier '{$name}' is not registered.");
        }

        return $this->drivers[$name];
    }

    public function getAvailable(): array
    {
        return array_keys($this->drivers);
    }

    private function registerDefaults(): void
    {
        $this->register('pathao', app(PathaoCourier::class));
        $this->register('steadfast', app(SteadfastCourier::class));
        $this->register('redx', app(RedXCourier::class));
        $this->register('sendo', app(SendoCourier::class));
        $this->register('paperfly', app(PaperflyCourier::class));
    }
}
```

### Courier Contracts & Data Objects

```php
// app/Modules/Order/Contracts/Courier/ShipmentData.php

readonly class ShipmentData
{
    public function __construct(
        public string $orderId,
        public string $recipientName,
        public string $recipientPhone,
        public string $recipientAddress,
        public string $city,
        public string $postalCode,
        public ?float $weight,
        public int $codAmount = 0,
        public ?string $notes = null,
    ) {}
}
```

```php
// app/Modules/Order/Contracts/Courier/TrackingResult.php

readonly class TrackingResult
{
    public function __construct(
        public string $trackingNumber,
        public string $status,
        public ?string $location,
        public ?CarbonImmutable $timestamp,
        public ?string $description,
        public ShipmentStatusEnum $mappedStatus,
    ) {}
}
```

```php
// app/Modules/Order/Contracts/Courier/ShipmentResult.php

readonly class ShipmentResult
{
    public function __construct(
        public string $trackingNumber,
        public string $courierStatus,
        public ?string $label = null,
        public ?float $chargedAmount = null,
        public ?string $error = null,
        public bool $success = true,
    ) {}
}
```

### Courier Implementations

Each courier provider implements `CourierProvider` and handles the carrier's specific API:

```php
// app/Modules/Order/Integration/Courier/PathaoCourier.php

class PathaoCourier implements CourierProvider
{
    public function __construct(
        private string $apiKey,
        private string $apiSecret,
        private ?string $baseUrl = null,
    ) {
        $this->baseUrl ??= config('services.pathao.base_url');
        $this->apiKey ??= config('services.pathao.api_key');
        $this->apiSecret ??= config('services.pathao.api_secret');
    }

    public function createShipment(ShipmentData $data): ShipmentResult
    {
        // POST /aladdin/api/v1/orders
    }

    public function trackShipment(string $trackingNumber): TrackingResult
    {
        // GET /aladdin/api/v1/orders/{tracking_code}/tracking
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        // POST /aladdin/api/v1/orders/{tracking_code}/cancel
    }

    public function generateLabel(string $trackingNumber): string
    {
        // GET /aladdin/api/v1/orders/{tracking_code}/label
    }
}
```

Same pattern for `SteadfastCourier`, `RedXCourier`, `SendoCourier`, `PaperflyCourier`.

### ShippingRateCalculator

```php
class ShippingRateCalculator
{
    public function __construct(
        private TenantConfig $config,
    ) {}

    public function calculate(
        string $storeId,
        string $zone,
        float $weight,
        int $orderAmount,
        ?string $preferredCourier = null,
    ): array
    {
        $query = ShippingRate::query()
            ->active()
            ->where('zone', $zone)
            ->where('store_id', $storeId);

        if ($preferredCourier) {
            $query->where('carrier', $preferredCourier);
        }

        return $query->get()
            ->filter(fn (ShippingRate $rate) => $weight >= ($rate->weight_min ?? 0)
                && (! $rate->weight_max || $weight <= $rate->weight_max))
            ->map(fn (ShippingRate $rate) => [
                'carrier' => $rate->carrier,
                'name' => $rate->name,
                'rate' => $this->calculateRate($rate, $weight, $orderAmount),
                'estimated_days' => [
                    'min' => $rate->estimated_delivery_days_min,
                    'max' => $rate->estimated_delivery_days_max,
                ],
            ])
            ->sortBy('rate')
            ->values()
            ->toArray();
    }

    private function calculateRate(ShippingRate $rate, float $weight, int $orderAmount): int
    {
        if (isset($rate->conditions['free_shipping_min_amount'])
            && $orderAmount >= $rate->conditions['free_shipping_min_amount']) {
            return 0;
        }

        $additionalKg = max(0, $weight - ($rate->weight_min ?? 0));

        return (int) ($rate->base_rate + $additionalKg * $rate->per_kg_rate);
    }
}
```

---

## 10. Controllers & Routes

### OrderController

```
GET     /orders                    -> index     (list with search, filters, sort)
GET     /orders/create             -> create    (manual order form)
POST    /orders                    -> store     (create order)
GET     /orders/{order}            -> show      (order detail)
GET     /orders/{order}/edit       -> edit      (edit order form)
PUT     /orders/{order}            -> update    (update order)
POST    /orders/{order}/status     -> updateStatus
POST    /orders/{order}/cancel     -> cancel
POST    /orders/{order}/refund     -> refund
POST    /orders/{order}/payment    -> recordPayment
GET     /orders/{order}/invoice    -> invoice   (PDF download)
GET     /orders/{order}/print      -> printReceipt
GET     /orders/export             -> export    (CSV/PDF bulk export)
POST    /orders/bulk/status        -> bulkUpdateStatus
POST     /orders/bulk/cancel        -> bulkCancel
POST    /orders/bulk/print         -> bulkPrint
```

### ShipmentController

```
GET     /orders/{order}/shipments        -> index        (list shipments for order)
POST    /orders/{order}/ship             -> store        (create + dispatch shipment)
GET     /shipments/{shipment}            -> show         (shipment detail)
POST    /shipments/{shipment}/status     -> updateStatus (tracking update)
POST    /shipments/{shipment}/cancel     -> cancel       (cancel shipment)
GET     /shipments/{shipment}/label      -> label        (download courier label)
GET     /shipments/{shipment}/track      -> track        (live tracking data)
```

### TrackingController

```
GET     /tracking/{trackingNumber}       -> show         (public tracking page — no auth)
POST    /courier/webhook/{carrier}       -> webhook      (courier status callback)
```

### Route Registration

```php
// In OrderServiceProvider::boot()
Route::middleware(['web', 'auth', InitializeTenancyByUser::class, 'store.context', 'subscription'])
    ->prefix('{store}')
    ->group(function () {
        Route::resource('orders', OrderController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::post('/{order}/status', [OrderController::class, 'updateStatus'])->name('status');
            Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
            Route::post('/{order}/refund', [OrderController::class, 'refund'])->name('refund');
            Route::post('/{order}/payment', [OrderController::class, 'recordPayment'])->name('payment');
            Route::get('/{order}/invoice', [OrderController::class, 'invoice'])->name('invoice');
            Route::get('/{order}/print', [OrderController::class, 'printReceipt'])->name('print');
            Route::get('/export', [OrderController::class, 'export'])->name('export');
            Route::post('/bulk/status', [OrderController::class, 'bulkUpdateStatus'])->name('bulk.status');
            Route::post('/bulk/cancel', [OrderController::class, 'bulkCancel'])->name('bulk.cancel');
            Route::post('/bulk/print', [OrderController::class, 'bulkPrint'])->name('bulk.print');

            // Shipment routes
            Route::get('/{order}/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
            Route::post('/{order}/ship', [ShipmentController::class, 'store'])->name('shipments.store');
        });

        // Shipment detail routes
        Route::prefix('shipments')->name('shipments.')->group(function () {
            Route::get('/{shipment}', [ShipmentController::class, 'show'])->name('show');
            Route::post('/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('status');
            Route::post('/{shipment}/cancel', [ShipmentController::class, 'cancel'])->name('cancel');
            Route::get('/{shipment}/label', [ShipmentController::class, 'label'])->name('label');
            Route::get('/{shipment}/track', [ShipmentController::class, 'track'])->name('track');
        });
    });

// Public tracking (no auth)
Route::get('/tracking/{trackingNumber}', [TrackingController::class, 'show'])
    ->name('tracking.show');

// Courier webhooks (no auth — validated by signature)
Route::post('/courier/webhook/{carrier}', [TrackingController::class, 'webhook'])
    ->name('courier.webhook');
```

---

## 11. Order Status Workflow

```
                        ┌──────────┐
                        │  Pending │
                        └────┬─────┘
                     ┌───────┴────────┐
                     ▼                ▼
              ┌──────────┐    ┌──────────┐
              │ Confirmed│    │ Cancelled│
              └────┬─────┘    └──────────┘
                   ▼
              ┌──────────┐
              │Processing│
              └────┬─────┘
                   ▼
              ┌──────────┐
              │ Shipped  │
              └────┬─────┘
                   ▼
              ┌──────────┐
              │ Delivered│
              └────┬─────┘
                   ▼
              ┌──────────┐
              │ Refunded │
              └──────────┘

Transitions:
  Pending    → Confirmed, Cancelled
  Confirmed  → Processing, Cancelled
  Processing → Shipped, Cancelled
  Shipped    → Delivered, Cancelled
  Delivered  → Refunded
  Cancelled  → (terminal)
  Refunded   → (terminal)
```

Each transition:
1. Validated by `OrderStatusEnum::canTransitionTo()`
2. Logged in `OrderStatusHistory`
3. Dispatches appropriate event (`OrderConfirmed`, `OrderShipped`, `OrderDelivered`, `OrderStatusChanged`)

### Fulfillment Sub-Workflow (E-Commerce / Courier)

For e-commerce orders (`OrderTypeEnum::Online`), the Shipped → Delivered transition is managed through courier tracking:

```
Order Status:     Processing
                        │
                        ▼  (FulfillmentService::createShipment)
Order Status:     Shipped
                        │
Shipment Status:  Pending ──► Courier API ──► Tracking Number
                        │
                        ▼
                  PickedUp
                        │
                        ▼
                  InTransit
                        │
                        ▼
                  OutForDelivery
                        │
                   ┌────┴────┐
                   ▼         ▼
              Delivered    Failed
                   │         │
                   │    ┌────┴────┐
                   │    ▼         ▼
                   │  Retry    ReturnedToSender
                   │    │         │
                   │    ▼         ▼
                   │  OutFor    Cancelled
                   │  Delivery
                   ▼
Order Status:  Delivered
```

**Fulfillment Lifecycle Rules:**
- `Processing` → `Shipped` occurs when the first shipment is created and dispatched to the courier
- `FulfillmentStatusEnum::PartiallyFulfilled` when some (not all) items are shipped
- `FulfillmentStatusEnum::Fulfilled` when all items are shipped
- `Shipped` → `Delivered` only when ALL shipments for the order reach `Delivered` status
- If any shipment is `ReturnedToSender`, the order fulfillment is flagged for review
- Courier webhooks automatically update shipment status via `TrackingController@webhook`
- `SyncShipmentTracking` cron job polls courier APIs for status updates

---

## 12. Multi-Store Integration

- `Order.store_id` is set automatically from `StoreContextManager` on creation
- All order queries are scoped to the current store via `scopeForStore()`
- `OrderNumberGenerator` can include store code prefix
- Orders appear in per-store dashboards and reporting
- Store context is always required — routes are inside `{store}` prefix

---

## 13. Order Number Generation

```php
// Configurable format stored in TenantConfig or store settings
// Default: {TYPE}{YYMMDD}{XXXXX}
// Example: ORD-20260720-00001

class OrderNumberGenerator
{
    public function __construct(
        private TenantConfig $config,
    ) {}

    public function generate(?string $storeCode = null): string
    {
        $prefix = $this->config->getSetting('order_number_prefix', 'ORD');
        $date = now()->format('Ymd');
        $sequence = $this->getNextSequence();

        return sprintf('%s-%s-%05d', $prefix, $date, $sequence);
    }

    private function getNextSequence(): int
    {
        // Use DB atomic increment or Redis INCR
        // Key: order_sequence:{tenant_id}:{date}
    }
}
```

---

## 14. Exceptions

```php
class InvalidOrderStatusTransitionException extends \RuntimeException
{
    public function __construct(
        string $fromStatus,
        string $toStatus,
    ) {
        parent::__construct("Cannot transition from '{$fromStatus}' to '{$toStatus}'.");
    }
}

class OrderNotFoundException extends \RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("Order with ID '{$id}' not found.");
    }

    public static function withNumber(string $number): self
    {
        return new self("Order with number '{$number}' not found.");
    }
}

class OrderRefundExceedsTotalException extends \RuntimeException
{
    public function __construct(int $requested, int $available)
    {
        parent::__construct(
            "Refund amount {$requested} exceeds available refundable amount {$available}."
        );
    }
}

class CourierException extends \RuntimeException
{
    public function __construct(string $message = 'Courier operation failed.', ?int $code = null)
    {
        parent::__construct($message, $code ?? 0);
    }
}

class CourierNotAvailableException extends \RuntimeException
{
    public function __construct(string $courier)
    {
        parent::__construct("Courier '{$courier}' is not registered or not available.");
    }
}

class ShipmentCreationFailedException extends \RuntimeException
{
    public function __construct(string $carrier, ?string $reason = null)
    {
        parent::__construct(
            "Shipment creation failed for carrier '{$carrier}'" . ($reason ? ": {$reason}" : "")
        );
    }
}
```

---

## 15. OrderService Provider

```php
// app/Providers/OrderServiceProvider.php

class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OrderNumberGenerator::class);
        $this->app->singleton(OrderService::class);
        $this->app->singleton(CourierManager::class);
        $this->app->singleton(ShipmentService::class);
        $this->app->singleton(FulfillmentService::class);
        $this->app->singleton(ShippingRateCalculator::class);
        $this->app->singleton(RefundService::class);
        $this->app->singleton(OrderTimelineService::class);
        $this->app->singleton(OrderIndustryIntegrator::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Modules/Order/Database/Migrations/Tenant');

        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Shipment::class, ShipmentPolicy::class);

        Order::observe(OrderObserver::class);
        Shipment::observe(ShipmentObserver::class);

        $this->registerCourierDrivers();

        // Notifications
        Event::listen(OrderCreated::class, SendOrderConfirmation::class);
        Event::listen(ShipmentCreated::class, NotifyCustomerOnShipment::class);

        // Order auto-completion
        Event::listen(ShipmentDelivered::class, AutoCompleteOrder::class);

        // Inventory stock movements (authoritative)
        Event::listen(OrderCreated::class, DeductInventoryStock::class);
        Event::listen(OrderCancelled::class, RestoreInventoryStock::class);
        Event::listen(OrderRefunded::class, RestoreInventoryOnRefund::class);
    }

    private function registerCourierDrivers(): void
    {
        $manager = $this->app->make(CourierManager::class);
        $manager->registerDefaults();
    }
}
```
```

### CourierServiceProvider

```php
class CourierServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CourierManager::class);
        $this->app->bind(PathaoCourier::class, fn () => new PathaoCourier(
            apiKey: config('services.pathao.api_key'),
            apiSecret: config('services.pathao.api_secret'),
        ));
        $this->app->bind(SteadfastCourier::class, fn () => new SteadfastCourier(
            apiKey: config('services.steadfast.api_key'),
            secretKey: config('services.steadfast.secret_key'),
        ));
        $this->app->bind(RedXCourier::class, fn () => new RedXCourier(
            apiKey: config('services.redx.api_key'),
        ));
        $this->app->bind(SendoCourier::class, fn () => new SendoCourier(
            apiKey: config('services.sendo.api_key'),
        ));
        $this->app->bind(PaperflyCourier::class, fn () => new PaperflyCourier(
            apiKey: config('services.paperfly.api_key'),
        ));
    }

    public function boot(): void
    {
        // Courier providers are lazily resolved when CourierManager::registerDefaults() runs
    }
}
```

Registered in `bootstrap/providers.php` before `OrderServiceProvider`:

```php
App\Modules\Order\Providers\CourierServiceProvider::class,
App\Modules\Order\Providers\OrderServiceProvider::class,
```

---

## 16. Multi-Tenant Considerations

### Data Isolation

| Tenant Mode | Order Tables Location | Isolation |
|-------------|----------------------|-----------|
| Shared (Free, Starter, Professional) | `souda_shared` database | `tenant_id` column + `HasTenantScope` trait |
| Dedicated (Enterprise) | `souda_tenant_{uuid}` database | Separate MySQL database |

All Order models use `HasTenantScope` for shared-mode compatibility.

### TenantConfig Integration

```php
class OrderService
{
    public function __construct(
        private TenantConfig $config,
    ) {}

    public function createOrder(CreateOrderDTO $dto): OrderDTO
    {
        // Check industry-specific features
        if ($this->config->hasFeature('table_management')) {
            // Handle dine-in table assignment
        }

        if ($this->config->hasFeature('delivery_tracking')) {
            // Handle delivery-specific fields
        }
    }
}
```

### Industry Pack Feature Flags Used by Orders

| Feature Flag | Industry Packs | Behavior |
|-------------|---------------|----------|
| `table_management` | Restaurant, Cafe | Adds table_number field to order |
| `delivery_tracking` | Restaurant, Cafe | Enables delivery address and tracking |
| `takeaway` | Restaurant, Cafe, Bakery | Enables takeaway order type |
| `tiered_pricing` | Wholesale | Applies tier-based pricing per customer |
| `serial_number_tracking` | Electronics | Requires serial numbers per line item |
| `batch_tracking` | Pharmacy, Grocery | Shows batch selection at checkout |
| `courier_integration` | All e-commerce capable (Electronics, Fashion, Bookstore, Wholesale) | Enables courier selection and shipment creation |
| `multiple_shipments` | All e-commerce capable | Allows partial fulfillment across multiple packages |
| `customer_tracking` | All e-commerce capable | Shows public tracking page link to customer |
| `cod_payment` | All e-commerce capable | Enables Cash on Delivery as payment method |

---

## 17. Testing Strategy

### Test Categories

| Category | Scope | Framework |
|----------|-------|-----------|
| Unit tests | Enums, DTOs, Services (mocked), Exceptions, OrderNumberGenerator | Pest |
| Feature tests | Order CRUD, status transitions, cancel/refund workflows | Pest |
| Integration | Cross-module events (Order → Inventory, Order → CRM) | Pest |

### Test Patterns

```php
// Unit test — Enum transitions
it('validates order status transitions', function () {
    expect(OrderStatusEnum::Pending->canTransitionTo(OrderStatusEnum::Confirmed))->toBeTrue();
    expect(OrderStatusEnum::Pending->canTransitionTo(OrderStatusEnum::Delivered))->toBeFalse();
    expect(OrderStatusEnum::Delivered->canTransitionTo(OrderStatusEnum::Refunded))->toBeTrue();
    expect(OrderStatusEnum::Refunded->canTransitionTo(OrderStatusEnum::Pending))->toBeFalse();
});

// Unit test — DTO
it('creates OrderDTO from array', function () {
    $dto = OrderDTO::fromArray([
        'order_id' => (string) Str::ulid(),
        'order_number' => 'ORD-001',
        'subtotal' => 10000,
        'grand_total' => 11500,
        // ...
    ]);

    expect($dto)->subtotal->toBe(10000);
    expect($dto)->grandTotal->toBe(11500);
});

// Feature test — Order creation
it('creates an order and dispatches OrderCreated', function () {
    Event::fake();

    $store = Store::factory()->create();
    $payload = [
        'customer_name' => 'John Doe',
        'items' => [/* ... */],
        'payment_method' => 'cash',
    ];

    $response = $this->post(route('orders.store', ['store' => $store->id]), $payload);

    $response->assertRedirect();
    $this->assertDatabaseHas('orders', ['order_number' => 'ORD-...']);

    Event::assertDispatched(OrderCreated::class);
});

// Unit test — Shipment status transitions
it('validates shipment status transitions', function () {
    expect(ShipmentStatusEnum::Pending->canTransitionTo(ShipmentStatusEnum::PickedUp))->toBeTrue();
    expect(ShipmentStatusEnum::Pending->canTransitionTo(ShipmentStatusEnum::Delivered))->toBeFalse();
    expect(ShipmentStatusEnum::InTransit->canTransitionTo(ShipmentStatusEnum::OutForDelivery))->toBeTrue();
    expect(ShipmentStatusEnum::OutForDelivery->canTransitionTo(ShipmentStatusEnum::Delivered))->toBeTrue();
    expect(ShipmentStatusEnum::OutForDelivery->canTransitionTo(ShipmentStatusEnum::Failed))->toBeTrue();
    expect(ShipmentStatusEnum::Delivered->canTransitionTo(ShipmentStatusEnum::Pending))->toBeFalse();
});

// Feature test — Courier shipment creation
it('creates a shipment and dispatches to courier', function () {
    Http::fake(); // Prevent real API calls
    Event::fake();

    $order = Order::factory()->create([
        'status' => OrderStatusEnum::Processing,
        'order_type' => OrderTypeEnum::Online,
    ]);

    $response = $this->post(route('orders.shipments.store', ['order' => $order->id]), [
        'carrier' => 'pathao',
        'items' => $order->items->pluck('id')->toArray(),
        'weight' => 0.5,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('shipments', ['order_id' => $order->id]);
    Event::assertDispatched(ShipmentCreated::class);
});

// Feature test — Courier webhook tracking update
it('updates shipment status via courier webhook', function () {
    $shipment = Shipment::factory()->create([
        'carrier' => 'pathao',
        'tracking_number' => 'PA-12345',
        'status' => ShipmentStatusEnum::Pending,
    ]);

    $response = $this->post(route('courier.webhook', ['carrier' => 'pathao']), [
        'tracking_code' => 'PA-12345',
        'status' => 'delivered',
        'timestamp' => now()->toISOString(),
    ]);

    $response->assertOk();
    $this->assertEquals(ShipmentStatusEnum::Delivered->value, $shipment->fresh()->status);
});

// Feature test — Shipping rate calculation
it('calculates shipping rate by zone and weight', function () {
    ShippingRate::factory()->create([
        'store_id' => $store->id,
        'carrier' => 'pathao',
        'zone' => 'inside_dhaka',
        'base_rate' => 6000,
        'per_kg_rate' => 2000,
        'weight_min' => 0,
        'weight_max' => 5,
    ]);

    $rates = app(ShippingRateCalculator::class)->calculate(
        storeId: $store->id,
        zone: 'inside_dhaka',
        weight: 2.5,
        orderAmount: 50000,
    );

    expect($rates)->toHaveCount(1);
    expect($rates[0]['rate'])->toBe(11000); // 6000 + (2.5 * 2000)
});
```

### Notification Tests

```php
// Unit test — OrderConfirmation notification data
it('sends order confirmation via mail channel', function () {
    $notification = new OrderConfirmation($order);

    expect($notification->via(new stdClass))->toContain('mail');
});

it('order confirmation mail passes order data to template', function () {
    $notification = new OrderConfirmation($order);

    $mail = $notification->toMail(new stdClass);

    expect($mail->subject)->toContain($order->orderNumber)
        ->and($mail->markdown)->toBe('emails.order-confirmation')
        ->and($mail->viewData['customerName'])->toBe('John Doe');
});

// Unit test — ShipmentNotification data
it('shipment notification passes tracking info to template', function () {
    $notification = new ShipmentNotification($shipment);

    $mail = $notification->toMail(new stdClass);

    expect($mail->viewData['trackingNumber'])->toBe('PA-123456')
        ->and($mail->viewData['carrier'])->toBe('pathao');
});

// Test notifications with no email (graceful handling)
it('notify customer skips without email', function () {
    Event::fake();

    $listener = new NotifyCustomerOnShipment();
    $listener->handle($shipmentEvent);  // order has no customer_email

    // No exception thrown, no notification sent — just logged
});
```

### Test Database Considerations

- Feature tests use `souda_testing` DB (shared between central and default connections)
- Do NOT run tests in parallel — `migrate:fresh` calls will collide
- Use `Store::factory()->create()` with explicit store context
- Follow existing `HasTenantScope` test-safe patterns
- Mock courier providers with `Http::fake()` to prevent real API calls
- Test webhook signature validation for each courier

---

## 18. Coding Standards & AI Guardrails

### MUST FOLLOW

- All monetary values in cents (integer) — `bigInteger` column type
- All quantities are integers (whole units)
- Use constructor property promotion for all services
- Use proper return type hints on all methods
- Use Form Request classes for validation — no inline validation
- Use PHPDoc for complex logic — no inline comments for simple code
- Run `vendor/bin/pint --format agent` after all PHP changes
- Add `->orderBy('id')` before `->each()` on query builders
- **Prefix route names with module** (e.g., `orders.index`, `orders.cancel`)
- **Pass scalar IDs to engines, not models** — `OrderService::cancel(string $orderId)`, not `cancel(Order $order)`
- **Orders must always carry `store_id`** — never create an order without a store context
- Use `EventDispatchable` trait on all order and shipment events
- Wrap order writes in `DB::transaction()` with retry
- **Courier providers must implement `CourierProvider` interface** — never use courier APIs directly
- **Shipment status transitions must use `ShipmentStatusEnum::canTransitionTo()`** — never update status directly
- **Track courier API calls** — log all courier requests/responses for debugging
- **Validate webhook signatures** — each courier webhook must verify its signature before processing

### NEVER

- Write stock directly from Order module — dispatch events instead
- Use `env()` outside config files
- Add hardcoded business type checks (`if ($slug === 'restaurant')`)
- Create per-industry tables or columns
- Use `DB::` when an Eloquent relationship exists
- Leave empty `__construct()` with zero params (unless private)
- Delete order records — use soft deletes or cancellation
- Modify order after terminal status — use refunds instead
- Commit secrets or credentials

### ALWAYS

- Create event listeners for cross-module communication
- Use `HasTenantScope` on shared-mode models
- Register events in `OrderServiceProvider::boot()`
- Use `OrderStatusEnum` for status values — never hardcoded strings
- Validate status transitions with `OrderStatusEnum::canTransitionTo()`
- Log all status changes in `OrderStatusHistory`
- Use `ShipmentStatusEnum` for shipment status values
- Validate shipment transitions with `ShipmentStatusEnum::canTransitionTo()`
- Register courier drivers in `CourierManager::registerDefaults()`
- Use `CourierManager::driver()` for all courier API calls
- Log all courier API requests and responses for debugging
- Mock `Http` facade in courier feature tests
- Run tests before marking order tasks complete
- Write feature tests for every order and shipment workflow

---

## 19. Database Migrations

### orders table (Tenant)

```php
Schema::create('orders', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('tenant_id', 36);
    $table->string('store_id', 36);
    $table->string('customer_id', 36)->nullable();
    $table->string('order_number', 50)->unique();
    $table->string('status', 30)->default('pending');
    $table->string('payment_status', 30)->default('pending');
    $table->string('fulfillment_status', 30)->default('unfulfilled');
    $table->string('order_type', 30)->default('in_store');
    $table->bigInteger('subtotal')->default(0);
    $table->bigInteger('tax_total')->default(0);
    $table->bigInteger('discount_total')->default(0);
    $table->bigInteger('grand_total')->default(0);
    $table->string('currency', 3)->default('BDT');
    $table->decimal('exchange_rate', 12, 6)->default(1);
    $table->string('payment_method', 50)->nullable();
    $table->string('customer_name', 255)->nullable();
    $table->string('customer_email', 255)->nullable();
    $table->string('customer_phone', 30)->nullable();
    $table->text('notes')->nullable();
    $table->text('internal_notes')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamp('placed_at');
    $table->timestamps();
    $table->softDeletes();

    $table->index('tenant_id');
    $table->index('store_id');
    $table->index('status');
    $table->index('placed_at');
    $table->index(['store_id', 'status']);
    $table->index(['store_id', 'placed_at']);
});
```

### order_items table (Tenant)

```php
Schema::create('order_items', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('order_id', 36);
    $table->string('product_id', 36)->nullable();
    $table->string('variant_id', 36)->nullable();
    $table->string('name', 255);
    $table->string('sku', 100)->nullable();
    $table->string('barcode', 100)->nullable();
    $table->integer('quantity');
    $table->bigInteger('unit_price');
    $table->bigInteger('total_price');
    $table->bigInteger('tax_amount')->nullable();
    $table->bigInteger('discount_amount')->nullable();
    $table->string('warehouse_id', 36)->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();

    $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
    $table->index('product_id');
    $table->index('sku');
});
```

### order_addresses table (Tenant)

```php
Schema::create('order_addresses', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('order_id', 36);
    $table->string('type', 20); // shipping, billing
    $table->string('name', 255);
    $table->string('phone', 30)->nullable();
    $table->string('email', 255)->nullable();
    $table->string('address_line_1', 255);
    $table->string('address_line_2', 255)->nullable();
    $table->string('city', 100);
    $table->string('state', 100)->nullable();
    $table->string('postal_code', 20);
    $table->string('country', 100);
    $table->timestamps();

    $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
});
```

### order_payments table (Tenant)

```php
Schema::create('order_payments', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('order_id', 36);
    $table->string('payment_method', 50);
    $table->bigInteger('amount');
    $table->string('reference', 255)->nullable();
    $table->string('gateway', 50)->nullable();
    $table->string('gateway_transaction_id', 255)->nullable();
    $table->string('status', 30)->default('completed');
    $table->timestamp('paid_at')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();

    $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
    $table->index('status');
});
```

### order_refunds table (Tenant)

```php
Schema::create('order_refunds', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('order_id', 36);
    $table->bigInteger('amount');
    $table->text('reason');
    $table->string('status', 30)->default('completed');
    $table->json('metadata')->nullable();
    $table->timestamps();

    $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
});
```

### order_status_history table (Tenant)

```php
Schema::create('order_status_history', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('order_id', 36);
    $table->string('from_status', 30)->nullable();
    $table->string('to_status', 30);
    $table->string('changed_by', 255)->nullable();
    $table->text('reason')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamp('created_at');

    $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
    $table->index('order_id');
});
```

### shipments table (Tenant)

```php
Schema::create('shipments', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('order_id', 36);
    $table->string('shipment_number', 50)->unique();
    $table->string('carrier', 50);
    $table->string('tracking_number', 255)->nullable()->index();
    $table->string('status', 30)->default('pending');
    $table->string('shipping_method', 50)->nullable();
    $table->decimal('weight', 12, 4)->nullable();
    $table->string('weight_unit', 10)->nullable();
    $table->decimal('length', 10, 2)->nullable();
    $table->decimal('width', 10, 2)->nullable();
    $table->decimal('height', 10, 2)->nullable();
    $table->string('dimension_unit', 10)->nullable();
    $table->bigInteger('shipping_cost')->default(0);
    $table->timestamp('estimated_delivery_at')->nullable();
    $table->timestamp('shipped_at')->nullable();
    $table->timestamp('delivered_at')->nullable();
    $table->json('pickup_address')->nullable();
    $table->json('delivery_address')->nullable();
    $table->text('notes')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();

    $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
    $table->index('order_id');
    $table->index('carrier');
    $table->index('status');
    $table->index(['carrier', 'status']);
});
```

### shipment_items table (Tenant)

```php
Schema::create('shipment_items', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('shipment_id', 36);
    $table->string('order_item_id', 36)->nullable();
    $table->string('product_id', 36)->nullable();
    $table->string('name', 255);
    $table->string('sku', 100)->nullable();
    $table->integer('quantity');

    $table->foreign('shipment_id')->references('id')->on('shipments')->cascadeOnDelete();
    $table->index('shipment_id');
});
```

### delivery_attempts table (Tenant)

```php
Schema::create('delivery_attempts', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('shipment_id', 36);
    $table->string('status', 20);      // success, failed
    $table->string('reason', 255)->nullable();
    $table->text('notes')->nullable();
    $table->string('attempted_by', 255)->nullable();
    $table->json('metadata')->nullable();
    $table->timestamp('created_at');

    $table->foreign('shipment_id')->references('id')->on('shipments')->cascadeOnDelete();
    $table->index('shipment_id');
});
```

### shipping_rates table (Tenant)

```php
Schema::create('shipping_rates', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('store_id', 36);
    $table->string('carrier', 50);
    $table->string('name', 255);
    $table->string('zone', 50);            // inside_dhaka, outside_dhaka, all_bd
    $table->decimal('weight_min', 12, 4)->nullable();
    $table->decimal('weight_max', 12, 4)->nullable();
    $table->bigInteger('base_rate')->default(0);
    $table->bigInteger('per_kg_rate')->default(0);
    $table->decimal('cod_fee_percent', 5, 2)->default(0);
    $table->unsignedSmallInteger('estimated_delivery_days_min')->nullable();
    $table->unsignedSmallInteger('estimated_delivery_days_max')->nullable();
    $table->boolean('is_active')->default(true);
    $table->json('conditions')->nullable();
    $table->timestamps();

    $table->index('store_id');
    $table->index('carrier');
    $table->index(['store_id', 'zone', 'is_active']);
});
```

---

## 20. Frontend Integration

### Inertia Shared Data

```php
// HandleInertiaRequests
'order_statuses' => fn () => collect(OrderStatusEnum::cases())->map(fn ($case) => [
    'value' => $case->value,
    'label' => $case->label(),
])->toArray(),
```

### Page Structure

```
resources/js/pages/orders/
├── index.tsx          (list with search, filters, bulk actions)
├── create.tsx         (manual order creation form)
├── [id]/
│   ├── index.tsx      (order detail)
│   ├── edit.tsx       (edit form)
│   ├── invoice.tsx    (invoice preview/print)
│   └── print.tsx      (receipt print view)
```

### React Query Hooks

```typescript
// resources/js/modules/orders/hooks/use-orders.ts
export function useOrders(filters?: OrderFilters) {
    return useQuery({
        queryKey: ['orders', filters],
        queryFn: () => axios.get(route('orders.index'), { params: filters }),
    });
}

export function useOrder(id: string) {
    return useQuery({
        queryKey: ['orders', id],
        queryFn: () => axios.get(route('orders.show', { order: id })),
    });
}

export function useCreateOrder() {
    return useMutation({
        mutationFn: (payload: CreateOrderPayload) =>
            axios.post(route('orders.store'), payload),
    });
}

export function useUpdateOrderStatus() {
    return useMutation({
        mutationFn: ({ id, status, reason }: { id: string; status: string; reason?: string }) =>
            axios.post(route('orders.status', { order: id }), { status, reason }),
    });
}

// Shipment hooks
export function useShipments(orderId: string) {
    return useQuery({
        queryKey: ['shipments', orderId],
        queryFn: () => axios.get(route('orders.shipments.index', { order: orderId })),
    });
}

export function useCreateShipment() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: ({ orderId, ...payload }: { orderId: string; carrier: string; items: string[] }) =>
            axios.post(route('orders.shipments.store', { order: orderId }), payload),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['shipments'] }),
    });
}

export function useTrackShipment(shipmentId: string) {
    return useQuery({
        queryKey: ['shipments', shipmentId, 'track'],
        queryFn: () => axios.get(route('shipments.track', { shipment: shipmentId })),
        refetchInterval: 60000, // poll every 60s
    });
}

// Available couriers (from shared Inertia data or separate endpoint)
export function useAvailableCouriers() {
    return useQuery({
        queryKey: ['couriers'],
        queryFn: () => axios.get(route('settings.shipping.couriers')),
    });
}
```

### Tracking Page Structure

```
resources/js/pages/orders/
├── index.tsx
├── create.tsx
├── [id]/
│   ├── index.tsx              (order detail with shipment timeline)
│   ├── edit.tsx
│   ├── invoice.tsx
│   ├── print.tsx
│   ├── shipments/
│   │   ├── index.tsx          (shipment list per order)
│   │   ├── create.tsx         (create shipment form)
│   │   └── [shipmentId]/
│   │       ├── index.tsx      (shipment detail with tracking)
│   │       └── label.tsx      (courier label view)

resources/js/pages/
├── tracking/
│   └── [trackingNumber].tsx   (public tracking page — no auth)
```

---

## 21. File Creation Templates

### New Order Service

```php
<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Modules\BusinessType\ValueObjects\TenantConfig;

class MyOrderService
{
    public function __construct(
        private TenantConfig $config,
    ) {}

    public function execute(): void
    {
        if ($this->config->hasFeature('table_management')) {
            // industry-specific behavior
        }
    }
}
```

### New Order Event

```php
<?php

declare(strict_types=1);

namespace App\Modules\Order\Events;

use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Shared\Contracts\DomainEvent;
use App\Modules\Shared\DTOs\EventEnvelope;
use App\Modules\Shared\Traits\EventDispatchable;
use Carbon\CarbonImmutable;

readonly class MyOrderEvent implements DomainEvent
{
    use EventDispatchable;

    public CarbonImmutable $occurredAt;
    public string $eventName;
    private EventEnvelope $envelope;

    public function __construct(
        public OrderDTO $order,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) {
        $resolvedCorrelationId = $correlationId ?? (string) str()->ulid();

        $this->eventName = 'order.my_event';
        $this->occurredAt = new CarbonImmutable;
        $this->envelope = EventEnvelope::make(
            eventName: $this->eventName,
            payload: ['order' => $this->order->toArray()],
            correlationId: $resolvedCorrelationId,
            causationId: $causationId,
            tenantId: $this->order->tenantId,
        );
    }

    public function toEnvelope(): EventEnvelope { return $this->envelope; }
    public function getEventName(): string { return $this->eventName; }
    public function getCorrelationId(): string { return $this->correlationId ?? $this->envelope->correlationId; }
    public function getTenantId(): ?string { return $this->order->tenantId; }
}
```

### New Order Model (Shared Mode)

```php
<?php

declare(strict_types=1);

namespace App\Modules\Order\Models;

use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Model;

class MyModel extends Model
{
    use HasTenantScope;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
```

---

## 22. Development Roadmap

### Phase 2A: Foundation (Week 1)

| Task | Deliverable |
|------|-------------|
| Create module structure | `app/Modules/Order/` with all directories |
| Create tenant migrations | orders, order_items, order_addresses, order_payments, order_refunds, order_status_history |
| Implement Models | Order, OrderItem, OrderAddress, OrderPayment, OrderRefund, OrderStatusHistory |
| Implement Enums | OrderStatusEnum, PaymentStatusEnum, FulfillmentStatusEnum, OrderTypeEnum |
| Implement OrderService | create, updateStatus, cancel, refund |
| Implement OrderNumberGenerator | Configurable prefix, date, sequence |
| Create OrderServiceProvider | Singleton bindings, migration loading, route registration |
| Create tests | Enum unit tests, DTO tests, Service unit tests |

### Phase 2B: Events & Cross-Module (Week 2)

| Task | Deliverable |
|------|-------------|
| Implement all 9 events | OrderCreated, Cancelled, Refunded, Confirmed, Shipped, Delivered, PaymentReceived, PaymentRefunded, StatusChanged |
| Register inventory listeners | DeductInventoryStock, RestoreInventoryStock, RestoreInventoryOnRefund |
| Create order level MarkProductUnavailable | Listen to StockDepleted |
| Create tests | Event unit tests, event dispatch tests |

### Phase 2C: HTTP & Frontend (Week 3)

| Task | Deliverable |
|------|-------------|
| Implement OrderController | Full CRUD with status workflow, search, bulk actions |
| Implement Form Requests | StoreOrderRequest, UpdateOrderRequest, CancelOrderRequest, RefundOrderRequest |
| Create OrderPolicy | Authorization rules |
| Build order list page | Search, filters, sort, bulk actions, pagination |
| Build order create form | Line item management, customer lookup |
| Build order detail page | Timeline, payments, refunds, status actions |
| Create tests | Controller feature tests, authorization tests |

### Phase 2D: Advanced Features (Week 4)

| Task | Deliverable |
|------|-------------|
| Implement invoice PDF generation | Printable invoice with store details |
| Implement receipt printing | Thermal receipt format |
| Implement CSV/PDF export | Bulk order export |
| Implement order notes & activity log | Internal notes, order timeline |
| Implement pending order expiry | Cron: expire pending orders after N hours |
| Implement industry pack integration | Feature flags for table_management, delivery, batch picking, etc. |
| Create tests | Export, invoice, and integration tests |

### Phase 2E: E-Commerce & Courier Tracking (Week 5)

| Task | Deliverable |
|------|-------------|
| Create courier tenant migrations | shipments, shipment_items, delivery_attempts, shipping_rates |
| Implement Shipment models | Shipment, ShipmentItem, DeliveryAttempt, ShippingRate |
| Implement ShipmentStatusEnum | Full lifecycle transitions |
| Create CourierProvider interface | createShipment, trackShipment, cancelShipment, generateLabel |
| Implement CourierManager | Driver registration and resolution |
| Implement 5 courier providers | Pathao, Steadfast, RedX, Sendo, Paperfly |
| Implement CourierServiceProvider | Container bindings for all courier drivers |
| Implement ShipmentService | dispatchToCourier, trackShipment, generateLabel, cancelShipment |
| Implement FulfillmentService | Shipment creation, status updates, fulfillment progress |
| Implement ShippingRateCalculator | Zone + weight + courier pricing |
| Implement ShipmentController | CRUD, status updates, label generation |
| Implement TrackingController | Public tracking page, courier webhook endpoint |
| Implement all 7 shipment events | Created, PickedUp, InTransit, OutForDelivery, Delivered, Failed, ReturnedToSender |
| Implement SyncShipmentTracking command | Cron: poll courier APIs for tracking updates |
| Implement RetryFailedShipments command | Cron: retry failed delivery attempts |
| Build shipment management UI | Create shipment, tracking timeline, label download |
| Build public tracking page | Customer-facing tracking by tracking number |
| Create courier settings UI | Configure API keys, default courier, rate tables |
| Create tests | Courier unit tests, shipment feature tests, webhook tests |

---

## 23. Phase 2 Completion Checklist

- [ ] All migrations created and ordered correctly (10 tables)
- [ ] `OrderServiceProvider` registered in `bootstrap/providers.php`
- [ ] `CourierServiceProvider` registered in `bootstrap/providers.php`
- [ ] All 5 enums implemented with labels and transitions
- [ ] All 9 DTOs implemented (3 existing + 6 new)
- [ ] All 16 events defined and dispatched (9 order + 7 shipment)
- [ ] All 7 exception classes implemented
- [ ] `OrderService` is the single public entry point for order operations
- [ ] Status transitions validated by `OrderStatusEnum::canTransitionTo()`
- [ ] Shipment status transitions validated by `ShipmentStatusEnum::canTransitionTo()`
- [ ] Cross-module events registered in `EventServiceProvider`
- [ ] Routes registered under `{store}` prefix with `order.` and `shipments.` name prefixes
- [ ] `HasTenantScope` applied to all shared-mode models
- [ ] Order numbering implemented with configurable format
- [ ] CourierProvider interface implemented
- [ ] Courier drivers registered and resolved via CourierManager
- [ ] Pathao, Steadfast, RedX, Sendo, Paperfly providers implemented
- [ ] Shipment → Courier API dispatch working
- [ ] Tracking number returned and stored on shipment creation
- [ ] Courier webhook endpoint handling status callbacks
- [ ] Public tracking page functional
- [ ] Shipping rate calculation by zone + weight
- [ ] Invoice and receipt printing functional
- [ ] Order export (CSV/PDF) implemented
- [ ] Search, filtering, and bulk actions working
- [ ] Order status history fully tracked
- [ ] Fulfillment progress tracking (PartiallyFulfilled → Fulfilled)
- [ ] SyncShipmentTracking cron command implemented
- [ ] Feature tests cover all order and shipment workflows
- [ ] Unit tests cover enums, DTOs, exceptions, events, notifications
- [ ] `OrderConfirmation` notification class exists (`app/Notifications/OrderConfirmation.php`)
- [ ] `ShipmentNotification` notification class exists (`app/Notifications/ShipmentNotification.php`)
- [ ] Email templates exist (`resources/views/emails/order-confirmation.blade.php`, `shipment-notification.blade.php`)
- [ ] `SendOrderConfirmation` sends real mail (not just log)
- [ ] `NotifyCustomerOnShipment` sends real mail with tracking details
- [ ] Legacy `DeductProductStock`/`RestoreProductStock` listeners removed from `OrderServiceProvider`
- [ ] `UpdateInventoryOnShipment` listener removed — no double deduction at courier pickup
- [ ] `vendor/bin/pint --format agent` run without errors
