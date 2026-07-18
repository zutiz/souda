<?php

declare(strict_types=1);

namespace App\Modules\Order\Events;

use App\Modules\Order\DTOs\LineItemDTO;
use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Shared\Contracts\DomainEvent;
use App\Modules\Shared\DTOs\EventEnvelope;
use App\Modules\Shared\Traits\EventDispatchable;
use Carbon\CarbonImmutable;

readonly class OrderItemAdded implements DomainEvent
{
    use EventDispatchable;

    public CarbonImmutable $occurredAt;

    public string $eventName;

    private EventEnvelope $envelope;

    public function __construct(
        public OrderDTO $order,
        public LineItemDTO $item,
        public ?string $correlationId = null,
        public ?string $causationId = null,
    ) {
        $resolvedCorrelationId = $correlationId ?? (string) str()->ulid();

        $this->eventName = 'order.item_added';
        $this->occurredAt = new CarbonImmutable;
        $this->envelope = EventEnvelope::make(
            eventName: $this->eventName,
            payload: [
                'order' => $this->order->toArray(),
                'item' => $this->item->toArray(),
            ],
            correlationId: $resolvedCorrelationId,
            causationId: $causationId,
            tenantId: $this->order->tenantId,
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
        return $this->order->tenantId;
    }
}
