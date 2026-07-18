<?php

declare(strict_types=1);

use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Services\ThermalReceiptRenderer;

function receiptOrderDTO(array $overrides = []): OrderDTO
{
    return OrderDTO::fromArray(array_merge([
        'order_id' => 'ord-1',
        'order_number' => 'ORD-001',
        'tenant_id' => 'tenant-1',
        'store_id' => 'store-1',
        'status' => 'confirmed',
        'subtotal' => 10000,
        'tax_total' => 1000,
        'discount_total' => 500,
        'shipping_total' => 0,
        'grand_total' => 10500,
        'paid_total' => 10500,
        'due_total' => 0,
        'currency' => 'BDT',
        'customer_name' => 'John Doe',
        'customer_phone' => '+8801712345678',
        'customer_email' => 'john@example.com',
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'order_type' => 'in_store',
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
                'sku' => 'SKU-001',
                'quantity' => 2,
                'unit_price' => 5000,
                'total_price' => 10000,
            ],
        ],
        'placed_at' => '2026-05-01T10:00:00Z',
    ], $overrides));
}

test('thermal receipt renders header with store name', function () {
    $order = receiptOrderDTO();
    $renderer = new ThermalReceiptRenderer;

    $output = $renderer->render($order, ['store_name' => 'My Store']);

    expect($output)->toContain('My Store')
        ->and($output)->toContain('ORD-001');
});

test('thermal receipt includes all line items', function () {
    $order = receiptOrderDTO();
    $renderer = new ThermalReceiptRenderer;

    $output = $renderer->render($order, []);

    expect($output)->toContain('Product A')
        ->and($output)->toContain('2 x')
        ->and($output)->toContain('100.00');
});

test('thermal receipt includes payment info', function () {
    $order = receiptOrderDTO(['payment_method' => 'card', 'payment_status' => 'paid']);
    $renderer = new ThermalReceiptRenderer;

    $output = $renderer->render($order, []);

    expect($output)->toContain('card')
        ->and($output)->toContain('Paid');
});

test('thermal receipt returns text content type', function () {
    $renderer = new ThermalReceiptRenderer;

    expect($renderer->contentType())->toBe('text/plain');
});
