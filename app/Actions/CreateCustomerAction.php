<?php

namespace App\Actions;

use App\Models\Customer;

class CreateCustomerAction
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function execute(array $payload): Customer
    {
        return Customer::query()->create([
            'user_id' => $payload['user_id'] ?? null,
            'email' => $payload['email'],
            'name' => $payload['name'],
            'default_payment_method' => $payload['default_payment_method'] ?? null,
            'metadata' => $payload['metadata'] ?? null,
        ]);
    }
}
