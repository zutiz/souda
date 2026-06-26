<?php

declare(strict_types=1);

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Contracts\SKUGenerator;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Models\Product;

class GenerateProductSKU
{
    public function __construct(
        protected SKUGenerator $skuGenerator,
    ) {}

    public function handle(ProductCreated $event): void
    {
        $product = Product::query()->find($event->product->id);

        if ($product !== null && empty($product->sku)) {
            $sku = $this->skuGenerator->generateForProduct($product);
            $product->updateQuietly(['sku' => $sku]);
        }
    }
}
