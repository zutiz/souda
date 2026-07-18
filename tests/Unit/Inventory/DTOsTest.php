<?php

declare(strict_types=1);

use App\Modules\Inventory\DTOs\CostLayerDTO;
use App\Modules\Inventory\DTOs\InventoryBalanceDTO;
use App\Modules\Inventory\DTOs\InventoryMovementDTO;
use App\Modules\Inventory\DTOs\ReservationDTO;
use App\Modules\Inventory\DTOs\StockMovementDTO;
use App\Modules\Inventory\Enums\CostingMethodEnum;
use App\Modules\Inventory\Enums\MovementTypeEnum;
use Carbon\CarbonImmutable;

describe('InventoryMovementDTO', function () {
    test('can be constructed with named arguments', function () {
        $now = new CarbonImmutable('2026-07-18T12:00:00Z');
        $dto = new InventoryMovementDTO(
            productId: 'prod-1',
            variantId: null,
            warehouseId: 'wh-1',
            quantityChange: -5,
            quantityAfter: 45,
            type: 'sale_deduction',
            referenceType: 'order',
            referenceId: 'ord-123',
            reason: 'Order fulfillment',
            metadata: ['source' => 'pos'],
            occurredAt: $now,
        );

        expect($dto->productId)->toBe('prod-1')
            ->and($dto->quantityChange)->toBe(-5)
            ->and($dto->type)->toBe('sale_deduction');
    });

    test('fromArray maps keys correctly', function () {
        $dto = InventoryMovementDTO::fromArray([
            'product_id' => 'prod-2',
            'warehouse_id' => 'wh-2',
            'quantity_change' => '10',
            'quantity_after' => '60',
            'type' => 'adjustment',
            'occurred_at' => '2026-07-18T12:00:00Z',
        ]);

        expect($dto->productId)->toBe('prod-2')
            ->and($dto->quantityChange)->toBe(10)
            ->and($dto->variantId)->toBeNull()
            ->and($dto->referenceId)->toBeNull();
    });

    test('toArray round-trips correctly', function () {
        $original = InventoryMovementDTO::fromArray([
            'product_id' => 'prod-3',
            'variant_id' => 'var-1',
            'warehouse_id' => 'wh-3',
            'quantity_change' => 25,
            'quantity_after' => 75,
            'type' => 'purchase_receipt',
            'reference_type' => 'purchase_order',
            'reference_id' => 'PO-456',
            'reason' => 'Restock',
            'metadata' => ['batch' => 'B-001'],
            'occurred_at' => '2026-07-18T12:00:00Z',
        ]);

        $array = $original->toArray();

        expect($array['product_id'])->toBe('prod-3')
            ->and($array['quantity_change'])->toBe(25)
            ->and($array['reference_id'])->toBe('PO-456')
            ->and($array['metadata']['batch'])->toBe('B-001');
    });

    test('fromArray defaults occurred_at to now when omitted', function () {
        $dto = InventoryMovementDTO::fromArray([
            'product_id' => 'prod-1',
            'warehouse_id' => 'wh-1',
            'quantity_change' => 0,
            'quantity_after' => 0,
            'type' => 'adjustment',
        ]);

        expect($dto->occurredAt)->toBeInstanceOf(CarbonImmutable::class);
    });
});

describe('StockMovementDTO', function () {
    test('can be constructed with required fields only', function () {
        $dto = new StockMovementDTO(
            productId: 'prod-1',
            variantId: null,
            warehouseId: 1,
            quantity: 100,
            type: MovementTypeEnum::PurchaseReceipt,
            reference: 'PO-001',
            referenceType: 'purchase_order',
        );

        expect($dto->productId)->toBe('prod-1')
            ->and($dto->quantity)->toBe(100)
            ->and($dto->type)->toBe(MovementTypeEnum::PurchaseReceipt)
            ->and($dto->unitCost)->toBeNull()
            ->and($dto->metadata)->toBe([]);
    });

    test('can be constructed with all optional fields', function () {
        $dto = new StockMovementDTO(
            productId: 'prod-1',
            variantId: 'var-1',
            warehouseId: 1,
            quantity: -25,
            type: MovementTypeEnum::SaleDeduction,
            reference: 'SALE-001',
            referenceType: 'sale',
            unitCost: 5000,
            batchId: 'batch-1',
            serialNumbers: ['SN-001', 'SN-002'],
            description: 'Test sale',
            createdBy: 'user-1',
            metadata: ['reason' => 'test'],
        );

        expect($dto->unitCost)->toBe(5000)
            ->and($dto->batchId)->toBe('batch-1')
            ->and($dto->serialNumbers)->toBe(['SN-001', 'SN-002'])
            ->and($dto->metadata['reason'])->toBe('test');
    });
});

describe('InventoryBalanceDTO', function () {
    test('can be constructed and accessed', function () {
        $dto = new InventoryBalanceDTO(
            productId: 'prod-1',
            variantId: null,
            warehouseId: 'wh-1',
            quantity: 100,
            reservedQuantity: 20,
            availableQuantity: 80,
            averageUnitCost: 5000,
            totalStockValue: 500000,
        );

        expect($dto->quantity)->toBe(100)
            ->and($dto->availableQuantity)->toBe(80)
            ->and($dto->averageUnitCost)->toBe(5000)
            ->and($dto->lastMovementAt)->toBeNull();
    });
});

describe('ReservationDTO', function () {
    test('can be constructed and accessed', function () {
        $dto = new ReservationDTO(
            productId: 'prod-1',
            variantId: null,
            warehouseId: 'wh-1',
            quantity: 10,
            reference: 'ORDER-001',
            referenceType: 'order',
        );

        expect($dto->quantity)->toBe(10)
            ->and($dto->reference)->toBe('ORDER-001')
            ->and($dto->ttlMinutes)->toBeNull();
    });

    test('can be constructed with ttl', function () {
        $dto = new ReservationDTO(
            productId: 'prod-1',
            variantId: null,
            warehouseId: 'wh-1',
            quantity: 10,
            reference: 'ORDER-001',
            referenceType: 'order',
            ttlMinutes: 30,
        );

        expect($dto->ttlMinutes)->toBe(30);
    });
});

describe('CostLayerDTO', function () {
    test('can be constructed and accessed', function () {
        $dto = new CostLayerDTO(
            productId: 'prod-1',
            variantId: null,
            warehouseId: 'wh-1',
            unitCost: 5000,
            quantityRemaining: 100,
            quantityOriginal: 100,
            costingMethod: CostingMethodEnum::WeightedAverage,
        );

        expect($dto->unitCost)->toBe(5000)
            ->and($dto->quantityRemaining)->toBe(100)
            ->and($dto->costingMethod)->toBe(CostingMethodEnum::WeightedAverage);
    });
});
