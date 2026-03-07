<?php

use App\Actions\CreatePaymentIntentAction;
use App\Models\Customer;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('creates webhook delivery only for endpoint owner of the aggregate', function () {
    Queue::fake();

    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $ownerCustomer = Customer::factory()->for($owner)->create();

    $ownerEndpoint = WebhookEndpoint::factory()->create([
        'user_id' => $owner->id,
        'events' => ['payment_intent.created'],
        'is_active' => true,
    ]);

    WebhookEndpoint::factory()->create([
        'user_id' => $otherUser->id,
        'events' => ['payment_intent.created'],
        'is_active' => true,
    ]);

    app(CreatePaymentIntentAction::class)->execute([
        'customer_id' => $ownerCustomer->id,
        'amount' => 2900,
        'currency' => 'USD',
    ]);

    expect(WebhookDelivery::query()->count())->toBe(1);
    expect(WebhookDelivery::query()->firstOrFail()->webhook_endpoint_id)->toBe($ownerEndpoint->id);
});
