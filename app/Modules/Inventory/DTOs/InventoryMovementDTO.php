<?php

declare(strict_types=1);

namespace App\Modules\Inventory\DTOs;

use Carbon\CarbonImmutable;

readonly class InventoryMovementDTO
{
    public function __construct(
        public string $productId,
        public ?string $variantId,
        public string $warehouseId,
        public int $quantityChange,
        public int $quantityAfter,
        public string $type,
        public ?string $referenceType,
        public ?string $referenceId,
        public ?string $reason,
        public ?array $metadata,
        public CarbonImmutable $occurredAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            variantId: $data['variant_id'] ?? null,
            warehouseId: $data['warehouse_id'],
            quantityChange: (int) $data['quantity_change'],
            quantityAfter: (int) $data['quantity_after'],
            type: $data['type'],
            referenceType: $data['reference_type'] ?? null,
            referenceId: $data['reference_id'] ?? null,
            reason: $data['reason'] ?? null,
            metadata: $data['metadata'] ?? null,
            occurredAt: new CarbonImmutable($data['occurred_at'] ?? 'now'),
        );
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'variant_id' => $this->variantId,
            'warehouse_id' => $this->warehouseId,
            'quantity_change' => $this->quantityChange,
            'quantity_after' => $this->quantityAfter,
            'type' => $this->type,
            'reference_type' => $this->referenceType,
            'reference_id' => $this->referenceId,
            'reason' => $this->reason,
            'metadata' => $this->metadata,
            'occurred_at' => $this->occurredAt->toISOString(),
        ];
    }
}
