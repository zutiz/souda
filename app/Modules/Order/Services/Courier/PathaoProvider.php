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

class PathaoProvider implements CourierProvider
{
    private ?string $accessToken = null;

    public function name(): string
    {
        return 'pathao';
    }

    public function createShipment(CourierShipmentData $data): CourierShipmentResult
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->post($this->apiUrl('/aladdin/api/v1/orders'), [
            'store_id' => config('services.pathao.store_id'),
            'merchant_order_id' => $data->orderReference,
            'recipient_name' => $data->recipientName,
            'recipient_phone' => $data->recipientPhone,
            'recipient_address' => $data->recipientAddress,
            'recipient_city' => $data->recipientCity,
            'recipient_zip_code' => $data->recipientPostalCode,
            'item_quantity' => count($data->items ?? []),
            'item_weight' => $data->totalWeightGrams,
            'amount_to_collect' => $data->codAmount,
            'item_description' => $data->notes ?? 'Order items',
        ]);

        if ($response->failed()) {
            return CourierShipmentResult::failed(
                $response->json('message', 'Pathao API error'),
                $response->json(),
            );
        }

        $body = $response->json('data', []);

        return new CourierShipmentResult(
            success: true,
            trackingNumber: $body['tracking_code'] ?? $body['consignment_id'] ?? '',
            trackingUrl: null,
            labelUrl: $body['label_url'] ?? null,
            shippingCost: isset($body['delivery_fee']) ? (int) ($body['delivery_fee'] * 100) : null,
            estimatedDeliveryDate: $body['expected_delivery'] ?? null,
            rawResponse: $body,
        );
    }

    public function trackShipment(string $trackingNumber): CourierTrackingResult
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->get(
            $this->apiUrl("/aladdin/api/v1/orders/{$trackingNumber}/tracking")
        );

        $body = $response->json('data', []);

        return new CourierTrackingResult(
            trackingNumber: $trackingNumber,
            status: $body['status'] ?? 'unknown',
            statusDescription: $body['status_message'] ?? null,
            checkpoints: $body['tracking'] ?? null,
            estimatedDelivery: $body['expected_delivery'] ?? null,
            currentLocation: $body['current_location'] ?? null,
            rawResponse: $body,
        );
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->post(
            $this->apiUrl("/aladdin/api/v1/orders/{$trackingNumber}/cancel")
        );

        return $response->successful();
    }

    public function generateLabel(string $trackingNumber): ?string
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->get(
            $this->apiUrl("/aladdin/api/v1/orders/{$trackingNumber}/label")
        );

        return $response->successful() ? $response->body() : null;
    }

    public function calculateRate(CourierRateRequest $request): CourierRateResult
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->post($this->apiUrl('/aladdin/api/v1/price-calculation'), [
            'store_id' => config('services.pathao.store_id'),
            'item_type' => 'document',
            'delivery_type' => 48,
            'item_weight' => $request->totalWeightGrams,
            'recipient_city' => $request->destinationCity,
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
            estimatedDays: $body['estimated_days'] ?? null,
            codAvailable: true,
        );
    }

    public function validateWebhookSignature(array $payload, string $signature, ?string $secret = null): bool
    {
        $secret = $secret ?? config('services.pathao.webhook_secret');

        $computed = hash_hmac('sha256', json_encode($payload), $secret ?? '');

        return hash_equals($computed, $signature);
    }

    private function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $response = Http::post($this->apiUrl('/aladdin/api/v1/issue-token'), [
            'client_id' => config('services.pathao.client_id'),
            'client_secret' => config('services.pathao.client_secret'),
            'username' => config('services.pathao.username'),
            'password' => config('services.pathao.password'),
            'grant_type' => 'password',
        ]);

        $this->accessToken = $response->json('access_token', '');

        return $this->accessToken;
    }

    private function apiUrl(string $path): string
    {
        $base = config('services.pathao.base_url', 'https://api-hermes.pathao.com');

        return $base.$path;
    }
}
