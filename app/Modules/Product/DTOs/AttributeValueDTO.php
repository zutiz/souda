<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Models\AttributeValue;

readonly class AttributeValueDTO
{
    public function __construct(
        public int $id,
        public int $attributeId,
        public string $value,
        public ?string $swatchColor,
        public int $sortOrder,
    ) {}

    public static function fromModel(AttributeValue $value): self
    {
        return new self(
            id: $value->id,
            attributeId: $value->attribute_id,
            value: $value->value,
            swatchColor: $value->swatch_color,
            sortOrder: $value->sort_order,
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            attributeId: (int) ($data['attribute_id'] ?? 0),
            value: $data['value'],
            swatchColor: $data['swatch_color'] ?? null,
            sortOrder: (int) ($data['sort_order'] ?? 0),
        );
    }
}
