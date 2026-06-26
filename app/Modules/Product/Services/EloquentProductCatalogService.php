<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\Contracts\ProductCatalogService;
use App\Modules\Product\DTOs\PriceResult;
use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\DTOs\TaxResult;
use App\Modules\Product\DTOs\VariantDTO;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Variant;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductCatalogService implements ProductCatalogService
{
    public function __construct(
        protected EloquentPricingCalculator $pricingCalculator,
        protected TaxService $taxService,
    ) {}

    public function getProduct(int $id): ?ProductDTO
    {
        $product = Product::query()->find($id);

        return $product !== null ? ProductDTO::fromModel($product) : null;
    }

    public function getVariant(int $id): ?VariantDTO
    {
        $variant = Variant::query()->find($id);

        return $variant !== null ? VariantDTO::fromModel($variant) : null;
    }

    public function getProductBySku(string $sku): ?ProductDTO
    {
        $product = Product::query()->where('sku', $sku)->first();

        return $product !== null ? ProductDTO::fromModel($product) : null;
    }

    public function getProductsByIds(array $ids): Collection
    {
        return Product::query()->whereIn('id', $ids)->get();
    }

    public function calculateProductTax(int $productId, ?int $variantId = null, ?array $location = null): TaxResult
    {
        $product = Product::query()->findOrFail($productId);

        return $this->taxService->calculateTaxForProduct(
            priceAmount: $product->base_price,
            product: $product,
            location: $location,
        );
    }

    public function calculateProductPrice(int $productId, ?int $variantId = null, ?int $quantity = null): PriceResult
    {
        return $this->pricingCalculator->calculatePrice(
            productId: (string) $productId,
            variantId: $variantId !== null ? (string) $variantId : null,
            context: $quantity !== null ? ['quantity' => $quantity] : null,
        );
    }
}
