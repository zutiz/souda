<?php

declare(strict_types=1);

namespace App\Modules\Product\Observers;

use App\Modules\Product\Events\VariantCreated;
use App\Modules\Product\Events\VariantDeleted;
use App\Modules\Product\Events\VariantUpdated;
use App\Modules\Product\Models\Variant;

class VariantObserver
{
    public function created(Variant $variant): void
    {
        event(VariantCreated::fromModel($variant));
    }

    public function updated(Variant $variant): void
    {
        if ($variant->wasChanged()) {
            event(VariantUpdated::fromModel($variant));
        }
    }

    public function deleted(Variant $variant): void
    {
        event(new VariantDeleted(
            variantId: $variant->id,
            productId: $variant->product_id,
        ));
    }
}
