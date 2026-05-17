<?php

namespace App\Modules\Billing\Webhooks;

use App\Modules\Billing\Models\Subscription;
use App\Modules\Billing\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StripeWebhookHandler extends WebhookHandler
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function handle(Request $request): Response
    {
        if (! $this->verifySignature($request)) {
            Log::warning('Stripe webhook signature verification failed');

            return $this->errorResponse('Invalid signature', 401);
        }

        $payload = $request->all();
        $eventType = $this->parseEventType($payload);

        try {
            return match ($eventType) {
                'checkout.session.completed' => $this->handleCheckoutCompleted($payload),
                'customer.subscription.updated', 'customer.subscription.deleted' => $this->handleSubscriptionUpdated($payload),
                'invoice.paid' => $this->handleInvoicePaid($payload),
                'invoice.payment_failed' => $this->handleInvoicePaymentFailed($payload),
                default => $this->successResponse("Unhandled event type: {$eventType}"),
            };
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handler error', [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Internal error', 500);
        }
    }

    protected function verifySignature(Request $request): bool
    {
        // @TODO: Verify Stripe webhook signature using \Stripe\Webhook::constructEvent().
        // $payload = $request->getContent();
        // $sigHeader = $request->header('Stripe-Signature');
        // $webhookSecret = config('billing.gateways.stripe.config.webhook_secret');
        // \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);

        return true;
    }

    protected function parseEventType(array $payload): string
    {
        return $payload['type'] ?? 'unknown';
    }

    private function handleCheckoutCompleted(array $payload): Response
    {
        $session = $payload['data']['object'] ?? [];
        $transactionId = $session['id'] ?? '';

        if (! $transactionId) {
            return $this->errorResponse('Missing session ID');
        }

        $this->subscriptionService->verifyAndActivate(
            transactionId: $transactionId,
            gateway: 'stripe',
            payload: $payload,
        );

        return $this->successResponse('Checkout completed');
    }

    private function handleSubscriptionUpdated(array $payload): Response
    {
        $stripeSubscription = $payload['data']['object'] ?? [];
        $gatewaySubscriptionId = $stripeSubscription['id'] ?? '';

        // @TODO: Sync subscription status changes from Stripe.
        // $subscription = Subscription::where('gateway_subscription_id', $gatewaySubscriptionId)->first();

        Log::info('Stripe subscription updated', [
            'gateway_subscription_id' => $gatewaySubscriptionId,
            'status' => $stripeSubscription['status'] ?? 'unknown',
        ]);

        return $this->successResponse('Subscription updated');
    }

    private function handleInvoicePaid(array $payload): Response
    {
        $invoice = $payload['data']['object'] ?? [];
        $transactionId = $invoice['id'] ?? '';
        $chargeId = $invoice['charge'] ?? '';

        Log::info('Stripe invoice paid', [
            'invoice_id' => $invoice['id'] ?? '',
            'charge_id' => $chargeId,
        ]);

        // @TODO: Update local payment record and activate subscription if needed.

        return $this->successResponse('Invoice paid');
    }

    private function handleInvoicePaymentFailed(array $payload): Response
    {
        $invoice = $payload['data']['object'] ?? [];

        Log::warning('Stripe invoice payment failed', [
            'invoice_id' => $invoice['id'] ?? '',
            'attempt_count' => $invoice['attempt_count'] ?? 0,
        ]);

        // @TODO: Notify tenant about failed payment, update subscription status.

        return $this->successResponse('Invoice payment failed recorded');
    }
}
