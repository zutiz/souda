<?php

namespace App\Modules\Billing\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

abstract class WebhookHandler
{
    /**
     * Handle an incoming webhook request from a payment gateway.
     */
    abstract public function handle(Request $request): Response;

    /**
     * Verify the webhook signature for authenticity.
     */
    abstract protected function verifySignature(Request $request): bool;

    /**
     * Parse the event type from the webhook payload.
     */
    abstract protected function parseEventType(array $payload): string;

    /**
     * Return a success response to the gateway.
     */
    protected function successResponse(string $message = 'Webhook processed'): Response
    {
        return response($message, 200);
    }

    /**
     * Return a failure response to the gateway.
     */
    protected function errorResponse(string $message = 'Webhook processing failed', int $status = 400): Response
    {
        return response($message, $status);
    }
}
