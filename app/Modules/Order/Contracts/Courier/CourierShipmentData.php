<?php

declare(strict_types=1);

namespace App\Modules\Order\Contracts\Courier;

readonly class CourierShipmentData
{
    public function __construct(
        public string $orderReference,
        public string $recipientName,
        public string $recipientPhone,
        public string $recipientAddress,
        public string $recipientCity,
        public ?string $recipientPostalCode,
        public int $totalWeightGrams,
        public int $declaredValue,
        public int $codAmount,
        public string $serviceType = 'standard',
        public ?string $notes = null,
        public ?array $items = null,
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
