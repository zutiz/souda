<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Models\TaxRate;

readonly class TaxRateDTO
{
    public function __construct(
        public ?int $id,
        public int $taxCategoryId,
        public string $name,
        public float $rate,
        public ?string $country,
        public ?string $state,
        public ?string $postalCode,
        public bool $isCompound,
        public bool $isActive,
        public int $priority,
    ) {}

    public static function fromModel(TaxRate $rate): self
    {
        return new self(
            id: $rate->id,
            taxCategoryId: $rate->tax_category_id,
            name: $rate->name,
            rate: (float) $rate->rate,
            country: $rate->country,
            state: $rate->state,
            postalCode: $rate->postal_code,
            isCompound: $rate->is_compound,
            isActive: $rate->is_active,
            priority: $rate->priority,
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: null,
            taxCategoryId: (int) $data['tax_category_id'],
            name: $data['name'],
            rate: (float) $data['rate'],
            country: $data['country'] ?? null,
            state: $data['state'] ?? null,
            postalCode: $data['postal_code'] ?? null,
            isCompound: (bool) ($data['is_compound'] ?? false),
            isActive: (bool) ($data['is_active'] ?? true),
            priority: (int) ($data['priority'] ?? 1),
        );
    }
}
