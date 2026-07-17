<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Inventory\Exceptions\CostingMethodNotSupportedException;
use App\Modules\Inventory\Models\CostLayer;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Services\Costing\CostingEngine;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Warehouse;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();

    tenancy()->initialize($this->user->tenant);

    $this->product = Product::factory()->create();
    $this->warehouse = Warehouse::factory()->create();
});

describe('Weighted Average Costing', function () {
    beforeEach(function () {
        config(['inventory.default_costing_method' => 'weighted_average']);
        $this->inventoryEngine = app(InventoryEngine::class);
    });

    test('inbound updates average unit cost', function () {
        $this->inventoryEngine->recordMovement(
            productId: $this->product->id,
            variantId: null,
            warehouseId: $this->warehouse->id,
            quantity: 100,
            movementType: 'initial_stock',
            reference: 'INIT-001',
            unitCost: 5000,
        );

        $balance = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        expect($balance->average_unit_cost)->toBe(5000)
            ->and($balance->total_stock_value)->toBe(500000);
    });

    test('multiple inbound movements recalculate weighted average', function () {
        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: 100,
            movementType: 'purchase_receipt', reference: 'PO-001', unitCost: 5000,
        );

        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: 100,
            movementType: 'purchase_receipt', reference: 'PO-002', unitCost: 7000,
        );

        $balance = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        expect($balance->average_unit_cost)->toBe(6000);
    });

    test('outbound preserves average unit cost', function () {
        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: 100,
            movementType: 'initial_stock', reference: 'INIT-001', unitCost: 5000,
        );

        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: -30,
            movementType: 'sale_deduction', reference: 'SALE-001',
        );

        $balance = InventoryBalance::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        expect($balance->average_unit_cost)->toBe(5000)
            ->and($balance->total_stock_value)->toBe(350000);
    });

    test('no cost layers created for weighted average', function () {
        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: 100,
            movementType: 'initial_stock', reference: 'INIT-001', unitCost: 5000,
        );

        expect(CostLayer::count())->toBe(0);
    });
});

describe('FIFO Costing', function () {
    beforeEach(function () {
        config(['inventory.default_costing_method' => 'fifo']);
        $this->inventoryEngine = app(InventoryEngine::class);
    });

    test('inbound creates cost layers', function () {
        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: 100,
            movementType: 'purchase_receipt', reference: 'PO-001', unitCost: 5000,
        );

        $layers = CostLayer::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->get();

        expect($layers)->toHaveCount(1)
            ->and($layers->first()->unit_cost)->toBe(5000)
            ->and($layers->first()->quantity_remaining)->toBe(100);
    });

    test('outbound consumes oldest layer first', function () {
        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: 100,
            movementType: 'purchase_receipt', reference: 'PO-001', unitCost: 5000,
        );

        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: 100,
            movementType: 'purchase_receipt', reference: 'PO-002', unitCost: 7000,
        );

        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: -120,
            movementType: 'sale_deduction', reference: 'SALE-001',
        );

        $layers = CostLayer::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->orderBy('id')
            ->get();

        expect($layers[0]->quantity_remaining)->toBe(0)
            ->and($layers[1]->quantity_remaining)->toBe(80);
    });

    test('outbound calculates total cost from consumed layers', function () {
        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: 100,
            movementType: 'purchase_receipt', reference: 'PO-001', unitCost: 5000,
        );

        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: 100,
            movementType: 'purchase_receipt', reference: 'PO-002', unitCost: 7000,
        );

        $ledger = $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: -50,
            movementType: 'sale_deduction', reference: 'SALE-001',
        );

        expect($ledger->total_cost)->toBe(250000);
    });
});

describe('LIFO Costing', function () {
    beforeEach(function () {
        config(['inventory.default_costing_method' => 'lifo']);
        $this->inventoryEngine = app(InventoryEngine::class);
    });

    test('outbound consumes newest layer first', function () {
        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: 100,
            movementType: 'purchase_receipt', reference: 'PO-001', unitCost: 5000,
        );

        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: 100,
            movementType: 'purchase_receipt', reference: 'PO-002', unitCost: 7000,
        );

        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: -120,
            movementType: 'sale_deduction', reference: 'SALE-001',
        );

        $layers = CostLayer::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->orderBy('id')
            ->get();

        expect($layers[0]->quantity_remaining)->toBe(80)
            ->and($layers[1]->quantity_remaining)->toBe(0);
    });

    test('outbound calculates total cost from newest layers', function () {
        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: 100,
            movementType: 'purchase_receipt', reference: 'PO-001', unitCost: 5000,
        );

        $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: 100,
            movementType: 'purchase_receipt', reference: 'PO-002', unitCost: 7000,
        );

        $ledger = $this->inventoryEngine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: -50,
            movementType: 'sale_deduction', reference: 'SALE-001',
        );

        expect($ledger->total_cost)->toBe(350000);
    });
});

describe('CostingEngine', function () {
    test('throws exception for unsupported costing method', function () {
        config(['inventory.default_costing_method' => 'invalid_method']);

        expect(fn () => app(CostingEngine::class))
            ->toThrow(CostingMethodNotSupportedException::class);
    });

    test('movement without unit cost does not create cost layer', function () {
        config(['inventory.default_costing_method' => 'fifo']);

        $engine = app(InventoryEngine::class);

        $engine->recordMovement(
            productId: $this->product->id, variantId: null,
            warehouseId: $this->warehouse->id, quantity: 100,
            movementType: 'initial_stock', reference: 'INIT-001',
        );

        expect(CostLayer::count())->toBe(0);
    });
});
