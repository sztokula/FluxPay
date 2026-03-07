<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'amount_due' => fake()->numberBetween(1000, 10000),
            'amount_paid' => 0,
            'currency' => 'USD',
            'status' => InvoiceStatus::Open,
            'due_at' => now()->addDay(),
        ];
    }
}
