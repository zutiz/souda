<?php

declare(strict_types=1);

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Jobs\IndexProductJob;

class IndexProductForSearch
{
    public function handle(ProductCreated $event): void
    {
        if ($event->product->id !== null) {
            IndexProductJob::dispatch($event->product->id);
        }
    }
}
