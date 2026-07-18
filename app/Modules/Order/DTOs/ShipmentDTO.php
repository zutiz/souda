<?php

declare(strict_types=1);

namespace App\Modules\Order\DTOs;

use Carbon\CarbonImmutable;

readonly class ShipmentDTO
{
    public function __construct(
        public string $shipmentId,
        public string $orderId,
        public string $shipmentNumber,
        public string $status,
        public ?string $courier,
        public ?string $courierService,
        public ?string $trackingNumber,
        public ?string $trackingUrl,
        public ?string $labelUrl,
        public ?string $recipientName,
        public ?string $recipientPhone,
        public ?string $recipientAddress,
        public ?string $recipientCity,
        public ?string $recipientPostalCode,
        public int $shippingCost,
        public int $codAmount,
        public int $declaredValue,
        public ?int $totalWeightGrams,
        public int $totalItems,
        public ?string $notes,
        public ?array $courierResponse,
        public array $items,
        public ?CarbonImmutable $shippedAt,
        public ?CarbonImmutable $estimatedDelivery,
        public ?CarbonImmutable $deliveredAt,
        public CarbonImmutable $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            shipmentId: $data['shipment_id'],
            orderId: $data['order_id'],
            shipmentNumber: $data['shipment_number'],
            status: $data['status'],
            courier: $data['courier'] ?? null,
            courierService: $data['courier_service'] ?? null,
            trackingNumber: $data['tracking_number'] ?? null,
            trackingUrl: $data['tracking_url'] ?? null,
            labelUrl: $data['label_url'] ?? null,
            recipientName: $data['recipient_name'] ?? null,
            recipientPhone: $data['recipient_phone'] ?? null,
            recipientAddress: $data['recipient_address'] ?? null,
            recipientCity: $data['recipient_city'] ?? null,
            recipientPostalCode: $data['recipient_postal_code'] ?? null,
            shippingCost: (int) ($data['shipping_cost'] ?? 0),
            codAmount: (int) ($data['cod_amount'] ?? 0),
            declaredValue: (int) ($data['declared_value'] ?? 0),
            totalWeightGrams: $data['total_weight_grams'] ?? null,
            totalItems: (int) ($data['total_items'] ?? 0),
            notes: $data['notes'] ?? null,
            courierResponse: $data['courier_response'] ?? null,
            items: array_map(fn (array $item) => ShipmentItemDTO::fromArray($item), $data['items'] ?? []),
            shippedAt: isset($data['shipped_at']) ? new CarbonImmutable($data['shipped_at']) : null,
            estimatedDelivery: isset($data['estimated_delivery']) ? new CarbonImmutable($data['estimated_delivery']) : null,
            deliveredAt: isset($data['delivered_at']) ? new CarbonImmutable($data['delivered_at']) : null,
            createdAt: new CarbonImmutable($data['created_at']),
        );
    }

    public function toArray(): array
    {
        return [
            'shipment_id' => $this->shipmentId,
            'order_id' => $this->orderId,
            'shipment_number' => $this->shipmentNumber,
            'status' => $this->status,
            'courier' => $this->courier,
            'courier_service' => $this->courierService,
            'tracking_number' => $this->trackingNumber,
            'tracking_url' => $this->trackingUrl,
            'label_url' => $this->labelUrl,
            'recipient_name' => $this->recipientName,
            'recipient_phone' => $this->recipientPhone,
            'recipient_address' => $this->recipientAddress,
            'recipient_city' => $this->recipientCity,
            'recipient_postal_code' => $this->recipientPostalCode,
            'shipping_cost' => $this->shippingCost,
            'cod_amount' => $this->codAmount,
            'declared_value' => $this->declaredValue,
            'total_weight_grams' => $this->totalWeightGrams,
            'total_items' => $this->totalItems,
            'notes' => $this->notes,
            'courier_response' => $this->courierResponse,
            'items' => array_map(fn (ShipmentItemDTO $item) => $item->toArray(), $this->items),
            'shipped_at' => $this->shippedAt?->toISOString(),
            'estimated_delivery' => $this->estimatedDelivery?->toISOString(),
            'delivered_at' => $this->deliveredAt?->toISOString(),
            'created_at' => $this->createdAt->toISOString(),
        ];
    }
}
