<?php

use App\Enums\FraudDecision;
use App\Enums\PaymentIntentStatus;
use App\Models\Customer;
use App\Models\PaymentIntent;
use App\Models\User;
use App\Services\FraudScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('marks customer for review when there are too many attempts in a short window', function () {
    $customer = Customer::factory()->for(User::factory())->create();

    PaymentIntent::factory()->count(6)->create([
        'customer_id' => $customer->id,
        'status' => PaymentIntentStatus::Failed,
        'created_at' => now()->subMinutes(5),
        'updated_at' => now()->subMinutes(5),
    ]);

    $decision = app(FraudScoringService::class)->score(
        customer: $customer,
        amount: 1900,
        cardNumber: '4242424242424242'
    );

    expect($decision)->toBe(FraudDecision::Review);
});

it('does not trigger short-window review for old attempts', function () {
    $customer = Customer::factory()->for(User::factory())->create();

    PaymentIntent::factory()->count(6)->create([
        'customer_id' => $customer->id,
        'status' => PaymentIntentStatus::Succeeded,
        'created_at' => now()->subMinutes(40),
        'updated_at' => now()->subMinutes(40),
    ]);

    $decision = app(FraudScoringService::class)->score(
        customer: $customer,
        amount: 1900,
        cardNumber: '4242424242424242'
    );

    expect($decision)->toBe(FraudDecision::Allow);
});
