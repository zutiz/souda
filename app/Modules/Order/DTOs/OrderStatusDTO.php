<?php

declare(strict_types=1);

namespace App\Modules\Order\DTOs;

use Carbon\CarbonImmutable;

readonly class OrderStatusDTO
{
    public function __construct(
        public string $orderId,
        public string $fromStatus,
        public string $toStatus,
        public ?string $changedBy,
        public ?string $reason,
        public CarbonImmutable $occurredAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            orderId: $data['order_id'],
            fromStatus: $data['from_status'],
            toStatus: $data['to_status'],
            changedBy: $data['changed_by'] ?? null,
            reason: $data['reason'] ?? null,
            occurredAt: new CarbonImmutable($data['occurred_at']),
        );
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'from_status' => $this->fromStatus,
            'to_status' => $this->toStatus,
            'changed_by' => $this->changedBy,
            'reason' => $this->reason,
            'occurred_at' => $this->occurredAt->toISOString(),
        ];
    }
}
