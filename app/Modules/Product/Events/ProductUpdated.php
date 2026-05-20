<?php

declare(strict_types=1);

namespace App\Modules\Product\Events;

use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\Models\Product;
use Carbon\CarbonImmutable;

readonly class ProductUpdated
{
    public function __construct(
        public ProductDTO $product,
        public array $changedAttributes,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            product: ProductDTO::fromModel($product),
            changedAttributes: $product->getChanges(),
        );
    }
}
