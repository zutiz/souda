<?php

declare(strict_types=1);

namespace App\Modules\Order\Contracts\Courier;

readonly class CourierRateResult
{
    public function __construct(
        public string $courier,
        public string $serviceType,
        public int $estimatedCost,
        public ?int $estimatedDays,
        public bool $codAvailable,
        public ?string $error = null,
    ) {}
}
