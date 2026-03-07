<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WebhookEndpointFactory extends Factory
{
    public function definition(): array
    {
        return [
            'url' => fake()->url(),
            'secret' => bin2hex(random_bytes(16)),
            'events' => ['payment_intent.succeeded'],
            'is_active' => true,
        ];
    }
}
