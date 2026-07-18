<?php

declare(strict_types=1);

namespace App\Modules\Order\DTOs;

readonly class ShipmentItemDTO
{
    public function __construct(
        public string $name,
        public int $quantity,
        public int $unitPrice,
        public ?string $productId = null,
        public ?string $variantId = null,
        public ?string $sku = null,
        public ?string $orderItemId = null,
        public string $id = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            quantity: (int) $data['quantity'],
            unitPrice: (int) ($data['unit_price'] ?? 0),
            productId: $data['product_id'] ?? null,
            variantId: $data['variant_id'] ?? null,
            sku: $data['sku'] ?? null,
            orderItemId: $data['order_item_id'] ?? null,
            id: $data['id'] ?? '',
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
