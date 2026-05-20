<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\Contracts\PricingCalculator;
use App\Modules\Product\DTOs\PriceResult;
use App\Modules\Product\DTOs\TaxResult;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\TaxCategory;
use App\Modules\Product\Models\Variant;
use Illuminate\Database\Eloquent\Collection;

class EloquentPricingCalculator implements PricingCalculator
{
    public function __construct(
        protected PricingRuleService $pricingRuleService,
        protected TaxService $taxService,
    ) {}

    public function calculatePrice(string $productId, ?string $variantId = null, ?array $context = null): PriceResult
    {
        $product = Product::query()->findOrFail($productId);

        $basePrice = $product->base_price;

        if ($variantId !== null) {
            $variant = Variant::query()->find($variantId);
            $basePrice = $variant?->price ?? $basePrice;
        }

        $rules = $this->pricingRuleService->getActiveRulesForProduct(
            $product,
            $variantId !== null ? Variant::query()->find($variantId) : null,
        );

        return $this->pricingRuleService->applyRules($basePrice, $rules);
    }

    public function calculateTax(int $priceAmount, ?int $taxCategoryId = null, ?array $location = null): TaxResult
    {
        $dummyProduct = new Product;
        $dummyProduct->tax_category_id = $taxCategoryId;

        if ($taxCategoryId !== null) {
            $dummyProduct->setRelation('taxCategory', TaxCategory::query()->find($taxCategoryId));
        }

        return $this->taxService->calculateTaxForProduct($priceAmount, $dummyProduct, $location);
    }

    public function getApplicableRules(string $productId, ?string $variantId = null): Collection
    {
        $product = Product::query()->findOrFail($productId);

        return $this->pricingRuleService->getActiveRulesForProduct(
            $product,
            $variantId !== null ? Variant::query()->find($variantId) : null,
        );
    }
}
