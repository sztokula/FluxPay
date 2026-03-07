<?php

use App\Models\Customer;
use App\Models\EventLog;
use App\Models\LedgerEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('lists only authenticated user ledger entries and event logs', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $ownerCustomer = Customer::factory()->for($owner)->create();
    $otherCustomer = Customer::factory()->for($otherUser)->create();

    $ownerLedger = LedgerEntry::factory()->create(['customer_id' => $ownerCustomer->id]);
    LedgerEntry::factory()->create(['customer_id' => $otherCustomer->id]);

    $ownerEvent = EventLog::factory()->create(['user_id' => $owner->id]);
    EventLog::factory()->create(['user_id' => $otherUser->id]);

    Sanctum::actingAs($owner);

    $ledgerResponse = $this->getJson('/api/ledger-entries');
    $eventLogsResponse = $this->getJson('/api/event-logs');

    $ledgerResponse->assertSuccessful();
    $eventLogsResponse->assertSuccessful();

    expect($ledgerResponse->json('data'))->toHaveCount(1);
    expect($ledgerResponse->json('data.0.id'))->toBe($ownerLedger->id);

    expect($eventLogsResponse->json('data'))->toHaveCount(1);
    expect($eventLogsResponse->json('data.0.id'))->toBe($ownerEvent->id);
});

it('forbids access to another users ledger entry and event log', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $ownerCustomer = Customer::factory()->for($owner)->create();
    $ownerLedger = LedgerEntry::factory()->create(['customer_id' => $ownerCustomer->id]);
    $ownerEvent = EventLog::factory()->create(['user_id' => $owner->id]);

    Sanctum::actingAs($intruder);

    $this->getJson("/api/ledger-entries/{$ownerLedger->id}")->assertForbidden();
    $this->getJson("/api/event-logs/{$ownerEvent->id}")->assertForbidden();
});
