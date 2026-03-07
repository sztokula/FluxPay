<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_name' => 'payment_intent.created',
            'aggregate_type' => 'payment_intent',
            'aggregate_id' => 1,
            'payload' => ['example' => true],
            'happened_at' => now(),
        ];
    }
}
