<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Database\Factories;

use App\Modules\Inventory\Models\DemandForecast;
use Illuminate\Database\Eloquent\Factories\Factory;

class DemandForecastFactory extends Factory
{
    protected $model = DemandForecast::class;

    public function definition(): array
    {
        $forecastQty = $this->faker->numberBetween(5, 500);
        $margin = (int) round($forecastQty * 0.2);

        return [
            'product_id' => (string) str()->ulid(),
            'warehouse_id' => 1,
            'forecast_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'forecast_quantity' => $forecastQty,
            'confidence_lower' => max(0, $forecastQty - $margin),
            'confidence_upper' => $forecastQty + $margin,
            'model_used' => 'moving_average',
            'period_start' => $this->faker->dateTimeBetween('-90 days', '-60 days'),
            'period_end' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'actual_quantity' => null,
            'accuracy_score' => null,
            'metadata' => ['window_days' => 30],
        ];
    }

    public function movingAverage(): static
    {
        return $this->state(fn (array $attributes) => [
            'model_used' => 'moving_average',
            'metadata' => ['window_days' => 30],
        ]);
    }

    public function seasonal(): static
    {
        return $this->state(fn (array $attributes) => [
            'model_used' => 'seasonal',
            'metadata' => ['period_months' => 12],
        ]);
    }

    public function resolved(): static
    {
        $forecastQty = $this->faker->numberBetween(5, 500);

        return $this->state(fn (array $attributes) => [
            'forecast_date' => $this->faker->dateTimeBetween('-90 days', '-1 day'),
            'actual_quantity' => $this->faker->numberBetween(5, 500),
            'accuracy_score' => $this->faker->randomFloat(2, 60, 99),
        ]);
    }

    public function accurate(): static
    {
        $qty = 100;

        return $this->state(fn (array $attributes) => [
            'forecast_quantity' => $qty,
            'actual_quantity' => $qty,
            'accuracy_score' => 100.0,
        ]);
    }
}
