<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('rejects unsupported webhook events', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/webhooks', [
        'url' => 'https://example.com/webhook',
        'events' => ['unknown.event'],
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['events.0']);
});

it('accepts supported webhook events', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/webhooks', [
        'url' => 'https://example.com/webhook',
        'events' => ['payment_intent.succeeded', 'invoice.paid'],
    ]);

    $response->assertCreated();
    expect($response->json('data.events'))->toBe(['payment_intent.succeeded', 'invoice.paid']);
});
