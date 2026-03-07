<?php

namespace Database\Factories;

use App\Enums\PayoutStatus;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayoutFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'amount' => fake()->numberBetween(1000, 7000),
            'currency' => 'USD',
            'status' => PayoutStatus::Pending,
        ];
    }
}
