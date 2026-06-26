<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Models\Payment;
use App\Modules\Billing\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

class InvoiceService
{
    /**
     * Generate an invoice number for a payment.
     */
    public function generateInvoiceNumber(Payment $payment): string
    {
        $prefix = config('billing.invoice_prefix', 'INV-');
        $year = now()->format('Y');
        $month = now()->format('m');

        return sprintf(
            '%s%s%s-%06d',
            $prefix,
            $year,
            $month,
            $payment->id,
        );
    }

    /**
     * Get all invoices for a tenant.
     *
     * @return Collection<int, Payment>
     */
    public function getTenantInvoices(string $tenantId): Collection
    {
        return Payment::forTenant($tenantId)
            ->whereIn('status', ['completed', 'refunded', 'partial_refunded'])
            ->latest()
            ->get();
    }

    /**
     * Get a single invoice by payment ID for a tenant.
     */
    public function getInvoice(string $tenantId, int $paymentId): ?Payment
    {
        return Payment::forTenant($tenantId)
            ->where('id', $paymentId)
            ->first();
    }

    /**
     * Get upcoming billing date and amount for a tenant.
     *
     * @return array{billing_date: string|null, amount: int|null, currency: string|null}
     */
    public function getUpcomingBilling(string $tenantId): array
    {
        $subscription = Subscription::forTenant($tenantId)
            ->accessible()
            ->latest('id')
            ->first();

        if (! $subscription || ! $subscription->next_billing_at) {
            return [
                'billing_date' => null,
                'amount' => null,
                'currency' => null,
            ];
        }

        return [
            'billing_date' => $subscription->next_billing_at->toISOString(),
            'amount' => $subscription->amount,
            'currency' => $subscription->currency,
        ];
    }
}
