<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\DTOs\AttributeDTO;
use App\Modules\Product\Models\Attribute;
use App\Modules\Product\Models\AttributeValue;
use Illuminate\Database\Eloquent\Collection;

class AttributeService
{
    public function createAttribute(AttributeDTO $dto): Attribute
    {
        return Attribute::query()->create([
            'name' => $dto->name,
            'slug' => $dto->slug,
            'frontend_type' => $dto->frontendType->value,
            'is_filterable' => $dto->isFilterable,
            'is_required' => $dto->isRequired,
            'is_variant' => $dto->isVariant,
            'sort_order' => $dto->sortOrder,
            'validation_rules' => $dto->validationRules,
        ]);
    }

    public function updateAttribute(Attribute $attribute, AttributeDTO $dto): Attribute
    {
        $attribute->update([
            'name' => $dto->name,
            'slug' => $dto->slug,
            'frontend_type' => $dto->frontendType->value,
            'is_filterable' => $dto->isFilterable,
            'is_required' => $dto->isRequired,
            'is_variant' => $dto->isVariant,
            'sort_order' => $dto->sortOrder,
            'validation_rules' => $dto->validationRules,
        ]);

        return $attribute;
    }

    public function deleteAttribute(Attribute $attribute): bool
    {
        $attribute->delete();

        return true;
    }

    public function getVariantAttributes(): Collection
    {
        return Attribute::query()->variant()->orderBy('sort_order')->get();
    }

    public function getFilterableAttributes(): Collection
    {
        return Attribute::query()->filterable()->orderBy('sort_order')->get();
    }

    public function addValue(Attribute $attribute, string $value, ?string $swatchColor = null): AttributeValue
    {
        return $attribute->values()->create([
            'value' => $value,
            'swatch_color' => $swatchColor,
        ]);
    }

    public function deleteValue(AttributeValue $value): bool
    {
        $value->delete();

        return true;
    }
}
