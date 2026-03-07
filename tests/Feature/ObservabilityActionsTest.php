<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('stores event logs for customer lifecycle actions', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $createResponse = $this->postJson('/api/customers', [
        'email' => 'new-customer@example.test',
        'name' => 'New Customer',
    ]);

    $createResponse->assertSuccessful();
    $customerId = $createResponse->json('data.id');

    $this->assertDatabaseHas('event_logs', [
        'user_id' => $user->id,
        'event_name' => 'customer.created',
        'aggregate_type' => 'customer',
        'aggregate_id' => $customerId,
    ]);

    $this->putJson("/api/customers/{$customerId}", [
        'email' => 'new-customer@example.test',
        'name' => 'Updated Name',
    ])->assertSuccessful();

    $this->assertDatabaseHas('event_logs', [
        'user_id' => $user->id,
        'event_name' => 'customer.updated',
        'aggregate_type' => 'customer',
        'aggregate_id' => $customerId,
    ]);

    $this->deleteJson("/api/customers/{$customerId}")->assertNoContent();

    $this->assertDatabaseHas('event_logs', [
        'user_id' => $user->id,
        'event_name' => 'customer.deleted',
        'aggregate_type' => 'customer',
        'aggregate_id' => $customerId,
    ]);
});

it('stores event logs for webhook endpoint lifecycle actions', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $createResponse = $this->postJson('/api/webhooks', [
        'url' => 'https://example.com/webhook',
        'events' => ['payment_intent.succeeded'],
    ]);

    $createResponse->assertCreated();
    $webhookId = $createResponse->json('data.id');

    $this->assertDatabaseHas('event_logs', [
        'user_id' => $user->id,
        'event_name' => 'webhook_endpoint.created',
        'aggregate_type' => 'webhook_endpoint',
        'aggregate_id' => $webhookId,
    ]);

    $this->putJson("/api/webhooks/{$webhookId}", [
        'url' => 'https://example.com/webhook-v2',
        'events' => ['invoice.paid'],
        'is_active' => true,
    ])->assertSuccessful();

    $this->assertDatabaseHas('event_logs', [
        'user_id' => $user->id,
        'event_name' => 'webhook_endpoint.updated',
        'aggregate_type' => 'webhook_endpoint',
        'aggregate_id' => $webhookId,
    ]);

    $this->deleteJson("/api/webhooks/{$webhookId}")->assertNoContent();

    $this->assertDatabaseHas('event_logs', [
        'user_id' => $user->id,
        'event_name' => 'webhook_endpoint.deleted',
        'aggregate_type' => 'webhook_endpoint',
        'aggregate_id' => $webhookId,
    ]);
});
