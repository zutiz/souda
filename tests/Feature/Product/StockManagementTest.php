<?php

declare(strict_types=1);

use App\Modules\Product\Models\WarehouseStock;
use App\Modules\Product\Services\StockLockService;

test('lockStockRecords sorts warehouse IDs in ascending order', function () {
    $service = Mockery::mock(StockLockService::class)->makePartial();

    $lockOrder = [];
    $service->shouldReceive('lockStockRecord')->andReturnUsing(function (int $warehouseId) use (&$lockOrder) {
        $lockOrder[] = $warehouseId;

        return Mockery::mock(WarehouseStock::class);
    });

    $reflection = new ReflectionMethod($service, 'lockStockRecords');
    $reflection->invoke($service, [20, 10], null, null);

    expect($lockOrder)->toBe([10, 20]);
});

test('lockStockRecords deduplicates warehouse IDs', function () {
    $service = Mockery::mock(StockLockService::class)->makePartial();

    $lockOrder = [];
    $service->shouldReceive('lockStockRecord')->andReturnUsing(function (int $warehouseId) use (&$lockOrder) {
        $lockOrder[] = $warehouseId;

        return Mockery::mock(WarehouseStock::class);
    });

    $reflection = new ReflectionMethod($service, 'lockStockRecords');
    $reflection->invoke($service, [7, 7, 7], null, null);

    expect($lockOrder)->toHaveCount(1)
        ->and($lockOrder[0])->toBe(7);
});

test('lockStockRecords returns records keyed by warehouse_id', function () {
    $service = Mockery::mock(StockLockService::class)->makePartial();

    $mocks = [];
    $service->shouldReceive('lockStockRecord')->andReturnUsing(function (int $warehouseId) use (&$mocks) {
        $mocks[$warehouseId] = Mockery::mock(WarehouseStock::class);

        return $mocks[$warehouseId];
    });

    $reflection = new ReflectionMethod($service, 'lockStockRecords');
    $records = $reflection->invoke($service, [3, 1], null, null);

    expect($records)->toHaveCount(2);
    expect($records[1])->toBe($mocks[1]);
    expect($records[3])->toBe($mocks[3]);
});
