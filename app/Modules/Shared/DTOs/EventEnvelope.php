<?php

declare(strict_types=1);

namespace App\Modules\Shared\DTOs;

use Carbon\CarbonImmutable;

readonly class EventEnvelope
{
    public function __construct(
        public string $eventId,
        public string $eventName,
        public string $correlationId,
        public ?string $causationId,
        public ?string $tenantId,
        public CarbonImmutable $occurredAt,
        public array $payload,
    ) {}

    public static function make(
        string $eventName,
        array $payload,
        ?string $correlationId = null,
        ?string $causationId = null,
        ?string $tenantId = null,
    ): self {
        return new self(
            eventId: (string) str()->ulid(),
            eventName: $eventName,
            correlationId: $correlationId ?? (string) str()->ulid(),
            causationId: $causationId,
            tenantId: $tenantId,
            occurredAt: new CarbonImmutable,
            payload: $payload,
        );
    }

    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_name' => $this->eventName,
            'correlation_id' => $this->correlationId,
            'causation_id' => $this->causationId,
            'tenant_id' => $this->tenantId,
            'occurred_at' => $this->occurredAt->toISOString(),
            'payload' => $this->payload,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            eventId: $data['event_id'],
            eventName: $data['event_name'],
            correlationId: $data['correlation_id'],
            causationId: $data['causation_id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            occurredAt: new CarbonImmutable($data['occurred_at']),
            payload: $data['payload'],
        );
    }

    public function idempotencyKey(string $listenerName): string
    {
        return "event:{$listenerName}:{$this->eventId}";
    }
}
