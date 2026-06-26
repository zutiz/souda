<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\Contracts\SKUGenerator;
use App\Modules\Product\DTOs\VariantDTO;
use App\Modules\Product\Events\VariantCreated;
use App\Modules\Product\Events\VariantDeleted;
use App\Modules\Product\Events\VariantUpdated;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Variant;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Collection;

class VariantService
{
    public function __construct(
        protected Dispatcher $events,
        protected SKUGenerator $skuGenerator,
    ) {}

    public function createVariant(VariantDTO $dto): Variant
    {
        $variant = Variant::query()->create([
            'product_id' => $dto->productId,
            'sku' => $dto->sku,
            'barcode' => $dto->barcode,
            'barcode_type' => $dto->barcodeType?->value,
            'name' => $dto->name,
            'price' => $dto->price,
            'compare_at_price' => $dto->compareAtPrice,
            'cost_price' => $dto->costPrice,
            'track_inventory' => $dto->trackInventory,
            'low_stock_threshold' => $dto->lowStockThreshold,
            'weight' => $dto->dimensions['weight'] ?? null,
            'length' => $dto->dimensions['length'] ?? null,
            'width' => $dto->dimensions['width'] ?? null,
            'height' => $dto->dimensions['height'] ?? null,
            'is_default' => $dto->isDefault,
            'sort_order' => $dto->sortOrder,
        ]);

        if (! empty($dto->attributeValueIds)) {
            $variant->attributeValues()->sync($dto->attributeValueIds);
        }

        $this->events->dispatch(VariantCreated::fromModel($variant));

        return $variant;
    }

    public function updateVariant(Variant $variant, VariantDTO $dto): Variant
    {
        $variant->update([
            'sku' => $dto->sku,
            'barcode' => $dto->barcode,
            'barcode_type' => $dto->barcodeType?->value,
            'name' => $dto->name,
            'price' => $dto->price,
            'compare_at_price' => $dto->compareAtPrice,
            'cost_price' => $dto->costPrice,
            'track_inventory' => $dto->trackInventory,
            'low_stock_threshold' => $dto->lowStockThreshold,
            'weight' => $dto->dimensions['weight'] ?? null,
            'length' => $dto->dimensions['length'] ?? null,
            'width' => $dto->dimensions['width'] ?? null,
            'height' => $dto->dimensions['height'] ?? null,
            'is_default' => $dto->isDefault,
            'sort_order' => $dto->sortOrder,
        ]);

        $variant->attributeValues()->sync($dto->attributeValueIds);

        $this->events->dispatch(VariantUpdated::fromModel($variant));

        return $variant;
    }

    public function deleteVariant(Variant $variant): bool
    {
        $id = $variant->id;
        $productId = $variant->product_id;

        $variant->delete();

        $this->events->dispatch(new VariantDeleted(
            variantId: $id,
            productId: $productId,
        ));

        return true;
    }

    public function generateVariants(Product $product, array $attributeCombinations): Collection
    {
        $variants = new Collection;

        foreach ($attributeCombinations as $combination) {
            $variant = Variant::query()->create([
                'product_id' => $product->id,
                'sku' => $this->skuGenerator->generateForVariant(new Variant(['product_id' => $product->id])),
                'name' => $combination['name'] ?? $product->name,
                'price' => $product->base_price,
                'track_inventory' => $product->track_inventory,
                'low_stock_threshold' => $product->low_stock_threshold,
            ]);

            if (! empty($combination['attribute_value_ids'])) {
                $variant->attributeValues()->sync($combination['attribute_value_ids']);
            }

            $this->events->dispatch(VariantCreated::fromModel($variant));

            $variants->push($variant);
        }

        return $variants;
    }

    public function setDefaultVariant(Product $product, Variant $variant): Product
    {
        $product->variants()->where('is_default', true)->update(['is_default' => false]);

        $variant->update(['is_default' => true]);

        return $product->fresh();
    }
}
