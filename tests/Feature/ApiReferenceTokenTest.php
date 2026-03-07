<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

it('renders api reference and manages api tokens', function () {
    $user = User::factory()->create([
        'email' => 'demo@local.test',
    ]);
    $this->actingAs($user);

    $this->get('/api-reference')
        ->assertSuccessful()
        ->assertSee('API Reference')
        ->assertSee('Logout')
        ->assertDontSee('Login');

    $this->post('/api-reference/tokens', [
        'token_name' => 'test-token',
    ])->assertRedirect(route('api.reference'));

    expect(PersonalAccessToken::query()->where('tokenable_id', $user->id)->count())->toBe(1);

    $tokenId = PersonalAccessToken::query()
        ->where('tokenable_id', $user->id)
        ->value('id');

    $this->delete('/api-reference/tokens', [
        'token_id' => $tokenId,
    ])->assertRedirect(route('api.reference'));

    expect(PersonalAccessToken::query()->where('tokenable_id', $user->id)->count())->toBe(0);
});

it('redirects guests from api reference to login', function () {
    $this->get('/api-reference')->assertRedirect('/login');
    $this->post('/api-reference/tokens', [])->assertRedirect('/login');
});
