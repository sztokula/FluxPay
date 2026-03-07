<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ApiReferenceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectDocsController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\StorefrontDashboardController;
use App\Http\Controllers\StorefrontSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/products', [StorefrontController::class, 'products'])->name('products.index');
Route::get('/product/{id}', [StorefrontController::class, 'showProduct'])->name('products.show');
Route::get('/documentation', [ProjectDocsController::class, 'documentation'])->name('docs.documentation');
Route::get('/changelog', [ProjectDocsController::class, 'changelog'])->name('docs.changelog');
Route::get('/what-i-learned', [ProjectDocsController::class, 'lessonsLearned'])->name('docs.lessons');
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login.form');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('auth.login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register.form');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1')->name('auth.register');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/api-reference', [ApiReferenceController::class, 'show'])->name('api.reference');
    Route::post('/api-reference/tokens', [ApiReferenceController::class, 'createToken'])->middleware('throttle:15,1')->name('api.tokens.create');
    Route::delete('/api-reference/tokens', [ApiReferenceController::class, 'revokeToken'])->middleware('throttle:30,1')->name('api.tokens.revoke');
    Route::get('/dashboard', [StorefrontDashboardController::class, 'overview'])->name('dashboard.overview');
    Route::get('/dashboard/payments', [StorefrontDashboardController::class, 'payments'])->name('dashboard.payments');
    Route::get('/dashboard/subscriptions', [StorefrontDashboardController::class, 'subscriptions'])->name('dashboard.subscriptions');
    Route::get('/dashboard/invoices', [StorefrontDashboardController::class, 'invoices'])->name('dashboard.invoices');
    Route::get('/dashboard/payouts', [StorefrontDashboardController::class, 'payouts'])->name('dashboard.payouts');
    Route::get('/dashboard/events', [StorefrontDashboardController::class, 'events'])->name('dashboard.events');
    Route::get('/dashboard/customers', [StorefrontDashboardController::class, 'customers'])->name('dashboard.customers');
    Route::get('/dashboard/ledger', [StorefrontDashboardController::class, 'ledger'])->name('dashboard.ledger');
    Route::get('/dashboard/webhooks', [StorefrontDashboardController::class, 'webhooks'])->name('dashboard.webhooks');
    Route::get('/dashboard/fraud', [StorefrontDashboardController::class, 'fraud'])->name('dashboard.fraud');
    Route::get('/dashboard/plans', [StorefrontDashboardController::class, 'plans'])->name('dashboard.plans');
    Route::get('/dashboard/orders', [StorefrontDashboardController::class, 'orders'])->name('dashboard.orders');
    Route::get('/dashboard/system', [StorefrontDashboardController::class, 'system'])->name('dashboard.system');
    Route::get('/dashboard/settings', [StorefrontSettingsController::class, 'show'])->name('dashboard.settings');
    Route::post('/dashboard/settings', [StorefrontSettingsController::class, 'update'])->name('dashboard.settings.update');
    Route::post('/dashboard/settings/actions', [StorefrontSettingsController::class, 'runAction'])->name('dashboard.settings.actions');
});

Route::get('/checkout/{product}', [CheckoutController::class, 'checkout'])->name('checkout.show');
Route::get('/payment/success', [CheckoutController::class, 'success'])->name('payment.success');
Route::get('/payment/failed', [CheckoutController::class, 'failed'])->name('payment.failed');
Route::get('/payment/{intent}', [CheckoutController::class, 'payment'])->name('payment.show');
Route::get('/payment/{intent}/status', [CheckoutController::class, 'status'])->name('payment.status');
Route::post('/payment/{intent}/confirm', [CheckoutController::class, 'confirm'])->name('payment.confirm');
