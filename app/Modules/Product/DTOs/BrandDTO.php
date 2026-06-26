<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Models\Brand;

readonly class BrandDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public ?string $logoPath,
        public ?string $websiteUrl,
        public bool $isActive,
    ) {}

    public static function fromModel(Brand $brand): self
    {
        return new self(
            id: $brand->id,
            name: $brand->name,
            slug: $brand->slug,
            description: $brand->description,
            logoPath: $brand->logo_path,
            websiteUrl: $brand->website_url,
            isActive: $brand->is_active,
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: null,
            name: $data['name'],
            slug: $data['slug'] ?? '',
            description: $data['description'] ?? null,
            logoPath: $data['logo_path'] ?? null,
            websiteUrl: $data['website_url'] ?? null,
            isActive: $data['is_active'] ?? true,
        );
    }
}
