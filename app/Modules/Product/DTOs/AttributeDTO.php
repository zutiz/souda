<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Enums\AttributeTypeEnum;
use App\Modules\Product\Models\Attribute;

readonly class AttributeDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $slug,
        public AttributeTypeEnum $frontendType,
        public bool $isFilterable,
        public bool $isRequired,
        public bool $isVariant,
        public int $sortOrder,
        public ?array $validationRules,
        public ?array $values,
    ) {}

    public static function fromModel(Attribute $attribute): self
    {
        return new self(
            id: $attribute->id,
            name: $attribute->name,
            slug: $attribute->slug,
            frontendType: $attribute->frontend_type instanceof AttributeTypeEnum
                ? $attribute->frontend_type
                : AttributeTypeEnum::from($attribute->frontend_type),
            isFilterable: $attribute->is_filterable,
            isRequired: $attribute->is_required,
            isVariant: $attribute->is_variant,
            sortOrder: $attribute->sort_order,
            validationRules: $attribute->validation_rules,
            values: $attribute->relationLoaded('values')
                ? $attribute->values->map(fn ($v) => AttributeValueDTO::fromModel($v))->toArray()
                : null,
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: null,
            name: $data['name'],
            slug: $data['slug'] ?? '',
            frontendType: AttributeTypeEnum::from($data['frontend_type']),
            isFilterable: $data['is_filterable'] ?? false,
            isRequired: $data['is_required'] ?? false,
            isVariant: $data['is_variant'] ?? false,
            sortOrder: (int) ($data['sort_order'] ?? 0),
            validationRules: $data['validation_rules'] ?? null,
            values: null,
        );
    }
}
