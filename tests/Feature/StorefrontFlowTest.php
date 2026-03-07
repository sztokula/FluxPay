<?php

use App\Models\Product;
use App\Models\Customer;
use App\Models\PaymentIntent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders storefront pages', function () {
    $product = Product::factory()->create();

    $this->get('/')->assertSuccessful();
    $this->get('/products')->assertSuccessful();
    $this->get("/product/{$product->id}")->assertSuccessful();
    $this->get("/checkout/{$product->id}")->assertSuccessful();
});

it('returns live payment status payload for storefront polling', function () {
    $customer = Customer::factory()->create();
    $intent = PaymentIntent::factory()->create([
        'customer_id' => $customer->id,
    ]);

    $this->getJson("/payment/{$intent->id}/status")
        ->assertSuccessful()
        ->assertJson([
            'id' => $intent->id,
            'status' => $intent->status->value,
        ]);
});
