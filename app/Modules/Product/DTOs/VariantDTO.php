<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Enums\BarcodeTypeEnum;
use App\Modules\Product\Models\Variant;

readonly class VariantDTO
{
    public function __construct(
        public ?string $id,
        public string $productId,
        public string $sku,
        public ?string $barcode,
        public ?BarcodeTypeEnum $barcodeType,
        public string $name,
        public int $price,
        public ?int $compareAtPrice,
        public ?int $costPrice,
        public bool $trackInventory,
        public int $lowStockThreshold,
        public ?array $dimensions,
        public bool $isDefault,
        public array $attributeValueIds,
        public int $sortOrder,
    ) {}

    public static function fromModel(Variant $variant): self
    {
        return new self(
            id: $variant->id,
            productId: $variant->product_id,
            sku: $variant->sku,
            barcode: $variant->barcode,
            barcodeType: $variant->barcode_type ? BarcodeTypeEnum::tryFrom($variant->barcode_type) : null,
            name: $variant->name,
            price: $variant->price,
            compareAtPrice: $variant->compare_at_price,
            costPrice: $variant->cost_price,
            trackInventory: $variant->track_inventory,
            lowStockThreshold: $variant->low_stock_threshold,
            dimensions: $variant->weight !== null ? [
                'weight' => $variant->weight,
                'length' => $variant->length,
                'width' => $variant->width,
                'height' => $variant->height,
            ] : null,
            isDefault: $variant->is_default,
            attributeValueIds: $variant->relationLoaded('attributeValues')
                ? $variant->attributeValues->pluck('id')->toArray()
                : [],
            sortOrder: $variant->sort_order,
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: null,
            productId: $data['product_id'],
            sku: $data['sku'],
            barcode: $data['barcode'] ?? null,
            barcodeType: isset($data['barcode_type']) ? BarcodeTypeEnum::tryFrom($data['barcode_type']) : null,
            name: $data['name'],
            price: (int) $data['price'],
            compareAtPrice: isset($data['compare_at_price']) ? (int) $data['compare_at_price'] : null,
            costPrice: isset($data['cost_price']) ? (int) $data['cost_price'] : null,
            trackInventory: (bool) ($data['track_inventory'] ?? true),
            lowStockThreshold: (int) ($data['low_stock_threshold'] ?? 5),
            dimensions: $data['dimensions'] ?? null,
            isDefault: (bool) ($data['is_default'] ?? false),
            attributeValueIds: $data['attribute_value_ids'] ?? [],
            sortOrder: (int) ($data['sort_order'] ?? 0),
        );
    }
}
