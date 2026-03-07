<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('applies api rate limiting to authenticated requests', function () {
    config()->set('payment.api_rate_limit_per_minute', 2);

    $user = User::factory()->create();
    Customer::factory()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/customers')->assertSuccessful();
    $this->getJson('/api/customers')->assertSuccessful();
    $this->getJson('/api/customers')->assertStatus(429);
});
