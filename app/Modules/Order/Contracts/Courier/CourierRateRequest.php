<?php

declare(strict_types=1);

namespace App\Modules\Order\Contracts\Courier;

readonly class CourierRateRequest
{
    public function __construct(
        public string $originCity,
        public string $destinationCity,
        public int $totalWeightGrams,
        public int $declaredValue,
        public string $serviceType = 'standard',
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
