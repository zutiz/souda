<?php

declare(strict_types=1);

namespace App\Modules\Billing\Listeners;

use App\Modules\Billing\DTOs\PaymentDTO;
use App\Modules\Billing\Events\InvoiceGenerated;
use App\Modules\Billing\Events\PaymentReceived;
use App\Modules\Billing\Services\InvoiceService;
use App\Modules\Shared\Traits\HasIdempotency;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateInvoice implements ShouldQueue
{
    use HasIdempotency;

    public string $queue = 'billing';

    public int $tries = 3;

    public array $backoff = [5, 15, 30];

    public function __construct(
        protected InvoiceService $invoiceService,
    ) {}

    public function handle(PaymentReceived $event): void
    {
        if ($this->alreadyProcessed($event)) {
            return;
        }

        $payment = $event->payment;
        $subscription = $event->subscription;

        $invoiceNumber = $this->invoiceService->generateInvoiceNumber($payment);

        $paymentDTO = PaymentDTO::fromArray([
            'transaction_id' => $payment->transaction_id,
            'gateway' => $payment->gateway,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'status' => $payment->status->value,
        ]);

        InvoiceGenerated::dispatch(
            invoiceNumber: $invoiceNumber,
            payment: $paymentDTO,
            amount: $payment->amount,
            currency: $payment->currency,
            tenantId: $subscription->tenant_id,
            subscriptionId: (string) $subscription->id,
            lineItems: null,
            correlationId: (string) str()->ulid(),
            causationId: $event->toEnvelope()->eventId,
        );

        $this->markProcessed($event);
    }

    public function failed(PaymentReceived $event, \Throwable $e): void
    {
        $this->releaseIdempotency($event);

        $this->logFailure($event, $e, [
            'payment_id' => $event->payment->id,
        ]);
    }
}
