<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\Contracts\SKUGenerator;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Variant;
use Illuminate\Support\Str;

class DefaultSKUGenerator implements SKUGenerator
{
    public function generateForProduct(Product $product): string
    {
        $prefix = strtoupper(Str::substr(Str::slug($product->name, ''), 0, 3));
        $uniqueId = strtoupper(Str::substr(md5($product->id.$product->name), 0, 6));

        $sku = "{$prefix}-{$uniqueId}";

        while (! $this->isUnique($sku, $product->id)) {
            $uniqueId = strtoupper(Str::substr(md5(uniqid()), 0, 6));
            $sku = "{$prefix}-{$uniqueId}";
        }

        return $sku;
    }

    public function generateForVariant(Variant $variant): string
    {
        $prefix = 'VAR';
        $uniqueId = strtoupper(Str::substr(md5($variant->id.uniqid()), 0, 6));

        $sku = "{$prefix}-{$uniqueId}";

        while (! $this->isUnique($sku)) {
            $uniqueId = strtoupper(Str::substr(md5(uniqid()), 0, 6));
            $sku = "{$prefix}-{$uniqueId}";
        }

        return $sku;
    }

    public function isUnique(string $sku, ?string $excludeProductId = null): bool
    {
        $productExists = Product::query()
            ->where('sku', $sku)
            ->when($excludeProductId, fn ($q) => $q->where('id', '!=', $excludeProductId))
            ->exists();

        $variantExists = Variant::query()
            ->where('sku', $sku)
            ->when($excludeProductId, fn ($q) => $q->where('product_id', '!=', $excludeProductId))
            ->exists();

        return ! $productExists && ! $variantExists;
    }
}
