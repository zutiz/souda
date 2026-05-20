<?php

declare(strict_types=1);

namespace App\Modules\Product\Contracts;

use App\Modules\Product\DTOs\ProductSearchCriteria;
use App\Modules\Product\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductResolver
{
    public function findById(string $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    public function findBySku(string $sku): ?Product;

    public function findByBarcode(string $barcode): ?Product;

    public function findActive(string $id): ?Product;

    public function search(ProductSearchCriteria $criteria): LengthAwarePaginator;
}
