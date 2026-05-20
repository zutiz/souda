<?php

use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Events\OrderRefunded;
use App\Modules\Shared\Contracts\DomainEvent;

function orderEventTestOrderDTO(): OrderDTO
{
    return OrderDTO::fromArray([
        'order_id' => 'ord-123',
        'order_number' => 'ORD-001',
        'tenant_id' => 'tenant-1',
        'customer_id' => 'cust-1',
        'status' => 'confirmed',
        'subtotal' => 10000,
        'tax_total' => 1000,
        'discount_total' => 0,
        'grand_total' => 11000,
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
                'quantity' => 2,
                'unit_price' => 5000,
                'total_price' => 10000,
                'warehouse_id' => 'wh-1',
            ],
        ],
        'placed_at' => '2026-05-01T10:00:00Z',
    ]);
}

test('order created event implements domain event contract', function () {
    $event = new OrderCreated(orderEventTestOrderDTO());

    expect($event)->toBeInstanceOf(DomainEvent::class);
});

test('order created event has correct event name', function () {
    $event = new OrderCreated(orderEventTestOrderDTO());

    expect($event->getEventName())->toBe('order.created');
});

test('order created event envelope contains order data', function () {
    $event = new OrderCreated(orderEventTestOrderDTO());
    $envelope = $event->toEnvelope();

    expect($envelope->eventName)->toBe('order.created')
        ->and($envelope->payload)->toHaveKey('order')
        ->and($envelope->payload['order']['order_id'])->toBe('ord-123');
});

test('order created retains correlation id across calls', function () {
    $event = new OrderCreated(orderEventTestOrderDTO());

    $first = $event->getCorrelationId();
    $second = $event->getCorrelationId();

    expect($first)->toBe($second);
});

test('order created lines items are accessible', function () {
    $event = new OrderCreated(orderEventTestOrderDTO());

    expect($event->order->lineItems)->toHaveCount(1)
        ->and($event->order->lineItems[0]->productId)->toBe('prod-1')
        ->and($event->order->lineItems[0]->quantity)->toBe(2);
});

test('order cancelled event has reason', function () {
    $event = new OrderCancelled(order: orderEventTestOrderDTO(), reason: 'Customer requested');

    expect($event->reason)->toBe('Customer requested')
        ->and($event->getEventName())->toBe('order.cancelled');
});

test('order refunded event has refund amount', function () {
    $event = new OrderRefunded(order: orderEventTestOrderDTO(), refundAmount: 5000, reason: 'Partial refund');

    expect($event->refundAmount)->toBe(5000)
        ->and($event->reason)->toBe('Partial refund')
        ->and($event->getEventName())->toBe('order.refunded');
});
