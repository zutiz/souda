<?php

declare(strict_types=1);

namespace App\Modules\Product\Database\Factories;

use App\Modules\Product\Models\WarehouseStock;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseStockFactory extends Factory
{
    protected $model = WarehouseStock::class;

    public function definition(): array
    {
        return [
            'quantity' => $this->faker->numberBetween(10, 1000),
            'reserved_quantity' => 0,
            'reorder_level' => 5,
        ];
    }
}
