<?php

declare(strict_types=1);

namespace App\Modules\Product\Database\Factories;

use App\Modules\Product\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company().' Warehouse',
            'code' => $this->faker->unique()->regexify('[A-Z0-9]{5}'),
            'is_active' => true,
        ];
    }
}
