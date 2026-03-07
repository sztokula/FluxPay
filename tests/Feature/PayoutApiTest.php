<?php

use App\Enums\LedgerEntryType;
use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('creates payout only when balance is sufficient', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $customer = Customer::factory()->for($user)->create();

    $insufficient = $this->postJson('/api/payouts', [
        'customer_id' => $customer->id,
        'amount' => 5000,
        'currency' => 'USD',
    ]);

    $insufficient->assertStatus(422);
    $this->assertDatabaseHas('event_logs', [
        'user_id' => $user->id,
        'event_name' => 'payout.failed',
    ]);

    LedgerEntry::query()->create([
        'customer_id' => $customer->id,
        'type' => LedgerEntryType::Charge,
        'reference_type' => 'payment_intent',
        'reference_id' => 1,
        'currency' => 'USD',
        'amount' => 7000,
        'direction' => 'credit',
    ]);

    $success = $this->postJson('/api/payouts', [
        'customer_id' => $customer->id,
        'amount' => 3000,
        'currency' => 'USD',
    ]);

    $success->assertCreated();
    $this->assertDatabaseHas('event_logs', [
        'user_id' => $user->id,
        'event_name' => 'payout.paid',
    ]);
});
