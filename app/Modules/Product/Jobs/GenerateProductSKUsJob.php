<?php

declare(strict_types=1);

namespace App\Modules\Product\Jobs;

use App\Modules\Product\Contracts\SKUGenerator;
use App\Modules\Product\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateProductSKUsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue = 'default';

    public $tries = 3;

    public function __construct(
        public string $productId,
    ) {}

    public function handle(SKUGenerator $skuGenerator): void
    {
        $product = Product::query()->find($this->productId);

        if ($product === null) {
            return;
        }

        if (empty($product->sku)) {
            $sku = $skuGenerator->generateForProduct($product);
            $product->updateQuietly(['sku' => $sku]);
        }

        foreach ($product->variants as $variant) {
            if (empty($variant->sku)) {
                $sku = $skuGenerator->generateForVariant($variant);
                $variant->updateQuietly(['sku' => $sku]);
            }
        }
    }
}
