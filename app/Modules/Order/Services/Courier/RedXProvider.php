<?php

declare(strict_types=1);

namespace App\Modules\Order\Services\Courier;

use App\Modules\Order\Contracts\Courier\CourierProvider;
use App\Modules\Order\Contracts\Courier\CourierRateRequest;
use App\Modules\Order\Contracts\Courier\CourierRateResult;
use App\Modules\Order\Contracts\Courier\CourierShipmentData;
use App\Modules\Order\Contracts\Courier\CourierShipmentResult;
use App\Modules\Order\Contracts\Courier\CourierTrackingResult;
use Illuminate\Support\Facades\Http;

class RedXProvider implements CourierProvider
{
    public function name(): string
    {
        return 'redx';
    }

    public function createShipment(CourierShipmentData $data): CourierShipmentResult
    {
        $response = Http::withToken(config('services.redx.api_token'))
            ->post($this->apiUrl('/api/v1/parcel/create'), [
                'reference' => $data->orderReference,
                'customer_name' => $data->recipientName,
                'customer_phone' => $data->recipientPhone,
                'delivery_address' => $data->recipientAddress,
                'area' => $data->recipientCity,
                'cash_collection_amount' => $data->codAmount,
                'product_value' => $data->declaredValue,
                'weight' => $data->totalWeightGrams,
                'remarks' => $data->notes,
            ]);

        if ($response->failed()) {
            return CourierShipmentResult::failed(
                $response->json('message', 'RedX API error'),
                $response->json(),
            );
        }

        $body = $response->json('data', []);

        return new CourierShipmentResult(
            success: true,
            trackingNumber: $body['tracking_id'] ?? $body['parcel_id'] ?? '',
            trackingUrl: $body['tracking_url'] ?? null,
            labelUrl: $body['label_url'] ?? null,
            shippingCost: isset($body['delivery_charge']) ? (int) ($body['delivery_charge'] * 100) : null,
            estimatedDeliveryDate: $body['expected_delivery'] ?? null,
            rawResponse: $body,
        );
    }

    public function trackShipment(string $trackingNumber): CourierTrackingResult
    {
        $response = Http::withToken(config('services.redx.api_token'))
            ->get($this->apiUrl("/api/v1/parcel/track/{$trackingNumber}"));

        $body = $response->json('data', []);

        return new CourierTrackingResult(
            trackingNumber: $trackingNumber,
            status: $body['status'] ?? 'unknown',
            statusDescription: $body['status_text'] ?? null,
            checkpoints: $body['tracking_events'] ?? null,
            estimatedDelivery: $body['estimated_delivery_date'] ?? null,
            currentLocation: $body['current_location'] ?? null,
            rawResponse: $body,
        );
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        $response = Http::withToken(config('services.redx.api_token'))
            ->post($this->apiUrl("/api/v1/parcel/cancel/{$trackingNumber}"));

        return $response->successful();
    }

    public function generateLabel(string $trackingNumber): ?string
    {
        $response = Http::withToken(config('services.redx.api_token'))
            ->get($this->apiUrl("/api/v1/parcel/label/{$trackingNumber}"));

        return $response->successful() ? $response->body() : null;
    }

    public function calculateRate(CourierRateRequest $request): CourierRateResult
    {
        $response = Http::withToken(config('services.redx.api_token'))
            ->post($this->apiUrl('/api/v1/rate'), [
                'origin_city' => $request->originCity,
                'destination_city' => $request->destinationCity,
                'weight' => $request->totalWeightGrams,
                'product_value' => $request->declaredValue,
            ]);

        if ($response->failed()) {
            return new CourierRateResult(
                courier: $this->name(),
                serviceType: $request->serviceType,
                estimatedCost: 0,
                estimatedDays: null,
                codAvailable: true,
                error: $response->json('message', 'Rate calculation failed'),
            );
        }

        $body = $response->json('data', []);

        return new CourierRateResult(
            courier: $this->name(),
            serviceType: $request->serviceType,
            estimatedCost: isset($body['delivery_charge']) ? $body['delivery_charge'] * 100 : 0,
            estimatedDays: $body['estimated_days'] ?? null,
            codAvailable: true,
        );
    }

    public function validateWebhookSignature(array $payload, string $signature, ?string $secret = null): bool
    {
        $secret = $secret ?? config('services.redx.webhook_secret');

        $computed = hash_hmac('sha256', json_encode($payload), $secret ?? '');

        return hash_equals($computed, $signature);
    }

    private function apiUrl(string $path): string
    {
        $base = config('services.redx.base_url', 'https://api.redx.com.bd');

        return $base.$path;
    }
}
