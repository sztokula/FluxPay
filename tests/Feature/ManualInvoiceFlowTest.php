<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates manual invoice and payment intent', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $customer = Customer::factory()->for($user)->create();

    $response = $this->postJson('/api/invoices', [
        'customer_id' => $customer->id,
        'amount_due' => 4500,
        'currency' => 'USD',
    ]);

    $response->assertCreated();

    $invoiceId = $response->json('data.id');
    $paymentIntentId = $response->json('meta.payment_intent_id');

    expect($paymentIntentId)->not->toBeNull();

    $this->assertDatabaseHas('invoices', ['id' => $invoiceId]);
    $this->assertDatabaseHas('payment_intents', ['id' => $paymentIntentId, 'invoice_id' => $invoiceId]);

    $invoice = Invoice::query()->findOrFail($invoiceId);
    expect($invoice->amount_due)->toBe(4500);
});
