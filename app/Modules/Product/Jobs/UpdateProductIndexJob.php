<?php

declare(strict_types=1);

namespace App\Modules\Product\Jobs;

use App\Modules\Product\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateProductIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [10, 30, 60];

    public function __construct(
        public string $productId,
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $product = Product::query()->find($this->productId);

        if ($product !== null) {
            $product->searchable();
        }
    }
}
