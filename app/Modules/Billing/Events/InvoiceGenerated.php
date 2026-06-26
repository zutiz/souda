<?php

declare(strict_types=1);

namespace App\Modules\Billing\Events;

use App\Modules\Billing\DTOs\PaymentDTO;
use App\Modules\Shared\Contracts\DomainEvent;
use App\Modules\Shared\DTOs\EventEnvelope;
use App\Modules\Shared\Traits\EventDispatchable;
use Carbon\CarbonImmutable;

readonly class InvoiceGenerated implements DomainEvent
{
    use EventDispatchable;

    public CarbonImmutable $occurredAt;

    public string $eventName;

    private EventEnvelope $envelope;

    public function __construct(
        public string $invoiceNumber,
        public PaymentDTO $payment,
        public int $amount,
        public string $currency,
        public string $tenantId,
        public ?string $subscriptionId,
        public ?array $lineItems,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) {
        $resolvedCorrelationId = $correlationId ?? (string) str()->ulid();

        $this->eventName = 'billing.invoice.generated';
        $this->occurredAt = new CarbonImmutable;
        $this->envelope = EventEnvelope::make(
            eventName: $this->eventName,
            payload: [
                'invoice_number' => $this->invoiceNumber,
                'payment' => [
                    'transaction_id' => $this->payment->transactionId,
                    'gateway' => $this->payment->gateway,
                    'amount' => $this->payment->amount,
                    'currency' => $this->payment->currency,
                    'status' => $this->payment->status,
                ],
                'amount' => $this->amount,
                'currency' => $this->currency,
                'subscription_id' => $this->subscriptionId,
                'line_items' => $this->lineItems,
            ],
            correlationId: $resolvedCorrelationId,
            causationId: $causationId,
            tenantId: $this->tenantId,
        );
    }

    public function toEnvelope(): EventEnvelope
    {
        return $this->envelope;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId ?? $this->envelope->correlationId;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }
}
