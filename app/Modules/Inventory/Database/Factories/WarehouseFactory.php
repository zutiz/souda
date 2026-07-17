<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Database\Factories;

use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company().' Warehouse',
            'slug' => $this->faker->unique()->slug(2),
            'type' => 'physical',
            'code' => $this->faker->unique()->regexify('[A-Z0-9]{5}'),
            'is_active' => true,
            'is_default' => false,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
