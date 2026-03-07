<?php

use App\Jobs\RenewSubscription;
use App\Models\Customer;
use App\Models\Price;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('dispatches renew jobs for due subscriptions from scheduler', function () {
    Queue::fake();

    $customer = Customer::factory()->for(User::factory())->create();
    $price = Price::factory()->create();

    Subscription::factory()->create([
        'customer_id' => $customer->id,
        'price_id' => $price->id,
        'current_period_end' => now()->subMinute(),
    ]);

    $this->artisan('schedule:run')->assertSuccessful();

    Queue::assertPushed(RenewSubscription::class);
});
