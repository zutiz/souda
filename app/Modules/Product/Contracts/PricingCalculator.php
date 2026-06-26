<?php

declare(strict_types=1);

namespace App\Modules\Product\Contracts;

use App\Modules\Product\DTOs\PriceResult;
use App\Modules\Product\DTOs\TaxResult;
use Illuminate\Database\Eloquent\Collection;

interface PricingCalculator
{
    public function calculatePrice(string $productId, ?string $variantId = null, ?array $context = null): PriceResult;

    public function calculateTax(int $priceAmount, ?int $taxCategoryId = null, ?array $location = null): TaxResult;

    public function getApplicableRules(string $productId, ?string $variantId = null): Collection;
}
