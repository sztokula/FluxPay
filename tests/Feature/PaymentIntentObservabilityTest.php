<?php

use App\Models\Customer;
use App\Models\PaymentIntent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('stores event log when payment intent is confirmed', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $customer = Customer::factory()->for($user)->create();
    $paymentIntent = PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
        'status' => \App\Enums\PaymentIntentStatus::RequiresConfirmation,
    ]);

    $this->postJson("/api/payment-intents/{$paymentIntent->id}/confirm", [
        'card_number' => '4242424242424242',
    ])->assertSuccessful();

    $this->assertDatabaseHas('event_logs', [
        'user_id' => $user->id,
        'event_name' => 'payment_intent.confirmed',
        'aggregate_type' => 'payment_intent',
        'aggregate_id' => $paymentIntent->id,
    ]);
});

it('stores event log when payment intent is canceled', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $customer = Customer::factory()->for($user)->create();
    $paymentIntent = PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
        'status' => \App\Enums\PaymentIntentStatus::RequiresConfirmation,
    ]);

    $this->postJson("/api/payment-intents/{$paymentIntent->id}/cancel")
        ->assertSuccessful();

    $this->assertDatabaseHas('event_logs', [
        'user_id' => $user->id,
        'event_name' => 'payment_intent.canceled',
        'aggregate_type' => 'payment_intent',
        'aggregate_id' => $paymentIntent->id,
    ]);
});
