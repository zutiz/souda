<?php

namespace Database\Factories;

use App\Modules\Billing\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'monthly_price' => $this->faker->randomElement([999, 1999, 2999, 4999]),
            'yearly_price' => $this->faker->randomElement([9990, 19990, 29990, 49990]),
            'currency' => 'BDT',
            'features' => ['feature_a', 'feature_b', 'feature_c'],
            'limits' => ['users' => 5, 'storage' => 100],
            'is_active' => true,
            'display_order' => 0,
            'popular' => false,
            'trial_enabled' => true,
            'trial_days' => 14,
            'trial_without_card' => true,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Free',
            'slug' => 'free',
            'monthly_price' => 0,
            'features' => ['basic_feature'],
            'limits' => ['users' => 1],
            'popular' => false,
        ]);
    }

    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'popular' => true,
        ]);
    }
}
