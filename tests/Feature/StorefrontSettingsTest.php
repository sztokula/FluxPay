<?php

use App\Enums\PaymentIntentStatus;
use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\PaymentIntent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders settings page', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/dashboard/settings')
        ->assertSuccessful()
        ->assertSee('Settings & Functions', false);
});

it('saves storefront settings', function () {
    $this->actingAs(User::factory()->create());

    $this->post('/dashboard/settings', [
        'project_name' => 'Platform Payments',
        'support_email' => 'support@example.test',
        'default_currency' => 'usd',
        'default_timezone' => 'Europe/Warsaw',
        'allow_guest_checkout' => '1',
        'auto_finalize_orders' => '1',
        'enable_processing_watchdog' => '1',
        'high_value_review_threshold' => 125000,
        'max_retry_attempts' => 2,
        'api_rate_limit_per_minute' => 300,
        'payment_live_poll_interval_ms' => 1500,
        'checkout_default_test_card' => '4242424242424242',
        'maintenance_banner_enabled' => '1',
        'maintenance_message' => 'Maintenance for environment upgrade.',
    ])->assertRedirect('/dashboard/settings');

    expect(AppSetting::query()->where('key', 'project_name')->exists())->toBeTrue();
    expect(data_get(AppSetting::query()->where('key', 'default_currency')->first()?->value, 'value'))->toBe('USD');
    expect(data_get(AppSetting::query()->where('key', 'max_retry_attempts')->first()?->value, 'value'))->toBe(2);
    expect(data_get(AppSetting::query()->where('key', 'maintenance_banner_enabled')->first()?->value, 'value'))->toBeTrue();
});

it('runs demo success action and creates succeeded payment intent', function () {
    $this->actingAs(User::factory()->create());

    $this->post('/dashboard/settings/actions', [
        'action' => 'demo_success',
    ])->assertRedirect('/dashboard/settings');

    $intent = PaymentIntent::query()->latest('id')->first();

    expect($intent)->not->toBeNull();
    expect($intent->status)->toBe(PaymentIntentStatus::Succeeded);
});

it('runs demo pack action and creates three intents', function () {
    $this->actingAs(User::factory()->create());

    $this->post('/dashboard/settings/actions', [
        'action' => 'demo_pack',
    ])->assertRedirect('/dashboard/settings');

    expect(PaymentIntent::query()->count())->toBe(3);
});

it('renders maintenance banner and default test card from settings', function () {
    $this->actingAs(User::factory()->create());

    AppSetting::query()->create(['key' => 'maintenance_banner_enabled', 'value' => ['value' => true]]);
    AppSetting::query()->create(['key' => 'maintenance_message', 'value' => ['value' => 'Maintenance banner']]);
    AppSetting::query()->create(['key' => 'checkout_default_test_card', 'value' => ['value' => '4000000000000002']]);

    $this->get('/')->assertSee('Maintenance banner');

    $customer = Customer::factory()->create([
        'email' => 'demo@local.test',
    ]);
    $product = Product::factory()->create();
    $intent = PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
        'amount' => $product->price,
        'status' => PaymentIntentStatus::RequiresConfirmation,
    ]);

    $this->get('/payment/'.$intent->id)
        ->assertSuccessful()
        ->assertSee('4000000000000002');
});

it('redirects guest from settings pages to login', function () {
    $this->get('/dashboard/settings')->assertRedirect('/login');
    $this->post('/dashboard/settings', [])->assertRedirect('/login');
});
