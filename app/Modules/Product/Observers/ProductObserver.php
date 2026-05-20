<?php

declare(strict_types=1);

namespace App\Modules\Product\Observers;

use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Models\Product;

class ProductObserver
{
    public function created(Product $product): void
    {
        event(ProductCreated::fromModel($product));
    }

    public function updated(Product $product): void
    {
        if ($product->wasChanged()) {
            event(ProductUpdated::fromModel($product));
        }
    }

    public function deleted(Product $product): void
    {
        event(new ProductDeleted(
            productId: $product->id,
            sku: $product->sku,
        ));
    }
}
