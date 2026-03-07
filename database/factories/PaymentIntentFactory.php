<?php

namespace Database\Factories;

use App\Enums\PaymentIntentStatus;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentIntentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'amount' => fake()->numberBetween(1000, 10000),
            'currency' => 'USD',
            'status' => PaymentIntentStatus::RequiresConfirmation,
            'payment_method' => null,
            'retry_count' => 0,
        ];
    }
}
