<?php

use App\Jobs\ProcessPaymentIntent;
use App\Jobs\RetryPaymentIntent;
use App\Models\Customer;
use App\Models\PaymentIntent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('schedules retry for temporary payment failures', function () {
    Queue::fake();

    $customer = Customer::factory()->for(User::factory())->create();

    $intent = PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
        'status' => \App\Enums\PaymentIntentStatus::Processing,
        'retry_count' => 0,
    ]);

    $job = new ProcessPaymentIntent($intent->id, '4000000000009995');
    $job->handle(
        app(\App\Services\FraudScoringService::class),
        app(\App\Services\PaymentSimulator::class),
        app(\App\Services\PaymentRetryPolicy::class),
        app(\App\Services\PaymentIntentStateMachine::class),
        app(\App\Services\LedgerService::class),
        app(\App\Services\WebhookService::class),
    );

    Queue::assertPushed(RetryPaymentIntent::class);

    $this->assertDatabaseHas('event_logs', [
        'user_id' => $customer->user_id,
        'event_name' => 'payment_intent.retry_scheduled',
        'aggregate_type' => 'payment_intent',
        'aggregate_id' => $intent->id,
    ]);
});

it('logs requires action state for card authentication flow', function () {
    $customer = Customer::factory()->for(User::factory())->create();

    $intent = PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
        'status' => \App\Enums\PaymentIntentStatus::Processing,
    ]);

    $job = new ProcessPaymentIntent($intent->id, '4000000000003063');
    $job->handle(
        app(\App\Services\FraudScoringService::class),
        app(\App\Services\PaymentSimulator::class),
        app(\App\Services\PaymentRetryPolicy::class),
        app(\App\Services\PaymentIntentStateMachine::class),
        app(\App\Services\LedgerService::class),
        app(\App\Services\WebhookService::class),
    );

    $intent->refresh();

    expect($intent->status->value)->toBe('requires_action');

    $this->assertDatabaseHas('event_logs', [
        'user_id' => $customer->user_id,
        'event_name' => 'payment_intent.requires_action',
        'aggregate_type' => 'payment_intent',
        'aggregate_id' => $intent->id,
    ]);
});
