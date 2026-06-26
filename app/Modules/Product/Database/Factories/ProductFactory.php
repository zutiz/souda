<?php

declare(strict_types=1);

namespace App\Modules\Product\Database\Factories;

use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'slug' => $this->faker->unique()->slug(),
            'type' => 'simple',
            'status' => 'active',
            'base_price' => $this->faker->numberBetween(100, 100000),
            'track_inventory' => true,
            'low_stock_threshold' => 5,
        ];
    }

    public function draft(): self
    {
        return $this->state(['status' => 'draft']);
    }

    public function archived(): self
    {
        return $this->state(['status' => 'archived']);
    }

    public function configurable(): self
    {
        return $this->state(['type' => 'configurable']);
    }

    public function bundle(): self
    {
        return $this->state(['type' => 'bundle']);
    }
}
