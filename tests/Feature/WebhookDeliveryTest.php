<?php

use App\Jobs\DeliverWebhookEvent;
use App\Models\EventLog;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('sends webhook signatures and stores retry metadata', function () {
    Queue::fake();

    Http::fake([
        '*' => Http::response([], 500),
    ]);

    $endpoint = WebhookEndpoint::factory()->create([
        'user_id' => User::factory()->create()->id,
        'events' => ['payment_intent.succeeded'],
    ]);

    $delivery = WebhookDelivery::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'event_name' => 'payment_intent.succeeded',
        'payload' => ['id' => 123, 'status' => 'succeeded'],
        'attempt' => 1,
    ]);

    (new DeliverWebhookEvent($delivery->id))->handle();

    Http::assertSent(function ($request) {
        return $request->hasHeader('X-Webhook-Signature')
            && $request->hasHeader('X-Event-Name');
    });

    Queue::assertPushed(DeliverWebhookEvent::class);

    $delivery->refresh();

    expect($delivery->attempt)->toBe(2)
        ->and($delivery->last_error)->toBe('non_2xx_response');

    $eventLog = EventLog::query()
        ->where('event_name', 'webhook.delivery.retry_scheduled')
        ->where('aggregate_type', 'webhook_delivery')
        ->where('aggregate_id', $delivery->id)
        ->first();

    expect($eventLog)->not->toBeNull()
        ->and($eventLog->user_id)->toBe($endpoint->user_id);
});

it('stores observability event for successful webhook delivery', function () {
    Http::fake([
        '*' => Http::response([], 200),
    ]);

    $endpoint = WebhookEndpoint::factory()->create([
        'user_id' => User::factory()->create()->id,
        'events' => ['payment_intent.succeeded'],
    ]);

    $delivery = WebhookDelivery::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'event_name' => 'payment_intent.succeeded',
        'payload' => ['id' => 123, 'status' => 'succeeded'],
        'attempt' => 1,
    ]);

    (new DeliverWebhookEvent($delivery->id))->handle();

    $delivery->refresh();

    expect($delivery->delivered_at)->not->toBeNull();

    $eventLog = EventLog::query()
        ->where('event_name', 'webhook.delivery.succeeded')
        ->where('aggregate_type', 'webhook_delivery')
        ->where('aggregate_id', $delivery->id)
        ->first();

    expect($eventLog)->not->toBeNull()
        ->and($eventLog->user_id)->toBe($endpoint->user_id);
});
