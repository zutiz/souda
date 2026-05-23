<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Enums\ProductStatusEnum;
use App\Modules\Product\Enums\ProductTypeEnum;
use App\Modules\Product\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

readonly class ProductDTO
{
    public function __construct(
        public ?string $id,
        public ?int $categoryId,
        public ?int $brandId,
        public ?int $taxCategoryId,
        public string $name,
        public string $slug,
        public ?string $sku,
        public ?string $barcode,
        public ?string $barcodeType,
        public ?string $description,
        public ?string $shortDescription,
        public ProductTypeEnum $type,
        public ProductStatusEnum $status,
        public int $basePrice,
        public ?int $compareAtPrice,
        public ?int $costPrice,
        public bool $taxInclusive,
        public bool $trackInventory,
        public int $lowStockThreshold,
        public ?array $dimensions,
        public ?array $categoryIds,
        public ?array $attributeValues,
        public ?array $metadata,
        public ?CarbonImmutable $publishedAt,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            categoryId: $product->category_id,
            brandId: $product->brand_id,
            taxCategoryId: $product->tax_category_id,
            name: $product->name,
            slug: $product->slug,
            sku: $product->sku,
            barcode: $product->barcode,
            barcodeType: $product->barcode_type,
            description: $product->description,
            shortDescription: $product->short_description,
            type: $product->type instanceof ProductTypeEnum
                ? $product->type
                : ProductTypeEnum::from($product->type),
            status: $product->status instanceof ProductStatusEnum
                ? $product->status
                : ProductStatusEnum::from($product->status),
            basePrice: $product->base_price,
            compareAtPrice: $product->compare_at_price,
            costPrice: $product->cost_price,
            taxInclusive: $product->tax_inclusive ?? false,
            trackInventory: $product->track_inventory ?? true,
            lowStockThreshold: $product->low_stock_threshold,
            dimensions: $product->weight !== null ? [
                'weight' => $product->weight,
                'length' => $product->length,
                'width' => $product->width,
                'height' => $product->height,
            ] : null,
            categoryIds: $product->relationLoaded('categories')
                ? $product->categories->pluck('id')->toArray()
                : null,
            attributeValues: $product->relationLoaded('attributeValues')
                ? $product->attributeValues->toArray()
                : null,
            metadata: $product->metadata,
            publishedAt: $product->published_at instanceof Carbon
                ? CarbonImmutable::instance($product->published_at)
                : null,
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: null,
            categoryId: $data['category_id'] ?? null,
            brandId: $data['brand_id'] ?? null,
            taxCategoryId: $data['tax_category_id'] ?? null,
            name: $data['name'],
            slug: $data['slug'] ?? '',
            sku: $data['sku'] ?? null,
            barcode: $data['barcode'] ?? null,
            barcodeType: $data['barcode_type'] ?? null,
            description: $data['description'] ?? null,
            shortDescription: $data['short_description'] ?? null,
            type: ProductTypeEnum::from($data['type']),
            status: ProductStatusEnum::from($data['status']),
            basePrice: (int) $data['base_price'],
            compareAtPrice: isset($data['compare_at_price']) ? (int) $data['compare_at_price'] : null,
            costPrice: isset($data['cost_price']) ? (int) $data['cost_price'] : null,
            taxInclusive: (bool) ($data['tax_inclusive'] ?? false),
            trackInventory: (bool) ($data['track_inventory'] ?? true),
            lowStockThreshold: (int) ($data['low_stock_threshold'] ?? 5),
            dimensions: $data['dimensions'] ?? null,
            categoryIds: $data['category_ids'] ?? null,
            attributeValues: $data['attribute_values'] ?? null,
            metadata: $data['metadata'] ?? null,
            publishedAt: isset($data['published_at']) ? CarbonImmutable::parse($data['published_at']) : null,
        );
    }
}
