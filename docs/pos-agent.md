# SOUDA — POS Module Architecture Guide

This file is for AI coding agents implementing the POS module. It defines the complete architecture, conventions, guardrails, patterns, interfaces, and implementation rules for the SOUDA Point-of-Sale Engine.

---

## 1. Project Scope & POS Philosophy

### Vision

SOUDA's POS module is not a simple checkout form — it is an **Industry-Aware Point-of-Sale Engine** that transforms based on business type. A pharmacy POS looks different from a restaurant POS, which looks different from an electronics POS. The POS module dynamically adapts its layout, search, checkout fields, tender types, and workflow based on the tenant's `IndustryPack.posConfig()` and `TenantTemplate.posDefaults()`.

### Golden Rules

> **1. The POS creates orders, not stock movements.** Every completed POS checkout dispatches `OrderCreated`. Only the Inventory Engine translates that into stock movements.

> **2. POS configurability comes from Industry Packs, not hardcoded switches.** All industry-specific behavior flows from `IndustryPack.posConfig()` and `TenantTemplate.posDefaults()` — never from `if ($slug === 'pharmacy')`.

> **3. Sessions must reconcile.** Every POS session has an opening balance and must be closed with a closing balance. Cash discrepancies are tracked and reported.

### Scope — Phase 1

| Feature | Included |
|---------|----------|
| Product search (by name, SKU, barcode) | ✅ |
| Cart management (add, update, remove items) | ✅ |
| Quantity override (whole + fractional) | ✅ |
| Customer selection (walk-in or existing) | ✅ |
| Multiple tender types per transaction | ✅ |
| Split payments (cash + card) | ✅ |
| Order type selection (dine-in, takeaway, delivery) | ✅ |
| Session management (open, close, reconcile) | ✅ |
| Receipt printing (thermal + A4) | ✅ |
| Quick actions per industry pack | ✅ |
| Checkout field customization per industry | ✅ |
| Industry-specific POS layout | ✅ |
| Multi-store support | ✅ |
| Offline resilience (basic) | Phase 2 |
| Customer loyalty integration | Phase 2 |
| Discount/promotion engine | Phase 2 |
| Kitchen display integration | Phase 2 |

---

## 2. Core Architectural Principles

### Principle 1: Industry-Drive Configuration

```
TenantTemplate.posDefaults()
    │
    ▼
IndustryPack.posConfig()
    │
    ├── layout (grid/list)
    ├── product_search_columns
    ├── quick_actions
    ├── checkout_fields
    ├── tender_types
    └── industry_specific_flags
    │
    ▼
TenantConfig (resolved at runtime)
    │
    ▼
POSController renders industry-specific UI
```

### Principle 2: Session-Based Operation

```
POS Session Lifecycle:

Open Session ──► (beginning of day)
    │
    ├── Transaction 1 (checkout → OrderCreated)
    ├── Transaction 2 (checkout → OrderCreated)
    ├── ...
    │
Close Session ──► (end of day)
    │
    ├── Calculate expected balance
    ├── Compare with actual closing balance
    ├── Log discrepancy (if any)
    └── Save session report
```

### Principle 3: Order Module Coupling

The POS module is the primary consumer of the Order module. Every POS checkout flows through:

```
POS Checkout
    │
    ├── Create Order (OrderService::createOrder)
    ├── Record Payment (OrderPaymentService::recordPayment)
    ├── Dispatch OrderCreated
    └── Print Receipt
```

### Principle 4: Price Resolution via Store

Product prices in POS are resolved through the `store_product` pivot table, which allows per-store price overrides from the master catalog.

---

## 3. Module Structure

```
app/Modules/POS/
├── Console/
│   └── Commands/
│       ├── CloseStaleSessions.php
│       └── GenerateDailySalesReport.php
├── Database/
│   ├── Factories/
│   │   ├── POSSessionFactory.php
│   │   └── POSRegisterFactory.php
│   └── Migrations/
│       └── Tenant/
│           ├── 2026_07_20_000001_create_pos_sessions_table.php
│           ├── 2026_07_20_000002_create_pos_registers_table.php
│           └── 2026_07_20_000003_create_pos_cart_items_table.php
├── DTOs/
│   ├── POSSessionDTO.php
│   ├── POSCartDTO.php
│   ├── POSCartItemDTO.php
│   └── POSCheckoutDTO.php
├── Enums/
│   ├── POSSessionStatusEnum.php
│   └── POSRegisterStatusEnum.php
├── Events/
│   ├── POSSessionOpened.php
│   ├── POSSessionClosed.php
│   └── POSCheckoutCompleted.php
├── Exceptions/
│   ├── POSSessionNotOpenException.php
│   ├── POSSessionAlreadyOpenException.php
│   ├── POSInvalidTenderException.php
│   └── POSInsufficientStockException.php
├── Http/
│   ├── Controllers/
│   │   ├── POSController.php
│   │   ├── POSSessionController.php
│   │   └── POSRegisterController.php
│   └── Requests/
│       ├── POSAddToCartRequest.php
│       ├── POSUpdateCartRequest.php
│       ├── POSCheckoutRequest.php
│       ├── POSOpenSessionRequest.php
│       └── POSCloseSessionRequest.php
├── Models/
│   ├── POSSession.php
│   ├── POSRegister.php
│   └── POSCartItem.php
├── Providers/
│   └── POSServiceProvider.php
└── Services/
    ├── POSSessionService.php
    ├── POSCartService.php
    ├── POSCheckoutService.php
    └── POSPriceResolver.php
```

---

## 4. Models

### POSSession

```php
// app/Modules/POS/Models/POSSession.php

class POSSession extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'store_id', 'user_id', 'cashier_name',
        'register_id',
        'opened_at', 'closed_at',
        'opening_balance', 'closing_balance',
        'expected_balance', 'difference',
        'total_sales', 'total_transactions',
        'status',
        'notes',
        'closing_notes',
        'summary_data',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'opening_balance' => 'integer',
            'closing_balance' => 'integer',
            'expected_balance' => 'integer',
            'difference' => 'integer',
            'total_sales' => 'integer',
            'total_transactions' => 'integer',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'summary_data' => 'array',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function register(): BelongsTo
    {
        return $this->belongsTo(POSRegister::class);
    }

    public function scopeOpen(Builder $query): void
    {
        $query->where('status', POSSessionStatusEnum::Open->value);
    }

    public function scopeForStore(Builder $query, string $storeId): void
    {
        $query->where('store_id', $storeId);
    }

    public function scopeForUser(Builder $query, string $userId): void
    {
        $query->where('user_id', $userId);
    }

    public function isOpen(): bool
    {
        return $this->status === POSSessionStatusEnum::Open->value;
    }

    protected static function booted(): void
    {
        static::creating(function (POSSession $session) {
            if (! $session->id) {
                $session->id = (string) Str::ulid();
            }
        });
    }
}
```

### POSRegister

```php
// app/Modules/POS/Models/POSRegister.php

class POSRegister extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'store_id', 'name', 'device_id',
        'status', 'config',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'config' => 'array',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(POSSession::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', POSRegisterStatusEnum::Active->value);
    }
}
```

### POSCartItem

```php
// app/Modules/POS/Models/POSCartItem.php
// Transient model — stores cart state in DB (for device switching) or Redis

class POSCartItem extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'session_id', 'user_id', 'store_id',
        'product_id', 'variant_id',
        'name', 'sku', 'barcode',
        'quantity', 'unit_price', 'total_price',
        'tax_amount', 'discount_amount',
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

---

## 5. Enums

### POSSessionStatusEnum

```php
enum POSSessionStatusEnum: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Open => __('Open'),
            self::Closed => __('Closed'),
            self::Suspended => __('Suspended'),
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Open => [self::Closed, self::Suspended],
            self::Suspended => [self::Open, self::Closed],
            self::Closed => [],
        };
    }
}
```

### POSRegisterStatusEnum

```php
enum POSRegisterStatusEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Inactive => __('Inactive'),
        };
    }
}
```

---

## 6. DTOs

### POSSessionDTO

```php
readonly class POSSessionDTO
{
    public function __construct(
        public string $sessionId,
        public string $storeId,
        public string $userId,
        public string $cashierName,
        public ?string $registerId,
        public string $status,
        public int $openingBalance,
        public ?int $closingBalance,
        public ?int $expectedBalance,
        public ?int $difference,
        public ?int $totalSales,
        public ?int $totalTransactions,
        public CarbonImmutable $openedAt,
        public ?CarbonImmutable $closedAt,
        public ?string $notes,
        public ?array $summaryData,
    ) {}

    public static function fromArray(array $data): self { /* ... */ }
    public static function fromModel(POSSession $session): self { /* ... */ }
    public function toArray(): array { /* ... */ }
}
```

### POSCartDTO

```php
readonly class POSCartDTO
{
    public function __construct(
        public string $sessionId,
        public string $storeId,
        public array $items,         // array of POSCartItemDTO
        public int $subtotal,
        public int $taxTotal,
        public int $discountTotal,
        public int $grandTotal,
        public int $itemCount,
    ) {}

    public static function fromArray(array $data): self { /* ... */ }
    public function toArray(): array { /* ... */ }
}
```

### POSCartItemDTO

```php
readonly class POSCartItemDTO
{
    public function __construct(
        public string $productId,
        public ?string $variantId,
        public string $name,
        public string $sku,
        public ?string $barcode,
        public int $quantity,
        public int $unitPrice,
        public int $totalPrice,
        public ?int $taxAmount,
        public ?int $discountAmount,
        public ?string $warehouseId,
        public ?array $metadata,
    ) {}

    public static function fromArray(array $data): self { /* ... */ }
    public function toArray(): array { /* ... */ }
}
```

### POSCheckoutDTO

```php
readonly class POSCheckoutDTO
{
    public function __construct(
        public string $storeId,
        public string $sessionId,
        public ?string $customerId,
        public ?string $customerName,
        public ?string $customerEmail,
        public ?string $customerPhone,
        public string $orderType,         // from OrderTypeEnum
        public array $items,              // array of POSCartItemDTO
        public array $payments,           // array of {method, amount}[]
        public ?string $notes,
        public ?array $metadata,
    ) {}

    public static function fromArray(array $data): self { /* ... */ }
    public function toArray(): array { /* ... */ }
}
```

---

## 7. Events

### POSSessionOpened

```php
readonly class POSSessionOpened implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public POSSessionDTO $session,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}
```

### POSSessionClosed

```php
readonly class POSSessionClosed implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public POSSessionDTO $session,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}
```

### POSCheckoutCompleted

```php
readonly class POSCheckoutCompleted implements DomainEvent
{
    use EventDispatchable;

    public function __construct(
        public OrderDTO $order,
        public POSSessionDTO $session,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) { /* ... */ }
}
```

---

## 8. Services

### POSSessionService

```php
class POSSessionService
{
    public function openSession(
        string $storeId,
        string $userId,
        string $cashierName,
        int $openingBalance,
        ?string $registerId = null,
        ?string $notes = null,
    ): POSSessionDTO
    {
        // 1. Check no open session exists for this user+store
        // 2. Throw POSSessionAlreadyOpenException if found
        // 3. Create POSSession with status 'open'
        // 4. Dispatch POSSessionOpened
        // 5. Return POSSessionDTO
    }

    public function closeSession(
        POSSession $session,
        int $closingBalance,
        ?string $notes = null,
    ): POSSessionDTO
    {
        // 1. Validate session is open
        // 2. Calculate expected balance from session.txns
        // 3. Calculate difference = closing - expected
        // 4. Update session with closing data
        // 5. Dispatch POSSessionClosed
        // 6. Return POSSessionDTO
    }

    public function getCurrentSession(string $storeId, string $userId): ?POSSession
    {
        return POSSession::query()
            ->forStore($storeId)
            ->forUser($userId)
            ->open()
            ->first();
    }

    public function getSessionReport(POSSession $session): array
    {
        // Aggregate: total sales, payment method breakdown,
        // item count, average order value, voided items
    }
}
```

### POSCartService

```php
class POSCartService
{
    public function __construct(
        private POSPriceResolver $priceResolver,
    ) {}

    public function addItem(POSSession $session, string $productId, int $quantity, ?string $variantId = null): POSCartDTO
    {
        // 1. Resolve product price via POSPriceResolver
        // 2. Check stock availability (via query, not direct model)
        // 3. Create or update POSCartItem
        // 4. Recalculate cart totals
        // 5. Return POSCartDTO
    }

    public function updateQuantity(POSSession $session, string $itemId, int $quantity): POSCartDTO
    {
        // 1. Update item quantity
        // 2. Recalculate line total
        // 3. Recalculate cart totals
        // 4. Return POSCartDTO
    }

    public function removeItem(POSSession $session, string $itemId): POSCartDTO
    {
        // 1. Delete cart item
        // 2. Recalculate cart totals
        // 3. Return POSCartDTO
    }

    public function clearCart(POSSession $session): void
    {
        POSCartItem::where('session_id', $session->id)->delete();
    }

    public function getCart(POSSession $session): POSCartDTO
    {
        // 1. Fetch all cart items for session
        // 2. Calculate totals
        // 3. Return POSCartDTO
    }

    private function recalculate(POSSession $session): POSCartDTO
    {
        $items = POSCartItem::where('session_id', $session->id)->get();

        $subtotal = $items->sum('total_price');
        $taxTotal = $items->sum('tax_amount');
        $discountTotal = $items->sum('discount_amount');

        return new POSCartDTO(
            sessionId: $session->id,
            storeId: $session->store_id,
            items: $items->map(fn ($i) => /* to DTO */)->toArray(),
            subtotal: $subtotal,
            taxTotal: $taxTotal,
            discountTotal: $discountTotal,
            grandTotal: $subtotal + $taxTotal - $discountTotal,
            itemCount: $items->count(),
        );
    }
}
```

### POSCheckoutService

```php
class POSCheckoutService
{
    public function __construct(
        private OrderService $orderService,
        private OrderPaymentService $paymentService,
        private POSCartService $cartService,
        private POSSessionService $sessionService,
        private TenantConfig $config,
    ) {}

    public function checkout(POSCheckoutDTO $dto): OrderDTO
    {
        // 1. Verify session is open
        $session = $this->sessionService->getCurrentSession($dto->storeId, auth()->id());
        if (! $session || ! $session->isOpen()) {
            throw new POSSessionNotOpenException();
        }

        // 2. Validate tender types against industry config
        $this->validateTenderTypes($dto->payments);

        // 3. Build CreateOrderDTO from POSCheckoutDTO
        $createOrderDTO = $this->buildOrderDTO($dto);

        // 4. Create order via OrderService (dispatches OrderCreated)
        $order = $this->orderService->createOrder($createOrderDTO);

        // 5. Record each payment
        foreach ($dto->payments as $payment) {
            $this->paymentService->recordPayment(
                order: /* resolve order from DTO */,
                method: $payment['method'],
                amount: $payment['amount'],
            );
        }

        // 6. Clear cart
        $this->cartService->clearCart($session);

        // 7. Dispatch POSCheckoutCompleted
        POSCheckoutCompleted::dispatch($order->toDTO(), $session);

        // 8. Return OrderDTO
        return $order;
    }

    private function validateTenderTypes(array $payments): void
    {
        $allowed = $this->config->posConfig()['tender_types'] ?? ['cash', 'card', 'mobile_banking'];

        foreach ($payments as $payment) {
            if (! in_array($payment['method'], $allowed, true)) {
                throw new POSInvalidTenderException($payment['method']);
            }
        }
    }

    private function buildOrderDTO(POSCheckoutDTO $dto): CreateOrderDTO
    {
        // Map POS cart items to line items
        // Set order type from DTO
        // Apply industry-specific checkout fields as metadata
    }
}
```

### POSPriceResolver

```php
class POSPriceResolver
{
    public function resolve(string $productId, string $storeId, ?string $variantId = null): array
    {
        // 1. Check store_product pivot for price override
        // 2. Fall back to product base price
        // 3. Apply variant price if applicable
        // 4. Apply tiered pricing if wholesale customer
        // 5. Return [unitPrice, compareAtPrice, taxAmount]
    }

    public function resolveBatch(string $productId, string $storeId): array
    {
        // If batch_tracking enabled, return available batches
        // with FEFO order (expiry_date ascending)
    }
}
```

---

## 9. POS Session Lifecycle

```
┌─────────────────────────────────────────────────────────────────┐
│                     POS Session Lifecycle                        │
└─────────────────────────────────────────────────────────────────┘

Open Session (beginning of shift)
    │
    ├── Cashier enters opening balance
    ├── System records opening_balance
    ├── Status: open
    └── Dispatch: POSSessionOpened
    │
    ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Active POS Session                            │
│                                                                  │
│   ┌──────────┐   ┌──────────┐   ┌──────────┐   ┌──────────┐   │
│   │ Add Item │──►│ Update   │──►│ Checkout │──►│ Print    │   │
│   │ to Cart  │   │ Cart     │   │          │   │ Receipt  │   │
│   └──────────┘   └──────────┘   └──────────┘   └──────────┘   │
│         │              │              │              │          │
│         ▼              ▼              ▼              ▼          │
│   POSCartItem    POSCartItem    OrderCreated    ReceiptQueue    │
│   (created)      (updated)     (dispatched)     (cached)       │
│                                                                  │
│   Repeat for each transaction ──────────────────────────────►   │
└─────────────────────────────────────────────────────────────────┘
    │
    ▼
Close Session (end of shift)
    │
    ├── Cashier counts drawer
    ├── Enters closing_balance
    ├── System calculates expected_balance = opening + total_sales
    ├── System calculates difference = closing - expected
    ├── If difference != 0 → flagged in session report
    ├── Status: closed
    ├── Dispatch: POSSessionClosed
    └── Generate: Session Summary Report
```

### Balance Calculation

```
expected_balance = opening_balance + sum(all payments received in session)
difference = closing_balance - expected_balance

If difference == 0:
    Session is balanced ✅
If difference > 0:
    Overage (positive difference) — flagged for review
If difference < 0:
    Shortage (negative difference) — flagged for review
```

---

## 10. Checkout Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                     POS Checkout Flow                            │
└─────────────────────────────────────────────────────────────────┘

Customer presents items
    │
    ▼
┌─────────────────┐
│ Scan / Search   │  ──►  POSPriceResolver resolves prices
│ Products        │        via store_product pivot
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Build Cart      │  ──►  Apply industry checkout fields
│ Apply Discounts │        (prescription#, table#, server, etc.)
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Select Customer │  ──►  Optional — walk-in or existing
│ (if any)        │        From CRM module via service contract
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Select Payments │  ──►  Split payment support
│                  │        Multiple tender types per order
│  Cash   $15.00  │        Tender types from IndustryPack
│  Card   $10.00  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Confirm         │  ──►  Display order summary
│ Checkout        │        Wait for confirmation
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────────┐
│                  POSCheckoutService::checkout()                  │
├─────────────────────────────────────────────────────────────────┤
│  1. Validate session is open                                    │
│  2. Validate tender types against config                        │
│  3. Build CreateOrderDTO from cart                              │
│  4. OrderService::createOrder(dto)                              │
│     ├─ Creates Order + OrderItems + OrderAddresses              │
│     └─ Dispatches OrderCreated                                  │
│         ├─ Inventory: DeductInventoryStock                      │
│         └─ Future: CRM, Notifications                           │
│  5. OrderPaymentService::recordPayment() for each tender        │
│  6. Clear POS cart                                              │
│  7. Dispatch POSCheckoutCompleted                               │
└─────────────────────────────────────────────────────────────────┘
    │
    ▼
┌─────────────────┐
│ Print Receipt   │  ──►  Generate receipt data
│                  │        Pass to print service
│ Thank you!      │        Thermal or A4 format
└─────────────────┘
```

---

## 11. Integration with Industry Packs

### POS Config Consumption

```php
class POSController
{
    public function __construct(
        private TenantConfig $config,
        private POSSessionService $sessionService,
        private POSCartService $cartService,
    ) {}

    public function index(): Response
    {
        $posConfig = $this->config->posConfig();

        $session = $this->sessionService->getCurrentSession(
            storeId: app(StoreContextManager::class)->id(),
            userId: auth()->id(),
        );

        if (! $session) {
            return Inertia::render('POS/SessionRequired', [
                'posConfig' => $posConfig,
            ]);
        }

        $cart = $this->cartService->getCart($session);

        return Inertia::render('POS/Index', [
            'session' => POSSessionDTO::fromModel($session),
            'cart' => $cart->toArray(),
            'posConfig' => $posConfig,
        ]);
    }
}
```

### Industry Config Per Pack

| Config Key | Type | Used By | Example Values |
|-----------|------|---------|---------------|
| `layout` | string | UI rendering | `grid`, `list`, `search` |
| `product_search_columns` | string[] | Search bar | `['name', 'sku', 'barcode', 'brand']` |
| `quick_actions` | array[] | Quick action buttons | `[{label: 'Dine-in', action: 'set_dine_in'}]` |
| `checkout_fields` | array[] | Checkout form | `[{slug: 'table_number', label: 'Table #', required: false}]` |
| `tender_types` | string[] | Payment selection | `['cash', 'card', 'mobile_banking']` |
| `batch_picking` | bool | Batch selection UI | `true`, `false` |
| `has_weight_scale` | bool | Quantity input | `true`, `false` |
| `has_tables` | bool | Table selection | `true`, `false` |
| `has_variant_selection` | bool | Variant picker | `true`, `false` |
| `has_staff_assignment` | bool | Staff assignment | `true`, `false` |
| `supports_fractional_quantity` | bool | Quantity input | `true`, `false` |
| `supports_tiered_pricing` | bool | Price display | `true`, `false` |
| `receipt_fields` | string[] | Receipt layout | `['items', 'subtotal', 'total']` |

### Industry-Specific Checkout Fields

| slug | Label | Type | Packs |
|------|-------|------|-------|
| `table_number` | Table # | text | Restaurant, Cafe |
| `guest_count` | Guests | number | Restaurant |
| `server_name` | Server | text | Restaurant |
| `prescription_number` | Prescription # | text | Pharmacy |
| `doctor_name` | Doctor Name | text | Pharmacy |
| `patient_name` | Patient Name | text | Pharmacy |
| `staff_id` | Staff Member | select | Salon, Spa |
| `service_duration` | Duration (mins) | number | Salon, Spa |
| `customer_name` | Name | text | Cafe, Bakery |
| `weight_kg` | Weight (kg) | decimal | Grocery, AgroShop |
| `quantity_kg` | Weight (kg) | decimal | AgroShop |
| `imei_number` | IMEI | text | Electronics |
| `serial_number` | Serial # | text | Electronics |
| `warranty_period` | Warranty (months) | number | Electronics |
| `size` | Size | select | Fashion |
| `color` | Color | select | Fashion |
| `customer_type` | Customer Type | select | Wholesale, AgroShop |
| `po_number` | PO Number | text | Wholesale |
| `custom_message` | Message on Cake | text | Bakery |
| `pickup_time` | Pickup Time | datetime | Bakery |
| `order_note` | Special Instructions | text | Restaurant, Cafe |
| `customer_phone` | Customer Phone | tel | Electronics |

---

## 12. POS Template Groups

### Concept

Not every business type needs a unique POS implementation. Similar business types share the same POS base layout, checkout flow, and feature set. Instead of each `IndustryPack.posConfig()` defining settings from scratch, business types are grouped into **POS Template Groups** that define shared defaults. Individual packs then override specific settings on top.

### Template Group Architecture

```
POS Template Group (defines base layout + features)
    │
    ├── Industry Pack A (inherits group defaults, overrides specifics)
    ├── Industry Pack B (inherits group defaults, overrides specifics)
    └── Industry Pack C (inherits group defaults, overrides specifics)
            │
            ▼
    Store POS Settings (final per-store overrides via UI)
```

### Defined Template Groups

| Group | Base Layout | Shared Features | Business Types |
|-------|-------------|----------------|----------------|
| **dine_in** | Grid with table/cart split | tables, takeaway, delivery, kitchen_display, order_type selector | Restaurant, Cafe |
| **service** | Grid with cart + appointment panel | staff_assignment, service_duration, packages, membership, duration_pricing | Salon, Spa |
| **retail** | Product grid with quick add | variant_selection, serial_tracking, warranty, shade_selection, fractional_qty | Fashion, Cosmetics, Electronics, Bookstore |
| **grocery** | Grid with weight scale | fractional_qty, perishable_tracking, weight_based_pricing, batch_picking | Grocery, Bakery, AgroShop |
| **pharmacy** | Grid with prescription panel | batch_picking, expiry_warning, prescription_check, insurance_billing | Pharmacy |
| **wholesale** | List with bulk quantities | tiered_pricing, bulk_discounts, PO_number, credit_terms | Wholesale, Hardware, Distribution |
| **hardware** | Grid with unit conversion | fractional_qty, unit_conversion, bulk_pricing | Hardware |
| **general** | Simple grid (default fallback) | basic_search, cash_card_mobile | Default (general retail) |

### Group Resolution

```php
// app/Modules/POS/Services/POSTemplateResolver.php

class POSTemplateResolver
{
    private array $groupMap = [
        'restaurant' => 'dine_in',
        'cafe'       => 'dine_in',
        'salon'      => 'service',
        'spa'        => 'service',
        'fashion'    => 'retail',
        'cosmetics'  => 'retail',
        'electronics' => 'retail',
        'bookstore'  => 'retail',
        'grocery'    => 'grocery',
        'bakery'     => 'grocery',
        'agro_shop'  => 'grocery',
        'pharmacy'   => 'pharmacy',
        'wholesale'  => 'wholesale',
        'hardware'   => 'hardware',
        'distribution' => 'wholesale',
    ];

    private array $groupDefaults = [
        'dine_in' => [
            'layout' => 'split',
            'tender_types' => ['cash', 'card', 'mobile_banking'],
            'has_tables' => true,
            'has_takeaway' => true,
            'has_delivery' => true,
            'has_kitchen_display' => true,
            'order_types' => ['dine_in', 'takeaway', 'delivery'],
            'receipt_fields' => ['items', 'table', 'server', 'subtotal', 'tax', 'total'],
        ],
        'service' => [
            'layout' => 'split',
            'tender_types' => ['cash', 'card', 'mobile_banking', 'membership'],
            'has_staff_assignment' => true,
            'has_service_duration' => true,
            'has_packages' => true,
            'has_memberships' => true,
            'receipt_fields' => ['services', 'staff', 'duration', 'subtotal', 'commission', 'total'],
        ],
        'retail' => [
            'layout' => 'grid',
            'tender_types' => ['cash', 'card', 'mobile_banking'],
            'has_variant_selection' => true,
            'receipt_fields' => ['items', 'subtotal', 'tax', 'total'],
        ],
        'grocery' => [
            'layout' => 'grid',
            'tender_types' => ['cash', 'card', 'mobile_banking'],
            'supports_fractional_quantity' => true,
            'has_weight_scale' => true,
            'batch_picking' => true,
            'receipt_fields' => ['items', 'weight', 'subtotal', 'total'],
        ],
        'pharmacy' => [
            'layout' => 'grid',
            'tender_types' => ['cash', 'card', 'mobile_banking', 'insurance'],
            'batch_picking' => true,
            'show_expiry_warning' => true,
            'require_prescription_check' => true,
            'receipt_fields' => ['drug_name', 'batch', 'expiry', 'manufacturer'],
        ],
        'wholesale' => [
            'layout' => 'list',
            'tender_types' => ['cash', 'bank_transfer', 'check', 'credit'],
            'supports_tiered_pricing' => true,
            'supports_bulk_discounts' => true,
            'show_unit_price' => true,
            'receipt_fields' => ['items', 'unit_price', 'quantity', 'discount', 'subtotal', 'total'],
        ],
        'hardware' => [
            'layout' => 'grid',
            'tender_types' => ['cash', 'card', 'mobile_banking'],
            'supports_fractional_quantity' => true,
            'supports_unit_conversion' => true,
            'receipt_fields' => ['items', 'unit', 'measurement', 'subtotal', 'total'],
        ],
        'general' => [
            'layout' => 'grid',
            'tender_types' => ['cash', 'card', 'mobile_banking'],
            'receipt_fields' => ['items', 'subtotal', 'total'],
        ],
    ];

    public function __construct(
        private TenantConfig $config,
    ) {}

    public function resolve(): array
    {
        $slug = $this->config->businessType;
        $group = $this->groupMap[$slug] ?? 'general';
        $defaults = $this->groupDefaults[$group] ?? $this->groupDefaults['general'];

        // Merge: group defaults ← industry pack overrides ← store overrides
        $industryConfig = $this->config->posConfig();
        $storeSettings = app(StoreContextManager::class)->current()?->pos_settings ?? [];

        return array_merge($defaults, $industryConfig, $storeSettings);
    }

    public function getGroup(): string
    {
        return $this->groupMap[$this->config->businessType] ?? 'general';
    }

    public function getGroupDefaults(string $group): ?array
    {
        return $this->groupDefaults[$group] ?? null;
    }
}
```

### Configuration Merge Chain

```
1. POS Template Group defaults  ──── base layout, shared features
         │
         ▼
2. IndustryPack.posConfig()    ──── industry-specific overrides
         │
         ▼
3. Store.pos_settings          ──── per-store UI/feature overrides
         │
         ▼
4. Final resolved config       ──── used by POSController to render UI
```

### Frontend Group Detection

```typescript
// resources/js/modules/pos/hooks/use-pos-template.ts
export function usePOSTemplate() {
    const config = usePOSConfig();

    const templateGroups = {
        dine_in: ['dine_in', 'takeaway', 'delivery'],
        service: ['walk_in', 'appointment'],
        retail: ['walk_in'],
        grocery: ['walk_in'],
        pharmacy: ['prescription', 'walk_in'],
        wholesale: ['po_based', 'walk_in'],
        hardware: ['walk_in'],
        general: ['walk_in'],
    };

    const templateGroup = config.template_group ?? 'general';
    const quickActions = templateGroups[templateGroup] ?? templateGroups.general;

    return {
        templateGroup,
        quickActions,
        isDineIn: templateGroup === 'dine_in',
        isService: templateGroup === 'service',
        isPharmacy: templateGroup === 'pharmacy',
        isWholesale: templateGroup === 'wholesale',
        hasWeightScale: config.supports_fractional_quantity || config.has_weight_scale,
        hasVariants: config.has_variant_selection,
        hasStaff: config.has_staff_assignment,
        hasBatchPicking: config.batch_picking,
    };
}
```

### Benefits

| Before | After |
|--------|-------|
| 15 independent POS configs, duplicated settings | 8 template groups, shared defaults, per-type overrides |
| `IndustryPack.posConfig()` must define everything | Only overrides needed — group fills the rest |
| Adding a new business type requires full posConfig() | Only need to map to a group + add specific overrides |
| POS frontend has no context about business category | Frontend knows the template group and adapts UI layout |

---

## 13. Multi-Store Integration

- POS sessions are scoped to a store via `store_id`
- Product prices resolved through `store_product` pivot
- Store-level `pos_settings` from `Store.pos_settings` JSON column
- Route is inside `{store}` prefix with `InitializeStoreContext` middleware
- Each store can have multiple registers
- Store switcher shows POS only for active stores

```php
// Store.pos_settings override IndustryPack defaults
$storePosSettings = $store->pos_settings ?? [];
$industryPosConfig = $this->config->posConfig();
$resolvedConfig = array_merge($industryPosConfig, $storePosSettings);
```

---

## 13. Frontend Architecture

### Page Structure

```
resources/js/pages/pos/
├── index.tsx                    (main POS interface)
├── session-open.tsx             (open session form)
├── session-close.tsx            (close session form)
├── components/
│   ├── product-search.tsx       (search bar with results)
│   ├── product-grid.tsx         (product grid/list)
│   ├── cart-panel.tsx           (right side cart)
│   ├── cart-item.tsx            (single cart line)
│   ├── checkout-panel.tsx       (checkout form)
│   ├── payment-methods.tsx      (tender type selection)
│   ├── split-payment.tsx        (split payment UI)
│   ├── customer-select.tsx      (customer lookup)
│   ├── receipt-preview.tsx      (receipt before print)
│   └── quick-actions.tsx        (industry quick actions)
```

### State Management

POS state is managed via React context + React Query:

```typescript
// resources/js/modules/pos/hooks/use-pos-session.ts
export function usePOSSession() {
    return useQuery({
        queryKey: ['pos', 'session'],
        queryFn: () => axios.get(route('pos.session.current')),
        refetchInterval: 30000, // refresh every 30s
    });
}

// resources/js/modules/pos/hooks/use-pos-cart.ts
export function usePOSCart() {
    return useQuery({
        queryKey: ['pos', 'cart'],
        queryFn: () => axios.get(route('pos.cart')),
    });
}

export function useAddToCart() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (payload: AddToCartPayload) =>
            axios.post(route('pos.cart.add'), payload),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['pos', 'cart'] }),
    });
}

export function useCheckout() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (payload: CheckoutPayload) =>
            axios.post(route('pos.checkout'), payload),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['pos', 'cart'] });
            queryClient.invalidateQueries({ queryKey: ['pos', 'session'] });
        },
    });
}
```

### Inertia Shared Data

```php
// HandleInertiaRequests
'pos_config' => fn () => app(TenantConfig::class)->posConfig(),
```

### Industry-POS UI Adaptation

```typescript
// resources/js/modules/pos/hooks/use-pos-config.ts
export function usePOSConfig() {
    const { pos_config } = usePage().props;
    return pos_config as POSConfig;
}

// In POS product search
function ProductSearch() {
    const config = usePOSConfig();
    const searchColumns = config.product_search_columns;
    // Render search with industry-specific columns
}

// In POS checkout
function CheckoutPanel() {
    const config = usePOSConfig();
    const fields = config.checkout_fields;
    const tenderTypes = config.tender_types;
    // Render industry-specific checkout form
}

// In POS quick actions
function QuickActions() {
    const config = usePOSConfig();
    const actions = config.quick_actions;
    // Render industry-specific action buttons
}
```

---

## 14. Testing Strategy

### Test Categories

| Category | Scope | Framework |
|----------|-------|-----------|
| Unit tests | Enums, DTOs, Services (mocked), Exceptions, PriceResolver | Pest |
| Feature tests | Session open/close, cart operations, checkout workflow | Pest |
| Integration | POS checkout → Order Created → Inventory stock deduction | Pest |

### Test Patterns

```php
// Unit test — Session lifecycle
it('prevents opening two sessions for same user', function () {
    $session = POSSession::factory()->create([
        'user_id' => $user->id,
        'store_id' => $store->id,
        'status' => POSSessionStatusEnum::Open,
    ]);

    expect(fn () => $service->openSession(
        storeId: $store->id,
        userId: $user->id,
        cashierName: 'John',
        openingBalance: 5000,
    ))->toThrow(POSSessionAlreadyOpenException::class);
});

// Feature test — Checkout flow
it('completes a POS checkout and creates order', function () {
    Event::fake();

    $store = Store::factory()->create();
    $session = POSSession::factory()->create([
        'store_id' => $store->id,
        'user_id' => auth()->id(),
        'status' => POSSessionStatusEnum::Open,
    ]);

    // Add item to cart
    $this->post(route('pos.cart.add', ['store' => $store->id]), [
        'product_id' => $product->ulid,
        'quantity' => 2,
    ])->assertOk();

    // Checkout
    $response = $this->post(route('pos.checkout', ['store' => $store->id]), [
        'payments' => [['method' => 'cash', 'amount' => 10000]],
        'order_type' => 'in_store',
    ]);

    $response->assertOk();
    Event::assertDispatched(OrderCreated::class);
});

// Test — Industry config affects checkout fields
it('shows prescription field for pharmacy POS', function () {
    // Mock TenantConfig to return pharmacy config
    // Then assert checkout_fields includes prescription_number
});
```

### Test Database Considerations

- Same `souda_testing` DB constraints as other modules
- Do NOT run tests in parallel
- Use `Store::factory()->create()` with explicit store context
- Session tests should clean up between cases

---

## 15. Coding Standards & AI Guardrails

### MUST FOLLOW

- All monetary values in cents (integer)
- POS config comes from `IndustryPack.posConfig()` — never hardcoded
- No `if ($slug === 'pharmacy')` checks anywhere in POS module
- Cart items are transient — never permanently stored after checkout
- Sessions are per-user+per-store — one open session per cashier per store
- Use `DB::transaction()` for checkout flow
- Print receipt asynchronously (queued job)
- Use `EventDispatchable` trait on all POS events
- Use Form Request classes for validation
- Add `->orderBy('id')` before `->each()` on query builders
- Run `vendor/bin/pint --format agent` after all PHP changes

### NEVER

- Write stock directly — dispatch events for Inventory Engine
- Hardcode industry-specific behavior
- Allow checkout without an open session
- Store cart data permanently after checkout
- Modify Order records directly — use OrderService
- Use `env()` outside config files
- Create per-industry tables or columns
- Leave empty `__construct()` with zero params (unless private)
- Commit secrets or credentials

### ALWAYS

- Validate session is open before checkout
- Check tender types against allowed config
- Use `StoreContextManager` for store context
- Use `TenantConfig` for industry-specific behavior
- Wrap checkout in `DB::transaction()` with retry
- Calculate and validate session balance on close
- Test both success and failure paths
- Write feature tests for every POS workflow

---

## 16. Exceptions

```php
class POSSessionNotOpenException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Cannot process checkout without an open POS session.');
    }
}

class POSSessionAlreadyOpenException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('A POS session is already open for this user and store.');
    }
}

class POSInvalidTenderException extends \RuntimeException
{
    public function __construct(string $tenderType)
    {
        parent::__construct("Tender type '{$tenderType}' is not allowed for this business type.");
    }
}

class POSInsufficientStockException extends \RuntimeException
{
    public function __construct(string $productName, int $available)
    {
        parent::__construct("Insufficient stock for '{$productName}'. Only {$available} available.");
    }
}
```

---

## 17. Database Migrations

### pos_sessions table (Tenant)

```php
Schema::create('pos_sessions', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('store_id', 36);
    $table->string('user_id', 36);
    $table->string('cashier_name', 255);
    $table->string('register_id', 36)->nullable();
    $table->timestamp('opened_at');
    $table->timestamp('closed_at')->nullable();
    $table->bigInteger('opening_balance')->default(0);
    $table->bigInteger('closing_balance')->nullable();
    $table->bigInteger('expected_balance')->nullable();
    $table->bigInteger('difference')->nullable();
    $table->bigInteger('total_sales')->default(0);
    $table->unsignedInteger('total_transactions')->default(0);
    $table->string('status', 20)->default('open');
    $table->text('notes')->nullable();
    $table->text('closing_notes')->nullable();
    $table->json('summary_data')->nullable();
    $table->timestamps();

    $table->foreign('store_id')->references('id')->on('stores');
    $table->index('store_id');
    $table->index('user_id');
    $table->index('status');
    $table->index(['store_id', 'user_id', 'status']);
});
```

### pos_registers table (Tenant)

```php
Schema::create('pos_registers', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('store_id', 36);
    $table->string('name', 255);
    $table->string('device_id', 255)->nullable();
    $table->string('status', 20)->default('active');
    $table->json('config')->nullable();
    $table->timestamps();

    $table->foreign('store_id')->references('id')->on('stores');
    $table->index('store_id');
});
```

### pos_cart_items table (Tenant)

```php
Schema::create('pos_cart_items', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->string('session_id', 36);
    $table->string('user_id', 36)->nullable();
    $table->string('store_id', 36);
    $table->string('product_id', 36);
    $table->string('variant_id', 36)->nullable();
    $table->string('name', 255);
    $table->string('sku', 100)->nullable();
    $table->string('barcode', 100)->nullable();
    $table->integer('quantity');
    $table->bigInteger('unit_price');
    $table->bigInteger('total_price');
    $table->bigInteger('tax_amount')->nullable();
    $table->bigInteger('discount_amount')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();

    $table->foreign('session_id')->references('id')->on('pos_sessions')->cascadeOnDelete();
    $table->index('session_id');
    $table->index('product_id');
});
```

---

## 18. POS Service Provider

```php
class POSServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(POSSessionService::class);
        $this->app->singleton(POSCartService::class);
        $this->app->singleton(POSCheckoutService::class);
        $this->app->singleton(POSPriceResolver::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations/Tenant');
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        Route::middleware([
            'web', 'auth', InitializeTenancyByUser::class,
            'store.context', 'subscription',
        ])->prefix('{store}')->name('pos.')->group(function () {

            // Session routes (no session required)
            Route::get('/pos', [POSController::class, 'index'])->name('index');
            Route::post('/pos/session/open', [POSSessionController::class, 'open'])->name('session.open');
            Route::post('/pos/session/close', [POSSessionController::class, 'close'])->name('session.close');
            Route::get('/pos/session/current', [POSSessionController::class, 'current'])->name('session.current');

            // Cart routes (session required — middleware)
            Route::middleware('pos.session')->group(function () {
                Route::get('/pos/cart', [POSController::class, 'cart'])->name('cart');
                Route::post('/pos/cart/add', [POSController::class, 'addToCart'])->name('cart.add');
                Route::put('/pos/cart/{item}', [POSController::class, 'updateCart'])->name('cart.update');
                Route::delete('/pos/cart/{item}', [POSController::class, 'removeFromCart'])->name('cart.remove');
                Route::post('/pos/cart/clear', [POSController::class, 'clearCart'])->name('cart.clear');

                // Checkout
                Route::post('/pos/checkout', [POSController::class, 'checkout'])->name('checkout');

                // Orders list within POS
                Route::get('/pos/orders', [POSController::class, 'orders'])->name('orders');
                Route::get('/pos/orders/{order}', [POSController::class, 'orderDetail'])->name('orders.show');
            });
        });
    }
}
```

### POS Session Middleware

```php
// app/Modules/POS/Http/Middleware/EnsurePOSSession.php

class EnsurePOSSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $session = app(POSSessionService::class)->getCurrentSession(
            storeId: app(StoreContextManager::class)->id(),
            userId: auth()->id(),
        );

        if (! $session) {
            return redirect()->route('pos.index', ['store' => $request->route('store')])
                ->with('error', 'Please open a POS session first.');
        }

        if (! $session->isOpen()) {
            return redirect()->route('pos.index', ['store' => $request->route('store')])
                ->with('error', 'Your POS session is not active.');
        }

        $request->attributes->set('pos_session', $session);

        return $next($request);
    }
}
```

---

## 19. Industry Pack POS Config — Complete Reference

All 15 IndustryPacks define `posConfig()`. The POS module reads these values at runtime. Here is a consolidated reference sorted by template group:

| Template Group | Pack | Layout | Search Columns | Tender Types | Specialty |
|----------------|------|--------|---------------|--------------|-----------|
| **dine_in** | Restaurant | split | name, sku, category | cash, card, mobile_banking | tables, takeaway, delivery, kitchen_display |
| **dine_in** | Cafe | split | name, category | cash, card, mobile_banking | tables, takeaway, kitchen_display |
| **service** | Salon | split | name, category, staff_name | cash, card, mobile_banking, membership | staff_assignment, service_duration, packages |
| **service** | Spa | split | name, category | cash, card, mobile_banking, membership | memberships, packages, rooms |
| **retail** | Fashion | grid | name, sku, barcode, brand | cash, card, mobile_banking | variant_selection, size_chart, color_swatches |
| **retail** | Cosmetics | grid | name, brand, shade, category | cash, card, mobile_banking | shade_selection |
| **retail** | Electronics | grid | name, sku, barcode, model, brand | cash, card, mobile_banking, installment | imei_scanning, serial_tracking, warranty |
| **retail** | Bookstore | grid | name, isbn, author, publisher | cash, card, mobile_banking | isbn_scanning, gift_wrap |
| **grocery** | Grocery | grid | name, sku, barcode | cash, card, mobile_banking | weight_scale, fractional_qty |
| **grocery** | Bakery | grid | name, category | cash, card, mobile_banking | custom_orders, pickup_time |
| **grocery** | AgroShop | grid | name, sku, category | cash, card, mobile_banking | fractional_qty, customer_type |
| **pharmacy** | Pharmacy | grid | name, sku, barcode, generic_name, strength | cash, card, mobile_banking, insurance | batch_picking, prescription_check |
| **wholesale** | Wholesale | list | name, sku, barcode | cash, bank_transfer, check, credit | tiered_pricing, bulk_discounts, PO |
| **wholesale** | Distribution | (no POS) | — | — | — |
| **hardware** | Hardware | grid | name, sku, barcode, category | cash, card, mobile_banking | fractional_qty, unit_conversion |

---

## 20. Receipt Printing

### Thermal Receipt Format

```php
interface ReceiptRenderer
{
    public function render(OrderDTO $order, POSSessionDTO $session, array $config): string;
}

class ThermalReceiptRenderer implements ReceiptRenderer
{
    public function render(OrderDTO $order, POSSessionDTO $session, array $config): string
    {
        $lines = [];

        // Header
        $lines[] = str_repeat('=', 32);
        $lines[] = str_pad(store('name'), 32, ' ', STR_PAD_BOTH);
        $lines[] = str_repeat('=', 32);
        $lines[] = '';

        // Items
        foreach ($order->lineItems as $item) {
            $lines[] = sprintf(
                "%-2dx %-20s %8s",
                $item->quantity,
                Str::limit($item->name, 20),
                number_format($item->totalPrice / 100, 2)
            );
        }

        // Totals
        $lines[] = str_repeat('-', 32);
        $lines[] = sprintf("%-24s %8s", 'Subtotal', number_format($order->subtotal / 100, 2));
        $lines[] = sprintf("%-24s %8s", 'Total', number_format($order->grandTotal / 100, 2));
        $lines[] = '';

        // Footer
        $lines[] = str_pad('Thank you!', 32, ' ', STR_PAD_BOTH);
        $lines[] = str_repeat('=', 32);

        return implode("\n", $lines);
    }
}
```

### A4 Invoice Format

```php
class A4InvoiceRenderer implements ReceiptRenderer
{
    public function render(OrderDTO $order, POSSessionDTO $session, array $config): string
    {
        // Generate HTML with store header, line items table, totals
        // Return HTML string for dompdf or similar
    }
}
```

---

## 21. Development Roadmap

### Phase 1A: Foundation (Week 1)

| Task | Deliverable |
|------|-------------|
| Create module structure | `app/Modules/POS/` with all directories |
| Create tenant migrations | pos_sessions, pos_registers, pos_cart_items |
| Implement Models | POSSession, POSRegister, POSCartItem |
| Implement Enums | POSSessionStatusEnum, POSRegisterStatusEnum |
| Implement POSSessionService | open, close, getCurrent, getReport |
| Implement POSCartService | addItem, updateQuantity, removeItem, clearCart, getCart |
| Create POSServiceProvider | Singleton bindings, migration loading, route registration |
| Create POS Session middleware | EnsurePOSSession |
| Create tests | Session lifecycle, cart operations |

### Phase 1B: Checkout & Orders (Week 2)

| Task | Deliverable |
|------|-------------|
| Implement POSPriceResolver | Store pivot price lookup, variant pricing |
| Implement POSCheckoutService | Complete checkout → Order flow |
| Implement POSCheckoutDTO | Checkout data transfer |
| Register POSCheckoutCompleted event | Event + dispatch |
| Connect to OrderService | Checkout creates order via Order module |
| Create tests | Full checkout flow, payment validation |

### Phase 1C: HTTP & Frontend (Week 3)

| Task | Deliverable |
|------|-------------|
| Implement POSController | index, cart operations, checkout |
| Implement POSSessionController | open, close, current |
| Implement Form Requests | POSAddToCartRequest, POSCheckoutRequest, etc. |
| Build POS main interface | Product search, grid, cart panel |
| Build session management | Open/close session forms |
| Build checkout panel | Customer select, payment, review |
| Build receipt view | Thermal receipt preview + print |

### Phase 1D: Industry Integration & Advanced (Week 4)

| Task | Deliverable |
|------|-------------|
| Implement POSTemplateResolver | Resolves template group per business type, merges config chain |
| Implement POS template groups | 8 groups defined with shared defaults (dine_in, service, retail, grocery, pharmacy, wholesale, hardware, general) |
| Integrate all 15 IndustryPack posConfig() | Dynamic UI per industry via template group |
| Implement quick actions | Industry-specific action buttons per template group |
| Implement checkout fields | Dynamic form fields per industry |
| Implement receipt rendering | Thermal + A4 formats |
| Implement session reporting | End-of-day summary, discrepancy tracking |
| Integrate multi-store | Store-scoped sessions and pricing |
| Create tests | Industry config tests, template group resolution tests, receipt tests |

---

## 22. Phase 1 Completion Checklist

- [ ] All migrations created and ordered correctly (3 tables)
- [ ] `POSServiceProvider` registered in `bootstrap/providers.php`
- [ ] All 2 enums implemented with labels and transitions
- [ ] All 4 DTOs implemented
- [ ] All 3 events defined and dispatched
- [ ] All 4 exception classes implemented
- [ ] POS Session middleware created and registered
- [ ] POSCheckoutService fully integrates with OrderService
- [ ] Routes registered under `{store}` prefix with `pos.` name prefix
- [ ] POSTemplateResolver resolves template group per business type (8 groups)
- [ ] Industry pack integration reads `posConfig()` dynamically
- [ ] Template group default configs merge with `posConfig()` and store overrides
- [ ] All 15 industry POS configs render correctly
- [ ] Multi-store scoping working (store_id on sessions)
- [ ] Session balance calculation and discrepancy tracking working
- [ ] Receipt printing (thermal + A4) implemented
- [ ] Feature tests cover session, cart, checkout, and industry config
- [ ] Unit tests cover enums, DTOs, services, exceptions
- [ ] `vendor/bin/pint --format agent` run without errors
