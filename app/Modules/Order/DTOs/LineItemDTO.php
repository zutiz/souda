<?php

declare(strict_types=1);

namespace App\Modules\Order\DTOs;

readonly class LineItemDTO
{
    public function __construct(
        public string $productId,
        public ?string $variantId,
        public string $name,
        public string $sku,
        public int $quantity,
        public int $unitPrice,
        public int $totalPrice,
        public ?int $taxAmount,
        public ?int $discountAmount,
        public ?string $warehouseId,
        public ?array $metadata,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            variantId: $data['variant_id'] ?? null,
            name: $data['name'],
            sku: $data['sku'] ?? '',
            quantity: (int) $data['quantity'],
            unitPrice: (int) ($data['unit_price'] ?? 0),
            totalPrice: (int) ($data['total_price'] ?? 0),
            taxAmount: isset($data['tax_amount']) ? (int) $data['tax_amount'] : null,
            discountAmount: isset($data['discount_amount']) ? (int) $data['discount_amount'] : null,
            warehouseId: $data['warehouse_id'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
