<?php

declare(strict_types=1);

namespace App\Modules\Order\Contracts\Courier;

readonly class CourierTrackingResult
{
    public function __construct(
        public string $trackingNumber,
        public string $status,
        public ?string $statusDescription,
        public ?array $checkpoints,
        public ?string $estimatedDelivery,
        public ?string $currentLocation,
        public ?array $rawResponse,
    ) {}
}
