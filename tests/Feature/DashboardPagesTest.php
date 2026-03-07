<?php

use App\Models\Customer;
use App\Models\EventLog;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\PaymentIntent;
use App\Models\Plan;
use App\Models\Payout;
use App\Models\Price;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders dashboard pages', function () {
    $this->actingAs(User::factory()->create());

    $customer = Customer::factory()->create([
        'email' => 'demo@local.test',
        'name' => 'Demo Customer',
    ]);

    $subscription = Subscription::factory()->create([
        'customer_id' => $customer->id,
    ]);

    $invoice = Invoice::factory()->create([
        'customer_id' => $customer->id,
        'subscription_id' => $subscription->id,
    ]);

    $paymentIntent = PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
        'invoice_id' => $invoice->id,
    ]);

    Payout::factory()->create([
        'customer_id' => $customer->id,
    ]);

    LedgerEntry::factory()->create([
        'customer_id' => $customer->id,
    ]);

    $endpoint = WebhookEndpoint::factory()->create();
    WebhookDelivery::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
    ]);

    EventLog::factory()->create([
        'user_id' => null,
        'aggregate_type' => 'payment_intent',
        'aggregate_id' => $paymentIntent->id,
    ]);

    $plan = Plan::factory()->create();
    Price::factory()->create(['plan_id' => $plan->id]);

    $this->get('/dashboard')->assertSuccessful()->assertSee('Operations Overview');
    $this->get('/dashboard/payments')->assertSuccessful()->assertSee('Payments');
    $this->get('/dashboard/subscriptions')->assertSuccessful()->assertSee('Subscriptions');
    $this->get('/dashboard/invoices')->assertSuccessful()->assertSee('Invoices');
    $this->get('/dashboard/payouts')->assertSuccessful()->assertSee('Payouts');
    $this->get('/dashboard/events')->assertSuccessful()->assertSee('Event Log');
    $this->get('/dashboard/customers')->assertSuccessful()->assertSee('Customers');
    $this->get('/dashboard/ledger')->assertSuccessful()->assertSee('Ledger');
    $this->get('/dashboard/webhooks')->assertSuccessful()->assertSee('Webhooks');
    $this->get('/dashboard/fraud')->assertSuccessful()->assertSee('Fraud Cases');
    $this->get('/dashboard/plans')->assertSuccessful()->assertSee('Plans & Prices', false);
    $this->get('/dashboard/orders')->assertSuccessful()->assertSee('Orders');
    $this->get('/dashboard/system')->assertSuccessful()->assertSee('System Observability');
    $this->get('/dashboard/settings')->assertSuccessful()->assertSee('Settings & Functions', false);
});

it('redirects guests from dashboard pages to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/dashboard/settings')->assertRedirect('/login');
});
