<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\PaymentIntent;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'payment_intent_id' => PaymentIntent::factory(),
            'product_id' => Product::factory(),
            'amount' => fake()->numberBetween(1000, 9000),
            'currency' => 'USD',
            'status' => 'paid',
            'fulfilled_at' => now(),
            'metadata' => ['source' => 'storefront'],
        ];
    }
}
