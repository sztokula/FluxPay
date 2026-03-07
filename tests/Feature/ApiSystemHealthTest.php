<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns system health payload for authenticated api user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/system/health')
        ->assertSuccessful()
        ->assertJsonStructure([
            'queue' => ['jobs_queued', 'jobs_failed'],
            'payments' => ['processing_total'],
            'heartbeats' => [
                'worker_last_job_at',
                'scheduler_last_tick_at',
                'watchdog_last_run_at',
                'worker_recent',
                'scheduler_recent',
                'watchdog_recent',
            ],
            'correlation_id',
        ]);
});
