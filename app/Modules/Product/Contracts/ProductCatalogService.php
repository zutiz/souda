<?php

declare(strict_types=1);

namespace App\Modules\Product\Contracts;

use App\Modules\Product\DTOs\PriceResult;
use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\DTOs\TaxResult;
use App\Modules\Product\DTOs\VariantDTO;
use Illuminate\Database\Eloquent\Collection;

interface ProductCatalogService
{
    public function getProduct(int $id): ?ProductDTO;

    public function getVariant(int $id): ?VariantDTO;

    public function getProductBySku(string $sku): ?ProductDTO;

    public function getProductsByIds(array $ids): Collection;

    public function calculateProductTax(int $productId, ?int $variantId = null, ?array $location = null): TaxResult;

    public function calculateProductPrice(int $productId, ?int $variantId = null, ?int $quantity = null): PriceResult;
}
