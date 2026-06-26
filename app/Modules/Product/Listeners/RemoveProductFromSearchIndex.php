<?php

declare(strict_types=1);

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Jobs\RemoveProductIndexJob;

class RemoveProductFromSearchIndex
{
    public function handle(ProductDeleted $event): void
    {
        RemoveProductIndexJob::dispatch($event->productId);
    }
}
