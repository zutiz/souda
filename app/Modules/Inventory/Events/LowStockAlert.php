<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use Illuminate\Foundation\Events\Dispatchable;

class LowStockAlert
{
    use Dispatchable;

    public function __construct(
        public readonly string $productId,
        public readonly string $warehouseId,
        public readonly int $currentQuantity,
        public readonly int $threshold,
    ) {}
}
