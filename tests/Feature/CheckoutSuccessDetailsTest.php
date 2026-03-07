<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentIntent;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows finalized order details on success page for a payment intent', function () {
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['name' => 'Developer T-Shirt']);
    $intent = PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
    ]);

    Order::factory()->create([
        'customer_id' => $customer->id,
        'payment_intent_id' => $intent->id,
        'product_id' => $product->id,
        'amount' => $intent->amount,
        'currency' => $intent->currency,
        'status' => 'paid',
    ]);

    $this->get('/payment/success?intent='.$intent->id)
        ->assertSuccessful()
        ->assertSeeText('Payment intent:')
        ->assertSeeText('#'.$intent->id)
        ->assertSeeText('Order:')
        ->assertSeeText('Developer T-Shirt');
});

it('shows failure details on failed page for a payment intent', function () {
    $customer = Customer::factory()->create();
    $intent = PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
        'failure_code' => 'insufficient_funds',
        'failure_message' => 'Insufficient funds.',
    ]);

    $this->get('/payment/failed?intent='.$intent->id)
        ->assertSuccessful()
        ->assertSeeText('Failure code:')
        ->assertSeeText('insufficient_funds');
});
