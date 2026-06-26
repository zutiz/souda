<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Enums\ProductStatusEnum;
use App\Modules\Product\Enums\ProductTypeEnum;
use App\Modules\Product\Models\Product;

readonly class ProductSummaryDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public ?string $sku,
        public ?string $thumbnailUrl,
        public int $basePrice,
        public ProductStatusEnum $status,
        public ProductTypeEnum $type,
        public ?string $categoryName,
        public ?string $brandName,
        public int $totalStock,
        public ?string $publishedAt,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            name: $product->name,
            slug: $product->slug,
            sku: $product->sku,
            thumbnailUrl: $product->getPrimaryMedia()?->file_path,
            basePrice: $product->base_price,
            status: $product->status instanceof ProductStatusEnum
                ? $product->status
                : ProductStatusEnum::from($product->status),
            type: $product->type instanceof ProductTypeEnum
                ? $product->type
                : ProductTypeEnum::from($product->type),
            categoryName: $product->relationLoaded('category') ? $product->category?->name : null,
            brandName: $product->relationLoaded('brand') ? $product->brand?->name : null,
            totalStock: $product->total_available,
            publishedAt: $product->published_at?->toIso8601String(),
        );
    }
}
