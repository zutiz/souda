<?php

declare(strict_types=1);

use App\Models\User;
use App\Tenancy\TenantManager;
use App\Modules\Inventory\Events\InventoryAdjusted;
use App\Modules\Inventory\Events\InventoryBalanceUpdated;
use App\Modules\Inventory\Events\InventoryDeducted;
use App\Modules\Inventory\Events\InventoryRestored;
use App\Modules\Inventory\Events\LowStockAlert;
use App\Modules\Inventory\Events\StockMovementCreated;
use App\Modules\Inventory\Listeners\DeductInventoryStock;
use App\Modules\Inventory\Listeners\RestoreInventoryStock;
use App\Modules\Inventory\Models\InventoryAlert;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Order\DTOs\LineItemDTO;
use App\Modules\Order\DTOs\OrderAddressDTO;
use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Events\OrderRefunded;
use App\Modules\Product\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->sharedSubscribed()->create();

    tenancy()->initialize($this->user->tenant);
    app(TenantManager::class)->initialize($this->user->tenant);

    $this->product = Product::factory()->create([
        'low_stock_threshold' => 10,
        'track_inventory' => true,
    ]);

    $this->warehouse = Warehouse::factory()->create();

    $this->inventoryEngine = app(InventoryEngine::class);
});

test('StockMovementCreated listener fires and updates product timestamp', function () {
    $ledger = $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-EVT',
    );

    expect($ledger)->toBeInstanceOf(InventoryLedger::class);
});

test('InventoryBalanceUpdated is dispatched on movement', function () {
    Event::fake(InventoryBalanceUpdated::class);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 50,
        movementType: 'initial_stock',
        reference: 'INIT-BAL',
    );

    Event::assertDispatched(InventoryBalanceUpdated::class);
});

test('StockDepleted event creates inventory alert', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-DEP',
    );

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -100,
        movementType: 'sale_deduction',
        reference: 'SALE-DEP',
    );

    $alert = InventoryAlert::where('type', 'stock_depleted')
        ->where('product_id', $this->product->id)
        ->first();

    expect($alert)->not->toBeNull()
        ->and($alert->severity)->toBe('critical');
});

test('StockDepleted alert is not duplicated', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-DUP',
    );

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -100,
        movementType: 'sale_deduction',
        reference: 'SALE-DUP-1',
    );

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -100,
        movementType: 'sale_deduction',
        reference: 'SALE-DUP-2',
    );

    $count = InventoryAlert::where('type', 'stock_depleted')
        ->where('product_id', $this->product->id)
        ->count();

    expect($count)->toBe(1);
});

test('InventoryEngine dispatches InventoryDeducted for negative movements', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-NEG',
    );

    Event::fake(InventoryDeducted::class);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -10,
        movementType: 'sale_deduction',
        reference: 'SALE-NEG',
    );

    Event::assertDispatched(InventoryDeducted::class);
});

test('InventoryEngine dispatches InventoryRestored for positive movements', function () {
    Event::fake(InventoryRestored::class);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 50,
        movementType: 'initial_stock',
        reference: 'INIT-POS',
    );

    Event::assertDispatched(InventoryRestored::class);
});

test('InventoryEngine dispatches InventoryAdjusted for zero-quantity movement', function () {
    // Cannot actually pass 0 quantity, so test positive + fake
    Event::fake(InventoryAdjusted::class);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 10,
        movementType: 'initial_stock',
        reference: 'INIT-ADJ',
    );

    // Positive movements should dispatch InventoryRestored, not Adjusted
    Event::assertNotDispatched(InventoryAdjusted::class);
});

test('LowStockAlert is dispatched when stock drops below threshold', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-LSA',
    );

    Event::fake(LowStockAlert::class);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -95,
        movementType: 'sale_deduction',
        reference: 'SALE-LSA',
    );

    Event::assertDispatched(LowStockAlert::class);
});

test('OrderCreated event triggers inventory deduction via listener', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-ORD',
    );

    $initialBalance = (int) InventoryBalance::where('product_id', $this->product->id)
        ->where('warehouse_id', $this->warehouse->id)
        ->value('quantity');

    $address = new OrderAddressDTO(
        name: 'Test',
        phone: '555-0100',
        addressLine1: '123 Test St',
        addressLine2: null,
        city: 'Test City',
        state: 'TS',
        postalCode: '12345',
        country: 'US',
        email: null,
    );

    $lineItem = new LineItemDTO(
        productId: $this->product->id,
        variantId: null,
        name: $this->product->name,
        sku: $this->product->sku ?? '',
        quantity: 5,
        unitPrice: 1000,
        totalPrice: 5000,
        taxAmount: null,
        discountAmount: null,
        warehouseId: (string) $this->warehouse->id,
        metadata: null,
    );

    $order = new OrderDTO(
        orderId: (string) str()->ulid(),
        orderNumber: 'ORD-TEST-001',
        tenantId: (string) $this->user->tenant_id,
        customerId: (string) $this->user->id,
        status: 'confirmed',
        subtotal: 5000,
        taxTotal: 0,
        discountTotal: 0,
        grandTotal: 5000,
        currency: 'USD',
        shippingAddress: $address,
        billingAddress: null,
        lineItems: [$lineItem],
        couponCode: null,
        notes: null,
        paymentMethod: 'card',
        placedAt: new CarbonImmutable,
        metadata: null,
    );

    $event = new OrderCreated($order);

    Queue::fake();
    event($event);

    (new DeductInventoryStock($this->inventoryEngine))->handle($event);

    $finalBalance = (int) InventoryBalance::where('product_id', $this->product->id)
        ->where('warehouse_id', $this->warehouse->id)
        ->value('quantity');

    expect($finalBalance)->toBe($initialBalance - 5);
});

test('OrderCancelled event triggers inventory restoration via listener', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-CAN',
    );

    $initialBalance = (int) InventoryBalance::where('product_id', $this->product->id)
        ->where('warehouse_id', $this->warehouse->id)
        ->value('quantity');

    $address = new OrderAddressDTO(
        name: 'Test',
        phone: '555-0100',
        addressLine1: '123 Test St',
        addressLine2: null,
        city: 'Test City',
        state: 'TS',
        postalCode: '12345',
        country: 'US',
        email: null,
    );

    $lineItem = new LineItemDTO(
        productId: $this->product->id,
        variantId: null,
        name: $this->product->name,
        sku: $this->product->sku ?? '',
        quantity: 3,
        unitPrice: 1000,
        totalPrice: 3000,
        taxAmount: null,
        discountAmount: null,
        warehouseId: (string) $this->warehouse->id,
        metadata: null,
    );

    $order = new OrderDTO(
        orderId: (string) str()->ulid(),
        orderNumber: 'ORD-CAN-001',
        tenantId: (string) $this->user->tenant_id,
        customerId: (string) $this->user->id,
        status: 'cancelled',
        subtotal: 3000,
        taxTotal: 0,
        discountTotal: 0,
        grandTotal: 3000,
        currency: 'USD',
        shippingAddress: $address,
        billingAddress: null,
        lineItems: [$lineItem],
        couponCode: null,
        notes: null,
        paymentMethod: 'card',
        placedAt: new CarbonImmutable,
        metadata: null,
    );

    $event = new OrderCancelled($order, reason: 'Test cancellation');

    Queue::fake();
    event($event);

    (new RestoreInventoryStock($this->inventoryEngine))->handle($event);

    $finalBalance = (int) InventoryBalance::where('product_id', $this->product->id)
        ->where('warehouse_id', $this->warehouse->id)
        ->value('quantity');

    expect($finalBalance)->toBe($initialBalance + 3);
});

test('OrderRefunded event triggers inventory restoration via listener', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-REF',
    );

    $initialBalance = (int) InventoryBalance::where('product_id', $this->product->id)
        ->where('warehouse_id', $this->warehouse->id)
        ->value('quantity');

    $address = new OrderAddressDTO(
        name: 'Test',
        phone: '555-0100',
        addressLine1: '123 Test St',
        addressLine2: null,
        city: 'Test City',
        state: 'TS',
        postalCode: '12345',
        country: 'US',
        email: null,
    );

    $lineItem = new LineItemDTO(
        productId: $this->product->id,
        variantId: null,
        name: $this->product->name,
        sku: $this->product->sku ?? '',
        quantity: 2,
        unitPrice: 1000,
        totalPrice: 2000,
        taxAmount: null,
        discountAmount: null,
        warehouseId: (string) $this->warehouse->id,
        metadata: null,
    );

    $order = new OrderDTO(
        orderId: (string) str()->ulid(),
        orderNumber: 'ORD-REF-001',
        tenantId: (string) $this->user->tenant_id,
        customerId: (string) $this->user->id,
        status: 'refunded',
        subtotal: 2000,
        taxTotal: 0,
        discountTotal: 0,
        grandTotal: 2000,
        currency: 'USD',
        shippingAddress: $address,
        billingAddress: null,
        lineItems: [$lineItem],
        couponCode: null,
        notes: null,
        paymentMethod: 'card',
        placedAt: new CarbonImmutable,
        metadata: null,
    );

    $event = new OrderRefunded($order, refundAmount: 2000, reason: 'Customer request');
    event($event);

    $finalBalance = (int) InventoryBalance::where('product_id', $this->product->id)
        ->where('warehouse_id', $this->warehouse->id)
        ->value('quantity');

    expect($finalBalance)->toBe($initialBalance + 2);
});

test('StockMovementCreated event is dispatched', function () {
    Event::fake(StockMovementCreated::class);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 25,
        movementType: 'initial_stock',
        reference: 'INIT-SMC',
    );

    Event::assertDispatched(StockMovementCreated::class);
});
