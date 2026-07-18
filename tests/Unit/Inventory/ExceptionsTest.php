<?php

declare(strict_types=1);

use App\Modules\Inventory\Exceptions\BatchNotFoundException;
use App\Modules\Inventory\Exceptions\CostingMethodNotSupportedException;
use App\Modules\Inventory\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Exceptions\InvalidTransferStateException;
use App\Modules\Inventory\Exceptions\ReservationNotFoundException;
use App\Modules\Inventory\Exceptions\SerialNumberAlreadyExistsException;
use App\Modules\Inventory\Exceptions\TransferNotFoundException;

test('InsufficientStockException has default message and code', function () {
    $exception = new InsufficientStockException;

    expect($exception->getMessage())->toBe('Insufficient stock available')
        ->and($exception->getCode())->toBe(422);
});

test('InsufficientStockException::forProduct formats message correctly', function () {
    $exception = InsufficientStockException::forProduct(
        productId: 'prod-123',
        requested: 50,
        available: 10,
    );

    expect($exception->getMessage())->toBe('Insufficient stock for product prod-123: requested 50, available 10')
        ->and($exception->getCode())->toBe(422);
});

test('InsufficientStockException::forVariant formats message correctly', function () {
    $exception = InsufficientStockException::forVariant(
        variantId: 'var-456',
        requested: 100,
        available: 0,
    );

    expect($exception->getMessage())->toBe('Insufficient stock for variant var-456: requested 100, available 0')
        ->and($exception->getCode())->toBe(422);
});

test('InsufficientStockException with zero available shows zero', function () {
    $exception = InsufficientStockException::forProduct(
        productId: 'prod-1',
        requested: 5,
        available: 0,
    );

    expect($exception->getMessage())->toContain('requested 5, available 0');
});

test('InvalidTransferStateException has default message and code', function () {
    $exception = new InvalidTransferStateException;

    expect($exception->getMessage())->toBe('Invalid transfer state for this operation')
        ->and($exception->getCode())->toBe(422);
});

test('InvalidTransferStateException with custom message', function () {
    $exception = new InvalidTransferStateException('Only draft transfers can be sent');

    expect($exception->getMessage())->toBe('Only draft transfers can be sent');
});

test('CostingMethodNotSupportedException formats message correctly', function () {
    $exception = new CostingMethodNotSupportedException('lifo');

    expect($exception->getMessage())->toBe('Costing method not supported: lifo')
        ->and($exception->getCode())->toBe(500);
});

test('TransferNotFoundException formats message correctly', function () {
    $exception = new TransferNotFoundException(42);

    expect($exception->getMessage())->toContain('42')
        ->and($exception->getCode())->toBe(404);
});

test('ReservationNotFoundException formats message correctly', function () {
    $exception = new ReservationNotFoundException(99);

    expect($exception->getMessage())->toContain('99')
        ->and($exception->getCode())->toBe(404);
});

test('SerialNumberAlreadyExistsException formats message correctly', function () {
    $exception = new SerialNumberAlreadyExistsException(
        serialNumber: 'SN-12345',
        productId: 'prod-1',
    );

    expect($exception->getMessage())->toContain('SN-12345')
        ->and($exception->getMessage())->toContain('prod-1')
        ->and($exception->getCode())->toBe(409);
});

test('BatchNotFoundException formats message correctly', function () {
    $exception = new BatchNotFoundException(
        batchNumber: 'B-007',
        productId: 'prod-1',
    );

    expect($exception->getMessage())->toContain('B-007')
        ->and($exception->getMessage())->toContain('prod-1')
        ->and($exception->getCode())->toBe(404);
});
