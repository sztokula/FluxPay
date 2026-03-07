<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns unauthorized for unauthenticated api access without login route errors', function () {
    $this->get('/api/customers')
        ->assertUnauthorized()
        ->assertHeader('X-Correlation-ID');

    $this->getJson('/api/customers')
        ->assertUnauthorized()
        ->assertJsonStructure(['message', 'correlation_id'])
        ->assertHeader('X-Correlation-ID');
});
