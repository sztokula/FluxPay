<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers logs in and logs out user', function () {
    $this->post('/register', [
        'name' => 'Platform User',
        'email' => 'user@example.test',
        'password' => 'secretpass',
        'password_confirmation' => 'secretpass',
    ])->assertRedirect('/dashboard');

    expect(User::query()->where('email', 'user@example.test')->exists())->toBeTrue();
    $this->assertAuthenticated();

    $this->post('/logout')
        ->assertRedirect('/');

    $this->assertGuest();

    User::query()->create([
        'name' => 'Login User',
        'email' => 'login@example.test',
        'password' => bcrypt('secretpass'),
    ]);

    $this->post('/login', [
        'email' => 'login@example.test',
        'password' => 'secretpass',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticated();
});

it('redirects to intended dashboard page after login', function () {
    User::factory()->create([
        'email' => 'intended@example.test',
        'password' => bcrypt('secretpass'),
    ]);

    $this->get('/dashboard/system')->assertRedirect('/login');

    $this->post('/login', [
        'email' => 'intended@example.test',
        'password' => 'secretpass',
    ])->assertRedirect('/dashboard/system');
});

it('redirects to intended protected page after register', function () {
    $this->get('/api-reference')->assertRedirect('/login');

    $this->post('/register', [
        'name' => 'New Intended User',
        'email' => 'new-intended@example.test',
        'password' => 'secretpass',
        'password_confirmation' => 'secretpass',
    ])->assertRedirect('/api-reference');
});

it('throttles repeated failed login attempts', function () {
    User::factory()->create([
        'email' => 'throttle@example.test',
        'password' => bcrypt('secretpass'),
    ]);

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->post('/login', [
            'email' => 'throttle@example.test',
            'password' => 'wrong-password',
        ])->assertRedirect();
    }

    $this->post('/login', [
        'email' => 'throttle@example.test',
        'password' => 'wrong-password',
    ])->assertTooManyRequests();
});
