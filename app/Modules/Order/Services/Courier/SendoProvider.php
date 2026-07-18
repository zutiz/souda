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

class SendoProvider implements CourierProvider
{
    public function name(): string
    {
        return 'sendo';
    }

    public function createShipment(CourierShipmentData $data): CourierShipmentResult
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.sendo.api_key'),
            'x-api-secret' => config('services.sendo.api_secret'),
        ])->post($this->apiUrl('/v1/shipments'), [
            'order_reference' => $data->orderReference,
            'delivery_name' => $data->recipientName,
            'delivery_phone' => $data->recipientPhone,
            'delivery_address' => $data->recipientAddress,
            'delivery_city' => $data->recipientCity,
            'delivery_zip' => $data->recipientPostalCode,
            'cod_amount' => $data->codAmount,
            'declared_value' => $data->declaredValue,
            'weight_grams' => $data->totalWeightGrams,
            'note' => $data->notes,
        ]);

        if ($response->failed()) {
            return CourierShipmentResult::failed(
                $response->json('message', 'Sendo API error'),
                $response->json(),
            );
        }

        $body = $response->json('data', []);

        return new CourierShipmentResult(
            success: true,
            trackingNumber: $body['tracking_id'] ?? $body['shipment_id'] ?? '',
            trackingUrl: $body['tracking_url'] ?? null,
            labelUrl: null,
            shippingCost: null,
            estimatedDeliveryDate: $body['estimated_delivery'] ?? null,
            rawResponse: $body,
        );
    }

    public function trackShipment(string $trackingNumber): CourierTrackingResult
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.sendo.api_key'),
            'x-api-secret' => config('services.sendo.api_secret'),
        ])->get($this->apiUrl("/v1/shipments/{$trackingNumber}/tracking"));

        $body = $response->json('data', []);

        return new CourierTrackingResult(
            trackingNumber: $trackingNumber,
            status: $body['status'] ?? 'unknown',
            statusDescription: $body['status_description'] ?? null,
            checkpoints: $body['tracking_history'] ?? null,
            estimatedDelivery: $body['estimated_delivery'] ?? null,
            currentLocation: $body['current_location'] ?? null,
            rawResponse: $body,
        );
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.sendo.api_key'),
            'x-api-secret' => config('services.sendo.api_secret'),
        ])->post($this->apiUrl("/v1/shipments/{$trackingNumber}/cancel"));

        return $response->successful();
    }

    public function generateLabel(string $trackingNumber): ?string
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.sendo.api_key'),
            'x-api-secret' => config('services.sendo.api_secret'),
        ])->get($this->apiUrl("/v1/shipments/{$trackingNumber}/label"));

        return $response->successful() ? $response->body() : null;
    }

    public function calculateRate(CourierRateRequest $request): CourierRateResult
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.sendo.api_key'),
            'x-api-secret' => config('services.sendo.api_secret'),
        ])->post($this->apiUrl('/v1/rates'), [
            'origin_city' => $request->originCity,
            'destination_city' => $request->destinationCity,
            'weight_grams' => $request->totalWeightGrams,
            'declared_value' => $request->declaredValue,
        ]);

        if ($response->failed()) {
            return new CourierRateResult(
                courier: $this->name(),
                serviceType: $request->serviceType,
                estimatedCost: 0,
                estimatedDays: null,
                codAvailable: false,
                error: $response->json('message', 'Rate calculation failed'),
            );
        }

        $body = $response->json('data', []);

        return new CourierRateResult(
            courier: $this->name(),
            serviceType: $request->serviceType,
            estimatedCost: $body['shipping_fee'] ?? 0,
            estimatedDays: $body['estimated_days'] ?? null,
            codAvailable: $body['cod_available'] ?? false,
        );
    }

    public function validateWebhookSignature(array $payload, string $signature, ?string $secret = null): bool
    {
        $secret = $secret ?? config('services.sendo.webhook_secret');

        $computed = hash_hmac('sha256', json_encode($payload), $secret ?? '');

        return hash_equals($computed, $signature);
    }

    private function apiUrl(string $path): string
    {
        $base = config('services.sendo.base_url', 'https://api.sendo.vn');

        return $base.$path;
    }
}
