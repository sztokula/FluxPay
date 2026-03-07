<?php

use App\Enums\PaymentIntentStatus;
use App\Enums\SubscriptionStatus;
use App\Jobs\ProcessPaymentIntent;
use App\Jobs\RetryPaymentIntent;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PaymentIntent;
use App\Models\Price;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('marks subscription as past_due on temporary failure', function () {
    Queue::fake();

    $customer = Customer::factory()->for(User::factory())->create();
    $price = Price::factory()->create();

    $subscription = Subscription::factory()->create([
        'customer_id' => $customer->id,
        'price_id' => $price->id,
        'status' => SubscriptionStatus::Active,
    ]);

    $invoice = Invoice::factory()->create([
        'customer_id' => $customer->id,
        'subscription_id' => $subscription->id,
    ]);

    $intent = PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
        'invoice_id' => $invoice->id,
        'status' => PaymentIntentStatus::Processing,
        'retry_count' => 0,
    ]);

    (new ProcessPaymentIntent($intent->id, '4000000000009995'))->handle(
        app(\App\Services\FraudScoringService::class),
        app(\App\Services\PaymentSimulator::class),
        app(\App\Services\PaymentRetryPolicy::class),
        app(\App\Services\PaymentIntentStateMachine::class),
        app(\App\Services\LedgerService::class),
        app(\App\Services\WebhookService::class),
    );

    Queue::assertPushed(RetryPaymentIntent::class);
    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::PastDue);
});

it('marks subscription as unpaid after max retries exhausted', function () {
    $customer = Customer::factory()->for(User::factory())->create();
    $price = Price::factory()->create();

    $subscription = Subscription::factory()->create([
        'customer_id' => $customer->id,
        'price_id' => $price->id,
        'status' => SubscriptionStatus::PastDue,
    ]);

    $invoice = Invoice::factory()->create([
        'customer_id' => $customer->id,
        'subscription_id' => $subscription->id,
    ]);

    $intent = PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
        'invoice_id' => $invoice->id,
        'status' => PaymentIntentStatus::Processing,
        'retry_count' => 4,
    ]);

    (new ProcessPaymentIntent($intent->id, '4000000000009995'))->handle(
        app(\App\Services\FraudScoringService::class),
        app(\App\Services\PaymentSimulator::class),
        app(\App\Services\PaymentRetryPolicy::class),
        app(\App\Services\PaymentIntentStateMachine::class),
        app(\App\Services\LedgerService::class),
        app(\App\Services\WebhookService::class),
    );

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Unpaid)
        ->and($intent->fresh()->status)->toBe(PaymentIntentStatus::Failed);
});
