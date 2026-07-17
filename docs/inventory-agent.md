# SOUDA — Inventory Module Architecture Guide

This file is for AI coding agents implementing the Inventory module. It defines the complete architecture, conventions, guardrails, patterns, interfaces, and implementation rules for the SOUDA Inventory Engine.

---

## 1. Project Scope & Inventory Philosophy

### Vision

SOUDA's Inventory module is not a stock-tracking CRUD — it is an **Automated Inventory Engine**. Every business operation (purchase, sale, return, transfer, manufacturing, adjustment) generates domain events. The Inventory Engine consumes those events and produces immutable stock movements, cost layers, balance snapshots, reservations, and alerts — all without manual stock entry.

### Target Audience

SME business owners in 15+ industries (retail, pharmacy, restaurant, grocery, electronics, fashion, etc.) who need real-time inventory visibility without operational overhead. The system must be intuitive enough that a shop manager can understand stock health in 10 seconds, yet powerful enough to handle batch tracking, expiry management, and multi-warehouse transfers.

### Scope — Phase 1

| Feature | Included |
|---------|----------|
| Immutable inventory ledger | ✅ |
| Stock movement recording | ✅ |
| Inventory balance (real-time snapshot) | ✅ |
| Weighted average costing | ✅ |
| FIFO costing layers | ✅ |
| Stock reservations & allocation | ✅ |
| Multi-warehouse support | ✅ |
| Warehouse bin/shelf locations | ✅ |
| Batch/lot tracking | ✅ |
| Serial number tracking | ✅ |
| Expiry date management | ✅ |
| Low stock & depletion alerts | ✅ |
| Stock transfer between warehouses | ✅ |
| Audit trail (who did what, when) | ✅ |
| Frontend management pages | ✅ |
| Industry Pack integration | ✅ |
| Automation rules engine | Phase 2 |
| Demand forecasting | Phase 2 |
| Reorder suggestions | Phase 2 |

### One Golden Rule

> **No module ever writes to stock directly.** Every module (POS, Purchase, Sales, Manufacturing, Returns) publishes domain events. Only the Inventory Engine translates those events into stock movements and inventory balances. This is non-negotiable.

---

## 2. Core Architectural Principles

### Principle 1: Ledger Immutability

The `inventory_ledger` table is INSERT-only. Once a stock movement is recorded, it is never updated or deleted. Corrections are made via offsetting entries (reversal movements), not row mutations. This guarantees a complete audit trail and prevents data loss.

```php
// WRONG — never update or delete ledger rows
InventoryLedger::where('id', $id)->update(['quantity' => 0]);

// RIGHT — reversal
StockMovementEngine::record(
    productId: $productId,
    warehouseId: $warehouseId,
    quantity: -$originalQuantity,
    type: MovementTypeEnum::Adjustment,
    reference: "Reversal of ledger entry #{$originalId}",
    metadata: ['reverses' => $originalId],
);
```

### Principle 2: Event-Driven Decoupling

Every inventory operation is triggered by a domain event. The Inventory Engine never exposes public write methods that bypass events. Modules communicate through events, not service calls.

```
SaleCompleted ──► InventoryEngine::onSaleCompleted()
                      └──► StockMovementEngine::record()
                            └──► InventoryBalanceService::recalculate()
                                  └──► CostingEngine::apply()
```

### Principle 3: Balance = Derived State

`InventoryBalance` is a computed snapshot derived from the ledger. It is updated synchronously on every write for read performance, but can always be rebuilt from scratch by replaying the ledger. This means:

- `InventoryBalance` can be dropped and rebuilt without data loss
- The ledger is the source of truth, not the balance
- Reconciliation means replaying the ledger and comparing the computed balance

### Principle 4: Costing Consistency

Every stock movement carries a `unit_cost` at the time of recording. The Costing Engine maintains cost layers (Weighted Average or FIFO) and recalculates the moving average cost on every inbound movement. Outbound movements consume cost from layers in FIFO order or at the current weighted average.

### Principle 5: Industry-Neutral Core

The core inventory engine (ledger, balance, costing, reservations) is completely industry-agnostic. Industry-specific behaviors (FEFO for pharmacy, recipe deduction for restaurants, IMEI for electronics) are implemented via Industry Pack feature flags and plugin listeners — never via hardcoded checks in the engine.

---

## 3. Module Boundaries & Responsibilities

```
┌─────────────────────────────────────────────────────────────────┐
│                     Inventory Module                              │
│                                                                   │
│  ┌─────────────────┐  ┌────────────────┐  ┌──────────────────┐  │
│  │ InventoryEngine  │  │ StockMovement  │  │ InventoryBalance  │  │
│  │ (Orchestrator)   │──│ Engine         │──│ Service           │  │
│  └─────────┬───────┘  └────────────────┘  └──────────────────┘  │
│            │                                                      │
│  ┌─────────▼───────┐  ┌────────────────┐  ┌──────────────────┐  │
│  │ CostingEngine    │  │ Reservation    │  │ BatchService      │  │
│  │ (FIFO/WAVG)      │──│ Engine         │──│ (Lot/Expiry)      │  │
│  └─────────────────┘  └────────────────┘  └──────────────────┘  │
│                                                                   │
│  ┌─────────────────┐  ┌────────────────┐  ┌──────────────────┐  │
│  │ SerialNumber     │  │ AuditService   │  │ AlertEngine       │  │
│  │ Service          │──│                │──│ (Thresholds)      │  │
│  └─────────────────┘  └────────────────┘  └──────────────────┘  │
│                                                                   │
│  ┌─────────────────┐  ┌────────────────┐                         │
│  │ WarehouseService │  │ TransferEngine │                         │
│  └─────────────────┘  └────────────────┘                         │
└─────────────────────────────────────────────────────────────────┘
```

### Module Responsibilities

| Component | Responsibility |
|-----------|---------------|
| `InventoryEngine` | Single public entry point. Subscribes to domain events, validates business rules, delegates to sub-engines |
| `StockMovementEngine` | Records immutable ledger entries. Generates movement reference numbers |
| `InventoryBalanceService` | Maintains real-time balance snapshots. Rebuilds from ledger on demand |
| `CostingEngine` | Manages cost layers. Weighted average recalculation. FIFO layer consumption |
| `ReservationEngine` | Creates, consumes, expires, and cancels stock reservations |
| `BatchService` | Batch/lot tracking. Expiry date management. FEFO picking order |
| `SerialNumberService` | Serial/IMEI tracking per unit. Status lifecycle: available to sold to returned |
| `WarehouseService` | Warehouse and bin/shelf location management |
| `TransferEngine` | Orchestrates inter-warehouse stock transfers with in-transit status |
| `AuditService` | Logs all inventory operations with before/after snapshots |
| `AlertEngine` | Evaluates thresholds (low stock, expiry, overstock, dead stock). Dispatches alerts |

---

## 4. Inventory Engine Architecture

### InventoryEngine (Orchestrator)

The `InventoryEngine` is the single public facade for all inventory write operations. It receives domain events, validates them against business rules, and coordinates the sub-engines.

```php
class InventoryEngine
{
    public function __construct(
        private StockMovementEngine $movementEngine,
        private InventoryBalanceService $balanceService,
        private CostingEngine $costingEngine,
        private ReservationEngine $reservationEngine,
        private BatchService $batchService,
        private SerialNumberService $serialService,
        private TransferEngine $transferEngine,
        private AuditService $auditService,
        private AlertEngine $alertEngine,
        private TenantConfig $config,
    ) {}

    // These are the ONLY public write methods on this class:
    public function handleSaleCompleted(SaleCompleted $event): void;
    public function handlePurchaseReceived(PurchaseReceived $event): void;
    public function handleReturnApproved(ReturnApproved $event): void;
    public function handleAdjustmentApproved(AdjustmentApproved $event): void;
    public function handleTransferInitiated(TransferInitiated $event): void;
    public function handleTransferCompleted(TransferCompleted $event): void;
    public function handleProductionCompleted(ProductionCompleted $event): void;
    public function handleRecipeConsumed(RecipeConsumed $event): void;
    public function handleReservationCreated(ReservationCreated $event): void;
    public function handleReservationCancelled(ReservationCancelled $event): void;
}
```

### Engine Workflow (Example: Sale)

```
1. SaleCompleted event received
2. InventoryEngine::handleSaleCompleted()
3.   Validate: product exists, warehouse exists, quantity is positive
4.   If batches tracked: resolve which batches to deduct (FIFO/FEFO)
5.   If serial tracked: validate each serial number is available
6.   ReserveEngine::consumeReservation(orderReference) if pre-reserved
7.   StockMovementEngine::record(sale, -qty, unitCost, reference)
8.   InventoryBalanceService::recalculate(productId, warehouseId)
9.   CostingEngine::consumeLayers(productId, qty, costingMethod)
10.  BatchService::deduct(productId, batchId, qty) if batch tracked
11.  SerialNumberService::markAsSold(serialNumbers) if serial tracked
12.  AuditService::log('sale', before, after, actor)
13.  AlertEngine::evaluate(productId, warehouseId)
14.  Dispatch InventoryBalanceUpdated, StockDepleted (if applicable)
```

---

## 5. Stock Movement Engine

### Movement Types

```php
enum MovementTypeEnum: string
{
    case PurchaseReceipt = 'purchase_receipt';
    case SaleDeduction = 'sale_deduction';
    case ReturnRestock = 'return_restock';
    case TransferOut = 'transfer_out';
    case TransferIn = 'transfer_in';
    case AdjustmentIncrease = 'adjustment_increase';
    case AdjustmentDecrease = 'adjustment_decrease';
    case ProductionOutput = 'production_output';
    case RecipeConsumption = 'recipe_consumption';
    case ReservationDeduction = 'reservation_deduction';
    case ReservationRelease = 'reservation_release';
    case Reversal = 'reversal';
    case InitialStock = 'initial_stock';
}
```

### StockMovementEngine

```php
class StockMovementEngine
{
    public function record(
        string $productId,
        ?string $variantId,
        string $warehouseId,
        int $quantity,
        MovementTypeEnum $type,
        string $reference,
        ?int $unitCost = null,
        ?string $batchId = null,
        ?array $serialNumbers = null,
        ?string $description = null,
        array $metadata = [],
    ): InventoryLedger;

    public function findById(string $id): ?InventoryLedger;
    public function findByReference(string $reference): Collection;
    public function findByProduct(string $productId, array $filters = []): Collection;
}
```

### Reference Number Format

```
MOV-{YYYYMMDD}-{XXXX}
    Example: MOV-20260715-0042

Prefixes by type:
  PUR-{date}-{seq}   Purchase Receipt
  SAL-{date}-{seq}   Sale Deduction
  RET-{date}-{seq}   Return Restock
  TRF-{date}-{seq}   Transfer
  ADJ-{date}-{seq}   Adjustment
  PROD-{date}-{seq}  Production
  REC-{date}-{seq}   Recipe Consumption
```

---

## 6. Inventory Ledger Design

### Table: `inventory_ledger`

```php
Schema::create('inventory_ledger', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id', 36);
    $table->ulid('product_id');
    $table->ulid('variant_id')->nullable();
    $table->unsignedBigInteger('warehouse_id');
    $table->unsignedBigInteger('bin_id')->nullable();
    $table->integer('quantity');
    $table->integer('quantity_before');
    $table->integer('quantity_after');
    $table->string('movement_type', 30);
    $table->string('reference', 100);
    $table->string('reference_type', 30);
    $table->unsignedBigInteger('batch_id')->nullable();
    $table->json('serial_numbers')->nullable();
    $table->bigInteger('unit_cost')->nullable();
    $table->bigInteger('total_cost')->nullable();
    $table->text('description')->nullable();
    $table->json('metadata')->nullable();
    $table->string('created_by', 36)->nullable();
    $table->timestamp('created_at')->useCurrent();

    $table->index(['product_id', 'warehouse_id', 'created_at']);
    $table->index(['reference', 'reference_type']);
    $table->index(['batch_id']);
    $table->index(['tenant_id', 'created_at']);
});
```

### Table Rules

- No `updated_at` — ledger entries are immutable
- No `deleted_at` — soft deletes defeat the audit purpose
- `quantity_before` and `quantity_after` as integrity checkpoints
- `unit_cost` is always in cents (integer) to avoid floating-point drift
- `total_cost = unit_cost * |quantity|` — pre-calculated for reporting
- `metadata` JSON column for extensibility without schema changes

---

## 7. Inventory Balance

### Table: `inventory_balances`

```php
Schema::create('inventory_balances', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id', 36);
    $table->ulid('product_id');
    $table->ulid('variant_id')->nullable();
    $table->unsignedBigInteger('warehouse_id');
    $table->integer('quantity')->default(0);
    $table->integer('reserved_quantity')->default(0);
    $table->integer('available_quantity')->default(0);
    $table->bigInteger('average_unit_cost')->default(0);
    $table->bigInteger('total_stock_value')->default(0);
    $table->unsignedBigInteger('lock_version')->default(0);
    $table->timestamp('last_movement_at')->nullable();
    $table->timestamps();

    $table->unique(['product_id', 'variant_id', 'warehouse_id']);
    $table->index(['warehouse_id', 'quantity']);
});
```

### Balance Calculation

```php
class InventoryBalanceService
{
    public function recalculate(
        string $productId,
        ?string $variantId,
        string $warehouseId,
    ): void;

    public function rebuildFromLedger(
        ?string $productId = null,
        ?string $warehouseId = null,
    ): void;

    public function recalculateBatch(array $productWarehousePairs): void;
}
```

### Optimistic Locking

`lock_version` uses Laravel's `HasOptimisticLocking` trait. On concurrent writes, the first request succeeds and the second receives a `ModelNotFoundException` — the caller must retry.

```php
try {
    $balance = InventoryBalance::where([
        'product_id' => $productId,
        'warehouse_id' => $warehouseId,
    ])->firstOrFail();

    $balance->decrement('quantity', $quantity);
} catch (ModelNotFoundException) {
    throw new ConcurrentInventoryUpdateException($productId, $warehouseId);
}
```

---

## 8. Costing Strategies

### CostLayer Model

```php
Schema::create('cost_layers', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id', 36);
    $table->ulid('product_id');
    $table->ulid('variant_id')->nullable();
    $table->unsignedBigInteger('warehouse_id');
    $table->bigInteger('unit_cost');
    $table->integer('quantity_remaining');
    $table->integer('quantity_original');
    $table->string('costing_method', 20);
    $table->unsignedBigInteger('ledger_entry_id');
    $table->timestamp('created_at')->useCurrent();

    $table->index(['product_id', 'warehouse_id', 'costing_method']);
});
```

### Weighted Average (Default)

```php
class WeightedAverageCosting
{
    public function applyInbound(CostLayer $layer): void
    {
        $currentBalance = InventoryBalance::where([
            'product_id' => $layer->product_id,
            'warehouse_id' => $layer->warehouse_id,
        ])->first();

        $totalCost = ($currentBalance->quantity * $currentBalance->average_unit_cost)
                   + ($layer->quantity_remaining * $layer->unit_cost);
        $totalQty = $currentBalance->quantity + $layer->quantity_remaining;
        $newAvg = $totalQty > 0 ? (int) round($totalCost / $totalQty) : 0;

        $currentBalance->update([
            'average_unit_cost' => $newAvg,
            'total_stock_value' => $totalQty * $newAvg,
        ]);
    }

    public function consumeOutbound(
        string $productId,
        ?string $variantId,
        string $warehouseId,
        int $quantity,
    ): array {
        $balance = InventoryBalance::where([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ])->firstOrFail();

        $unitCost = $balance->average_unit_cost;
        return [
            'total_cost' => $quantity * $unitCost,
            'unit_cost' => $unitCost,
        ];
    }
}
```

### FIFO

```php
class FifoCosting
{
    public function consumeOutbound(
        string $productId,
        ?string $variantId,
        string $warehouseId,
        int $quantity,
    ): array {
        $layers = CostLayer::where([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'costing_method' => 'fifo',
        ])
        ->where('quantity_remaining', '>', 0)
        ->orderBy('created_at')
        ->get();

        $totalCost = 0;
        $remaining = $quantity;

        foreach ($layers as $layer) {
            $consume = min($remaining, $layer->quantity_remaining);
            $layer->decrement('quantity_remaining', $consume);
            $totalCost += $consume * $layer->unit_cost;
            $remaining -= $consume;
            if ($remaining <= 0) break;
        }

        return [
            'total_cost' => $totalCost,
            'unit_cost' => $quantity > 0 ? (int) round($totalCost / $quantity) : 0,
            'layers_consumed' => $layers->count(),
        ];
    }
}
```

### Costing Method Selection

```php
class Product extends Model
{
    public function costingMethod(): string
    {
        return $this->costing_method ?? config('inventory.default_costing_method', 'weighted_average');
    }
}

class CostingEngine
{
    public function __construct(
        private WeightedAverageCosting $weightedAverage,
        private FifoCosting $fifo,
    ) {}

    public function consume(
        string $productId,
        ?string $variantId,
        string $warehouseId,
        int $quantity,
    ): CostResult {
        $product = Product::findOrFail($productId);
        $strategy = $product->costingMethod() === 'fifo' ? $this->fifo : $this->weightedAverage;
        return $strategy->consumeOutbound($productId, $variantId, $warehouseId, $quantity);
    }
}
```

---

## 9. Reservation & Allocation Engine

### Reservation Lifecycle

```
Created (active) ──► Consumed (stock deducted)
    │                      │
    ▼                      ▼
Expired (auto-release)   Cancelled (manual)
```

### Table: `stock_reservations`

```php
Schema::create('stock_reservations', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id', 36);
    $table->ulid('product_id');
    $table->ulid('variant_id')->nullable();
    $table->unsignedBigInteger('warehouse_id');
    $table->integer('quantity');
    $table->string('status', 20);
    $table->string('reference', 100);
    $table->string('reference_type', 30);
    $table->timestamp('expires_at')->nullable();
    $table->timestamp('consumed_at')->nullable();
    $table->string('created_by', 36)->nullable();
    $table->timestamps();

    $table->index(['status', 'expires_at']);
    $table->index(['product_id', 'warehouse_id', 'status']);
    $table->index(['reference', 'reference_type']);
});
```

### ReservationEngine

```php
class ReservationEngine
{
    public function reserve(
        string $productId,
        ?string $variantId,
        string $warehouseId,
        int $quantity,
        string $reference,
        ?int $ttlMinutes = 30,
    ): StockReservation;

    public function consume(string $reference): void;

    public function release(string $reference): void;

    public function cancel(string $reference): void;

    public function expirePending(): int;

    public function available(
        string $productId,
        ?string $variantId,
        string $warehouseId,
    ): int;

    public function isAvailable(
        string $productId,
        ?string $variantId,
        string $warehouseId,
        int $quantity,
    ): bool;
}
```

### Allocation Rules

| Scenario | Rule |
|----------|------|
| POS sale with cart | Reserve on add-to-cart, expire in 30 min if not checked out |
| Online order | Reserve on creation, consume on payment |
| Backorder | Reserve when stock arrives (requires reorder engine) |
| Manual pick list | Reserve on pick list creation, consume on ship |
| Multi-warehouse | Reserve from nearest/default warehouse, fallback to others |

---

## 10. Warehouse, Location & Transfer Architecture

### Warehouse Hierarchy

```
Warehouse (physical location)
  └── Zone (e.g., Dry Storage, Cold Storage)
       └── Aisle (e.g., A, B, C)
            └── Rack (e.g., R01, R02)
                 └── Shelf (e.g., SH01, SH02)
                      └── Bin (e.g., A-R01-SH01-BIN01)
```

### Warehouse Table

```php
Schema::create('warehouses', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id', 36);
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('code', 20)->nullable();
    $table->string('type', 20)->default('physical');
    $table->boolean('is_default')->default(false);
    $table->string('address_line1')->nullable();
    $table->string('city')->nullable();
    $table->string('country', 2)->nullable();
    $table->string('contact_name')->nullable();
    $table->string('contact_email')->nullable();
    $table->string('contact_phone')->nullable();
    $table->json('metadata')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();

    $table->unique(['tenant_id', 'slug']);
});
```

### Bin Locations

```php
Schema::create('inventory_bins', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id', 36);
    $table->unsignedBigInteger('warehouse_id');
    $table->string('code', 50);
    $table->string('zone')->nullable();
    $table->string('aisle')->nullable();
    $table->string('rack')->nullable();
    $table->string('shelf')->nullable();
    $table->boolean('is_pickable')->default(true);
    $table->integer('max_weight_kg')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();

    $table->unique(['warehouse_id', 'code']);
    $table->index(['warehouse_id', 'zone']);
});
```

### Transfer Engine

```php
class TransferEngine
{
    public function initiate(
        string $fromWarehouseId,
        string $toWarehouseId,
        array $items,
        ?string $reference = null,
        ?string $description = null,
    ): TransferOrder;

    public function send(string $transferId): void;
    public function receive(string $transferId): void;
    public function cancel(string $transferId): void;

    public function partialReceive(
        string $transferId,
        array $receivedItems,
    ): void;
}
```

### Transfer Flow

```
1. Initiate  Source items reserved, TransferOrder status = draft
2. Send  Source stock deducted (TransferOut movement), status = in_transit
3. Receive  Destination stock added (TransferIn movement), status = completed
4. Cancel  Source reservation released, status = cancelled

For partial receives:
  - Only received quantities are transferred
  - Unreceived quantities stay in_transit
```

### Transfer Tables

```php
Schema::create('inventory_transfers', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id', 36);
    $table->string('reference', 50)->unique();
    $table->unsignedBigInteger('from_warehouse_id');
    $table->unsignedBigInteger('to_warehouse_id');
    $table->string('status', 20);
    $table->text('description')->nullable();
    $table->string('created_by', 36)->nullable();
    $table->timestamp('sent_at')->nullable();
    $table->timestamp('received_at')->nullable();
    $table->timestamps();
});

Schema::create('inventory_transfer_items', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('transfer_id');
    $table->ulid('product_id');
    $table->ulid('variant_id')->nullable();
    $table->integer('quantity');
    $table->integer('quantity_received')->default(0);
    $table->timestamps();
});
```

---

## 11. Batch, Expiry & Serial Number Management

### Batch Table

```php
Schema::create('inventory_batches', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id', 36);
    $table->ulid('product_id');
    $table->ulid('variant_id')->nullable();
    $table->string('batch_number', 100);
    $table->string('supplier_batch', 100)->nullable();
    $table->date('manufacturing_date')->nullable();
    $table->date('expiry_date')->nullable();
    $table->date('best_before')->nullable();
    $table->integer('initial_quantity');
    $table->integer('remaining_quantity');
    $table->bigInteger('unit_cost');
    $table->string('status', 20);
    $table->timestamps();

    $table->unique(['product_id', 'batch_number']);
    $table->index(['expiry_date', 'status']);
    $table->index(['warehouse_id']);
});
```

### FEFO Picking

```php
class BatchService
{
    public function pickBatches(
        string $productId,
        ?string $variantId,
        string $warehouseId,
        int $quantity,
        string $method = 'fefo',
    ): Collection {
        $query = InventoryBatch::where([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ])->where('remaining_quantity', '>', 0)
          ->whereIn('status', ['active']);

        if ($method === 'fefo') {
            $query->where('expiry_date', '>=', now())
                  ->orderBy('expiry_date');
        } elseif ($method === 'fifo') {
            $query->orderBy('manufacturing_date')->orderBy('id');
        } else {
            $query->orderByDesc('id');
        }

        return $this->allocateFromBatches($query->get(), $quantity);
    }

    public function receive(
        string $productId,
        ?string $variantId,
        string $warehouseId,
        string $batchNumber,
        int $quantity,
        ?string $supplierBatch = null,
        ?Carbon $manufacturingDate = null,
        ?Carbon $expiryDate = null,
        ?Carbon $bestBefore = null,
        int $unitCost = 0,
    ): InventoryBatch;

    public function deduct(string $batchId, int $quantity): void;
    public function quarantine(string $batchId, ?string $reason = null): void;
    public function findExpiring(Carbon $withinDays): Collection;
}
```

### Serial Number Table

```php
Schema::create('serial_numbers', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id', 36);
    $table->ulid('product_id');
    $table->ulid('variant_id')->nullable();
    $table->string('serial_number', 200);
    $table->string('status', 20);
    $table->unsignedBigInteger('warehouse_id')->nullable();
    $table->unsignedBigInteger('batch_id')->nullable();
    $table->string('order_reference')->nullable();
    $table->timestamp('sold_at')->nullable();
    $table->timestamp('warranty_expires_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->unique(['product_id', 'serial_number']);
    $table->index(['status', 'warehouse_id']);
});
```

### SerialNumberService

```php
class SerialNumberService
{
    public function register(
        string $productId,
        string $serialNumber,
        ?string $warehouseId = null,
        ?string $batchId = null,
    ): SerialNumber;

    public function validate(string $serialNumber, string $productId): bool;
    public function markAsSold(string $serialNumber, string $orderReference): void;
    public function markAsReturned(string $serialNumber): void;

    public function registerBatch(
        string $productId,
        array $serialNumbers,
        ?string $warehouseId = null,
        ?string $batchId = null,
    ): Collection;

    public function findByStatus(string $status, ?string $productId = null): Collection;
    public function warrantyStatus(string $serialNumber): string;
}
```

---

## 12. Manufacturing & Recipe Consumption

### Recipe / Bill of Materials

```php
Schema::create('recipes', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id', 36);
    $table->ulid('product_id');
    $table->ulid('variant_id')->nullable();
    $table->string('name');
    $table->text('instructions')->nullable();
    $table->integer('yield_quantity')->default(1);
    $table->string('waste_percentage')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('recipe_ingredients', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('recipe_id');
    $table->ulid('ingredient_product_id');
    $table->ulid('ingredient_variant_id')->nullable();
    $table->decimal('quantity', 12, 4);
    $table->string('unit', 20);
    $table->unsignedBigInteger('warehouse_id');
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});
```

### Production Flow

```
ProductionCompleted event
  InventoryEngine::handleProductionCompleted()
    For each ingredient in recipe:
      StockMovementEngine::record(ingredient, -qty, RecipeConsumption)
      InventoryBalanceService::recalculate(ingredient)
      BatchService::deduct(ingredientBatch, qty)
    StockMovementEngine::record(finishedGood, +yield, ProductionOutput)
    InventoryBalanceService::recalculate(finishedGood)
    CostingEngine::applyInbound(finishedGood, totalIngredientCost)
    AuditService::log('production', recipe, ingredients, yield)
```

---

## 13. Automation & Rule Engine (Phase 2)

### Rule Engine Design

```php
interface AutomationRule
{
    public function evaluate(AutomationContext $context): bool;
    public function execute(AutomationContext $context): void;
    public function name(): string;
    public function description(): string;
}

class ReorderRule implements AutomationRule
{
    public function evaluate(AutomationContext $context): bool
    {
        return $context->balance->quantity <= $context->product->reorder_point;
    }

    public function execute(AutomationContext $context): void
    {
        PurchaseOrder::create([
            'product_id' => $context->product->id,
            'quantity' => $context->product->reorder_quantity,
            'status' => 'draft',
        ]);
    }
}
```

### Available Rule Triggers

| Trigger | Evaluation | Actions |
|---------|-----------|---------|
| On balance updated | Check: below reorder point | Create PO draft, notify manager |
| On batch created | Check: expiry within threshold | Send expiry alert |
| On stock depleted | Check: is backorder product | Notify supplier |
| On transfer received | Check: auto-receive | Mark transfer completed |
| On daily schedule | Check: dead stock (90d no movement) | Send dead stock report |

---

## 14. Event-Driven Workflow

### Event Flow Diagram

```
┌─────────────┐     ┌──────────────────┐     ┌──────────────┐
│ POS Module   │     │ Purchase Module  │     │ Manufacturing│
│ SaleCompleted│     │ PurchaseReceived │     │ ProdComplete │
└──────┬──────┘     └────────┬─────────┘     └──────┬───────┘
       │                     │                      │
       └──────────┬──────────┴──────────┬───────────┘
                  │                     │
         ┌────────▼────────┐   ┌────────▼────────┐
         │ InventoryEngine  │   │ InventoryEngine  │
         │ (Listener)       │   │ (Listener)       │
         └────────┬────────┘   └────────┬────────┘
                  │                     │
         ┌────────▼─────────────────────▼─────────┐
         │           StockMovementEngine           │
         │           (record ledger entry)         │
         └────────────────┬───────────────────────┘
                          │
         ┌────────────────▼───────────────────────┐
         │      Domain Events Dispatched           │
         │  ┌──────────────┐  ┌────────────────┐  │
         │  │ StockMovement │  │ InventoryBalance│  │
         │  │ Created       │  │ Updated         │  │
         │  └──────────────┘  └────────────────┘  │
         │  ┌──────────────┐  ┌────────────────┐  │
         │  │ StockDepleted│  │ LowStockAlert   │  │
         │  └──────────────┘  └────────────────┘  │
         └────────────────┬───────────────────────┘
                          │
         ┌────────────────▼───────────────────────┐
         │            External Listeners           │
         │  Notifications, Search Indexing,        │
         │  Reorder Engine, Audit Log, Dashboard   │
         └─────────────────────────────────────────┘
```

---

## 15. Domain Events & Listeners

### Inventory Events

| Event | Payload | Dispatched By | Listeners |
|-------|---------|--------------|-----------|
| `StockMovementCreated` | product_id, warehouse_id, type, quantity, reference | StockMovementEngine | UpdateProductStockCache, NotifyInventoryUpdate |
| `InventoryBalanceUpdated` | product_id, warehouse_id, qty_before, qty_after | InventoryBalanceService | CheckReorderThreshold, UpdateProductSearchIndex |
| `StockDepleted` | product_id, warehouse_id, variant_id | InventoryBalanceService | MarkProductUnavailable, SendStockDepletedNotification |
| `LowStockAlert` | product_id, warehouse_id, current_qty, threshold | AlertEngine | SendLowStockNotification, DashboardAlert |
| `StockReserved` | product_id, warehouse_id, quantity, reference | ReservationEngine | TrackReservation |
| `ReservationConsumed` | reference, product_id, quantity | ReservationEngine | UpdateReservationStatus |
| `ReservationExpired` | reservation_id, product_id, quantity | ReservationEngine (cron) | ReleaseReservedStock, NotifyUser |
| `TransferInitiated` | transfer_id, from, to, items | TransferEngine | SendTransferNotification |
| `TransferCompleted` | transfer_id, from, to, items, received_at | TransferEngine | UpdateInventoryDashboard |
| `TransferCancelled` | transfer_id, reason | TransferEngine | ReleaseReservedStock |
| `BatchExpiring` | batch_id, product_id, expiry_date, days_remaining | BatchService (cron) | SendExpiryAlert, DashboardAlert |
| `BatchDepleted` | batch_id, product_id | StockMovementEngine | UpdateBatchStatus |
| `BatchQuarantined` | batch_id, reason | BatchService | NotifyQualityControl |
| `CostUpdated` | product_id, warehouse_id, old_avg, new_avg | CostingEngine | UpdateProductCostCache |
| `ProductionCompleted` | product_id, recipe_id, yield, ingredient_costs | InventoryEngine | UpdateInventoryDashboard |
| `SerialNumberSold` | serial_number, product_id, order_ref | SerialNumberService | UpdateWarrantyStatus |
| `AdjustmentApproved` | product_id, warehouse_id, qty, reason, approved_by | InventoryEngine | AuditLog, NotifyManager |

### Event Listener Registration

```php
// In InventoryServiceProvider::boot()
Event::listen(SaleCompleted::class, [InventoryEngine::class, 'handleSaleCompleted']);
Event::listen(PurchaseReceived::class, [InventoryEngine::class, 'handlePurchaseReceived']);
Event::listen(StockMovementCreated::class, UpdateProductStockCache::class);
Event::listen(LowStockAlert::class, SendLowStockNotification::class);
Event::listen(StockDepleted::class, MarkProductUnavailable::class);
Event::listen(InventoryBalanceUpdated::class, CheckReorderThreshold::class);
Event::listen(TransferInitiated::class, SendTransferNotification::class);
Event::listen(BatchExpiring::class, SendExpiryAlert::class);
```

### Cron Jobs

| Frequency | Command | Purpose |
|-----------|---------|---------|
| Every minute | `inventory:expire-reservations` | Release expired stock reservations |
| Every 6 hours | `inventory:expiry-alerts` | Find batches expiring within threshold, dispatch BatchExpiring |
| Daily | `inventory:dead-stock-report` | Find products with no movement in 90 days |
| Daily | `inventory:reconcile` | Compare ledger totals vs balance snapshots |

---

## 16. Database Conventions

### Naming Conventions

| Aspect | Convention | Example |
|--------|-----------|---------|
| Tables | snake_case, plural | `inventory_ledger`, `stock_reservations` |
| Columns | snake_case | `product_id`, `average_unit_cost` |
| Tenant FK | `tenant_id` (string, 36) | Shared-mode isolation |
| Product FK | `product_id` (ulid) | References Product ULID |
| Variant FK | `variant_id` (ulid, nullable) | References Variant ULID |
| Warehouse FK | `warehouse_id` (unsignedBigInteger) | References warehouses.id |
| Money | `bigInteger` in cents | `unit_cost`, `total_cost` |
| Quantity | `integer` | Whole units; decimal(12,4) for weight |
| Status | `string(20)` with enum backing | `MovementTypeEnum`, `ReservationStatusEnum` |
| Timestamps | `created_at`, `updated_at` | Ledger has only `created_at` |
| Soft deletes | `deleted_at` for reference data | warehouses, batches (not ledger) |

### Migration Rules

- All inventory tables run on tenant DB (dedicated) or shared DB (shared mode)
- Migrations live in `app/Modules/Inventory/Database/Migrations/Tenant/`
- Registered via `InventoryServiceProvider::boot()` `loadMigrationsFrom()`
- All tables include `$table->string('tenant_id', 36)` for shared-mode scoping
- Anonymous migration classes — no typed properties (PHP 8.4 compatibility)
- Always add `->orderBy('id')` before `->each()` in migrations (Laravel 12 req)

### Indexing Strategy

| Table | Critical Indexes | Rationale |
|-------|-----------------|-----------|
| `inventory_ledger` | (product_id, warehouse_id, created_at), (reference, reference_type), (batch_id) | Movement history queries, reference lookup, batch audit |
| `inventory_balances` | (product_id, variant_id, warehouse_id) UNIQUE | UPSERT lookup, fast single-row access |
| `cost_layers` | (product_id, warehouse_id, costing_method), (created_at) | FIFO ordering, costing method queries |
| `stock_reservations` | (status, expires_at), (product_id, warehouse_id, status) | Expiry cron, availability checks |
| `inventory_batches` | (expiry_date, status), (product_id, warehouse_id) | FEFO picking, expiry alerts |
| `serial_numbers` | (product_id, serial_number) UNIQUE, (status, warehouse_id) | Serial lookup, availability queries |
| `inventory_transfers` | (from_warehouse_id, status), (reference) | Transfer tracking, reference lookup |

---

## 17. Model Relationships

```php
class InventoryLedger extends Model
{
    public function product(): BelongsTo { ... }
    public function variant(): BelongsTo { ... }
    public function warehouse(): BelongsTo { ... }
    public function batch(): BelongsTo { ... }
    public function bin(): BelongsTo { ... }
}

class InventoryBalance extends Model
{
    public function product(): BelongsTo { ... }
    public function variant(): BelongsTo { ... }
    public function warehouse(): BelongsTo { ... }
}

class CostLayer extends Model
{
    public function product(): BelongsTo { ... }
    public function warehouse(): BelongsTo { ... }
    public function ledgerEntry(): BelongsTo { ... }
}

class StockReservation extends Model
{
    public function product(): BelongsTo { ... }
    public function variant(): BelongsTo { ... }
    public function warehouse(): BelongsTo { ... }
}

class InventoryBatch extends Model
{
    public function product(): BelongsTo { ... }
    public function warehouse(): BelongsTo { ... }
    public function ledgerEntries(): HasMany { ... }
    public function serialNumbers(): HasMany { ... }
}

class SerialNumber extends Model
{
    public function product(): BelongsTo { ... }
    public function warehouse(): BelongsTo { ... }
    public function batch(): BelongsTo { ... }
}

class Warehouse extends Model
{
    public function bins(): HasMany { ... }
    public function balances(): HasMany { ... }
    public function outboundTransfers(): HasMany { ... }
    public function inboundTransfers(): HasMany { ... }
}

class InventoryBin extends Model
{
    public function warehouse(): BelongsTo { ... }
    public function ledgerEntries(): HasMany { ... }
}

class Recipe extends Model
{
    public function product(): BelongsTo { ... }
    public function ingredients(): HasMany { ... }
}

class RecipeIngredient extends Model
{
    public function recipe(): BelongsTo { ... }
    public function ingredientProduct(): BelongsTo { ... }
}
```

### Product Module Cross-References

The Inventory module references these models from the Product module by ULID only:

- `App\Modules\Product\Models\Product`
- `App\Modules\Product\Models\Variant`

Inventory never accesses Product properties or methods directly. When inventory needs product data (costing method, reorder point), it uses the Product module's published contracts via dependency injection.

---

## 18. Industry Pack Extension Rules

### Feature Flags for Inventory

Each Industry Pack defines feature flags in its `featureFlags()` method. The Inventory Engine checks these flags at runtime.

```php
// PharmacyPack
public function featureFlags(): array
{
    return [
        'batch_tracking' => true,
        'expiry_tracking' => true,
        'fefo_picking' => true,
        'serial_number_tracking' => false,
        'recipe_management' => false,
        'weight_based_inventory' => false,
        'bin_locations' => true,
        'quarantine_management' => true,
    ];
}

// RestaurantPack
public function featureFlags(): array
{
    return [
        'recipe_management' => true,
        'recipe_consumption' => true,
        'waste_tracking' => true,
        'expiry_tracking' => true,
        'batch_costing' => true,
        'batch_tracking' => false,
        'serial_number_tracking' => false,
    ];
}

// ElectronicsPack
public function featureFlags(): array
{
    return [
        'serial_number_tracking' => true,
        'warranty_tracking' => true,
        'imei_validation' => true,
        'batch_tracking' => false,
        'bin_locations' => false,
    ];
}
```

### Flag Resolution

```php
if ($this->config->hasFeature('batch_tracking')) {
    $this->batchService->record($movement);
}

if ($this->config->hasFeature('serial_number_tracking')) {
    $this->serialService->validate($serialNumbers);
}

$pickingMethod = $this->config->hasFeature('fefo_picking') ? 'fefo' : 'fifo';
```

### NEVER — Hardcoded Industry Checks

```php
// WRONG
if ($config->businessType === 'pharmacy') { ... }
match ($config->businessType) { 'pharmacy' => ..., 'restaurant' => ... };
```

### ALWAYS — Feature Flag Pattern

```php
if ($config->hasFeature('fefo_picking')) {
    $this->batchService->pickBatches(..., method: 'fefo');
}
```

---

## 19. API Design Conventions

### Controller Routes

| Method | Path | Controller | Purpose |
|--------|------|-----------|---------|
| GET | `/inventory` | InventoryController@index | Inventory dashboard |
| GET | `/inventory/ledger` | InventoryController@ledger | Movement history |
| GET | `/inventory/balances` | InventoryController@balances | Current stock per product+warehouse |
| GET | `/inventory/balances/{product}` | InventoryController@productBalance | Single product stock |
| GET | `/inventory/transfers` | TransferController@index | Transfer list |
| POST | `/inventory/transfers` | TransferController@store | Initiate transfer |
| POST | `/inventory/transfers/{id}/send` | TransferController@send | Send transfer |
| POST | `/inventory/transfers/{id}/receive` | TransferController@receive | Receive transfer |
| GET | `/inventory/batches` | BatchController@index | Batch list |
| POST | `/inventory/batches` | BatchController@store | Register batch |
| GET | `/inventory/serials` | SerialController@index | Serial numbers |
| POST | `/inventory/serials` | SerialController@store | Register serials |
| GET | `/inventory/reservations` | ReservationController@index | Active reservations |
| POST | `/inventory/reservations` | ReservationController@store | Create reservation |
| DELETE | `/inventory/reservations/{id}` | ReservationController@destroy | Cancel reservation |
| GET | `/inventory/adjustments` | AdjustmentController@index | Adjustment history |
| POST | `/inventory/adjustments` | AdjustmentController@store | Submit adjustment |
| GET | `/inventory/warehouses` | WarehouseController@index | Warehouse list |
| POST | `/inventory/warehouses` | WarehouseController@store | Create warehouse |
| GET | `/inventory/recipes` | RecipeController@index | BOM/recipe list |
| POST | `/inventory/recipes` | RecipeController@store | Create recipe |
| GET | `/inventory/alerts` | AlertController@index | Active alerts |

### Form Request Validation

```php
class StoreTransferRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'from_warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'string', 'exists:products,ulid'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}

class StockAdjustmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'string', 'exists:products,ulid'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'quantity' => ['required', 'integer'],
            'reason' => ['required', 'string', 'in:damage,loss,theft,found,correction,other'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
```

---

## 20. UI/UX Principles for SME Users

### Design Tenets

| Principle | Implementation |
|-----------|---------------|
| **10-second insight** | Dashboard shows stock value, today's movements, low stock count, top alerts |
| **One-click actions** | Most common actions (adjust stock, create transfer) are 2 clicks max |
| **Visual stock health** | Color-coded badges: green (in stock), yellow (low), red (out), gray (discontinued) |
| **Progressive disclosure** | Simple views for cashiers, detailed views for managers |
| **Search-first** | Every inventory page has a global search bar |
| **Bulk operations** | Select multiple rows to adjust, transfer, or print labels |
| **Export** | Every table can be exported to CSV/PDF with one click |
| **Mobile-friendly** | Core operations work on tablets for warehouse staff |

### Page Layouts

**Inventory Dashboard (Manager View)**
```
+----------------------------------------------------------+
| Inventory Dashboard                                       |
+----------+----------+-------------+----------------------+
| Stock    | Today's  | Low Stock   | Expiring in 30 Days  |
| Value    | Movements| Items       | Items                |
| $84,200  | +12 in   | 8 items     | 3 batches            |
|          | -5 out   | below min   |                      |
+----------+----------+-------------+----------------------+
| Fast Movers              | Slow Movers                    |
| 1. Milk (84 sold)       | 1. Paint (0 in 90d)            |
| 2. Bread (62 sold)      | 2. Oil (2 in 90d)              |
| 3. Eggs (58 sold)       | 3. Spices (0 in 90d)           |
+--------------------------+-------------------------------+
| Recent Movements                                          |
| Time    | Product   | Type  | Qty   | Ref    | By       |
| 2:30    | Paracet.  | Sale  | -10   | INV-23 | Cashier  |
| 2:15    | Bandages  | Adj   | +50   | ADJ-04 | Manager  |
| 1:45    | Syringes  | Pur   | +200  | PO-404 | Supplier |
+----------------------------------------------------------+
```

---

## 21. Performance & Concurrency Requirements

### Read Performance

| Query Pattern | Strategy |
|---------------|----------|
| Dashboard (aggregates) | Materialized dashboard cache, recompute every 5 min |
| Product stock lookup | Direct `InventoryBalance` read via unique key |
| Movement history | Paginated query on `inventory_ledger` with composite indexes |
| Batch/expiry queries | Indexed on `(expiry_date, status)` |
| Search by SKU/barcode | MySQL index or Scout/Algolia |

### Write Performance

| Operation | Strategy |
|-----------|----------|
| POS sale (single deduction) | Synchronous — one ledger INSERT + one balance UPSERT + costing |
| Bulk import (1000+ items) | Queue job — batches of 100 with chunked inserts |
| Stock transfer (full warehouse) | Queue job — async processing |
| End-of-day reconciliation | Queue job — ledger replay |

### Concurrency Protection

| Threat | Protection |
|--------|-----------|
| Two POS terminals deduct same item simultaneously | Optimistic locking on `lock_version` |
| Transfer and sale race on same stock | Lock version check fails second operation, caller retries |
| Reservation expires while being consumed | Atomic check-then-act in DB transaction |
| Bulk import conflicts with live sales | Row-level locking via `lockForUpdate()` |

### Transaction Strategy

```php
DB::transaction(function () use ($movement) {
    $ledger = $this->stockMovementEngine->record(...);
    $this->inventoryBalanceService->recalculate(...);
    $this->costingEngine->consume(...);

    StockMovementCreated::dispatch($ledger);
}, attempts: 3);
```

---

## 22. Multi-Tenant Considerations

### Data Isolation

| Tenant Mode | Inventory Tables Location | Isolation |
|-------------|--------------------------|-----------|
| Shared (Free, Starter, Professional) | `souda_shared` database | `tenant_id` column + `HasTenantScope` trait |
| Dedicated (Enterprise) | `souda_tenant_{uuid}` database | Separate MySQL database |

### HasTenantScope on Inventory Models

```php
class InventoryLedger extends Model
{
    use HasTenantScope;
    protected $guarded = [];
    public $timestamps = false;
}

class InventoryBalance extends Model
{
    use HasTenantScope;
    protected $guarded = [];
}

class CostLayer extends Model
{
    use HasTenantScope;
    // ...
}
```

### InventoryServiceProvider

```php
class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(InventoryEngine::class);
        $this->app->singleton(StockMovementEngine::class);
        $this->app->singleton(InventoryBalanceService::class);
        $this->app->singleton(CostingEngine::class);
        $this->app->singleton(ReservationEngine::class);
        $this->app->singleton(BatchService::class);
        $this->app->singleton(SerialNumberService::class);
        $this->app->singleton(WarehouseService::class);
        $this->app->singleton(TransferEngine::class);
        $this->app->singleton(AuditService::class);
        $this->app->singleton(AlertEngine::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations/Tenant');
        $this->registerRoutes();
        $this->registerEvents();
        $this->registerObservers();
    }
}
```

### Tenant Config Integration

```php
class InventoryEngine
{
    public function handlePurchaseReceived(PurchaseReceived $event): void
    {
        $movement = $this->movementEngine->record(/* ... */);

        if ($this->config->hasFeature('batch_tracking')) {
            $this->batchService->receive(/* ... */);
        }

        if ($this->config->hasFeature('serial_number_tracking')) {
            $this->serialService->registerBatch(/* ... */);
        }
    }
}
```

---

## 23. Testing Strategy

### Test Categories

| Category | Scope | Framework | Location |
|----------|-------|-----------|----------|
| Unit tests | Engine services, cost calculations, validation | Pest | `tests/Unit/Inventory/` |
| Feature tests | Full workflows (sale, ledger, balance, alert) | Pest | `tests/Feature/Inventory/` |
| Integration | Cross-module (Order, Inventory, Purchase) | Pest | `tests/Feature/` |
| Reconciliation | Ledger replay matches expected balance | Pest | `tests/Feature/Inventory/ReconciliationTest.php` |

### Required Coverage

| Feature | Minimum Tests |
|---------|--------------|
| StockMovementEngine::record | 5+ (various types, with/without batch/serial) |
| InventoryBalanceService::recalculate | 3+ (inbound, outbound, zero) |
| CostingEngine (weighted average) | 5+ (multiple layers, zero balance, edge cases) |
| CostingEngine (FIFO) | 5+ (layer consumption order, partial layers) |
| ReservationEngine | 5+ (create, consume, expire, cancel, concurrent) |
| TransferEngine | 4+ (initiate, send, receive, cancel, partial) |
| BatchService | 4+ (receive, deduct, fifo/fefo, quarantine) |
| SerialNumberService | 4+ (register, validate, mark sold, warranty) |
| AlertEngine | 3+ (low stock, expiry, dead stock) |
| Multi-warehouse | 2+ (transfer flow, balance isolation) |
| Concurrent writes | 2+ (optimistic locking, retry) |
| Reconciliation | 1+ (replay ledger to verify balance) |

### Test Patterns

```php
// Unit test
it('records a purchase receipt movement', function () {
    $engine = app(StockMovementEngine::class);

    $ledger = $engine->record(
        productId: $product->ulid,
        warehouseId: $warehouse->id,
        quantity: 100,
        type: MovementTypeEnum::PurchaseReceipt,
        reference: 'PO-001',
        unitCost: 1500,
    );

    expect($ledger)
        ->quantity->toBe(100)
        ->quantity_before->toBe(0)
        ->quantity_after->toBe(100)
        ->movement_type->toBe('purchase_receipt')
        ->unit_cost->toBe(1500);
});

// Feature test
it('deducts stock and updates balance on sale', function () {
    $product = Product::factory()->create(['costing_method' => 'weighted_average']);
    $warehouse = Warehouse::factory()->create(['is_default' => true]);
    InventoryBalance::factory()->create([
        'product_id' => $product->ulid,
        'warehouse_id' => $warehouse->id,
        'quantity' => 50,
        'average_unit_cost' => 1000,
    ]);

    $event = new SaleCompleted(
        productId: $product->ulid,
        warehouseId: $warehouse->id,
        quantity: 5,
        orderReference: 'ORD-001',
    );

    app(InventoryEngine::class)->handleSaleCompleted($event);

    $balance = InventoryBalance::where([
        'product_id' => $product->ulid,
        'warehouse_id' => $warehouse->id,
    ])->first();

    expect($balance)->quantity->toBe(45);
})->shared();
```

### Test Database Considerations

- Feature tests use `souda_testing` DB (shared between central and default connections)
- Do NOT run tests in parallel — `migrate:fresh` calls will collide
- Use `RefreshDatabase` trait or manual migration refresh in setUp
- Factory state: explicit `product_id`, `warehouse_id` in creates

---

## 24. Coding Standards & AI Guardrails

### MUST FOLLOW

- All inventory write operations go through `InventoryEngine` — never call `StockMovementEngine` directly
- `InventoryLedger` is INSERT-only — never UPDATE or DELETE ledger rows
- Use `MovementTypeEnum` for movement types — never hardcoded strings
- All monetary values in cents (integer) — `bigInteger` column type
- All quantities are integers (whole units) — use `decimal(12,4)` only for weight-based
- Use constructor property promotion for all services
- Use proper return type hints on all methods
- Use Form Request classes for validation — no inline validation
- Use PHPDoc for complex logic — no inline comments for simple code
- Run `vendor/bin/pint --format agent` after all PHP changes
- Add `->orderBy('id')` before `->each()` on query builders

### NEVER

- Write stock directly from another module (POS, Order, Purchase, etc.)
- Use `env()` outside config files
- Add hardcoded business type checks (`if ($slug === 'pharmacy')`)
- Create per-industry tables or columns
- Use `DB::` when an Eloquent relationship exists
- Leave empty `__construct()` with zero params (unless private)
- Delete inventory ledger rows
- Commit secrets or credentials

### ALWAYS

- Create event listeners for cross-module communication
- Wrap inventory writes in `DB::transaction()` with retry
- Use `HasTenantScope` on shared-mode models
- Register events in `InventoryServiceProvider::boot()`
- Run tests before marking inventory tasks complete
- Write feature tests for every new inventory workflow

---

## 25. Development Roadmap

### Phase 1A: Foundation (Week 1-2)

| Task | Deliverable |
|------|-------------|
| Create module structure | `app/Modules/Inventory/` with all directories |
| Create tenant migrations | `inventory_ledger`, `inventory_balances`, `cost_layers`, `stock_reservations` |
| Create shared migrations | Same tables with `tenant_id` for shared mode |
| Implement Models | `InventoryLedger`, `InventoryBalance`, `CostLayer`, `StockReservation` |
| Implement Enums | `MovementTypeEnum`, `CostingMethodEnum`, `ReservationStatusEnum` |
| Implement StockMovementEngine | Core `record()` method with reference generation |
| Implement InventoryBalanceService | `recalculate()`, `rebuildFromLedger()` |
| Create InventoryServiceProvider | Singleton bindings, migration loading |
| Create tests | StockMovementEngine unit tests, InventoryBalance feature tests |

### Phase 1B: Costing & Reservations (Week 3-4)

| Task | Deliverable |
|------|-------------|
| Implement CostingEngine | Weighted average + FIFO strategies |
| Implement CostLayer model + migration | Layer tracking per product+warehouse |
| Implement ReservationEngine | Reserve, consume, expire, cancel |
| Implement reservation migration | `stock_reservations` table |
| Create ExpireReservations command | Cron: every minute |
| Create tests | CostingEngine unit tests, ReservationEngine feature tests |

### Phase 1C: Warehouse & Transfer (Week 5)

| Task | Deliverable |
|------|-------------|
| Move Warehouse model from Product | Backward-compatible alias |
| Add bins/locations tables + models | `inventory_bins` |
| Implement TransferEngine | Initiate, send, receive, cancel, partial receive |
| Implement transfer tables + models | `inventory_transfers`, `inventory_transfer_items` |
| Implement AuditService | Log all operations |
| Create tests | TransferEngine feature tests, multi-warehouse tests |

### Phase 1D: Batch, Serial & Alerts (Week 6)

| Task | Deliverable |
|------|-------------|
| Implement BatchService | Receive, deduct, FEFO/FIFO picking, quarantine |
| Implement batch migration + model | `inventory_batches` |
| Implement SerialNumberService | Register, validate, lifecycle |
| Implement serial migration + model | `serial_numbers` |
| Implement AlertEngine | Low stock, depletion, expiry evaluation |
| Create expiry alerts command | Cron: every 6 hours |
| Create tests | BatchService, SerialNumberService, AlertEngine |

### Phase 1E: Frontend (Week 7-8)

| Task | Deliverable |
|------|-------------|
| Inventory Dashboard | Stock value, movements, alerts, fast/slow movers |
| Stock Movement History | Filterable table with pagination |
| Inventory Balance View | Per-product+warehouse stock snapshot |
| Warehouse Management | CRUD for warehouses and bins |
| Transfer Management | Create, send, receive transfers |
| Batch Management | Create batches, view expiries |
| Serial Number Management | Register serials, view lifecycle |
| Stock Adjustments | Submit and approve adjustments |
| Alerts Panel | Low stock, expiring, dead stock alerts |
| Frontend API layer | React Query hooks for all inventory endpoints |

### Phase 1F: Industry Pack Integration (Week 9)

| Task | Deliverable |
|------|-------------|
| Update all 15 Industry Packs | Add inventory feature flags |
| Integrate Pharmacy Pack | `batch_tracking`, `expiry_tracking`, `fefo_picking` |
| Integrate Restaurant Pack | `recipe_management`, `recipe_consumption`, `waste_tracking` |
| Integrate Electronics Pack | `serial_number_tracking`, `warranty_tracking` |
| Integrate Grocery Pack | `weight_based_inventory`, `perishable_tracking` |
| Integrate Fashion Pack | `variant_size_color` inventory views |
| Test all pack configurations | Verify feature flag gating |

### Phase 2: Automation & Smart Features (Future)

| Feature | Complexity |
|---------|-----------|
| Auto Reorder Engine | Medium |
| Purchase Suggestions | High |
| Dead Stock Detection | Low |
| Slow/Fast Moving Classification | Medium |
| Seasonal Demand Prediction | High |
| Automation Rules Engine (IF-THEN) | High |
| Inventory Dashboard with Charts | Medium |
| AI Assistant (chat-based) | Very High |

---

## 26. File Creation Templates

### New Inventory Service

```php
<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\BusinessType\ValueObjects\TenantConfig;

class MyInventoryService
{
    public function __construct(
        private TenantConfig $config,
    ) {}

    public function execute(): void
    {
        if ($this->config->hasFeature('batch_tracking')) {
            // industry-specific behavior
        }
    }
}
```

### New Inventory Model (Shared Mode)

```php
<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Model;

class MyModel extends Model
{
    use HasTenantScope;

    protected $guarded = [];

    public function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
```

### New Inventory Event

```php
<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use Illuminate\Foundation\Events\Dispatchable;

class MyInventoryEvent
{
    use Dispatchable;

    public function __construct(
        public string $productId,
        public string $warehouseId,
        public int $quantity,
    ) {}
}
```

### New Inventory Listener

```php
<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Listeners;

use App\Modules\Inventory\Events\MyInventoryEvent;

class MyInventoryListener
{
    public function handle(MyInventoryEvent $event): void
    {
        // Handle the event
    }
}
```

### New Tenant Migration

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
        Schema::create('my_inventory_table', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36);
            $table->ulid('product_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_inventory_table');
    }
};
```

### New React Query Hook (Frontend)

```typescript
// resources/js/modules/inventory/hooks/use-inventory.ts
import { useQuery, useMutation } from '@tanstack/react-query';
import axios from 'axios';
import type { InventoryBalance, InventoryMovement } from '../types/inventory';

export function useInventoryBalances(warehouseId?: string) {
    return useQuery({
        queryKey: ['inventory', 'balances', warehouseId],
        queryFn: async () => {
            const { data } = await axios.get<InventoryBalance[]>(
                route('inventory.balances'),
                { params: { warehouse_id: warehouseId } }
            );
            return data;
        },
    });
}

export function useMovementHistory(productId?: string) {
    return useQuery({
        queryKey: ['inventory', 'movements', productId],
        queryFn: async () => {
            const { data } = await axios.get<InventoryMovement[]>(
                route('inventory.ledger'),
                { params: { product_id: productId } }
            );
            return data;
        },
    });
}

export function useCreateTransfer() {
    return useMutation({
        mutationFn: async (payload: TransferPayload) => {
            const { data } = await axios.post(route('inventory.transfers.store'), payload);
            return data;
        },
    });
}
```

---

## 27. Final Checklist — Before Beginning Phase 1A

Before you write any code, verify:

- [ ] All 10+ migrations are designed and ordered correctly
- [ ] `InventoryServiceProvider` is registered in `bootstrap/providers.php` (after `ProductServiceProvider`)
- [ ] All 15 Industry Packs have feature flags for their inventory needs
- [ ] `HasTenantScope` is applied to every shared-mode model
- [ ] `InventoryEngine` is the only class with public write methods
- [ ] Events are defined for all 17 inventory domain events
- [ ] Existing `WarehouseStock` model is deprecated with a read-only facade
- [ ] Database indexes cover all critical query patterns
- [ ] Optimistic locking is implemented on `inventory_balances`
- [ ] The golden rule is followed: **no module writes stock directly**
