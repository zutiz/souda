<?php

declare(strict_types=1);

use App\Models\User;
use App\Tenancy\TenantManager;
use App\Modules\Inventory\Enums\SerialStatusEnum;
use App\Modules\Inventory\Events\SerialNumberSold;
use App\Modules\Inventory\Exceptions\SerialNumberAlreadyExistsException;
use App\Modules\Inventory\Models\SerialNumber;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\SerialNumberService;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->sharedSubscribed()->create();

    tenancy()->initialize($this->user->tenant);
    app(TenantManager::class)->initialize($this->user->tenant);

    $this->product = Product::factory()->create();
    $this->warehouse = Warehouse::factory()->create(['slug' => 'serial-wh']);
    $this->serialService = app(SerialNumberService::class);
});

test('can register a serial number', function () {
    $serial = $this->serialService->register(
        productId: $this->product->id,
        serialNumber: 'SN-001',
        warehouseId: $this->warehouse->id,
    );

    expect($serial)->toBeInstanceOf(SerialNumber::class)
        ->and($serial->serial_number)->toBe('SN-001')
        ->and($serial->status)->toBe(SerialStatusEnum::Available)
        ->and($serial->warehouse_id)->toBe($this->warehouse->id);
});

test('register throws on duplicate serial number', function () {
    $this->serialService->register(
        productId: $this->product->id,
        serialNumber: 'SN-001',
    );

    expect(fn () => $this->serialService->register(
        productId: $this->product->id,
        serialNumber: 'SN-001',
    ))->toThrow(SerialNumberAlreadyExistsException::class);
});

test('can register multiple serial numbers in batch', function () {
    $serials = $this->serialService->registerBatch(
        productId: $this->product->id,
        serialNumbers: ['SN-001', 'SN-002', 'SN-003'],
        warehouseId: $this->warehouse->id,
    );

    expect($serials)->toHaveCount(3);

    expect(SerialNumber::where('product_id', $this->product->id)->count())->toBe(3);
});

test('validate returns true for available serial number', function () {
    $this->serialService->register(
        productId: $this->product->id,
        serialNumber: 'SN-001',
    );

    expect($this->serialService->validate('SN-001', $this->product->id))->toBeTrue();
});

test('validate returns false for sold serial number', function () {
    $this->serialService->register(
        productId: $this->product->id,
        serialNumber: 'SN-001',
        warehouseId: $this->warehouse->id,
    );

    $this->serialService->markAsSold(
        serialNumber: 'SN-001',
        productId: $this->product->id,
        orderReference: 'ORD-001',
    );

    expect($this->serialService->validate('SN-001', $this->product->id))->toBeFalse();
});

test('can mark serial as sold', function () {
    $this->serialService->register(
        productId: $this->product->id,
        serialNumber: 'SN-001',
    );

    $serial = $this->serialService->markAsSold(
        serialNumber: 'SN-001',
        productId: $this->product->id,
        orderReference: 'ORD-001',
    );

    expect($serial->status)->toBe(SerialStatusEnum::Sold)
        ->and($serial->order_reference)->toBe('ORD-001')
        ->and($serial->sold_at)->not->toBeNull();
});

test('markAsSold dispatches SerialNumberSold event', function () {
    Event::fake([SerialNumberSold::class]);

    $this->serialService->register(
        productId: $this->product->id,
        serialNumber: 'SN-001',
    );

    $this->serialService->markAsSold(
        serialNumber: 'SN-001',
        productId: $this->product->id,
        orderReference: 'ORD-001',
    );

    Event::assertDispatched(SerialNumberSold::class);
});

test('can mark serial as returned', function () {
    $this->serialService->register(
        productId: $this->product->id,
        serialNumber: 'SN-001',
    );

    $this->serialService->markAsSold(
        serialNumber: 'SN-001',
        productId: $this->product->id,
        orderReference: 'ORD-001',
    );

    $serial = $this->serialService->markAsReturned(
        serialNumber: 'SN-001',
        productId: $this->product->id,
    );

    expect($serial->status)->toBe(SerialStatusEnum::Returned);
});

test('findByStatus filters by status and product', function () {
    $this->serialService->register(
        productId: $this->product->id,
        serialNumber: 'SN-001',
    );

    $this->serialService->register(
        productId: $this->product->id,
        serialNumber: 'SN-002',
    );

    $this->serialService->markAsSold(
        serialNumber: 'SN-001',
        productId: $this->product->id,
        orderReference: 'ORD-001',
    );

    $available = $this->serialService->findByStatus('available');
    expect($available)->toHaveCount(1)
        ->and($available->first()->serial_number)->toBe('SN-002');

    $sold = $this->serialService->findByStatus('sold', $this->product->id);
    expect($sold)->toHaveCount(1);
});

test('warrantyStatus returns correct state', function () {
    $this->serialService->register(
        productId: $this->product->id,
        serialNumber: 'SN-001',
        warrantyExpiresAt: now()->addYear(),
    );

    expect($this->serialService->warrantyStatus('SN-001'))->toBe('active');

    $this->serialService->register(
        productId: $this->product->id,
        serialNumber: 'SN-002',
    );

    expect($this->serialService->warrantyStatus('SN-002'))->toBe('no_warranty');

    expect($this->serialService->warrantyStatus('SN-NONEXISTENT'))->toBe('not_found');
});
