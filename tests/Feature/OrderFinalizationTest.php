<?php

use App\Enums\PaymentIntentStatus;
use App\Jobs\ProcessPaymentIntent;
use App\Models\Customer;
use App\Models\PaymentIntent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('finalizes storefront order after successful payment intent processing', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->for($user)->create([
        'email' => 'demo@local.test',
    ]);
    $product = Product::factory()->create();

    $paymentIntent = PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
        'status' => PaymentIntentStatus::Processing,
        'invoice_id' => null,
        'metadata' => ['product_id' => $product->id],
    ]);

    (new ProcessPaymentIntent($paymentIntent->id, '4242424242424242'))->handle(
        app(\App\Services\FraudScoringService::class),
        app(\App\Services\PaymentSimulator::class),
        app(\App\Services\PaymentRetryPolicy::class),
        app(\App\Services\PaymentIntentStateMachine::class),
        app(\App\Services\LedgerService::class),
        app(\App\Services\WebhookService::class),
    );

    $this->assertDatabaseHas('orders', [
        'customer_id' => $customer->id,
        'payment_intent_id' => $paymentIntent->id,
        'product_id' => $product->id,
        'status' => 'paid',
    ]);

    $this->assertDatabaseHas('event_logs', [
        'user_id' => $user->id,
        'event_name' => 'order.finalized',
        'aggregate_type' => 'order',
    ]);

    $this->actingAs($user);

    $this->get('/dashboard/events')
        ->assertSuccessful()
        ->assertSee('order.finalized');
});
