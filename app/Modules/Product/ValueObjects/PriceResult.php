<?php

declare(strict_types=1);

namespace App\Modules\Product\ValueObjects;

use App\Modules\Product\Models\PricingRule;

readonly class PriceResult
{
    public function __construct(
        public int $basePrice,
        public int $finalPrice,
        public int $discountAmount,
        public ?PricingRule $appliedRule = null,
        public bool $taxInclusive = false,
    ) {}

    public function toArray(): array
    {
        return [
            'base_price' => $this->basePrice,
            'final_price' => $this->finalPrice,
            'discount_amount' => $this->discountAmount,
            'tax_inclusive' => $this->taxInclusive,
        ];
    }
}
