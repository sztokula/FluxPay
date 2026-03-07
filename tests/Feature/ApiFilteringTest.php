<?php

use App\Enums\PaymentIntentStatus;
use App\Models\Customer;
use App\Models\PaymentIntent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('filters payment intents by status and date range', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $customer = Customer::factory()->for($user)->create();

    $oldIntent = PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
        'status' => PaymentIntentStatus::Failed,
        'created_at' => now()->subDays(5),
        'updated_at' => now()->subDays(5),
    ]);

    PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
        'status' => PaymentIntentStatus::Succeeded,
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    $response = $this->getJson('/api/payment-intents?status=failed&from='.now()->subDays(10)->toDateString().'&to='.now()->toDateString());

    $response->assertSuccessful();
    $response->assertJsonCount(1, 'data');
    expect($response->json('data.0.id'))->toBe($oldIntent->id);
});
