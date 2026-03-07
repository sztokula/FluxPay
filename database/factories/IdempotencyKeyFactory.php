<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IdempotencyKeyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'idempotency_key' => fake()->uuid(),
            'route' => 'api/payment-intents',
            'method' => 'POST',
            'request_hash' => hash('sha256', fake()->uuid()),
            'response_code' => 201,
            'response_body' => ['id' => 1],
        ];
    }
}
