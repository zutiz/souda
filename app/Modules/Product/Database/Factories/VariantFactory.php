<?php

declare(strict_types=1);

namespace App\Modules\Product\Database\Factories;

use App\Modules\Product\Models\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;

class VariantFactory extends Factory
{
    protected $model = Variant::class;

    public function definition(): array
    {
        return [
            'sku' => $this->faker->unique()->regexify('[A-Z0-9]{10}'),
            'name' => $this->faker->words(3, true),
            'price' => $this->faker->numberBetween(100, 50000),
            'track_inventory' => true,
        ];
    }
}
