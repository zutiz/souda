<?php

declare(strict_types=1);

namespace App\Modules\Product\Enums;

enum PricingRuleScopeEnum: string
{
    case Product = 'product';
    case Category = 'category';
    case Brand = 'brand';
    case All = 'all';

    public function label(): string
    {
        return match ($this) {
            self::Product => 'Product',
            self::Category => 'Category',
            self::Brand => 'Brand',
            self::All => 'All Products',
        };
    }
}
