<?php

declare(strict_types=1);

namespace App\Modules\Product\ValueObjects;

use App\Modules\Product\Enums\ProductStatusEnum;
use App\Modules\Product\Enums\ProductTypeEnum;

readonly class ProductSearchCriteria
{
    public function __construct(
        public ?string $search = null,
        public ?int $categoryId = null,
        public ?int $brandId = null,
        public ?ProductStatusEnum $status = null,
        public ?ProductTypeEnum $type = null,
        public ?int $minPrice = null,
        public ?int $maxPrice = null,
        public ?array $attributeFilters = null,
        public ?string $sortBy = null,
        public string $sortDirection = 'asc',
        public int $perPage = 15,
        public int $page = 1,
    ) {}

    public function toQueryParams(): array
    {
        return array_filter([
            'search' => $this->search,
            'category_id' => $this->categoryId,
            'brand_id' => $this->brandId,
            'status' => $this->status?->value,
            'type' => $this->type?->value,
            'min_price' => $this->minPrice,
            'max_price' => $this->maxPrice,
            'attribute_filters' => $this->attributeFilters,
            'sort_by' => $this->sortBy,
            'sort_direction' => $this->sortDirection,
            'per_page' => $this->perPage,
            'page' => $this->page,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            brandId: isset($data['brand_id']) ? (int) $data['brand_id'] : null,
            status: isset($data['status']) ? ProductStatusEnum::tryFrom($data['status']) : null,
            type: isset($data['type']) ? ProductTypeEnum::tryFrom($data['type']) : null,
            minPrice: isset($data['min_price']) ? (int) $data['min_price'] : null,
            maxPrice: isset($data['max_price']) ? (int) $data['max_price'] : null,
            attributeFilters: $data['attribute_filters'] ?? null,
            sortBy: $data['sort_by'] ?? null,
            sortDirection: $data['sort_direction'] ?? 'asc',
            perPage: (int) ($data['per_page'] ?? 15),
            page: (int) ($data['page'] ?? 1),
        );
    }
}
