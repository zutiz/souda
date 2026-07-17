<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Database\Factories;

use App\Modules\Inventory\Models\InventoryBin;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryBinFactory extends Factory
{
    protected $model = InventoryBin::class;

    public function definition(): array
    {
        return [
            'warehouse_id' => WarehouseFactory::new(),
            'code' => $this->faker->unique()->regexify('[A-Z]{2}-\d{3}'),
            'zone' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'is_pickable' => true,
        ];
    }

    public function unpickable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pickable' => false,
        ]);
    }
}
