<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

readonly class ProductWithStockDTO
{
    public function __construct(
        public ProductDTO $product,
        public int $totalQuantity,
        public int $totalReserved,
        public int $totalAvailable,
        public array $warehouseBreakdown,
    ) {}
}
