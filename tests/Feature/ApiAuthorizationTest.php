<?php

use App\Models\Customer;
use App\Models\EventLog;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\PaymentIntent;
use App\Models\Payout;
use App\Models\Price;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns 403 when accessing resources owned by another user', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $customer = Customer::factory()->for($owner)->create();
    $price = Price::factory()->create();
    $subscription = Subscription::factory()->create([
        'customer_id' => $customer->id,
        'price_id' => $price->id,
    ]);
    $invoice = Invoice::factory()->create([
        'customer_id' => $customer->id,
        'subscription_id' => $subscription->id,
    ]);
    $paymentIntent = PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
        'invoice_id' => $invoice->id,
    ]);
    $webhook = WebhookEndpoint::factory()->create(['user_id' => $owner->id]);
    $payout = Payout::factory()->create(['customer_id' => $customer->id]);
    $ledgerEntry = LedgerEntry::factory()->create(['customer_id' => $customer->id]);
    $eventLog = EventLog::factory()->create(['user_id' => $owner->id]);

    Sanctum::actingAs($intruder);

    $this->getJson("/api/customers/{$customer->id}")->assertForbidden();
    $this->getJson("/api/payment-intents/{$paymentIntent->id}")->assertForbidden();
    $this->getJson("/api/subscriptions/{$subscription->id}")->assertForbidden();
    $this->getJson("/api/invoices/{$invoice->id}")->assertForbidden();
    $this->getJson("/api/webhooks/{$webhook->id}")->assertForbidden();
    $this->getJson("/api/payouts/{$payout->id}")->assertForbidden();
    $this->getJson("/api/ledger-entries/{$ledgerEntry->id}")->assertForbidden();
    $this->getJson("/api/event-logs/{$eventLog->id}")->assertForbidden();
});
