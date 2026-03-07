<?php

use App\Enums\PaymentIntentStatus;
use App\Models\PaymentIntent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

it('marks stale processing payment intents as failed', function () {
    $intent = PaymentIntent::factory()->create([
        'status' => PaymentIntentStatus::Processing,
        'confirmed_at' => now()->subMinutes(10),
        'next_retry_at' => null,
        'retry_count' => 0,
    ]);

    $this->artisan('payment-intents:mark-stale-processing')
        ->assertSuccessful();

    $intent->refresh();

    expect($intent->status)->toBe(PaymentIntentStatus::Failed)
        ->and($intent->failure_code)->toBe('processing_timeout');
    expect(Cache::get('system.watchdog_last_run_at'))->not->toBeNull();
});
