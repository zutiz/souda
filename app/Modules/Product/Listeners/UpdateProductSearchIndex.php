<?php

declare(strict_types=1);

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Jobs\UpdateProductIndexJob;

class UpdateProductSearchIndex
{
    public function handle(ProductUpdated $event): void
    {
        if ($event->product->id !== null) {
            UpdateProductIndexJob::dispatch($event->product->id);
        }
    }
}
