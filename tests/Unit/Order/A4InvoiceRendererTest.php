<?php

declare(strict_types=1);

use App\Modules\Order\Services\A4InvoiceRenderer;

test('a4 invoice renders html', function () {
    $order = receiptOrderDTO();
    $renderer = new A4InvoiceRenderer;

    $output = $renderer->render($order, [
        'store_name' => 'My Store',
        'store_address' => '123 Main St, Dhaka 1205',
        'store_phone' => '+8801712345678',
        'store_email' => 'store@example.com',
    ]);

    expect($output)->toContain('My Store')
        ->and($output)->toContain('Invoice')
        ->and($output)->toContain('ORD-001')
        ->and($output)->toContain('Product A')
        ->and($output)->toContain('50.00');
});

test('a4 invoice includes customer info', function () {
    $order = receiptOrderDTO([
        'customer_name' => 'Jane Smith',
        'customer_phone' => '+8801712340000',
        'shipping_address' => [
            'name' => 'Jane Smith',
            'phone' => '+8801712340000',
            'address_line_1' => '123 Main St',
            'city' => 'Dhaka',
            'postal_code' => '1205',
            'country' => 'BD',
        ],
    ]);
    $renderer = new A4InvoiceRenderer;

    $output = $renderer->render($order, []);

    expect($output)->toContain('Jane Smith')
        ->and($output)->toContain('+8801712340000');
});

test('a4 invoice returns html content type', function () {
    $renderer = new A4InvoiceRenderer;

    expect($renderer->contentType())->toBe('text/html');
});
