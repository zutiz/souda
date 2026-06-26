<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Enums\MediaTypeEnum;
use App\Modules\Product\Models\ProductMedia;

readonly class ProductMediaDTO
{
    public function __construct(
        public ?int $id,
        public string $productId,
        public ?string $variantId,
        public string $filePath,
        public MediaTypeEnum $fileType,
        public string $mimeType,
        public int $fileSize,
        public ?string $altText,
        public bool $isPrimary,
        public int $sortOrder,
    ) {}

    public static function fromModel(ProductMedia $media): self
    {
        return new self(
            id: $media->id,
            productId: $media->product_id,
            variantId: $media->variant_id,
            filePath: $media->file_path,
            fileType: MediaTypeEnum::from($media->file_type),
            mimeType: $media->mime_type,
            fileSize: $media->file_size,
            altText: $media->alt_text,
            isPrimary: $media->is_primary,
            sortOrder: $media->sort_order,
        );
    }
}
