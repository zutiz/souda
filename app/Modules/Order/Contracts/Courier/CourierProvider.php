<?php

declare(strict_types=1);

namespace App\Modules\Order\Contracts\Courier;

interface CourierProvider
{
    public function name(): string;

    public function createShipment(CourierShipmentData $data): CourierShipmentResult;

    public function trackShipment(string $trackingNumber): CourierTrackingResult;

    public function cancelShipment(string $trackingNumber): bool;

    public function generateLabel(string $trackingNumber): ?string;

    public function calculateRate(CourierRateRequest $request): CourierRateResult;

    public function validateWebhookSignature(array $payload, string $signature, ?string $secret = null): bool;
}
