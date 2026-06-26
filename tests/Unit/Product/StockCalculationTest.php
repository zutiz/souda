<?php

declare(strict_types=1);

use App\Modules\Product\Models\WarehouseStock;

test('available quantity is quantity minus reserved', function () {
    $stock = new WarehouseStock;
    $stock->quantity = 100;
    $stock->reserved_quantity = 30;

    expect($stock->getAvailableQuantity())->toBe(70);
});

test('warehouse stock is low when at or below reorder level', function () {
    $stock = new WarehouseStock;
    $stock->quantity = 5;
    $stock->reserved_quantity = 0;
    $stock->reorder_level = 5;

    expect($stock->isLowStock())->toBeTrue();

    $stock->quantity = 6;

    expect($stock->isLowStock())->toBeFalse();
});

test('warehouse stock is not low when above reorder level', function () {
    $stock = new WarehouseStock;
    $stock->quantity = 100;
    $stock->reserved_quantity = 20;
    $stock->reorder_level = 10;

    expect($stock->getAvailableQuantity())->toBe(80)
        ->and($stock->isLowStock())->toBeFalse();
});
