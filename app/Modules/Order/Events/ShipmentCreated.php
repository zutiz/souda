<?php

declare(strict_types=1);

namespace App\Modules\Order\Events;

use App\Modules\Order\DTOs\ShipmentDTO;
use App\Modules\Shared\Contracts\DomainEvent;
use App\Modules\Shared\DTOs\EventEnvelope;
use App\Modules\Shared\Traits\EventDispatchable;
use Carbon\CarbonImmutable;

readonly class ShipmentCreated implements DomainEvent
{
    use EventDispatchable;

    public CarbonImmutable $occurredAt;

    public string $eventName;

    private EventEnvelope $envelope;

    public function __construct(
        public ShipmentDTO $shipment,
        public string $orderId,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) {
        $resolvedCorrelationId = $correlationId ?? (string) str()->ulid();

        $this->eventName = 'shipment.created';
        $this->occurredAt = new CarbonImmutable;
        $this->envelope = EventEnvelope::make(
            eventName: $this->eventName,
            payload: [
                'shipment' => $this->shipment->toArray(),
                'order_id' => $this->orderId,
            ],
            correlationId: $resolvedCorrelationId,
            causationId: $causationId,
            tenantId: null,
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
        return null;
    }
}
