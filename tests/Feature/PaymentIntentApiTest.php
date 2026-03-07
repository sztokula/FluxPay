<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates payment intent and allows confirmation', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $customer = Customer::factory()->for($user)->create();

    $response = $this->postJson('/api/payment-intents', [
        'customer_id' => $customer->id,
        'amount' => 2900,
        'currency' => 'USD',
    ]);

    $response->assertCreated();

    $paymentIntentId = $response->json('data.id');

    $this->postJson("/api/payment-intents/{$paymentIntentId}/confirm", [
        'card_number' => '4242424242424242',
    ])->assertSuccessful();

    $status = \App\Models\PaymentIntent::query()->findOrFail($paymentIntentId)->status->value;

    expect($status)->toBeIn(['processing', 'succeeded']);
});
