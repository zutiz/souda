<?php

declare(strict_types=1);

namespace App\Modules\Product\Rules;

use App\Modules\Product\Models\WarehouseStock;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StockAvailable implements ValidationRule
{
    public function __construct(
        protected int $warehouseId,
        protected ?string $productId,
        protected ?string $variantId,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $quantity = (int) $value;

        $stock = WarehouseStock::query()
            ->where('warehouse_id', $this->warehouseId)
            ->where('product_id', $this->productId)
            ->where('variant_id', $this->variantId)
            ->first();

        if ($stock === null) {
            $fail('Product not found in warehouse.');

            return;
        }

        $available = $stock->quantity - $stock->reserved_quantity;

        if ($quantity > $available) {
            $fail("Insufficient stock available in the selected warehouse. Requested: {$quantity}, Available: {$available}.");
        }
    }
}
