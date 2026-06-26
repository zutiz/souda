<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\Contracts\ProductResolver;
use App\Modules\Product\Models\Product;
use App\Modules\Product\ValueObjects\ProductSearchCriteria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProductResolver implements ProductResolver
{
    public function findById(string $id): ?Product
    {
        return Product::query()->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::query()->where('slug', $slug)->first();
    }

    public function findBySku(string $sku): ?Product
    {
        return Product::query()->where('sku', $sku)->first();
    }

    public function findByBarcode(string $barcode): ?Product
    {
        return Product::query()->where('barcode', $barcode)->first();
    }

    public function findActive(string $id): ?Product
    {
        return Product::query()
            ->where('id', $id)
            ->where('status', 'active')
            ->first();
    }

    public function search(ProductSearchCriteria $criteria): LengthAwarePaginator
    {
        return Product::search($criteria->search ?? '')
            ->when($criteria->categoryId, fn ($q) => $q->where('category_id', $criteria->categoryId))
            ->when($criteria->brandId, fn ($q) => $q->where('brand_id', $criteria->brandId))
            ->when($criteria->minPrice, fn ($q) => $q->where('base_price', '>=', $criteria->minPrice))
            ->when($criteria->maxPrice, fn ($q) => $q->where('base_price', '<=', $criteria->maxPrice))
            ->paginate($criteria->perPage);
    }
}
