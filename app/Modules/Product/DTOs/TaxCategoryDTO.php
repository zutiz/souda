<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Models\TaxCategory;

readonly class TaxCategoryDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $description,
        public ?array $rates,
    ) {}

    public static function fromModel(TaxCategory $category): self
    {
        return new self(
            id: $category->id,
            name: $category->name,
            description: $category->description,
            rates: $category->relationLoaded('rates')
                ? $category->rates->map(fn ($r) => TaxRateDTO::fromModel($r))->toArray()
                : null,
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: null,
            name: $data['name'],
            description: $data['description'] ?? null,
            rates: null,
        );
    }
}
