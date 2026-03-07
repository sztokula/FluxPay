<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->word()).' Plan',
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
