<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\DTOs\OverageInvoiceDTO;
use App\Modules\Billing\Events\SeatOverageInvoiced;
use App\Modules\Billing\Models\Subscription;
use Illuminate\Support\Facades\Log;

class OverageInvoiceService
{
    public function __construct(
        private readonly SeatService $seatService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function generateOverageInvoice(string $tenantId): ?OverageInvoiceDTO
    {
        $subscription = $this->subscriptionService->getTenantSubscription($tenantId);

        if (! $subscription || ! $subscription->plan) {
            return null;
        }

        $plan = $subscription->plan;
        $strategy = $this->seatService->strategy($plan);
        $overage = $strategy->calculateOverage($tenantId, $plan, $subscription);

        if ($overage['overage'] <= 0) {
            return null;
        }

        $billingPeriodStart = $subscription->starts_at?->toISOString() ?? now()->startOfMonth()->toISOString();
        $billingPeriodEnd = $subscription->expires_at?->toISOString() ?? now()->endOfMonth()->toISOString();

        $dto = OverageInvoiceDTO::fromCalculation(
            calculation: [
                'tenant_id' => $tenantId,
                'subscription_id' => $subscription->id,
                'total_billable' => $overage['total_billable'],
                'overage' => $overage['overage'],
            ],
            plan: [
                'default_seats' => $plan->default_seats ?? 1,
                'seat_price' => $plan->seat_price ?? 0,
                'currency' => $plan->currency,
            ],
            billingPeriodStart: $billingPeriodStart,
            billingPeriodEnd: $billingPeriodEnd,
        );

        Log::info('Overage invoice generated', [
            'tenant_id' => $tenantId,
            'subscription_id' => $subscription->id,
            'overage_seats' => $dto->overageSeats,
            'overage_amount' => $dto->overageAmount,
        ]);

        SeatOverageInvoiced::dispatch($dto);

        return $dto;
    }

    public function generateAllTenantOverageInvoices(): int
    {
        $count = 0;

        $tenantIds = Subscription::accessible()
            ->distinct()
            ->pluck('tenant_id');

        foreach ($tenantIds as $tenantId) {
            try {
                $result = $this->generateOverageInvoice($tenantId);

                if ($result !== null) {
                    $count++;
                }
            } catch (\Throwable $e) {
                Log::error('Failed to generate overage invoice', [
                    'tenant_id' => $tenantId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }
}
