<?php

declare(strict_types=1);

namespace App\Modules\Product\Traits;

use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Variant;
use Illuminate\Database\Eloquent\Model;

/** @mixin Model */
trait HasProductStock
{
    public function getTotalStock(): int
    {
        if ($this instanceof Product) {
            return $this->total_quantity ?? 0;
        }

        return (int) $this->warehouseStock()->sum('quantity');
    }

    public function getAvailableStock(): int
    {
        if ($this instanceof Product) {
            return ($this->total_quantity ?? 0) - ($this->total_reserved ?? 0);
        }

        return (int) $this->warehouseStock()->sum('available_quantity');
    }

    public function getReservedStock(): int
    {
        if ($this instanceof Product) {
            return $this->total_reserved ?? 0;
        }

        return (int) $this->warehouseStock()->sum('reserved_quantity');
    }

    public function isInStock(): bool
    {
        return $this->getAvailableStock() > 0;
    }

    public function isLowStock(): bool
    {
        $threshold = $this instanceof Variant
            ? ($this->low_stock_threshold ?? 5)
            : ($this->low_stock_threshold ?? 5);

        return $this->getAvailableStock() <= $threshold;
    }

    public function refreshStockTotals(): void
    {
        if ($this instanceof Product) {
            $totals = $this->warehouseStock()
                ->selectRaw('COALESCE(SUM(quantity), 0) as total_qty')
                ->selectRaw('COALESCE(SUM(reserved_quantity), 0) as total_res')
                ->selectRaw('COUNT(DISTINCT warehouse_id) as wh_count')
                ->first();

            $this->updateQuietly([
                'total_quantity' => $totals->total_qty,
                'total_reserved' => $totals->total_res,
                'warehouse_count' => $totals->wh_count,
            ]);
        }
    }
}
