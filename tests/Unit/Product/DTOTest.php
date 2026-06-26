<?php

declare(strict_types=1);

use App\Modules\Product\DTOs\CategoryDTO;
use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\Enums\ProductStatusEnum;
use App\Modules\Product\Enums\ProductTypeEnum;

test('category dto can be created from request', function () {
    $dto = CategoryDTO::fromRequest([
        'name' => 'Test Category',
        'parent_id' => null,
        'description' => 'A test category',
    ]);

    expect($dto->name)->toBe('Test Category')
        ->and($dto->parentId)->toBeNull()
        ->and($dto->description)->toBe('A test category')
        ->and($dto->isActive)->toBeTrue();
});

test('product dto can be created from request', function () {
    $dto = ProductDTO::fromRequest([
        'name' => 'Test Product',
        'type' => 'simple',
        'status' => 'draft',
        'base_price' => '1000',
    ]);

    expect($dto->name)->toBe('Test Product')
        ->and($dto->type)->toBe(ProductTypeEnum::Simple)
        ->and($dto->status)->toBe(ProductStatusEnum::Draft)
        ->and($dto->basePrice)->toBe(1000);
});

test('product dto handles optional fields', function () {
    $dto = ProductDTO::fromRequest([
        'name' => 'Test Product',
        'type' => 'configurable',
        'status' => 'active',
        'base_price' => '5000',
        'sku' => 'TEST-SKU',
        'track_inventory' => true,
    ]);

    expect($dto->sku)->toBe('TEST-SKU')
        ->and($dto->barcode)->toBeNull()
        ->and($dto->description)->toBeNull();
});
