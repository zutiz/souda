<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Inventory\Events\StockReservationCancelled;
use App\Modules\Inventory\Events\StockReservationCreated;
use App\Modules\Inventory\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Exceptions\ReservationNotFoundException;
use App\Modules\Inventory\Models\StockReservation;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Inventory\Services\ReservationEngine;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Warehouse;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();

    tenancy()->initialize($this->user->tenant);

    $this->product = Product::factory()->create();
    $this->warehouse = Warehouse::factory()->create();
    $this->inventoryEngine = app(InventoryEngine::class);
    $this->reservationEngine = app(ReservationEngine::class);
});

test('can reserve stock when sufficient quantity exists', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id, variantId: null,
        warehouseId: $this->warehouse->id, quantity: 100,
        movementType: 'initial_stock', reference: 'INIT-001',
    );

    $reservation = $this->reservationEngine->reserve(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
        variantId: null,
        quantity: 30,
        reference: 'ORDER-001',
        referenceType: 'order',
    );

    expect($reservation)->toBeInstanceOf(StockReservation::class)
        ->and($reservation->status->value)->toBe('active')
        ->and($reservation->quantity)->toBe(30)
        ->and($reservation->reference)->toBe('ORDER-001');
});

test('throws exception when insufficient stock', function () {
    $reservation = $this->reservationEngine->reserve(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
        variantId: null,
        quantity: 10,
        reference: 'ORDER-001',
        referenceType: 'order',
    );
})->throws(InsufficientStockException::class);

test('reservation deducts from available quantity', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id, variantId: null,
        warehouseId: $this->warehouse->id, quantity: 50,
        movementType: 'initial_stock', reference: 'INIT-001',
    );

    $this->reservationEngine->reserve(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
        variantId: null,
        quantity: 20,
        reference: 'ORDER-001',
        referenceType: 'order',
    );

    $available = $this->reservationEngine->getAvailableQuantity(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
    );

    expect($available)->toBe(30);
});

test('reservation with variant isolation', function () {
    $variantA = '01JX4XC0Z1V3N0B4H7E2Y8T5M9';
    $variantB = '01JX4XC0Z2V3N0B4H7E2Y8T5M0';

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id, variantId: $variantA,
        warehouseId: $this->warehouse->id, quantity: 50,
        movementType: 'initial_stock', reference: 'INIT-A',
    );

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id, variantId: $variantB,
        warehouseId: $this->warehouse->id, quantity: 50,
        movementType: 'initial_stock', reference: 'INIT-B',
    );

    $this->reservationEngine->reserve(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
        variantId: $variantA,
        quantity: 30,
        reference: 'ORDER-A',
        referenceType: 'order',
    );

    expect($this->reservationEngine->getAvailableQuantity(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
        variantId: $variantA,
    ))->toBe(20);

    expect($this->reservationEngine->getAvailableQuantity(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
        variantId: $variantB,
    ))->toBe(50);
});

test('can consume a reservation', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id, variantId: null,
        warehouseId: $this->warehouse->id, quantity: 100,
        movementType: 'initial_stock', reference: 'INIT-001',
    );

    $reservation = $this->reservationEngine->reserve(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
        variantId: null,
        quantity: 30,
        reference: 'ORDER-001',
        referenceType: 'order',
    );

    $consumed = $this->reservationEngine->consume($reservation->id);

    expect($consumed->status->value)->toBe('consumed');
});

test('can cancel a reservation', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id, variantId: null,
        warehouseId: $this->warehouse->id, quantity: 100,
        movementType: 'initial_stock', reference: 'INIT-001',
    );

    $reservation = $this->reservationEngine->reserve(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
        variantId: null,
        quantity: 30,
        reference: 'ORDER-001',
        referenceType: 'order',
    );

    $cancelled = $this->reservationEngine->cancel($reservation->id);

    expect($cancelled->status->value)->toBe('cancelled');
    expect($this->reservationEngine->getAvailableQuantity(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
    ))->toBe(100);
});

test('consume throws on non-existent reservation', function () {
    $this->reservationEngine->consume(99999);
})->throws(ReservationNotFoundException::class);

test('cancel throws on non-existent reservation', function () {
    $this->reservationEngine->cancel(99999);
})->throws(ReservationNotFoundException::class);

test('getActiveReservations returns only active reservations', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id, variantId: null,
        warehouseId: $this->warehouse->id, quantity: 100,
        movementType: 'initial_stock', reference: 'INIT-001',
    );

    $r1 = $this->reservationEngine->reserve(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
        variantId: null,
        quantity: 20,
        reference: 'ORDER-001',
        referenceType: 'order',
    );

    $this->reservationEngine->reserve(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
        variantId: null,
        quantity: 30,
        reference: 'ORDER-002',
        referenceType: 'order',
    );

    $this->reservationEngine->consume($r1->id);

    $active = $this->reservationEngine->getActiveReservations(
        productId: $this->product->id,
    );

    expect($active)->toHaveCount(1)
        ->and($active->first()->reference)->toBe('ORDER-002');
});

test('getAvailableQuantity returns zero for product with no stock', function () {
    $available = $this->reservationEngine->getAvailableQuantity(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
    );

    expect($available)->toBe(0);
});

test('reservation uses configurable TTL', function () {
    config(['inventory.reservation_ttl_minutes' => 5]);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id, variantId: null,
        warehouseId: $this->warehouse->id, quantity: 100,
        movementType: 'initial_stock', reference: 'INIT-001',
    );

    $reservation = $this->reservationEngine->reserve(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
        variantId: null,
        quantity: 10,
        reference: 'ORDER-001',
        referenceType: 'order',
    );

    expect($reservation->expires_at)->not->toBeNull();
});

test('expireOldReservations marks expired reservations', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id, variantId: null,
        warehouseId: $this->warehouse->id, quantity: 100,
        movementType: 'initial_stock', reference: 'INIT-001',
    );

    $this->reservationEngine->reserve(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
        variantId: null,
        quantity: 30,
        reference: 'ORDER-001',
        referenceType: 'order',
        expiresAt: CarbonImmutable::now()->subMinute(),
    );

    $expired = $this->reservationEngine->expireOldReservations();

    expect($expired)->toBe(1);

    expect(StockReservation::where('status', 'active')->count())->toBe(0);

    expect($this->reservationEngine->getAvailableQuantity(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
    ))->toBe(100);
});

test('reserve dispatches StockReservationCreated event', function () {
    Event::fake();

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id, variantId: null,
        warehouseId: $this->warehouse->id, quantity: 100,
        movementType: 'initial_stock', reference: 'INIT-001',
    );

    $this->reservationEngine->reserve(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
        variantId: null,
        quantity: 10,
        reference: 'ORDER-001',
        referenceType: 'order',
    );

    Event::assertDispatched(StockReservationCreated::class);
});

test('cancel dispatches StockReservationCancelled event', function () {
    Event::fake();

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id, variantId: null,
        warehouseId: $this->warehouse->id, quantity: 100,
        movementType: 'initial_stock', reference: 'INIT-001',
    );

    $reservation = $this->reservationEngine->reserve(
        warehouseId: $this->warehouse->id,
        productId: $this->product->id,
        variantId: null,
        quantity: 10,
        reference: 'ORDER-001',
        referenceType: 'order',
    );

    $this->reservationEngine->cancel($reservation->id);

    Event::assertDispatched(StockReservationCancelled::class);
});
