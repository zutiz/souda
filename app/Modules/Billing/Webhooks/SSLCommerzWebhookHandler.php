<?php

namespace App\Modules\Billing\Webhooks;

use App\Modules\Billing\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SSLCommerzWebhookHandler extends WebhookHandler
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function handle(Request $request): Response
    {
        $payload = $request->all();

        if (! $this->verifySignature($request)) {
            Log::warning('SSLCommerz webhook signature verification failed');

            return $this->errorResponse('Invalid signature', 401);
        }

        $eventType = $this->parseEventType($payload);

        try {
            return match ($eventType) {
                'success' => $this->handleSuccess($payload),
                'fail' => $this->handleFail($payload),
                'cancel' => $this->handleCancel($payload),
                default => $this->successResponse("Unhandled event type: {$eventType}"),
            };
        } catch (\Throwable $e) {
            Log::error('SSLCommerz webhook handler error', [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Internal error', 500);
        }
    }

    protected function verifySignature(Request $request): bool
    {
        // @TODO: Verify SSLCommerz IPN hash.
        // Reference: https://developer.sslcommerz.com/ipn/
        // $hash = strtoupper(md5($storePassword . $verifyKey));
        // return $hash === $request->input('verify_hash');

        return true;
    }

    protected function parseEventType(array $payload): string
    {
        // SSLCommerz sends status in 'status' field.
        return $payload['status'] ?? 'unknown';
    }

    private function handleSuccess(array $payload): Response
    {
        $transactionId = $payload['tran_id'] ?? '';

        if (! $transactionId) {
            return $this->errorResponse('Missing transaction ID');
        }

        $this->subscriptionService->verifyAndActivate(
            transactionId: $transactionId,
            gateway: 'sslcommerz',
            payload: $payload,
        );

        return $this->successResponse('Payment successful');
    }

    private function handleFail(array $payload): Response
    {
        Log::warning('SSLCommerz payment failed', [
            'tran_id' => $payload['tran_id'] ?? '',
            'error' => $payload['error'] ?? 'Unknown error',
        ]);

        return $this->successResponse('Payment failure recorded');
    }

    private function handleCancel(array $payload): Response
    {
        Log::info('SSLCommerz payment cancelled by user', [
            'tran_id' => $payload['tran_id'] ?? '',
        ]);

        return $this->successResponse('Payment cancelled');
    }
}
