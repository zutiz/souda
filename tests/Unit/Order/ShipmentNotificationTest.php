<?php

declare(strict_types=1);

use App\Modules\Order\DTOs\ShipmentDTO;
use App\Notifications\ShipmentNotification;

function shipmentTestDTO(array $overrides = []): ShipmentDTO
{
    return ShipmentDTO::fromArray(array_merge([
        'shipment_id' => 'shp-1',
        'order_id' => 'ord-1',
        'shipment_number' => 'SHP-001',
        'status' => 'in_transit',
        'courier' => 'pathao',
        'courier_service' => 'express',
        'tracking_number' => 'PA-123456',
        'tracking_url' => 'https://pathao.com/track/PA-123456',
        'label_url' => null,
        'recipient_name' => 'John Doe',
        'recipient_phone' => '+8801712345678',
        'recipient_address' => '123 Main St',
        'recipient_city' => 'Dhaka',
        'recipient_postal_code' => '1205',
        'shipping_cost' => 6000,
        'cod_amount' => 0,
        'declared_value' => 10000,
        'total_weight_grams' => 500,
        'total_items' => 2,
        'notes' => null,
        'courier_response' => null,
        'items' => [],
        'shipped_at' => '2026-05-02T10:00:00Z',
        'estimated_delivery' => '2026-05-05T18:00:00Z',
        'delivered_at' => null,
        'created_at' => '2026-05-02T09:00:00Z',
    ], $overrides));
}

test('shipment notification sends via mail channel', function () {
    $shipment = shipmentTestDTO();
    $notification = new ShipmentNotification($shipment);

    $channels = $notification->via(new stdClass);

    expect($channels)->toContain('mail');
});

test('shipment notification mail subject matches shipment number', function () {
    $shipment = shipmentTestDTO();
    $notification = new ShipmentNotification($shipment);

    $mail = $notification->toMail(new stdClass);

    expect($mail->subject)->toContain('SHP-001');
});

test('shipment notification mail uses markdown template', function () {
    $shipment = shipmentTestDTO();
    $notification = new ShipmentNotification($shipment);

    $mail = $notification->toMail(new stdClass);

    expect($mail->markdown)->toBe('emails.shipment-notification');
});

test('shipment notification mail passes shipment data to template', function () {
    $shipment = shipmentTestDTO();
    $notification = new ShipmentNotification($shipment);

    $mail = $notification->toMail(new stdClass);

    expect($mail->viewData['shipmentNumber'])->toBe('SHP-001')
        ->and($mail->viewData['carrier'])->toBe('pathao')
        ->and($mail->viewData['trackingNumber'])->toBe('PA-123456')
        ->and($mail->viewData['status'])->toBe('in_transit')
        ->and($mail->viewData['recipientName'])->toBe('John Doe');
});

test('shipment notification mail passes tracking url when available', function () {
    $shipment = shipmentTestDTO();
    $notification = new ShipmentNotification($shipment);

    $mail = $notification->toMail(new stdClass);

    expect($mail->viewData['trackingUrl'])->toBe('https://pathao.com/track/PA-123456')
        ->and($mail->viewData['totalItems'])->toBe(2);
});

test('shipment notification handles missing tracking url', function () {
    $shipment = shipmentTestDTO(['tracking_url' => null]);
    $notification = new ShipmentNotification($shipment);

    $mail = $notification->toMail(new stdClass);

    expect($mail->viewData['trackingUrl'])->toBeNull();
});

test('shipment notification handles missing optional fields', function () {
    $shipment = shipmentTestDTO([
        'tracking_number' => null,
        'recipient_name' => null,
        'recipient_city' => null,
        'estimated_delivery' => null,
    ]);
    $notification = new ShipmentNotification($shipment);

    $mail = $notification->toMail(new stdClass);

    expect($mail->subject)->toContain('SHP-001')
        ->and($mail->viewData['trackingNumber'])->toBeNull()
        ->and($mail->viewData['recipientName'])->toBeNull();
});
