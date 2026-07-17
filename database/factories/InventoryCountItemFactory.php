<?php

namespace Database\Factories;

use App\Modules\Inventory\Models\InventoryCount;
use App\Modules\Inventory\Models\InventoryCountItem;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryCountItemFactory extends Factory
{
    protected $model = InventoryCountItem::class;

    public function definition(): array
    {
        $expected = fake()->numberBetween(0, 100);

        return [
            'count_id' => InventoryCount::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,
            'bin_id' => null,
            'expected_quantity' => $expected,
            'physical_quantity' => $expected,
            'discrepancy' => null,
            'unit_cost' => fake()->numberBetween(100, 5000),
            'status' => 'pending',
        ];
    }

    public function counted(): static
    {
        $physical = fake()->numberBetween(0, 100);

        return $this->state([
            'physical_quantity' => $physical,
            'discrepancy' => $physical - ($this->state['expected_quantity'] ?? 0),
            'status' => 'counted',
        ]);
    }

    public function withDiscrepancy(int $diff = 5): static
    {
        $expected = fake()->numberBetween(10, 100);
        $physical = $expected + $diff;

        return $this->state([
            'expected_quantity' => $expected,
            'physical_quantity' => $physical,
            'discrepancy' => $diff,
            'status' => 'counted',
        ]);
    }
}
