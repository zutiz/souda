<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\Contracts\SKUGenerator;
use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\DTOs\ProductSummaryDTO;
use App\Modules\Product\DTOs\ProductWithStockDTO;
use App\Modules\Product\Enums\ProductStatusEnum;
use App\Modules\Product\Events\ProductArchived;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Events\ProductPublished;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Models\Product;
use App\Modules\Product\ValueObjects\ProductSearchCriteria;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    public function __construct(
        protected Dispatcher $events,
        protected SKUGenerator $skuGenerator,
    ) {}

    public function createProduct(ProductDTO $dto): Product
    {
        $product = Product::query()->create([
            'category_id' => $dto->categoryId,
            'brand_id' => $dto->brandId,
            'tax_category_id' => $dto->taxCategoryId,
            'name' => $dto->name,
            'slug' => $dto->slug,
            'sku' => $dto->sku,
            'barcode' => $dto->barcode,
            'barcode_type' => $dto->barcodeType,
            'description' => $dto->description,
            'short_description' => $dto->shortDescription,
            'type' => $dto->type->value,
            'status' => $dto->status->value,
            'base_price' => $dto->basePrice,
            'compare_at_price' => $dto->compareAtPrice,
            'cost_price' => $dto->costPrice,
            'tax_inclusive' => $dto->taxInclusive,
            'track_inventory' => $dto->trackInventory,
            'low_stock_threshold' => $dto->lowStockThreshold,
            'weight' => $dto->dimensions['weight'] ?? null,
            'length' => $dto->dimensions['length'] ?? null,
            'width' => $dto->dimensions['width'] ?? null,
            'height' => $dto->dimensions['height'] ?? null,
            'metadata' => null,
            'published_at' => $dto->publishedAt,
        ]);

        if ($dto->categoryIds !== null) {
            $product->categories()->sync($dto->categoryIds);
        }

        if ($dto->attributeValues !== null) {
            $this->syncAttributeValues($product, $dto->attributeValues);
        }

        $this->events->dispatch(ProductCreated::fromModel($product));

        return $product;
    }

    public function updateProduct(Product $product, ProductDTO $dto): Product
    {
        $product->update([
            'category_id' => $dto->categoryId,
            'brand_id' => $dto->brandId,
            'tax_category_id' => $dto->taxCategoryId,
            'name' => $dto->name,
            'slug' => $dto->slug,
            'sku' => $dto->sku,
            'barcode' => $dto->barcode,
            'barcode_type' => $dto->barcodeType,
            'description' => $dto->description,
            'short_description' => $dto->shortDescription,
            'type' => $dto->type->value,
            'status' => $dto->status->value,
            'base_price' => $dto->basePrice,
            'compare_at_price' => $dto->compareAtPrice,
            'cost_price' => $dto->costPrice,
            'tax_inclusive' => $dto->taxInclusive,
            'track_inventory' => $dto->trackInventory,
            'low_stock_threshold' => $dto->lowStockThreshold,
            'weight' => $dto->dimensions['weight'] ?? null,
            'length' => $dto->dimensions['length'] ?? null,
            'width' => $dto->dimensions['width'] ?? null,
            'height' => $dto->dimensions['height'] ?? null,
            'published_at' => $dto->publishedAt,
        ]);

        if ($dto->categoryIds !== null) {
            $product->categories()->sync($dto->categoryIds);
        }

        $this->events->dispatch(ProductUpdated::fromModel($product));

        return $product->fresh();
    }

    public function deleteProduct(Product $product): bool
    {
        $id = $product->id;
        $sku = $product->sku;

        $product->delete();

        $this->events->dispatch(new ProductDeleted(
            productId: $id,
            sku: $sku,
        ));

        return true;
    }

    public function archiveProduct(Product $product): Product
    {
        $product->update(['status' => ProductStatusEnum::Archived]);

        $this->events->dispatch(ProductArchived::fromModel($product));

        return $product;
    }

    public function restoreProduct(Product $product): Product
    {
        $product->update(['status' => ProductStatusEnum::Draft]);

        return $product;
    }

    public function publishProduct(Product $product): Product
    {
        $product->update([
            'status' => ProductStatusEnum::Active,
            'published_at' => now(),
        ]);

        $this->events->dispatch(ProductPublished::fromModel($product));

        return $product;
    }

    public function duplicateProduct(Product $product): Product
    {
        $clone = $product->replicate()->fill([
            'name' => $product->name.' (Copy)',
            'slug' => $product->slug.'-copy',
            'status' => ProductStatusEnum::Draft,
            'published_at' => null,
        ]);

        $clone->push();

        foreach ($product->media as $media) {
            $clone->media()->create($media->replicate()->toArray());
        }

        foreach ($product->variants as $variant) {
            $clone->variants()->create($variant->replicate()->toArray());
        }

        $categoryIds = $product->categories()->pluck('categories.id')->toArray();
        $clone->categories()->sync($categoryIds);

        $this->events->dispatch(ProductCreated::fromModel($clone));

        return $clone;
    }

    public function listProducts(ProductSearchCriteria $criteria): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['category', 'brand', 'primaryMedia']);

        if ($criteria->search !== null) {
            $query->where(function (Builder $q) use ($criteria) {
                $q->where('name', 'like', "%{$criteria->search}%")
                    ->orWhere('sku', 'like', "%{$criteria->search}%");
            });
        }

        if ($criteria->categoryId !== null) {
            $query->where('category_id', $criteria->categoryId);
        }

        if ($criteria->brandId !== null) {
            $query->where('brand_id', $criteria->brandId);
        }

        if ($criteria->status !== null) {
            $query->where('status', $criteria->status->value);
        }

        if ($criteria->type !== null) {
            $query->where('type', $criteria->type->value);
        }

        if ($criteria->minPrice !== null) {
            $query->where('base_price', '>=', $criteria->minPrice);
        }

        if ($criteria->maxPrice !== null) {
            $query->where('base_price', '<=', $criteria->maxPrice);
        }

        if ($criteria->sortBy !== null) {
            $query->orderBy($criteria->sortBy, $criteria->sortDirection);
        } else {
            $query->latest();
        }

        return $query->paginate($criteria->perPage, ['*'], 'page', $criteria->page);
    }

    public function getProductSummary(Product $product): ProductSummaryDTO
    {
        $product->loadMissing(['category', 'brand', 'media']);

        return ProductSummaryDTO::fromModel($product);
    }

    public function getProductWithStock(Product $product): ProductWithStockDTO
    {
        $product->loadMissing(['warehouseStock.warehouse']);

        $warehouseBreakdown = $product->warehouseStock->map(fn ($ws) => [
            'warehouse_id' => $ws->warehouse_id,
            'warehouse_name' => $ws->warehouse->name,
            'quantity' => $ws->quantity,
            'reserved_quantity' => $ws->reserved_quantity,
            'available_quantity' => $ws->getAvailableQuantity(),
        ])->toArray();

        return new ProductWithStockDTO(
            product: ProductDTO::fromModel($product),
            totalQuantity: $product->total_quantity,
            totalReserved: $product->total_reserved,
            totalAvailable: $product->total_available,
            warehouseBreakdown: $warehouseBreakdown,
        );
    }

    protected function syncAttributeValues(Product $product, array $attributeValues): void
    {
        $product->attributeValues()->delete();

        foreach ($attributeValues as $av) {
            $product->attributeValues()->create([
                'attribute_id' => $av['attribute_id'],
                'attribute_value_id' => $av['attribute_value_id'] ?? null,
            ]);
        }
    }
}
