<?php

use App\Models\Customer;
use App\Models\EventLog;
use App\Models\Price;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns cached response for the same idempotency key', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $customer = Customer::factory()->for($user)->create();

    $headers = ['Idempotency-Key' => 'abc-123'];

    $first = $this->withHeaders($headers)->postJson('/api/payment-intents', [
        'customer_id' => $customer->id,
        'amount' => 1900,
        'currency' => 'USD',
    ]);

    $second = $this->withHeaders($headers)->postJson('/api/payment-intents', [
        'customer_id' => $customer->id,
        'amount' => 1900,
        'currency' => 'USD',
    ]);

    $first->assertCreated();
    $second->assertCreated();

    expect($first->json('data.id'))->toBe($second->json('data.id'));

    $this->assertDatabaseHas('event_logs', [
        'user_id' => $user->id,
        'event_name' => 'idempotency.cache_hit',
        'aggregate_type' => 'idempotency',
    ]);
});

it('rejects different payload with reused idempotency key', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $customer = Customer::factory()->for($user)->create();

    $headers = ['Idempotency-Key' => 'same-key'];

    $this->withHeaders($headers)->postJson('/api/payment-intents', [
        'customer_id' => $customer->id,
        'amount' => 1900,
        'currency' => 'USD',
    ])->assertCreated();

    $this->withHeaders($headers)->postJson('/api/payment-intents', [
        'customer_id' => $customer->id,
        'amount' => 2900,
        'currency' => 'USD',
    ])->assertStatus(409);

    $this->assertDatabaseHas('event_logs', [
        'user_id' => $user->id,
        'event_name' => 'idempotency.conflict',
        'aggregate_type' => 'idempotency',
    ]);
});

it('returns cached no-content response for delete with the same idempotency key', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $customer = Customer::factory()->for($user)->create();
    $price = Price::factory()->create();
    $subscription = Subscription::factory()->create([
        'customer_id' => $customer->id,
        'price_id' => $price->id,
    ]);

    $headers = ['Idempotency-Key' => 'cancel-subscription-key'];

    $first = $this->withHeaders($headers)->deleteJson("/api/subscriptions/{$subscription->id}");
    $second = $this->withHeaders($headers)->deleteJson("/api/subscriptions/{$subscription->id}");

    $first->assertNoContent();
    $second->assertNoContent();

    $eventCount = EventLog::query()
        ->where('event_name', 'subscription.canceled')
        ->where('aggregate_type', 'subscription')
        ->where('aggregate_id', $subscription->id)
        ->count();

    expect($eventCount)->toBe(1);
});

it('returns cached response for repeated hard delete with the same key', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $customer = Customer::factory()->for($user)->create();
    $headers = ['Idempotency-Key' => 'hard-delete-customer-key'];

    $first = $this->withHeaders($headers)->deleteJson("/api/customers/{$customer->id}");
    $second = $this->withHeaders($headers)->deleteJson("/api/customers/{$customer->id}");

    $first->assertNoContent();
    $second->assertNoContent();
});
