<?php

namespace App\Modules\Billing\Listeners;

use App\Modules\Billing\Events\PaymentFailed;
use App\Modules\Billing\Events\PaymentReceived;
use App\Modules\Billing\Events\SubscriptionActivated;
use App\Modules\Billing\Events\SubscriptionCancelled;
use App\Modules\Billing\Events\SubscriptionExpired;
use App\Services\BillingEmailService;
use Illuminate\Support\Facades\Log;

class SendSubscriptionNotification
{
    public function __construct(
        private readonly BillingEmailService $emailService,
    ) {}

    /**
     * Handle subscription activated events.
     */
    public function handleSubscriptionActivated(SubscriptionActivated $event): void
    {
        $subscription = $event->subscription;
        $tenant = $subscription->tenant;

        if (! $tenant) {
            return;
        }

        try {
            $this->emailService->sendSubscriptionActivated($tenant);
        } catch (\Throwable $e) {
            Log::error('Failed to send subscription activated email', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle subscription expired events.
     */
    public function handleSubscriptionExpired(SubscriptionExpired $event): void
    {
        Log::info('Subscription expired', [
            'subscription_id' => $event->subscription->id,
            'tenant_id' => $event->subscription->tenant_id,
        ]);
    }

    /**
     * Handle payment received events.
     */
    public function handlePaymentReceived(PaymentReceived $event): void
    {
        $subscription = $event->subscription;
        $tenant = $subscription->tenant;

        if (! $tenant) {
            return;
        }

        try {
            $this->emailService->sendInvoicePaid($tenant);
        } catch (\Throwable $e) {
            Log::error('Failed to send invoice paid email', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle payment failed events.
     */
    public function handlePaymentFailed(PaymentFailed $event): void
    {
        $subscription = $event->subscription;
        $tenant = $subscription->tenant;

        if (! $tenant) {
            return;
        }

        try {
            $this->emailService->sendPaymentFailed($tenant);
        } catch (\Throwable $e) {
            Log::error('Failed to send payment failed email', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle subscription cancelled events.
     */
    public function handleSubscriptionCancelled(SubscriptionCancelled $event): void
    {
        $subscription = $event->subscription;
        $tenant = $subscription->tenant;

        if (! $tenant) {
            return;
        }

        try {
            $this->emailService->sendSubscriptionCanceled($tenant);
        } catch (\Throwable $e) {
            Log::error('Failed to send subscription cancelled email', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
