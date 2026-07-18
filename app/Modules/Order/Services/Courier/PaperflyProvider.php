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

class PaperflyProvider implements CourierProvider
{
    public function name(): string
    {
        return 'paperfly';
    }

    public function createShipment(CourierShipmentData $data): CourierShipmentResult
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.paperfly.api_key'),
            'x-secret-key' => config('services.paperfly.secret_key'),
        ])->post($this->apiUrl('/api/v1/parcel'), [
            'merchant_order_id' => $data->orderReference,
            'customer_name' => $data->recipientName,
            'customer_phone' => $data->recipientPhone,
            'delivery_address' => $data->recipientAddress,
            'city' => $data->recipientCity,
            'post_code' => $data->recipientPostalCode,
            'product_value' => $data->declaredValue,
            'cod_amount' => $data->codAmount,
            'weight' => $data->totalWeightGrams,
            'note' => $data->notes,
        ]);

        if ($response->failed()) {
            return CourierShipmentResult::failed(
                $response->json('message', 'Paperfly API error'),
                $response->json(),
            );
        }

        $body = $response->json('data', []);

        return new CourierShipmentResult(
            success: true,
            trackingNumber: $body['tracking_id'] ?? $body['parcel_id'] ?? '',
            trackingUrl: $body['tracking_url'] ?? null,
            labelUrl: $body['label_url'] ?? null,
            shippingCost: isset($body['delivery_fee']) ? (int) ($body['delivery_fee'] * 100) : null,
            estimatedDeliveryDate: $body['estimated_delivery'] ?? null,
            rawResponse: $body,
        );
    }

    public function trackShipment(string $trackingNumber): CourierTrackingResult
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.paperfly.api_key'),
            'x-secret-key' => config('services.paperfly.secret_key'),
        ])->get($this->apiUrl("/api/v1/parcel/{$trackingNumber}/track"));

        $body = $response->json('data', []);

        return new CourierTrackingResult(
            trackingNumber: $trackingNumber,
            status: $body['status'] ?? 'unknown',
            statusDescription: $body['status_description'] ?? null,
            checkpoints: $body['tracking_history'] ?? null,
            estimatedDelivery: $body['estimated_delivery_date'] ?? null,
            currentLocation: $body['current_location'] ?? null,
            rawResponse: $body,
        );
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.paperfly.api_key'),
            'x-secret-key' => config('services.paperfly.secret_key'),
        ])->post($this->apiUrl("/api/v1/parcel/{$trackingNumber}/cancel"));

        return $response->successful();
    }

    public function generateLabel(string $trackingNumber): ?string
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.paperfly.api_key'),
            'x-secret-key' => config('services.paperfly.secret_key'),
        ])->get($this->apiUrl("/api/v1/parcel/{$trackingNumber}/label"));

        return $response->successful() ? $response->body() : null;
    }

    public function calculateRate(CourierRateRequest $request): CourierRateResult
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.paperfly.api_key'),
            'x-secret-key' => config('services.paperfly.secret_key'),
        ])->post($this->apiUrl('/api/v1/rate'), [
            'origin_city' => $request->originCity,
            'destination_city' => $request->destinationCity,
            'weight' => $request->totalWeightGrams,
            'declared_value' => $request->declaredValue,
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
            estimatedCost: $body['delivery_charge'] ?? 0,
            estimatedDays: $body['estimated_days'] ?? null,
            codAvailable: true,
        );
    }

    public function validateWebhookSignature(array $payload, string $signature, ?string $secret = null): bool
    {
        $secret = $secret ?? config('services.paperfly.webhook_secret');

        $computed = hash_hmac('sha256', json_encode($payload), $secret ?? '');

        return hash_equals($computed, $signature);
    }

    private function apiUrl(string $path): string
    {
        $base = config('services.paperfly.base_url', 'https://api.paperfly.com.bd');

        return $base.$path;
    }
}
