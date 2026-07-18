<?php

declare(strict_types=1);

use App\Modules\Order\DTOs\LineItemDTO;
use App\Modules\Order\DTOs\OrderAddressDTO;
use App\Modules\Order\DTOs\OrderDTO;
use App\Notifications\OrderConfirmation;
use Carbon\CarbonImmutable;

function confirmationOrderDTO(): OrderDTO
{
    return new OrderDTO(
        orderId: 'ord-1',
        orderNumber: 'ORD-001',
        tenantId: 'tenant-1',
        status: 'confirmed',
        subtotal: 10000,
        grandTotal: 11500,
        currency: 'BDT',
        shippingAddress: new OrderAddressDTO(
            name: 'John Doe',
            phone: '+8801712345678',
            email: 'john@example.com',
            addressLine1: '123 Main St',
            addressLine2: null,
            city: 'Dhaka',
            state: null,
            postalCode: '1205',
            country: 'BD',
        ),
        lineItems: [
            new LineItemDTO(
                productId: 'prod-1',
                variantId: null,
                name: 'Product A',
                sku: 'SKU-001',
                quantity: 2,
                unitPrice: 5000,
                totalPrice: 10000,
                taxAmount: null,
                discountAmount: null,
                warehouseId: null,
                metadata: null,
            ),
        ],
        placedAt: new CarbonImmutable('2026-05-01T10:00:00Z'),
        customerName: 'John Doe',
        customerEmail: 'john@example.com',
        customerPhone: '+8801712345678',
        paymentMethod: 'cash',
        paymentStatus: 'paid',
        taxTotal: 1000,
        discountTotal: 500,
        shippingTotal: 1000,
    );
}

test('order confirmation sends via mail channel', function () {
    $notification = new OrderConfirmation(confirmationOrderDTO());

    $channels = $notification->via(new stdClass);

    expect($channels)->toContain('mail');
});

test('order confirmation mail subject matches order number', function () {
    $order = confirmationOrderDTO();
    $notification = new OrderConfirmation($order);

    $mail = $notification->toMail(new stdClass);

    expect($mail->subject)->toContain('ORD-001');
});

test('order confirmation mail uses markdown template', function () {
    $order = confirmationOrderDTO();
    $notification = new OrderConfirmation($order);

    $mail = $notification->toMail(new stdClass);

    expect($mail->markdown)->toBe('emails.order-confirmation');
});

test('order confirmation mail passes line items to template', function () {
    $order = confirmationOrderDTO();
    $notification = new OrderConfirmation($order);

    $mail = $notification->toMail(new stdClass);

    expect($mail->viewData['orderNumber'])->toBe('ORD-001')
        ->and($mail->viewData['customerName'])->toBe('John Doe')
        ->and($mail->viewData['customerEmail'])->toBe('john@example.com');
});

test('order confirmation mail passes total amounts formatted', function () {
    $order = confirmationOrderDTO();
    $notification = new OrderConfirmation($order);

    $mail = $notification->toMail(new stdClass);

    expect($mail->viewData['subtotal'])->toBe('100.00')
        ->and($mail->viewData['grandTotal'])->toBe('115.00')
        ->and($mail->viewData['currency'])->toBe('BDT');
});

test('order confirmation mail passes line items array', function () {
    $order = confirmationOrderDTO();
    $notification = new OrderConfirmation($order);

    $mail = $notification->toMail(new stdClass);

    expect($mail->viewData['lineItems'])->toHaveCount(1)
        ->and($mail->viewData['lineItems'][0]->name)->toBe('Product A')
        ->and($mail->viewData['lineItems'][0]->quantity)->toBe(2);
});

test('order confirmation handles missing optional fields', function () {
    $order = new OrderDTO(
        orderId: 'ord-2',
        orderNumber: 'ORD-002',
        tenantId: 'tenant-1',
        status: 'confirmed',
        subtotal: 5000,
        grandTotal: 5000,
        currency: 'BDT',
        shippingAddress: new OrderAddressDTO(
            name: 'Jane Doe',
            phone: '+8801712345679',
            email: null,
            addressLine1: '456 Oak St',
            addressLine2: null,
            city: 'Chittagong',
            state: null,
            postalCode: '4000',
            country: 'BD',
        ),
        lineItems: [],
        placedAt: new CarbonImmutable('2026-05-01T10:00:00Z'),
    );

    $notification = new OrderConfirmation($order);
    $mail = $notification->toMail(new stdClass);

    expect($mail->subject)->toContain('ORD-002')
        ->and($mail->viewData['customerEmail'])->toBeNull();
});
