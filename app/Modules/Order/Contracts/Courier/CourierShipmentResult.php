<?php

declare(strict_types=1);

namespace App\Modules\Order\Contracts\Courier;

readonly class CourierShipmentResult
{
    public function __construct(
        public bool $success,
        public string $trackingNumber,
        public ?string $trackingUrl = null,
        public ?string $labelUrl = null,
        public ?int $shippingCost = null,
        public ?string $estimatedDeliveryDate = null,
        public ?string $error = null,
        public ?array $rawResponse = null,
    ) {}

    public static function failed(string $error, ?array $rawResponse = null): self
    {
        return new self(
            success: false,
            trackingNumber: '',
            error: $error,
            rawResponse: $rawResponse,
        );
    }
}
