<?php

namespace Database\Factories;

use App\Enums\LedgerEntryType;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class LedgerEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'type' => LedgerEntryType::Charge,
            'reference_type' => 'payment_intent',
            'reference_id' => 1,
            'currency' => 'USD',
            'amount' => fake()->numberBetween(1000, 5000),
            'direction' => 'credit',
            'description' => fake()->sentence(),
        ];
    }
}
