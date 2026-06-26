<?php

declare(strict_types=1);

namespace App\Modules\Product\Database\Factories;

use App\Modules\Product\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'slug' => $this->faker->unique()->slug(),
            'is_active' => true,
        ];
    }
}
