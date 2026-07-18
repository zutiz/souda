<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Modules\Order\Contracts\Courier\CourierProvider;
use App\Modules\Order\Contracts\Courier\CourierRateRequest;
use App\Modules\Order\Contracts\Courier\CourierRateResult;
use App\Modules\Order\Contracts\Courier\CourierShipmentData;
use App\Modules\Order\Contracts\Courier\CourierShipmentResult;
use App\Modules\Order\Contracts\Courier\CourierTrackingResult;
use App\Modules\Order\Exceptions\CourierNotAvailableException;

class CourierManager
{
    private array $drivers = [];

    public function register(string $name, CourierProvider $provider): void
    {
        $this->drivers[$name] = $provider;
    }

    public function driver(?string $name = null): CourierProvider
    {
        $name = $name ?? config('order.courier.default', 'pathao');

        if (! isset($this->drivers[$name])) {
            throw new CourierNotAvailableException($name);
        }

        return $this->drivers[$name];
    }

    public function registerDefaults(): void
    {
        $this->register('pathao', new Courier\PathaoProvider);
        $this->register('steadfast', new Courier\SteadfastProvider);
        $this->register('redx', new Courier\RedXProvider);
        $this->register('sendo', new Courier\SendoProvider);
        $this->register('paperfly', new Courier\PaperflyProvider);
    }

    public function createShipment(string $courier, CourierShipmentData $data): CourierShipmentResult
    {
        $provider = $this->driver($courier);

        return $provider->createShipment($data);
    }

    public function trackShipment(string $courier, string $trackingNumber): CourierTrackingResult
    {
        $provider = $this->driver($courier);

        return $provider->trackShipment($trackingNumber);
    }

    public function cancelShipment(string $courier, string $trackingNumber): bool
    {
        $provider = $this->driver($courier);

        return $provider->cancelShipment($trackingNumber);
    }

    public function getRates(CourierRateRequest $request, array $couriers = []): array
    {
        $rates = [];
        $targets = $couriers ?: array_keys($this->drivers);

        foreach ($targets as $name) {
            try {
                $result = $this->driver($name)->calculateRate($request);
                $rates[] = $result;
            } catch (CourierNotAvailableException) {
                continue;
            }
        }

        usort($rates, fn (CourierRateResult $a, CourierRateResult $b) => $a->estimatedCost <=> $b->estimatedCost);

        return $rates;
    }

    public function getAvailableCouriers(): array
    {
        return array_keys($this->drivers);
    }
}
