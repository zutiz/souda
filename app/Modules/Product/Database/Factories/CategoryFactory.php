<?php

declare(strict_types=1);

namespace App\Modules\Product\Database\Factories;

use App\Modules\Product\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'slug' => $this->faker->unique()->slug(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function withParent(): self
    {
        return $this->state(function () {
            return [
                'parent_id' => Category::factory(),
            ];
        });
    }

    public function inactive(): self
    {
        return $this->state(['is_active' => false]);
    }
}
