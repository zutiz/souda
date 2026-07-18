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

class SteadfastProvider implements CourierProvider
{
    public function name(): string
    {
        return 'steadfast';
    }

    public function createShipment(CourierShipmentData $data): CourierShipmentResult
    {
        $response = Http::withHeaders([
            'Api-Key' => config('services.steadfast.api_key'),
            'Secret-Key' => config('services.steadfast.secret_key'),
        ])->post($this->apiUrl('/api/v1/create_order'), [
            'invoice' => $data->orderReference,
            'recipient_name' => $data->recipientName,
            'recipient_phone' => $data->recipientPhone,
            'recipient_address' => $data->recipientAddress,
            'recipient_city' => $data->recipientCity,
            'recipient_zone' => $data->recipientPostalCode,
            'cod_amount' => $data->codAmount,
            'note' => $data->notes,
        ]);

        if ($response->failed()) {
            return CourierShipmentResult::failed(
                $response->json('message', 'Steadfast API error'),
                $response->json(),
            );
        }

        $body = $response->json('data', []);

        return new CourierShipmentResult(
            success: true,
            trackingNumber: $body['tracking_code'] ?? $body['consignment_id'] ?? '',
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
            'Api-Key' => config('services.steadfast.api_key'),
            'Secret-Key' => config('services.steadfast.secret_key'),
        ])->get($this->apiUrl("/api/v1/status_by_invoice/{$trackingNumber}"));

        $body = $response->json('data', []);

        return new CourierTrackingResult(
            trackingNumber: $trackingNumber,
            status: $body['status'] ?? 'unknown',
            statusDescription: $body['status_message'] ?? null,
            checkpoints: null,
            estimatedDelivery: null,
            currentLocation: $body['current_location'] ?? null,
            rawResponse: $body,
        );
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        $response = Http::withHeaders([
            'Api-Key' => config('services.steadfast.api_key'),
            'Secret-Key' => config('services.steadfast.secret_key'),
        ])->post($this->apiUrl("/api/v1/cancel_order/{$trackingNumber}"));

        return $response->successful();
    }

    public function generateLabel(string $trackingNumber): ?string
    {
        $response = Http::withHeaders([
            'Api-Key' => config('services.steadfast.api_key'),
            'Secret-Key' => config('services.steadfast.secret_key'),
        ])->get($this->apiUrl("/api/v1/label/{$trackingNumber}"));

        return $response->successful() ? $response->body() : null;
    }

    public function calculateRate(CourierRateRequest $request): CourierRateResult
    {
        $response = Http::withHeaders([
            'Api-Key' => config('services.steadfast.api_key'),
            'Secret-Key' => config('services.steadfast.secret_key'),
        ])->post($this->apiUrl('/api/v1/price_calculation'), [
            'sender_city' => $request->originCity,
            'recipient_city' => $request->destinationCity,
            'weight' => $request->totalWeightGrams,
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
            estimatedCost: isset($body['price']) ? (int) ($body['price'] * 100) : 0,
            estimatedDays: $body['estimated_delivery_days'] ?? null,
            codAvailable: true,
        );
    }

    public function validateWebhookSignature(array $payload, string $signature, ?string $secret = null): bool
    {
        $secret = $secret ?? config('services.steadfast.webhook_secret');

        $computed = hash_hmac('sha256', json_encode($payload), $secret ?? '');

        return hash_equals($computed, $signature);
    }

    private function apiUrl(string $path): string
    {
        $base = config('services.steadfast.base_url', 'https://portal.steadfast.com.bd');

        return $base.$path;
    }
}
