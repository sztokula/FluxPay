<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'amount' => fake()->numberBetween(1000, 10000),
            'currency' => 'USD',
            'interval' => 'month',
            'interval_count' => 1,
            'trial_days' => 0,
            'is_active' => true,
        ];
    }
}
