<?php

declare(strict_types=1);

namespace App\Modules\Product\Database\Factories;

use App\Modules\Product\Models\StockReservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockReservationFactory extends Factory
{
    protected $model = StockReservation::class;

    public function definition(): array
    {
        return [
            'quantity' => $this->faker->numberBetween(1, 50),
            'reference_type' => 'order',
            'reference_id' => $this->faker->randomNumber(),
            'expires_at' => now()->addHours(24),
            'status' => 'active',
        ];
    }

    public function active(): self
    {
        return $this->state(['status' => 'active', 'expires_at' => now()->addHours(24)]);
    }

    public function consumed(): self
    {
        return $this->state(['status' => 'consumed']);
    }

    public function expired(): self
    {
        return $this->state(['status' => 'expired', 'expires_at' => now()->subHour()]);
    }

    public function cancelled(): self
    {
        return $this->state(['status' => 'cancelled']);
    }
}
