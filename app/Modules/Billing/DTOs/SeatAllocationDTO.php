<?php

namespace App\Modules\Billing\DTOs;

use App\Modules\Billing\Enums\SeatStatus;
use App\Modules\Billing\Enums\SeatType;

class SeatAllocationDTO
{
    public function __construct(
        public readonly string $tenantId,
        public readonly SeatType $seatType,
        public readonly SeatStatus $status,
        public readonly ?int $subscriptionId = null,
        public readonly ?int $userId = null,
        public readonly ?string $email = null,
        public readonly ?string $invitationToken = null,
        public readonly ?string $allocatedAt = null,
        public readonly ?string $releasedAt = null,
        public readonly ?string $billingStartAt = null,
        public readonly array $metadata = [],
    ) {}

    public static function fromModel(object $allocation): self
    {
        return new self(
            tenantId: $allocation->tenant_id,
            seatType: $allocation->seat_type instanceof SeatType
                ? $allocation->seat_type
                : SeatType::from($allocation->seat_type),
            status: $allocation->status instanceof SeatStatus
                ? $allocation->status
                : SeatStatus::from($allocation->status),
            subscriptionId: $allocation->subscription_id,
            userId: $allocation->user_id,
            email: $allocation->email,
            invitationToken: $allocation->invitation_token,
            allocatedAt: $allocation->allocated_at?->toISOString(),
            releasedAt: $allocation->released_at?->toISOString(),
            billingStartAt: $allocation->billing_start_at?->toISOString(),
            metadata: $allocation->metadata ?? [],
        );
    }
}
