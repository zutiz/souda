<?php

declare(strict_types=1);

namespace App\Modules\Product\Contracts;

use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Variant;

interface SKUGenerator
{
    public function generateForProduct(Product $product): string;

    public function generateForVariant(Variant $variant): string;

    public function isUnique(string $sku, ?string $excludeProductId = null): bool;
}
