<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Database\Factories;

use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryBatchFactory extends Factory
{
    protected $model = InventoryBatch::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'warehouse_id' => WarehouseFactory::new(),
            'batch_number' => 'BAT-'.$this->faker->unique()->randomNumber(6),
            'initial_quantity' => 100,
            'remaining_quantity' => 100,
            'unit_cost' => $this->faker->numberBetween(100, 10000),
            'status' => 'active',
        ];
    }

    public function expiring(int $withinDays = 7): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => now()->addDays($withinDays),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => now()->subDay(),
        ]);
    }

    public function depleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'remaining_quantity' => 0,
            'status' => 'depleted',
        ]);
    }

    public function quarantined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'quarantined',
        ]);
    }
}
