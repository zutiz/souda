<?php

declare(strict_types=1);

use App\Modules\Order\DTOs\LineItemDTO;
use App\Modules\Order\DTOs\OrderAddressDTO;
use App\Modules\Order\DTOs\OrderDTO;

test('order address dto can be created from array', function () {
    $dto = OrderAddressDTO::fromArray([
        'name' => 'John Doe',
        'phone' => '+8801712345678',
        'address_line_1' => '123 Main St',
        'address_line_2' => 'Apt 4B',
        'city' => 'Dhaka',
        'state' => 'Dhaka Division',
        'postal_code' => '1205',
        'country' => 'BD',
        'email' => 'john@example.com',
    ]);

    expect($dto->name)->toBe('John Doe')
        ->and($dto->phone)->toBe('+8801712345678')
        ->and($dto->city)->toBe('Dhaka')
        ->and($dto->country)->toBe('BD');
});

test('line item dto can be created from array', function () {
    $dto = LineItemDTO::fromArray([
        'product_id' => 'prod-1',
        'variant_id' => 'var-1',
        'name' => 'Test Product',
        'sku' => 'TST-001',
        'quantity' => 2,
        'unit_price' => 5000,
        'total_price' => 10000,
        'tax_amount' => 1000,
        'discount_amount' => 500,
        'warehouse_id' => 'wh-1',
    ]);

    expect($dto->productId)->toBe('prod-1')
        ->and($dto->quantity)->toBe(2)
        ->and($dto->totalPrice)->toBe(10000);
});

test('order dto can be created from array', function () {
    $dto = OrderDTO::fromArray([
        'order_id' => 'ord-123',
        'order_number' => 'ORD-20260501-001',
        'tenant_id' => 'tenant-1',
        'customer_id' => 'cust-1',
        'status' => 'confirmed',
        'subtotal' => 10000,
        'tax_total' => 1000,
        'discount_total' => 500,
        'grand_total' => 10500,
        'currency' => 'BDT',
        'shipping_address' => [
            'name' => 'John Doe',
            'phone' => '+8801712345678',
            'address_line_1' => '123 Main St',
            'city' => 'Dhaka',
            'postal_code' => '1205',
            'country' => 'BD',
        ],
        'line_items' => [
            [
                'product_id' => 'prod-1',
                'name' => 'Product A',
                'quantity' => 1,
                'unit_price' => 5000,
                'total_price' => 5000,
            ],
        ],
        'placed_at' => '2026-05-01T10:00:00Z',
    ]);

    expect($dto->orderId)->toBe('ord-123')
        ->and($dto->grandTotal)->toBe(10500)
        ->and($dto->lineItems)->toHaveCount(1)
        ->and($dto->shippingAddress->city)->toBe('Dhaka');
});

test('order dto serializes to array', function () {
    $dto = OrderDTO::fromArray([
        'order_id' => 'ord-1',
        'order_number' => 'ORD-001',
        'tenant_id' => 'tenant-1',
        'status' => 'pending',
        'subtotal' => 5000,
        'grand_total' => 5000,
        'currency' => 'BDT',
        'shipping_address' => [
            'name' => 'Jane Doe',
            'phone' => '+8801712345679',
            'address_line_1' => '456 Oak St',
            'city' => 'Chittagong',
            'postal_code' => '4000',
            'country' => 'BD',
        ],
        'line_items' => [],
        'placed_at' => '2026-05-01T10:00:00Z',
    ]);

    $array = $dto->toArray();

    expect($array['order_id'])->toBe('ord-1')
        ->and($array['grand_total'])->toBe(5000)
        ->and($array['shipping_address']['city'])->toBe('Chittagong');
});
