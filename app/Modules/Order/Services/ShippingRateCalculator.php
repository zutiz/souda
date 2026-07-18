<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Modules\Order\Contracts\Courier\CourierRateRequest;
use App\Modules\Order\Contracts\Courier\CourierRateResult;

class ShippingRateCalculator
{
    public function __construct(
        protected CourierManager $courierManager,
    ) {}

    public function calculateForOrder(
        string $originCity,
        string $destinationCity,
        int $totalWeightGrams,
        int $declaredValue,
        array $preferredCouriers = [],
        string $serviceType = 'standard',
    ): array {
        $request = new CourierRateRequest(
            originCity: $originCity,
            destinationCity: $destinationCity,
            totalWeightGrams: $totalWeightGrams,
            declaredValue: $declaredValue,
            serviceType: $serviceType,
        );

        return $this->courierManager->getRates($request, $preferredCouriers);
    }

    public function getCheapestRate(array $rates): ?CourierRateResult
    {
        if (empty($rates)) {
            return null;
        }

        return $rates[0];
    }

    public function formatRate(CourierRateResult $rate): array
    {
        return [
            'courier' => $rate->courier,
            'service' => $rate->serviceType,
            'cost' => $rate->estimatedCost,
            'cost_formatted' => number_format($rate->estimatedCost / 100, 2),
            'estimated_days' => $rate->estimatedDays,
            'cod_available' => $rate->codAvailable,
        ];
    }
}
